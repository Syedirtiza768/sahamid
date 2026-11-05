<?php

namespace SAHamid\BI\Directors;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Security\AuthorizationContext;

/** Every visual, drill-through and export uses the same authorized fact query. */
class DirectorsService
{
    private $db;
    private $context;
    private $authorization;
    private $catalog;
    private $currency;
    private $runner;
    private $started;
    private $rootPath;
    private $companyRecord;
    private $resolvedDefinitions = array();

    /** Screening thresholds for the "Needs attention" list. Rules, not statistical tests. */
    const MATERIAL_CHANGE_PERCENT = 20;
    const OVERDUE_SHARE_PERCENT = 30;
    const CONCENTRATION_SHARE_PERCENT = 25;
    const STUCK_DC_DAYS = 90;
    /** 1 = a rise is favourable, -1 = a rise is unfavourable, 0 = neither. */
    private static $polarity = array('gl_revenue' => 1, 'gl_result' => 1, 'gl_cost_of_sales' => -1, 'gl_expenses' => -1,
        'petty_cash' => -1, 'posted_invoices' => 1, 'shop_sales' => 1, 'invoice_value' => 1, 'po_received' => 1,
        'cash_movement' => 1, 'sales_revenue' => 1, 'gross_margin_pct' => 1, 'dso_days' => -1, 'customer_concentration_pct' => -1,
        'supplier_concentration_pct' => -1, 'order_to_invoice_days' => -1, 'bank_balance' => 1);

    public function __construct($db, AuthorizationContext $context, array $session, $currency = 'PKR', ?callable $runner = null, $rootPath = '/sahamid/')
    {
        $this->db = $db;
        $this->context = $context;
        $this->authorization = new DirectorsAuthorization($context, $session);
        $this->authorization->assertAccess();
        $this->catalog = new DatasetCatalog($context);
        $this->currency = $currency;
        $this->runner = $runner;
        $this->started = microtime(true);
        $this->rootPath = '/' . (trim($rootPath, '/') === '' ? '' : trim($rootPath, '/') . '/');
        $this->companyRecord = isset($session['CompanyRecord']) ? $session['CompanyRecord'] : array();
    }

    public function overview(DirectorsRequest $request)
    {
        if ($request->filters || $request->search !== '') {
            throw new BIException('invalid_filters', 'Open a reporting area to apply its supported dimensions or search.', 400);
        }
        $metrics = array();
        $risks = array();
        foreach ($this->catalog->all() as $id => $definition) {
            if (!$this->authorization->canRead($definition)) {
                $metrics[] = $this->metric($definition, null, null, 'Your role does not have access to this source report.');
                continue;
            }
            try {
                $definition = $this->resolveDefinition($definition);
                $pair = $this->summaryPair($definition, $request, $this->canCompare($definition, $request));
                $current = $pair['current'];
                $previous = $pair['previous'];
                if ($id === 'cash_movement' && $current['rows'] === 0 && !$this->hasBankAccess()) {
                    // No assigned bank accounts is not "no cash movement": never show a reliable-looking zero.
                    $metrics[] = $this->metric($definition, null, null, 'No bank accounts are assigned to your user, so bank movement cannot be shown.');
                    continue;
                }
                $metric = $this->metric($definition, $current, $previous);
                if ($definition['kind'] === 'flow' && $pair['undated'] > 0) {
                    $risks[] = array('title' => $definition['title'] . ': missing business dates', 'message' => $pair['undated'] . ' source rows across all dates cannot be assigned to a reporting period and are omitted from dated totals.', 'dataset' => $id);
                }
                if ($current['invalid_amounts'] > 0) {
                    $risks[] = array('title' => $definition['title'] . ': incomplete value', 'message' => $current['invalid_amounts'] . ' source rows have invalid amounts or ambiguous joins and are excluded from the total.', 'dataset' => $id, 'group_by' => 'status');
                }
                if ($current['overdue'] > 0) {
                    $risks[] = array('title' => $definition['title'] . ': overdue exposure', 'message' => 'Positive overdue exposure ' . number_format($current['overdue'], 2) . ' ' . $this->unit($definition) . '. Open aging to investigate.', 'dataset' => $id, 'group_by' => 'age_bucket', 'filters' => array('due_status' => 'overdue'));
                }
                if ($current['quality_issues'] > 0 || $current['unknown_due'] > 0) {
                    $risks[] = array('title' => $definition['title'] . ': records to review', 'message' => $current['quality_issues'] . ' quality exceptions; ' . $current['unknown_due'] . ' balances with unknown due dates.', 'dataset' => $id, 'group_by' => 'status');
                }
                if ($metric['percent'] !== null && abs($metric['percent']) >= 20) {
                    $risks[] = array('title' => $definition['title'] . ': material movement', 'message' => number_format($metric['percent'], 1) . '% versus the comparison period. Review contributors; this threshold is a screening rule, not a statistical anomaly.', 'dataset' => $id);
                }
                $metrics[] = $metric;
            } catch (\Throwable $e) {
                $this->logFailure($id, $e);
                $metrics[] = $this->metric($definition, null, null, 'Source data is unavailable. Refresh or ask an administrator to check the source schema.');
            }
        }
        $gl = $this->catalog->get('gl_result');
        if ($this->authorization->canRead($gl)) {
            try {
                $diagnostic = $this->query("SELECT
                    COALESCE(SUM(g.posted<>1 AND ag.pandl=1),0) AS unposted,
                    COALESCE(SUM(cm.accountcode IS NULL OR ag.groupname IS NULL),0) AS unmapped
                    FROM gltrans g LEFT JOIN chartmaster cm ON cm.accountcode=g.account
                    LEFT JOIN accountgroups ag ON ag.groupname=cm.group_
                    WHERE g.trandate>=? AND g.trandate<?", 'ss', array($request->period['start'], (new \DateTimeImmutable($request->period['end']))->modify('+1 day')->format('Y-m-d')));
                if ((int) $diagnostic[0]['unposted'] || (int) $diagnostic[0]['unmapped']) {
                    $risks[] = array('title' => 'Ledger reporting completeness', 'message' => $diagnostic[0]['unposted'] . ' unposted P&L journal rows and ' . $diagnostic[0]['unmapped'] . ' journal rows with missing account/group masters are excluded from reported P&L in this period.', 'dataset' => 'gl_result');
                }
                $controls = array();
                foreach (array('gllink_debtors' => 'receivables', 'gllink_creditors' => 'payables', 'gllink_stock' => 'inventory') as $key => $label) {
                    $controls[] = $label . ': ' . (!isset($this->companyRecord[$key]) ? 'unverified' : ((int) $this->companyRecord[$key] === 1 ? 'enabled' : 'DISABLED'));
                }
                $risks[] = array('title' => 'Ledger integration configuration', 'message' => implode('; ', $controls) . '. Enabled linkage is configuration evidence only; it does not establish historical posting completeness.', 'dataset' => 'gl_result');
            } catch (\Throwable $e) { $this->logFailure('ledger_diagnostic', $e); }
        }
        $byId = array_column($metrics, null, 'id');
        // Supplier payments cannot be scheduled for balances with no due date. Never show that as "0 due".
        if (isset($byId['payables_due_soon'], $byId['payables']) && (int) $byId['payables']['unknown_due'] > 0) {
            foreach ($metrics as $index => $metric) {
                if ($metric['id'] !== 'payables_due_soon') { continue; }
                $unknown = (int) $byId['payables']['unknown_due'];
                if ($metric['rows'] === 0) {
                    $metrics[$index] = $this->metric($this->catalog->get('payables_due_soon'), null, null, number_format($unknown) . ' open supplier balances have no recorded due date and no usable payment terms, so payments due cannot be scheduled. Record due dates or payment terms to enable this.');
                } else {
                    $metrics[$index]['caveat'] .= ' ' . number_format($unknown) . ' open balances have no due date and are in no window.';
                }
            }
            $byId = array_column($metrics, null, 'id');
        }
        $metrics = array_merge($this->compositeMetrics($byId, $request), $metrics);
        $byId = array_column($metrics, null, 'id');
        $attention = $this->attentionItems($risks, $byId, $request);
        $dataHealth = array();
        foreach ($risks as $risk) {
            if (!preg_match('/overdue exposure|material movement/', $risk['title'])) { $dataHealth[] = $risk; }
        }
        $budgetComparison = null;
        if (isset($byId['gl_budget'], $byId['gl_budget_actual']) && $byId['gl_budget']['available'] && $byId['gl_budget_actual']['available']) {
            $budget = $byId['gl_budget']['value'];
            $actual = $byId['gl_budget_actual']['value'];
            $budgetComparison = array_merge(array('budget' => $budget, 'actual' => $actual, 'status' => 'Recorded, unversioned',
                'caveat' => 'Same complete accounting periods and P&L accounts from chartdetails; no daily proration. A recorded budget is not evidence of approval.'), DirectorsRequest::change($actual, $budget));
        }
        return array('metadata' => $this->metadata($request), 'metrics' => $metrics, 'risks' => $risks,
            'attention' => $attention, 'data_health' => $dataHealth,
            'budget_comparison' => $budgetComparison, 'limitations' => self::limitations());
    }

    public function explore(DirectorsRequest $request, $forExport = false)
    {
        $definition = $this->authorizedDefinition($request);
        $current = $this->summary($definition, $request, false);
        $previous = $this->canCompare($definition, $request) ? $this->summary($definition, $request, true) : null;
        $metric = $this->metric($definition, $current, $previous);
        $current['value'] = $metric['value'];
        $current['previous'] = $previous && $previous['rows'] > 0 ? $previous['value'] : null;
        $current['comparison_complete'] = $metric['comparison_complete'];
        $current['delta'] = $metric['delta'];
        $current['percent'] = $metric['percent'];
        if ($forExport && $current['rows'] > 5000) {
            throw new BIException('export_too_large', 'Detailed exports are limited to 5,000 records. Narrow the filters before exporting.', 422);
        }
        $where = $this->where($definition, $request, false);
        $page = min($request->page, max(1, (int) ceil($current['rows'] / $request->pageSize)));
        $limit = $forExport ? 5000 : $request->pageSize;
        $offset = $forExport ? 0 : ($page - 1) * $request->pageSize;
        $rows = $this->query('SELECT q.* FROM (' . $definition['sql'] . ') q' . $where['sql']
            . ' ORDER BY q.`' . $request->sort . '` ' . strtoupper($request->direction) . ', q.id ASC LIMIT ' . $limit . ' OFFSET ' . $offset,
            $where['types'], $where['params']);
        foreach ($rows as &$row) {
            $row['amount'] = $row['amount'] === null ? null : (float) $row['amount'];
            if (isset($row['quantity'])) { $row['quantity'] = (float) $row['quantity']; }
            $row['source_url'] = $this->sourceUrl($definition, $row);
        }
        unset($row);
        if ($forExport) {
            $rowValue = array_sum(array_column($rows, 'amount'));
            if (count($rows) !== $current['rows'] || ($current['value'] !== null && abs($rowValue - $current['value']) > max(0.0001, abs($rowValue) * 0.00000001))) {
                throw new BIException('source_changed', 'Source records changed while preparing the export. Refresh and retry.', 409);
            }
        }
        $breakdown = $forExport ? array() : $this->breakdown($definition, $request, $current, $previous);
        return array('metadata' => $this->metadata($request), 'metric' => $metric, 'summary' => $current,
            'trend' => !$forExport && $definition['kind'] !== 'snapshot' ? $this->trend($definition, $request, false) : array(),
            'comparison_trend' => !$forExport && $this->canCompare($definition, $request) ? $this->trend($definition, $request, true) : array(),
            'breakdown' => $breakdown, 'group_by' => $this->groupBy($definition, $request),
            'dimensions' => $definition['dimensions'], 'filters' => $request->filters,
            'details' => array('rows' => $rows, 'total' => $current['rows'], 'page' => $page, 'page_size' => $limit));
    }

    private function authorizedDefinition(DirectorsRequest $request)
    {
        $definition = $this->catalog->get($request->dataset);
        if (!$this->authorization->canRead($definition)) { throw new BIException('forbidden', 'Your role does not have access to this source report.', 403); }
        foreach ($request->filters as $dimension => $value) {
            if (!isset($definition['dimensions'][$dimension])) { throw new BIException('invalid_dimension', 'This dimension is unavailable for the selected area: ' . $dimension, 400); }
        }
        $this->groupBy($definition, $request);
        return $this->resolveDefinition($definition);
    }

    /** Current summaries of snapshot measures, for the daily capture job. Unreadable measures are skipped. */
    public function snapshotSummaries(array $ids)
    {
        $request = DirectorsRequest::fromArray(array('comparison' => 'none'));
        $out = array();
        foreach ($ids as $id) {
            $definition = $this->catalog->get($id);
            if ($definition['kind'] !== 'snapshot' || !$this->authorization->canRead($definition)) { continue; }
            $out[$id] = $this->summaryPair($this->resolveDefinition($definition), $request, false)['current'];
        }
        return $out;
    }

    public function record($dataset, $id)
    {
        if (!is_string($id) || $id === '' || strlen($id) > 100) { throw new BIException('invalid_record', 'A valid source row ID is required.', 400); }
        $definition = $this->catalog->get($dataset);
        if (!$this->authorization->canRead($definition)) { throw new BIException('forbidden', 'Your role does not have access to this source report.', 403); }
        $definition = $this->resolveDefinition($definition);
        $params = array_merge($definition['params'], array($id));
        $rows = $this->query('SELECT q.* FROM (' . $definition['sql'] . ') q WHERE CAST(q.id AS CHAR) = ? LIMIT 2', $definition['types'] . 's', $params);
        if (count($rows) !== 1) { throw new BIException('record_unavailable', 'The source row is unavailable or ambiguous in your authorized reporting scope.', 404); }
        return array('title' => $definition['title'], 'definition' => $definition['definition'], 'caveat' => $definition['caveat'],
            'unit' => $this->unit($definition), 'row' => $rows[0], 'company' => $this->context->getCompanyName(), 'generated_at_utc' => gmdate('Y-m-d H:i:s'));
    }

    private function groupBy(array $definition, DirectorsRequest $request)
    {
        $group = $request->groupBy !== '' ? $request->groupBy : key($definition['dimensions']);
        if ($group !== null && !isset($definition['dimensions'][$group])) { throw new BIException('invalid_dimension', 'Choose a supported breakdown dimension.', 400); }
        return $group;
    }

    private function resolveDefinition(array $definition)
    {
        if ($definition['id'] !== 'pending_deliveries') { return $definition; }
        if (isset($this->resolvedDefinitions[$definition['id']])) { return $this->resolvedDefinitions[$definition['id']]; }
        // Match the established sxGroupedDcNumbers rule. Parse the group register
        // once, rather than rescan every CSV group for every DC in each chart.
        // Only canonical numeric document IDs become SQL literals; no request or
        // unvalidated database text is ever interpolated.
        $rows = $this->query("SELECT dcnos FROM dcgroups WHERE dcnos IS NOT NULL AND dcnos<>''", '', array());
        $numbers = array();
        foreach ($rows as $row) {
            foreach (explode(',', $row['dcnos']) as $token) {
                $token = trim($token);
                if ($token === '') { continue; }
                if (!preg_match('/^(0|[1-9][0-9]{0,9})$/D', $token) || (float) $token > 2147483647) {
                    throw new BIException('invalid_group_membership', 'DC group membership contains an invalid document number; pending counts are unavailable until it is reviewed.', 503);
                }
                $numbers[$token] = true;
            }
        }
        $predicate = $numbers ? 'd.orderno NOT IN (' . implode(',', array_keys($numbers)) . ')' : '1=1';
        $original = "NOT EXISTS (SELECT 1 FROM dcgroups g WHERE FIND_IN_SET(CAST(d.orderno AS CHAR), REPLACE(g.dcnos,' ',''))>0)";
        if (strpos($definition['sql'], $original) === false) { throw new \LogicException('DC membership SQL contract changed.'); }
        $definition['sql'] = str_replace($original, $predicate, $definition['sql']);
        $this->resolvedDefinitions[$definition['id']] = $definition;
        return $definition;
    }

    private function summary(array $definition, DirectorsRequest $request, $previous)
    {
        $where = $this->where($definition, $request, $previous);
        $overdue = $definition['has_overdue'] ? 'COALESCE(SUM(q.overdue_amount), 0)' : '0';
        $quality = $definition['has_quality'] ? 'COALESCE(SUM(q.quality_issue), 0)' : '0';
        $unknown = isset($definition['dimensions']['due_status']) ? "COALESCE(SUM(q.due_status = 'unknown_due'), 0)" : '0';
        $estimated = isset($definition['dimensions']['due_basis']) ? "COALESCE(SUM(q.due_basis LIKE 'Estimated%'), 0)" : '0';
        $rows = $this->query('SELECT COUNT(*) AS `rows`, COALESCE(SUM(q.amount), 0) AS value,
            COUNT(*) - COUNT(q.amount) AS invalid_amounts,
            MIN(q.date) AS first_record_date, MAX(q.date) AS last_record_date,
            COALESCE(SUM(CASE WHEN q.amount > 0 THEN q.amount ELSE 0 END), 0) AS inflow,
            COALESCE(SUM(CASE WHEN q.amount < 0 THEN -q.amount ELSE 0 END), 0) AS outflow,
            COALESCE(SUM(ABS(q.amount)), 0) AS absolute_value,
            ' . $overdue . ' AS overdue, ' . $quality . ' AS quality_issues, ' . $unknown . ' AS unknown_due, ' . $estimated . ' AS estimated_due
            FROM (' . $definition['sql'] . ') q' . $where['sql'], $where['types'], $where['params']);
        $row = $rows[0];
        foreach (array('rows', 'invalid_amounts', 'quality_issues', 'unknown_due', 'estimated_due') as $key) { $row[$key] = (int) $row[$key]; }
        foreach (array('value', 'inflow', 'outflow', 'absolute_value', 'overdue') as $key) { $row[$key] = (float) $row[$key]; }
        // No valid facts must never become a reliable zero.
        if ($row['rows'] > 0 && $row['invalid_amounts'] === $row['rows']) { $row['value'] = null; }
        return $row;
    }

    /**
     * Current and comparison summaries and the undated-row count in ONE pass over the
     * dataset. Equivalent to two summary() calls plus an undated scan (checked by
     * bi/tests/directors_pair.php); the overview calls it once per measure.
     */
    private function summaryPair(array $definition, DirectorsRequest $request, $withPrevious)
    {
        $snapshot = $definition['kind'] === 'snapshot';
        $budget = $definition['kind'] === 'budget';
        $flagSql = array();
        $params = array();
        $types = '';
        foreach (array('in_current' => false, 'in_previous' => true) as $alias => $previous) {
            if ($snapshot) { $flagSql[] = ($previous ? '0' : '1') . ' AS ' . $alias; continue; }
            if ($previous && !$withPrevious) { $flagSql[] = '0 AS ' . $alias; continue; }
            $start = $request->period[$previous ? 'comparison_start' : 'start'];
            $end = $request->period[$previous ? 'comparison_end' : 'end'];
            $flagSql[] = '(q.date >= ? AND q.date < ?' . ($budget ? ' AND q.period_start >= ?' : '') . ') AS ' . $alias;
            $params[] = $start;
            $params[] = (new \DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d');
            $types .= 'ss';
            if ($budget) { $params[] = $start; $types .= 's'; }
        }
        $overdue = $definition['has_overdue'] ? 'x.overdue_amount' : 'NULL';
        $quality = $definition['has_quality'] ? 'x.quality_issue' : 'NULL';
        $unknown = isset($definition['dimensions']['due_status']) ? "(x.due_status = 'unknown_due')" : 'NULL';
        $estimated = isset($definition['dimensions']['due_basis']) ? "(x.due_basis LIKE 'Estimated%')" : 'NULL';
        $aggregates = function ($flag, $p) use ($overdue, $quality, $unknown, $estimated) {
            return 'SUM(CASE WHEN ' . $flag . ' THEN 1 ELSE 0 END) AS ' . $p . '_rows,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN x.amount END), 0) AS ' . $p . '_value,
                SUM(CASE WHEN ' . $flag . ' AND x.amount IS NULL THEN 1 ELSE 0 END) AS ' . $p . '_invalid,
                MIN(CASE WHEN ' . $flag . ' THEN x.date END) AS ' . $p . '_first, MAX(CASE WHEN ' . $flag . ' THEN x.date END) AS ' . $p . '_last,
                COALESCE(SUM(CASE WHEN ' . $flag . ' AND x.amount > 0 THEN x.amount ELSE 0 END), 0) AS ' . $p . '_inflow,
                COALESCE(SUM(CASE WHEN ' . $flag . ' AND x.amount < 0 THEN -x.amount ELSE 0 END), 0) AS ' . $p . '_outflow,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN ABS(x.amount) END), 0) AS ' . $p . '_absolute,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN ' . $overdue . ' END), 0) AS ' . $p . '_overdue,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN ' . $quality . ' END), 0) AS ' . $p . '_quality,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN ' . $unknown . ' END), 0) AS ' . $p . '_unknown,
                COALESCE(SUM(CASE WHEN ' . $flag . ' THEN ' . $estimated . ' END), 0) AS ' . $p . '_estimated';
        };
        $sql = 'SELECT ' . $aggregates('x.in_current = 1', 'c') . ', ' . $aggregates('x.in_previous = 1', 'p')
            . ", SUM(CASE WHEN x.date IS NULL OR x.date < '1900-01-01' THEN 1 ELSE 0 END) AS undated
            FROM (SELECT q.*, " . implode(', ', $flagSql) . ' FROM (' . $definition['sql'] . ') q) x';
        $r = $this->query($sql, $types . $definition['types'], array_merge($params, $definition['params']))[0];
        $build = function ($p) use ($r) {
            $row = array('rows' => (int) $r[$p . '_rows'], 'value' => (float) $r[$p . '_value'], 'invalid_amounts' => (int) $r[$p . '_invalid'],
                'first_record_date' => $r[$p . '_first'], 'last_record_date' => $r[$p . '_last'], 'inflow' => (float) $r[$p . '_inflow'],
                'outflow' => (float) $r[$p . '_outflow'], 'absolute_value' => (float) $r[$p . '_absolute'], 'overdue' => (float) $r[$p . '_overdue'],
                'quality_issues' => (int) $r[$p . '_quality'], 'unknown_due' => (int) $r[$p . '_unknown'], 'estimated_due' => (int) $r[$p . '_estimated']);
            if ($row['rows'] > 0 && $row['invalid_amounts'] === $row['rows']) { $row['value'] = null; }
            return $row;
        };
        return array('current' => $build('c'), 'previous' => $withPrevious ? $build('p') : null, 'undated' => (int) $r['undated']);
    }

    private function where(array $definition, DirectorsRequest $request, $previous)
    {
        $conditions = array();
        $params = $definition['params'];
        $types = $definition['types'];
        if ($definition['kind'] !== 'snapshot') {
            $start = $request->period[$previous ? 'comparison_start' : 'start'];
            $end = $request->period[$previous ? 'comparison_end' : 'end'];
            $conditions[] = 'q.date >= ? AND q.date < ?';
            $params[] = $start;
            $params[] = (new \DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d');
            $types .= 'ss';
            if ($definition['kind'] === 'budget') {
                $conditions[] = 'q.period_start >= ?';
                $params[] = $start;
                $types .= 's';
            }
        }
        foreach ($request->filters as $dimension => $value) {
            // Only invoked after authorizedDefinition validates these identifiers.
            if (!isset($definition['dimensions'][$dimension])) { throw new BIException('invalid_dimension', 'Unsupported filter.', 400); }
            $conditions[] = 'COALESCE(CAST(q.`' . $dimension . '` AS CHAR), \'Unassigned\') = ?';
            $params[] = $value;
            $types .= 's';
        }
        if ($request->search !== '') {
            $parts = array();
            foreach (array_merge(array('reference'), array_keys($definition['dimensions']), isset($definition['search_columns']) ? $definition['search_columns'] : array()) as $column) {
                $parts[] = 'LOCATE(?, CAST(q.`' . $column . '` AS CHAR)) > 0';
                $params[] = $request->search;
                $types .= 's';
            }
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }
        return array('sql' => $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', 'params' => $params, 'types' => $types);
    }

    private function trend(array $definition, DirectorsRequest $request, $previous)
    {
        $where = $this->where($definition, $request, $previous);
        $rows = $this->query("SELECT DATE_FORMAT(q.date, '%Y-%m-01') AS period, SUM(q.amount) AS value, COUNT(*) AS `rows`
            FROM (" . $definition['sql'] . ') q' . $where['sql'] . ' GROUP BY period ORDER BY period', $where['types'], $where['params']);
        $indexed = array_column($rows, null, 'period');
        $start = new \DateTimeImmutable($request->period[$previous ? 'comparison_start' : 'start']);
        $end = $request->period[$previous ? 'comparison_end' : 'end'];
        $series = array();
        for ($month = $start->modify('first day of this month'); $month->format('Y-m-d') <= $end; $month = $month->modify('+1 month')) {
            $key = $month->format('Y-m-d');
            $row = isset($indexed[$key]) ? $indexed[$key] : array('period' => $key, 'value' => 0, 'rows' => 0);
            $row['value'] = $row['value'] === null ? null : (float) $row['value'];
            $row['rows'] = (int) $row['rows'];
            $series[] = $row;
        }
        return $series;
    }

    private function breakdown(array $definition, DirectorsRequest $request, array $current, $previous)
    {
        $group = $this->groupBy($definition, $request);
        if ($group === null) { return array(); }
        $where = $this->where($definition, $request, false);
        $groupSql = 'COALESCE(CAST(q.`' . $group . '` AS CHAR), \'Unassigned\')';
        $sub = 'SELECT ' . $groupSql . ' AS group_key, q.amount AS current_value, 0 AS previous_value, 1 AS current_rows, 0 AS previous_rows,
            (q.amount IS NOT NULL) AS current_valid, 0 AS previous_valid
            FROM (' . $definition['sql'] . ') q' . $where['sql'];
        $params = $where['params'];
        $types = $where['types'];
        if ($this->canCompare($definition, $request)) {
            $pw = $this->where($definition, $request, true);
            $sub .= ' UNION ALL SELECT ' . $groupSql . ', 0, q.amount, 0, 1, 0, (q.amount IS NOT NULL) FROM (' . $definition['sql'] . ') q' . $pw['sql'];
            $params = array_merge($params, $pw['params']);
            $types .= $pw['types'];
        }
        $rows = $this->query('SELECT group_key AS `key`, SUM(current_value) AS value, SUM(previous_value) AS previous,
            SUM(current_rows) AS `rows`, SUM(previous_rows) AS previous_rows,
            SUM(current_valid) AS current_valid, SUM(previous_valid) AS previous_valid FROM (' . $sub . ') b
            GROUP BY group_key ORDER BY ABS(SUM(current_value)) + ABS(SUM(previous_value)) DESC, group_key ASC LIMIT 500', $types, $params);
        $labels = array();
        if ($rows && isset($definition['dimension_labels'][$group])) {
            // Group using stable narrow keys, then enrich only the bounded
            // result. Grouping 400k stock rows by long display labels spills to
            // disk on the ERP's default MariaDB temporary-table limits.
            $labelColumn = $definition['dimension_labels'][$group];
            $labelRows = $this->query('SELECT ' . $groupSql . ' AS label_key, MIN(q.`' . $labelColumn . '`) AS label
                FROM (' . $definition['sql'] . ') q WHERE q.`' . $group . '` IN (' . implode(',', array_fill(0, count($rows), '?')) . ') GROUP BY q.`' . $group . '`',
                $definition['types'] . str_repeat('s', count($rows)), array_merge($definition['params'], array_column($rows, 'key')));
            $labels = array_column($labelRows, 'label', 'label_key');
        }
        $sum = $prevSum = $count = $prevCount = 0;
        foreach ($rows as &$row) {
            $row['label'] = isset($labels[$row['key']]) ? $labels[$row['key']] : $row['key'];
            $row['value'] = (int) $row['rows'] > 0 && (int) $row['current_valid'] === 0 ? null : (float) $row['value'];
            $row['previous'] = $previous && $previous['rows'] > 0 && !((int) $row['previous_rows'] > 0 && (int) $row['previous_valid'] === 0) ? (float) $row['previous'] : null;
            $row['rows'] = (int) $row['rows'];
            $row['invalid_amounts'] = (int) $row['rows'] - (int) $row['current_valid'];
            $row['comparison_invalid_amounts'] = (int) $row['previous_rows'] - (int) $row['previous_valid'];
            $row += $row['invalid_amounts'] === 0 && $row['comparison_invalid_amounts'] === 0
                ? DirectorsRequest::change($row['value'], $row['previous']) : array('delta' => null, 'percent' => null);
            $row['share_percent'] = $row['value'] !== null && $current['value'] !== null && abs($current['value']) > 0.000001 ? $row['value'] / $current['value'] * 100 : null;
            $sum += $row['value']; $prevSum += $row['previous']; $count += $row['rows']; $prevCount += (int) $row['previous_rows'];
        }
        unset($row);
        if ($current['rows'] > $count || ($previous && $previous['rows'] > $prevCount)) {
            $other = array('key' => null, 'label' => 'Other groups (beyond top 500)', 'value' => $current['value'] === null ? null : $current['value'] - $sum,
                'previous' => $previous && $previous['rows'] > 0 ? $previous['value'] - $prevSum : null, 'rows' => $current['rows'] - $count);
            $other += $current['invalid_amounts'] === 0 && (!$previous || $previous['invalid_amounts'] === 0)
                ? DirectorsRequest::change($other['value'], $other['previous']) : array('delta' => null, 'percent' => null);
            $rows[] = $other;
        }
        return $rows;
    }

    private function metric(array $definition, $current, $previous, $error = null)
    {
        $value = $current ? $current['value'] : null;
        if ($current && $current['rows'] > 0 && $current['invalid_amounts'] === $current['rows']) {
            $error = 'All source amounts are unavailable. Investigate the records and their quality status.';
        }
        if ($current && $definition['id'] === 'gl_budget' && $current['absolute_value'] < 0.000001) {
            $error = 'No non-zero recorded budget exists for complete periods in this selection.';
            $value = null;
        }
        if ($current && strpos($definition['id'], 'gl_') === 0 && $current['rows'] === 0) {
            $error = 'No matching ledger or complete accounting-period records are available.';
            $value = null;
        }
        $prior = $previous && $previous['rows'] > 0 ? $previous['value'] : null;
        $comparisonComplete = $current && $previous && $current['invalid_amounts'] === 0 && $previous['invalid_amounts'] === 0;
        $movement = $comparisonComplete ? DirectorsRequest::change($value, $prior) : array('delta' => null, 'percent' => null);
        return array_merge(array_intersect_key($definition, array_flip(array('id', 'title', 'unit', 'kind', 'status', 'source', 'definition', 'caveat', 'dimensions', 'section'))),
            array('unit' => $this->unit($definition), 'value' => $value, 'previous' => $prior, 'rows' => $current ? $current['rows'] : 0,
                'available' => $error === null && $value !== null, 'can_investigate' => $current !== null, 'error' => $error,
                'overdue' => $current ? $current['overdue'] : null, 'invalid_amounts' => $current ? $current['invalid_amounts'] : null,
                'polarity' => isset(self::$polarity[$definition['id']]) ? self::$polarity[$definition['id']] : 0,
                'completeness' => $current && $current['invalid_amounts'] > 0 ? 'partial' : 'available_source_rows',
                'comparison_complete' => (bool) $comparisonComplete, 'comparison_invalid_amounts' => $previous ? $previous['invalid_amounts'] : null,
                'first_record_date' => $current ? $current['first_record_date'] : null, 'last_record_date' => $current ? $current['last_record_date'] : null,
                'quality_issues' => $current ? $current['quality_issues'] : null, 'unknown_due' => $current ? $current['unknown_due'] : null,
                'estimated_due' => $current ? $current['estimated_due'] : null), $movement);
    }

    /** Bank movement is always limited to the signed-in user's assigned bank accounts. */
    private function hasBankAccess()
    {
        $rows = $this->query('SELECT COUNT(*) AS n FROM bankaccountusers WHERE userid = ?', 's', array($this->context->getUserId()));
        return (int) $rows[0]['n'] > 0;
    }

    /** Headline measures built only from other measures that are already available. */
    private function compositeMetrics(array $byId, DirectorsRequest $request)
    {
        $template = function ($id, $title, $unit, $status, $definition, $caveat, $value, $previous, $movement, array $components, $error) {
            return array_merge(array('id' => $id, 'title' => $title, 'unit' => $unit, 'kind' => 'flow', 'status' => $status,
                'source' => 'Combined from: ' . implode(', ', array_column($components, 'title')), 'definition' => $definition, 'caveat' => $caveat,
                'dimensions' => array(), 'section' => 'Headline', 'value' => $value, 'previous' => $previous, 'rows' => 0,
                'available' => $error === null && $value !== null, 'can_investigate' => false, 'error' => $error, 'overdue' => null,
                'invalid_amounts' => 0, 'polarity' => isset(self::$polarity[$id]) ? self::$polarity[$id] : 0, 'completeness' => 'available_source_rows',
                'comparison_complete' => $previous !== null, 'comparison_invalid_amounts' => 0, 'first_record_date' => null, 'last_record_date' => null,
                'quality_issues' => null, 'unknown_due' => null, 'estimated_due' => null, 'components' => $components), $movement);
        };
        $usable = function ($id) use ($byId) { return isset($byId[$id]) && $byId[$id]['available'] && (int) $byId[$id]['invalid_amounts'] === 0; };
        $out = array();

        // Sales revenue = posted invoices + completed shop sales (decision of 2026-11-05).
        $parts = array('posted_invoices', 'shop_sales');
        $components = array();
        foreach ($parts as $id) { if (isset($byId[$id])) { $components[] = array('id' => $id, 'title' => $byId[$id]['title'], 'value' => $byId[$id]['value']); } }
        if (count($components) === count($parts) && $usable('posted_invoices') && $usable('shop_sales')) {
            $value = $byId['posted_invoices']['value'] + $byId['shop_sales']['value'];
            $priorOk = $byId['posted_invoices']['previous'] !== null && $byId['shop_sales']['previous'] !== null
                && $byId['posted_invoices']['comparison_complete'] && $byId['shop_sales']['comparison_complete'];
            $prior = $priorOk ? $byId['posted_invoices']['previous'] + $byId['shop_sales']['previous'] : null;
            $movement = $priorOk ? DirectorsRequest::change($value, $prior) : array('delta' => null, 'percent' => null);
            $out[] = $template('sales_revenue', 'Sales revenue', $this->currency, 'actual',
                'Posted invoices (debtortrans type 10, not reversed) plus completed shop sales (type 750, not reversed), dated by invoice or shop-sale date.',
                'Includes tax, as posted. Invoices and shop sales are different documents and are not counted twice. This is an operational sales figure; general-ledger revenue is shown separately and is not expected to equal it.',
                $value, $prior, $movement, $components, null);
        } else {
            $out[] = $template('sales_revenue', 'Sales revenue', $this->currency, 'unavailable', 'Posted invoices plus completed shop sales.', '', null, null,
                array('delta' => null, 'percent' => null), $components, 'One of its source measures is unavailable or has excluded amounts.');
        }

        // Gross margin % from the general ledger. Estimated: depends on GL postings being complete.
        if ($usable('gl_revenue') && $usable('gl_cost_of_sales') && $byId['gl_revenue']['value'] > 0) {
            $margin = function ($revenue, $cost) { return $revenue > 0 ? ($revenue - $cost) / $revenue * 100 : null; };
            $value = $margin($byId['gl_revenue']['value'], $byId['gl_cost_of_sales']['value']);
            $prior = $byId['gl_revenue']['previous'] !== null && $byId['gl_cost_of_sales']['previous'] !== null
                ? $margin($byId['gl_revenue']['previous'], $byId['gl_cost_of_sales']['previous']) : null;
            $movement = $prior !== null && $value !== null ? array('delta' => $value - $prior, 'percent' => null) : array('delta' => null, 'percent' => null);
            $components = array(array('id' => 'gl_revenue', 'title' => $byId['gl_revenue']['title'], 'value' => $byId['gl_revenue']['value']),
                array('id' => 'gl_cost_of_sales', 'title' => $byId['gl_cost_of_sales']['title'], 'value' => $byId['gl_cost_of_sales']['value']));
            $out[] = $template('gross_margin_pct', 'Gross margin', '%', 'estimated',
                '(GL revenue − cost of sales) ÷ GL revenue × 100, from posted general-ledger journals in the period.',
                'Estimated: it is only as complete as ledger posting and stock-cost postings. The change is in percentage points, not a percentage of a percentage.',
                $value, $prior, $movement, $components, null);
        } else {
            $out[] = $template('gross_margin_pct', 'Gross margin', '%', 'unavailable', '(GL revenue − cost of sales) ÷ GL revenue.', '', null, null,
                array('delta' => null, 'percent' => null), array(), 'General-ledger revenue or cost of sales is unavailable for this period.');
        }

        $place = function (array $metric, $section) { $metric['section'] = $section; return $metric; };
        $none = array('delta' => null, 'percent' => null);

        // Days sales outstanding: invoices still outstanding, as days of invoiced sales in the selected period.
        if ($usable('outstanding_invoices') && $usable('posted_invoices') && $byId['posted_invoices']['value'] > 0) {
            $days = (int) $request->period['days'];
            $value = $byId['outstanding_invoices']['value'] / $byId['posted_invoices']['value'] * $days;
            $out[] = $place($template('dso_days', 'Days sales outstanding', 'days', 'estimated',
                'Outstanding invoices today ÷ posted invoices in the selected period × days in the period (' . $days . ').',
                'Estimated. The numerator is today\'s outstanding balance and the denominator is the selected period, so it is a current-state ratio with no historical comparison. Use a period of at least a month; short periods swing widely. Outstanding follows the Outstanding Invoices rule (invoices on a sales case and invoice group).',
                $value, null, $none, array(array('id' => 'outstanding_invoices', 'title' => 'Outstanding', 'value' => $byId['outstanding_invoices']['value']),
                    array('id' => 'posted_invoices', 'title' => 'Invoiced in period', 'value' => $byId['posted_invoices']['value'])), null), 'Cash & working capital');
        } else {
            $out[] = $place($template('dso_days', 'Days sales outstanding', 'days', 'unavailable', 'Outstanding invoices ÷ posted invoices × days.', '', null, null, $none, array(), 'Outstanding invoices or posted invoices are unavailable for this period.'), 'Cash & working capital');
        }

        // Concentration: share of the period's value held by the 10 largest customers / suppliers.
        foreach (array(array('customer_concentration_pct', 'Top-10 customers: share of invoiced sales', 'posted_invoices', 'customer', 'customers'),
                       array('supplier_concentration_pct', 'Top-10 suppliers: share of purchases received', 'purchasing', 'supplier', 'suppliers')) as $spec) {
            list($id, $title, $dataset, $dimension, $noun) = $spec;
            try {
                $current = $this->topShare($dataset, $dimension, $request->period['start'], $request->period['end']);
                $previous = $request->period['comparison_start'] !== null ? $this->topShare($dataset, $dimension, $request->period['comparison_start'], $request->period['comparison_end']) : null;
                if ($current === null) { throw new \RuntimeException('No valid amounts in this period.'); }
                $movement = $previous !== null ? array('delta' => $current['share'] - $previous['share'], 'percent' => null) : $none;
                $out[] = $place($template($id, $title, '%', 'actual',
                    'The 10 largest ' . $noun . ' by value in the period, as a percentage of all valid amounts in the period. Source: ' . $dataset . '.',
                    'A high share means dependence on a few ' . $noun . '. Only valid amounts count; excluded amounts are not in either side. The change is in percentage points.',
                    $current['share'], $previous !== null ? $previous['share'] : null, $movement,
                    array(array('id' => $dataset, 'title' => 'Top 10 ' . $noun, 'value' => $current['top']), array('title' => 'All ' . $noun, 'value' => $current['total'])), null),
                    $dimension === 'customer' ? 'Sales & customers' : 'Purchasing & suppliers');
            } catch (\Throwable $e) {
                $this->logFailure($id, $e);
                $out[] = $place($template($id, $title, '%', 'unavailable', $title . '.', '', null, null, $none, array(), 'The source for this measure is unavailable for this period.'), $dimension === 'customer' ? 'Sales & customers' : 'Purchasing & suppliers');
            }
        }

        // Order-to-invoice time: days from the first order confirmation (OC) on a sales case to each posted invoice on it.
        try {
            $current = $this->cycleTimes($request->period['start'], $request->period['end']);
            $previous = $request->period['comparison_start'] !== null ? $this->cycleTimes($request->period['comparison_start'], $request->period['comparison_end']) : null;
            if ($current === null) { throw new \RuntimeException('No invoices with an order date in this period.'); }
            $movement = $previous !== null ? array('delta' => $current['median'] - $previous['median'], 'percent' => null) : $none;
            $out[] = $place($template('order_to_invoice_days', 'Order to invoice: median days', 'days', 'estimated',
                'For posted invoices dated in the period: days from the first order confirmation (OC) on the invoice\'s sales case to the invoice date. The median is shown; the 90th percentile is in the tile detail.',
                'Estimated. Uses the earliest OC on the case, so a case invoiced in several deliveries counts each invoice from that first OC. Invoices without a sales case or OC are left out. The change is in days.',
                $current['median'], $previous !== null ? $previous['median'] : null, $movement,
                array(array('id' => 'posted_invoices', 'title' => 'Invoices measured', 'value' => $current['n']), array('title' => '90th percentile (days)', 'value' => $current['p90'])), null), 'Sales & customers');
        } catch (\Throwable $e) {
            $this->logFailure('order_to_invoice_days', $e);
            $out[] = $place($template('order_to_invoice_days', 'Order to invoice: median days', 'days', 'unavailable', 'Median days from the first order confirmation to the invoice.', '', null, null, $none, array(), 'No invoices with an order date are available for this period.'), 'Sales & customers');
        }
        return $out;
    }

    /** Share of valid value held by the 10 largest groups of one dimension in a date range; null without valid amounts. */
    private function topShare($datasetId, $dimension, $start, $end)
    {
        $definition = $this->resolveDefinition($this->catalog->get($datasetId));
        if (!$this->authorization->canRead($definition) || !isset($definition['dimensions'][$dimension])) { return null; }
        $rows = $this->query('SELECT SUM(q.amount) AS v FROM (' . $definition['sql'] . ') q WHERE q.date >= ? AND q.date < ? AND q.amount IS NOT NULL GROUP BY q.`' . $dimension . '` ORDER BY v DESC',
            $definition['types'] . 'ss', array_merge($definition['params'], array($start, (new \DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d'))));
        $values = array_map('floatval', array_column($rows, 'v'));
        $total = array_sum(array_filter($values, function ($v) { return $v > 0; }));
        if ($total <= 0) { return null; }
        $top = array_sum(array_slice(array_filter($values, function ($v) { return $v > 0; }), 0, 10));
        return array('share' => $top / $total * 100, 'top' => $top, 'total' => $total);
    }

    /** Median and 90th percentile of days from first OC to each posted invoice dated in the range. */
    private function cycleTimes($start, $end)
    {
        $definition = $this->catalog->get('posted_invoices');
        if (!$this->authorization->canRead($definition)) { return null; }
        $rows = $this->query("SELECT DATEDIFF(q.date, o.first_oc) AS d FROM (" . $definition['sql'] . ") q
            INNER JOIN (SELECT salescaseref, MIN(orddate) AS first_oc FROM ocs WHERE orddate > '1900-01-01' GROUP BY salescaseref) o ON o.salescaseref = q.project
            WHERE q.date >= ? AND q.date < ? AND DATEDIFF(q.date, o.first_oc) >= 0 ORDER BY d",
            $definition['types'] . 'ss', array_merge($definition['params'], array($start, (new \DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d'))));
        $days = array_map('intval', array_column($rows, 'd'));
        $n = count($days);
        if ($n === 0) { return null; }
        $median = $n % 2 ? $days[intdiv($n, 2)] : ($days[$n / 2 - 1] + $days[$n / 2]) / 2;
        return array('median' => (float) $median, 'p90' => (float) $days[(int) max(0, ceil(0.9 * $n) - 1)], 'n' => $n);
    }

    /** Ranked business items for the executive "Needs attention" list. Largest money at risk first. */
    private function attentionItems(array $risks, array $byId, DirectorsRequest $request)
    {
        $items = array();
        foreach ($risks as $risk) {
            if (!preg_match('/material movement/', $risk['title']) || !isset($risk['dataset'], $byId[$risk['dataset']])) { continue; }
            $metric = $byId[$risk['dataset']];
            if ($metric['percent'] === null || $metric['delta'] === null) { continue; }
            // Only moves with a known unfavourable direction need attention; neutral and favourable moves stay on the tiles.
            if ($metric['polarity'] === 0 || (($metric['delta'] > 0) === ($metric['polarity'] > 0))) { continue; }
            $items[] = array('id' => 'move_' . $risk['dataset'], 'title' => $metric['title'] . ($metric['delta'] > 0 ? ' is up' : ' is down'),
                'amount' => abs($metric['delta']), 'unit' => $metric['unit'], 'severity' => abs($metric['percent']) >= 40 ? 'high' : 'medium', 'favourable' => false,
                'message' => number_format(abs($metric['percent']), 1) . '% ' . ($metric['delta'] > 0 ? 'higher' : 'lower') . ' than the comparison period.',
                'dataset' => $risk['dataset']);
        }
        if (isset($byId['outstanding_invoices']) && $byId['outstanding_invoices']['available']) {
            $m = $byId['outstanding_invoices'];
            $share = $m['value'] > 0 ? $m['overdue'] / $m['value'] * 100 : 0;
            if ($m['overdue'] > 0 && $share >= self::OVERDUE_SHARE_PERCENT) {
                $items[] = array('id' => 'overdue_invoices', 'title' => 'Overdue customer invoices', 'amount' => $m['overdue'], 'unit' => $m['unit'],
                    'severity' => $share >= 50 ? 'high' : 'medium', 'favourable' => false,
                    'message' => number_format($share, 0) . '% of outstanding invoices are past their due date.', 'dataset' => 'outstanding_invoices',
                    'group_by' => 'age_bucket', 'filters' => array('due_status' => 'overdue'));
            }
        }
        try {
            $pending = $this->catalog->get('pending_deliveries');
            if ($this->authorization->canRead($pending)) {
                $pending = $this->resolveDefinition($pending);
                $rows = $this->query("SELECT COUNT(*) AS n, COALESCE(SUM(q.amount),0) AS v FROM (" . $pending['sql'] . ") q WHERE q.age_bucket = 'Over 90 days'", $pending['types'], $pending['params']);
                if ((int) $rows[0]['n'] > 0) {
                    $items[] = array('id' => 'stuck_dcs', 'title' => 'Delivery challans waiting over ' . self::STUCK_DC_DAYS . ' days', 'amount' => (float) $rows[0]['v'], 'unit' => 'PKR',
                        'severity' => 'medium', 'favourable' => false, 'message' => $rows[0]['n'] . ' DCs are on the Pending DC List and still not invoiced.',
                        'dataset' => 'pending_deliveries', 'group_by' => 'age_bucket', 'filters' => array('age_bucket' => 'Over 90 days'));
                }
            }
        } catch (\Throwable $e) { $this->logFailure('attention_pending_dc', $e); }
        try {
            $invoices = $this->catalog->get('posted_invoices');
            if ($this->authorization->canRead($invoices)) {
                $where = $this->where($invoices, $request, false);
                $rows = $this->query("SELECT q.customer AS customer, SUM(q.amount) AS v FROM (" . $invoices['sql'] . ") q" . $where['sql'] . " GROUP BY q.customer ORDER BY v DESC LIMIT 1", $where['types'], $where['params']);
                $total = isset($byId['posted_invoices']['value']) ? $byId['posted_invoices']['value'] : 0;
                if ($rows && $total > 0 && (float) $rows[0]['v'] / $total * 100 >= self::CONCENTRATION_SHARE_PERCENT) {
                    $share = (float) $rows[0]['v'] / $total * 100;
                    $items[] = array('id' => 'customer_concentration', 'title' => 'One customer is ' . number_format($share, 0) . '% of invoiced sales', 'amount' => (float) $rows[0]['v'], 'unit' => 'PKR',
                        'severity' => $share >= 40 ? 'high' : 'medium', 'favourable' => false, 'message' => $rows[0]['customer'] . ' in the selected period.',
                        'dataset' => 'posted_invoices', 'group_by' => 'customer');
                }
            }
        } catch (\Throwable $e) { $this->logFailure('attention_concentration', $e); }
        if (isset($byId['payables_due_soon']) && $byId['payables_due_soon']['available']) {
            try {
                $due = $this->catalog->get('payables_due_soon');
                $rows = $this->query("SELECT COUNT(*) AS n, COALESCE(SUM(q.amount),0) AS v FROM (" . $due['sql'] . ") q WHERE q.due_window = 'Due in 0-7 days'", $due['types'], $due['params']);
                if ((int) $rows[0]['n'] > 0 && (float) $rows[0]['v'] > 0) {
                    $items[] = array('id' => 'payables_due_7', 'title' => 'Supplier payments due within 7 days', 'amount' => (float) $rows[0]['v'], 'unit' => $this->currency,
                        'severity' => 'medium', 'favourable' => false, 'message' => $rows[0]['n'] . ' supplier balances fall due in the next 7 days.',
                        'dataset' => 'payables_due_soon', 'group_by' => 'due_window', 'filters' => array('due_window' => 'Due in 0-7 days'));
                }
            } catch (\Throwable $e) { $this->logFailure('attention_payables_due', $e); }
        }
        usort($items, function ($a, $b) { return $b['amount'] <=> $a['amount']; });
        return array_slice($items, 0, 8);
    }

    private function unit(array $definition) { return $definition['unit'] === 'base' ? $this->currency : $definition['unit']; }
    private function canCompare(array $definition, DirectorsRequest $request) { return $definition['kind'] !== 'snapshot' && $request->period['comparison_start'] !== null; }

    private function metadata(DirectorsRequest $request)
    {
        return array('company' => $this->context->getCompanyName(), 'currency' => $this->currency, 'generated_at_utc' => gmdate('Y-m-d H:i:s'),
            'generated_local' => date('Y-m-d H:i:s'), 'time_zone' => date_default_timezone_get(),
            'server_ms' => (int) round((microtime(true) - $this->started) * 1000), 'period' => $request->period,
            'scope' => 'Active ERP company only; source page permissions, assigned expense tabs and authorized bank accounts apply.',
            'comparison_definition' => 'Percentage change = (current − previous) / absolute(previous). Zero or missing baselines have no percentage. Snapshots do not have historical comparisons.');
    }

    private function sourceUrl(array $definition, array $row)
    {
        if (empty($definition['source_route'])) { return null; }
        $route = $definition['source_route'];
        if ($route === 'GLTransInquiry.php') {
            $type = isset($row['source_type']) ? $row['source_type'] : $row['transaction_type'];
            return $this->rootPath . $route . '?' . http_build_query(array('TypeID' => $type, 'TransNo' => $row['transaction_number']));
        }
        if ($definition['id'] === 'petty_cash' || $definition['kind'] === 'budget' || $route === 'v2/bi/directors-record.php') {
            return $this->rootPath . 'v2/bi/directors-record.php?' . http_build_query(array('dataset' => $definition['id'], 'id' => (string) $row['id']));
        }
        $key = isset($row['source_key']) ? $row['source_key'] : $row['id'];
        return $this->rootPath . $route . '?' . http_build_query(array($definition['source_param'] => $key));
    }

    private function query($sql, $types, array $params)
    {
        if ($this->runner) { return call_user_func($this->runner, $sql, $types, $params); }
        $stmt = @mysqli_prepare($this->db, $sql);
        if (!$stmt) { throw new BIException('source_unavailable', 'The reporting source could not be prepared.', 503); }
        try {
            if ($types !== '') {
                $bindings = array($stmt, $types);
                foreach ($params as $key => $value) { $bindings[] = &$params[$key]; }
                if (!call_user_func_array('mysqli_stmt_bind_param', $bindings)) { throw new \RuntimeException('Could not bind reporting filters.'); }
            }
            if (!@mysqli_stmt_execute($stmt)) { throw new \RuntimeException('Could not execute reporting source.'); }
            $result = @mysqli_stmt_get_result($stmt);
            if (!$result) { throw new \RuntimeException('Could not read reporting source.'); }
            $rows = array();
            while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }
            mysqli_free_result($result);
            return $rows;
        } finally { mysqli_stmt_close($stmt); }
    }

    private function logFailure($id, \Throwable $e) { error_log('[directors] ' . $id . ': ' . get_class($e) . ': ' . $e->getMessage()); }

    public static function limitations()
    {
        return array(
            'Current AR, AP, stock, open cases and commitments are live snapshots and ignore the flow date filter. Historical allocations can be edited or deleted; immutable balance and valuation snapshots are required for reliable historic comparisons.',
            'GL figures use posted journals by journal date. Accounting-period budget comparisons use chartdetails for complete periods only. Source posting completeness and company GL-link settings must be verified before treating ledger results as complete business profit.',
            'Operational invoice values, shop sales, GL revenue, MPIW purchases, payables and petty-cash claims overlap. They are separate views and are never added into a combined business-volume or spend figure.',
            'Pipeline enquiry values, standard-cost stock valuation and unreceived purchasing commitments are estimates. There is no approved probability model, dated sales-target history or integrated cash forecast; forecasts and target achievement are unavailable.',
            'Company switching remains in the ERP session. No cross-company consolidation or currency conversion across companies is implied. Branch means customer branch, location means stock location; petty-cash tab types are cost-center proxies, not a department master.',
            'Product-level historical gross margin, delivery service levels, historical stock aging, fixed-asset roll-forwards and project profitability require validated source lineage and cost/status history. Current coverage and evidence are documented in docs/directors-reporting-business-audit.md and docs/directors-reporting-finance-audit.md.',
            'Source rows with invalid exchange rates or ambiguous joins are excluded from monetary totals and counted explicitly. Review unknown due dates, missing costs and receipt/approval exceptions before acting on the figures.',
        );
    }
}
