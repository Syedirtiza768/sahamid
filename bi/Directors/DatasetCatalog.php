<?php

namespace SAHamid\BI\Directors;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Security\AuthorizationContext;

class DatasetCatalog
{
    private $definitions;

    public function __construct(AuthorizationContext $context)
    {
        $this->definitions = array_merge(FinanceDatasetCatalog::definitions($context->getUserId()),
            OperationsDatasetCatalog::definitions($context->getUserId(), $context->isAdministrator()));
        $restricted = $context->isAdministrator() ? '' : ' AND EXISTS (SELECT 1 FROM expense_listing_access a WHERE a.user = ? AND a.can_access = d.tabcode)';
        $this->definitions['petty_cash'] = array(
            'title' => 'Petty-cash net claims', 'unit' => 'PKR', 'kind' => 'flow', 'status' => 'actual',
            'section' => 'Expenses', 'route' => 'PcReportTab.php',
            'source' => 'pcashdetails → pctabs → pcexpenses; expense_listing_access',
            'definition' => 'Signed net claims = −amount, excluding ASSIGNCASH funding transfers; includes local purchases, matching Expense Intelligence default.',
            'caveat' => 'PKR reporting policy matches Expense Intelligence; currency metadata mismatches are flagged. Includes unposted claims and balance-sheet/local-purchase items, so this is not P&L expense. Do not add it to GL expenses or MPIW purchasing. Delegated directors see only assigned expense tabs.',
            'dimensions' => array('category' => 'Expense description', 'owner' => 'Tab owner', 'department' => 'Petty-cash tab type (cost-center proxy)',
                'tab' => 'Petty-cash tab', 'account' => 'Expense GL account', 'approval' => 'Approval / posting', 'receipt' => 'Receipt evidence', 'currency' => 'Tab currency metadata'),
            'sql' => "SELECT d.counterindex AS id, d.date, CONCAT(d.counterindex, ' / ', d.notes) AS reference,
                -d.amount AS amount, 1 AS quantity, d.counterindex AS source_key,
                COALESCE(e.description, 'Missing expense definition') AS category,
                COALESCE(t.usercode, 'Missing tab') AS owner, COALESCE(t.typetabcode, 'Unassigned') AS department,
                d.tabcode AS tab, COALESCE(e.glaccount, 'Unmapped') AS account,
                CASE WHEN d.posted = 1 THEN 'posted' WHEN d.authorized IS NULL OR d.authorized = '0000-00-00'
                    THEN 'pending_authorization' ELSE 'authorized_unposted' END AS approval,
                CASE WHEN COALESCE(TRIM(d.receipt), '') = '' AND COALESCE(TRIM(d.receiptimage), '') = ''
                    THEN 'Missing receipt' ELSE 'Recorded receipt' END AS receipt,
                COALESCE(t.currency, 'Unknown') AS currency,
                CASE WHEN e.codeexpense IS NULL OR t.tabcode IS NULL OR COALESCE(t.currency, '') <> 'PKR'
                    OR (COALESCE(TRIM(d.receipt), '') = '' AND COALESCE(TRIM(d.receiptimage), '') = '') THEN 1 ELSE 0 END AS quality_issue,
                CASE WHEN d.posted = 1 THEN 'posted' WHEN d.authorized IS NULL OR d.authorized = '0000-00-00'
                    THEN 'pending_authorization' ELSE 'authorized_unposted' END AS status
                FROM pcashdetails d LEFT JOIN pctabs t ON t.tabcode = d.tabcode
                LEFT JOIN pcexpenses e ON e.codeexpense = d.codeexpense
                WHERE UPPER(TRIM(d.codeexpense)) <> 'ASSIGNCASH'" . $restricted,
            'params' => $context->isAdministrator() ? array() : array($context->getUserId()),
            'types' => $context->isAdministrator() ? '' : 's',
            'source_route' => 'v2/bi/expenses.php', 'source_param' => 'transaction',
        );
        foreach ($this->definitions as $id => &$definition) {
            $definition['id'] = $id;
            $definition += array('params' => array(), 'types' => '', 'section' => $this->section($id));
            $definition['dimensions']['status'] = 'Source status';
            // These are code-owned SQL contracts, never identifiers from a request.
            $definition['has_overdue'] = (bool) preg_match('/\bAS\s+overdue_amount\b/i', $definition['sql']);
            $definition['has_quality'] = (bool) preg_match('/\bAS\s+quality_issue\b/i', $definition['sql']);
        }
        unset($definition);
    }

    public function all() { return $this->definitions; }

    public function get($id)
    {
        if (!isset($this->definitions[$id])) { throw new BIException('unknown_dataset', 'This reporting area is not available.', 404); }
        return $this->definitions[$id];
    }

    private function section($id)
    {
        if (strpos($id, 'gl_') === 0) { return 'Financial performance'; }
        if (in_array($id, array('receivables', 'payables', 'cash_movement'), true)) { return 'Cash & working capital'; }
        if (strpos($id, 'purchas') === 0 || in_array($id, array('legacy_po_commitments', 'procurement_pipeline'), true)) { return 'Purchasing & suppliers'; }
        if (in_array($id, array('inventory', 'stock_exceptions'), true)) { return 'Inventory'; }
        if (in_array($id, array('invoice_value', 'shop_sales', 'posted_invoices', 'po_received', 'outstanding_invoices', 'pending_deliveries', 'open_cases', 'pipeline'), true)) { return 'Sales & customers'; }
        return 'Operations & pipeline';
    }
}
