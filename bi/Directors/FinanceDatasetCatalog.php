<?php

namespace SAHamid\BI\Directors;

/**
 * Read-only finance facts at their native primary-key grain.
 *
 * These SQL fragments contain no request input. The only contextual binding is
 * the bank-account user restriction; all outer filters are bound by the report
 * service. Snapshot facts deliberately retain current allocations and are not
 * represented as historical balances.
 */
class FinanceDatasetCatalog
{
    public static function definitions($userId = '')
    {
        $definitions = array();
        $glDimensions = array(
            'account' => 'GL account', 'category' => 'Account group',
            'section' => 'Account section', 'project' => 'Job reference',
            'tag' => 'GL tag', 'transaction_type' => 'Transaction type',
        );
        $glFields = "g.counterindex AS id, g.trandate AS date,
            CONCAT(g.type, '-', g.typeno, ' / ', g.narrative) AS reference,
            1 AS quantity, g.account AS source_key,
            CONCAT(g.account, ' · ', cm.accountname) AS account,
            ag.groupname AS category,
            COALESCE(ac.sectionname, CONCAT('Section ', ag.sectioninaccounts)) AS section,
            COALESCE(NULLIF(TRIM(g.jobref), ''), 'Unassigned') AS project,
            CAST(g.tag AS CHAR) AS tag, CAST(g.type AS CHAR) AS transaction_type,
            g.periodno AS accounting_period, g.typeno AS transaction_number,
            'actual' AS status";
        $glFrom = ' FROM gltrans g
            INNER JOIN chartmaster cm ON cm.accountcode = g.account
            INNER JOIN accountgroups ag ON ag.groupname = cm.group_
            LEFT JOIN accountsection ac ON ac.sectionid = ag.sectioninaccounts
            WHERE g.posted = 1 AND ag.pandl = 1';
        $glMeasures = array(
            'gl_result' => array('Net profit / loss', '-g.amount', '', 'Negative sum of posted P&L journal amounts; a positive value is profit.'),
            'gl_revenue' => array('GL revenue', '-g.amount', ' AND ag.sectioninaccounts = 1', 'Negative sum of posted income-section journal amounts (section 1).'),
            'gl_cost_of_sales' => array('Cost of sales', 'g.amount', ' AND ag.sectioninaccounts = 2', 'Signed debit sum of posted cost-of-sales journals (section 2).'),
            'gl_expenses' => array('Operating / other P&L costs', 'g.amount', ' AND ag.sectioninaccounts NOT IN (1, 2)', 'Signed debit sum of posted P&L journals outside income and cost of sales.'),
        );
        foreach ($glMeasures as $key => $measure) {
            $definitions[$key] = array(
                'title' => $measure[0], 'unit' => 'base', 'kind' => 'flow', 'status' => 'actual',
                'source' => 'gltrans → chartmaster → accountgroups; accountsection',
                'definition' => $measure[3],
                'caveat' => 'Journal-date basis, posted=1 only. The legacy P&L uses accounting periods and chartdetails; locked-period posting can move a journal into a different accounting period. Unposted journals and unmapped accounts are excluded. Petty-cash claims and operational invoice values are separate measures and must not be added to GL costs or revenue.',
                'route' => 'GLProfit_Loss.php', 'dimensions' => $glDimensions,
                'sql' => 'SELECT ' . $glFields . ', ' . $measure[1] . ' AS amount' . $glFrom . $measure[2],
                'params' => array(), 'types' => '',
                'source_route' => 'GLTransInquiry.php', 'source_param' => 'TransNo',
            );
        }

        // Operational invoice numbers are not unique in the schema. Aggregate
        // before joining, and never pick an arbitrary due date from duplicates.
        $arDue = "CASE WHEN t.type = 10 AND inv.document_count = 1 THEN inv.due_date
            WHEN t.type = 750 AND shop.document_count = 1 THEN shop.due_date ELSE NULL END";
        $arBalance = '(t.ovamount + t.ovgst + t.ovfreight + t.ovdiscount - t.alloc)';
        $arAmount = 'CASE WHEN t.rate > 0 THEN ' . $arBalance . ' / t.rate ELSE NULL END';
        $definitions['receivables'] = array(
            'title' => 'Current net receivables', 'unit' => 'base', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'debtortrans; debtorsmaster; unique invoice / shopsale due dates',
            'definition' => 'Current signed open ledger balance = (ovamount + ovgst + ovfreight + ovdiscount − alloc) / transaction rate for non-reversed transactions. Unapplied receipts and credit balances remain negative.',
            'caveat' => 'Current snapshot using live allocations, irrespective of selected flow period. No historical balance comparison is claimed. Missing or non-positive exchange rates exclude the row amount. Overdue exposure includes positive balances with a unique valid operational due date; unmatched, duplicate and other transaction types have unknown due dates. Net receivables and gross overdue exposure therefore answer different questions.',
            'route' => 'AgedDebtors.php',
            'dimensions' => array('customer' => 'Customer', 'branch' => 'Customer branch', 'salesperson' => 'Ledger salesperson code', 'transaction_type' => 'Transaction type', 'currency' => 'Customer currency', 'age_bucket' => 'Overdue age', 'due_status' => 'Due status', 'settlement' => 'Stored settlement flag'),
            'sql' => "SELECT t.id, DATE(t.trandate) AS date,
                CONCAT(t.type, '-', t.transno, ' / ', t.reference) AS reference,
                " . $arAmount . " AS amount, 1 AS quantity, t.debtorno AS source_key,
                CONCAT(t.debtorno, ' · ', COALESCE(d.name, 'Missing customer')) AS customer,
                CONCAT(t.debtorno, '/', COALESCE(NULLIF(t.branchcode, ''), 'Unassigned')) AS branch,
                COALESCE(NULLIF(t.salesperson, ''), 'Unassigned') AS salesperson,
                CAST(t.type AS CHAR) AS transaction_type,
                COALESCE(NULLIF(d.currcode, ''), 'Unknown') AS currency,
                " . $arDue . " AS due_date,
                " . self::ageBucketSql($arDue, $arBalance) . " AS age_bucket,
                " . self::dueStatusSql($arDue, $arBalance) . " AS due_status,
                " . self::dueStatusSql($arDue, $arBalance) . " AS status,
                CASE WHEN t.settled=1 THEN 'Settled with residual' ELSE 'Open flag' END AS settlement,
                CASE WHEN t.settled=1 OR d.debtorno IS NULL THEN 1 ELSE 0 END AS quality_issue,
                CASE WHEN " . $arBalance . " > 0 AND " . $arDue . " < CURRENT_DATE
                    THEN " . $arAmount . " ELSE 0 END AS overdue_amount,
                t.type AS source_type, t.transno AS transaction_number
                FROM debtortrans t
                LEFT JOIN debtorsmaster d ON d.debtorno = t.debtorno
                LEFT JOIN (
                    SELECT invoiceno, debtorno, branchcode, COUNT(*) AS document_count,
                        MAX(CASE WHEN due >= '1000-01-01' THEN due ELSE NULL END) AS due_date
                    FROM invoice GROUP BY invoiceno, debtorno, branchcode
                ) inv ON t.type = 10 AND inv.invoiceno = t.transno
                    AND inv.debtorno = t.debtorno AND inv.branchcode = t.branchcode
                LEFT JOIN (
                    SELECT orderno, debtorno, branchcode, COUNT(*) AS document_count,
                        MAX(CASE WHEN due >= '1000-01-01' THEN due ELSE NULL END) AS due_date
                    FROM shopsale GROUP BY orderno, debtorno, branchcode
                ) shop ON t.type = 750 AND shop.orderno = t.transno
                    AND shop.debtorno = t.debtorno AND shop.branchcode = t.branchcode
                WHERE COALESCE(t.reversed, 0) = 0 AND ABS(" . $arBalance . ") > 0.000001",
            'params' => array(), 'types' => '',
            'source_route' => 'CustomerInquiry.php', 'source_param' => 'CustomerID',
        );

        $apBalance = '(t.ovamount + t.ovgst - t.alloc)';
        $apAmount = 'CASE WHEN t.rate > 0 THEN ' . $apBalance . ' / t.rate ELSE NULL END';
        // Preserve explicit due dates and terms semantics. Clamp the following
        // month's target day, fixing the legacy month-end overflow behavior.
        $apDue = "CASE WHEN t.duedate >= '1000-01-01' THEN t.duedate
            WHEN pt.daysbeforedue > 0 THEN DATE_ADD(DATE(t.trandate), INTERVAL pt.daysbeforedue DAY)
            WHEN pt.dayinfollowingmonth BETWEEN 1 AND 31 THEN
                DATE_ADD(LAST_DAY(t.trandate),
                    INTERVAL LEAST(pt.dayinfollowingmonth,
                        DAYOFMONTH(LAST_DAY(DATE_ADD(t.trandate, INTERVAL 1 MONTH)))) DAY)
            ELSE NULL END";
        $definitions['payables'] = array(
            'title' => 'Current net payables', 'unit' => 'base', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'supptrans; suppliers; paymentterms',
            'definition' => 'Current signed open AP ledger balance = (ovamount + ovgst − alloc) / transaction rate for non-reversed transactions. Unallocated payments and supplier credit balances remain negative.',
            'caveat' => 'Current live-allocation snapshot with no historical comparison. Positive due exposure uses the stored due date, falling back to current payment terms; fallback dates are estimates, shown by due basis. Following-month due days are clamped to the actual next month, correcting the legacy supplier helper month-end overflow. Non-positive rates exclude amounts. Net payables and gross overdue exposure differ because supplier credits are not netted against specific invoices.',
            'route' => 'AgedSuppliers.php',
            'dimensions' => array('supplier' => 'Supplier', 'category' => 'Supplier type', 'transaction_type' => 'Transaction type', 'currency' => 'Supplier currency', 'age_bucket' => 'Overdue age', 'due_status' => 'Due status', 'due_basis' => 'Due-date basis', 'hold' => 'Payment hold', 'settlement' => 'Stored settlement flag'),
            'sql' => "SELECT t.id, DATE(t.trandate) AS date,
                CONCAT(t.type, '-', t.transno, ' / ', t.suppreference) AS reference,
                " . $apAmount . " AS amount, 1 AS quantity, t.supplierno AS source_key,
                CONCAT(t.supplierno, ' · ', COALESCE(s.suppname, 'Missing supplier')) AS supplier,
                COALESCE(CAST(s.supptype AS CHAR), 'Unknown') AS category,
                CAST(t.type AS CHAR) AS transaction_type,
                COALESCE(NULLIF(s.currcode, ''), 'Unknown') AS currency,
                " . $apDue . " AS due_date,
                " . self::ageBucketSql($apDue, $apBalance) . " AS age_bucket,
                " . self::dueStatusSql($apDue, $apBalance) . " AS due_status,
                " . self::dueStatusSql($apDue, $apBalance) . " AS status,
                CASE WHEN t.settled=1 THEN 'Settled with residual' ELSE 'Open flag' END AS settlement,
                CASE WHEN t.settled=1 OR s.supplierid IS NULL THEN 1 ELSE 0 END AS quality_issue,
                CASE WHEN t.duedate >= '1000-01-01' THEN 'Recorded'
                    WHEN pt.daysbeforedue > 0 OR pt.dayinfollowingmonth BETWEEN 1 AND 31 THEN 'Estimated from current terms'
                    ELSE 'Unavailable' END AS due_basis,
                CASE WHEN t.hold = 1 THEN 'On hold' ELSE 'Not held' END AS hold,
                CASE WHEN " . $apBalance . " > 0 AND " . $apDue . " < CURRENT_DATE
                    THEN " . $apAmount . " ELSE 0 END AS overdue_amount,
                t.type AS source_type, t.transno AS transaction_number
                FROM supptrans t
                LEFT JOIN suppliers s ON s.supplierid = t.supplierno
                LEFT JOIN paymentterms pt ON pt.termsindicator = s.paymentterms
                WHERE COALESCE(t.reversed, 0) = 0 AND ABS(" . $apBalance . ") > 0.000001",
            'params' => array(), 'types' => '',
            'source_route' => 'SupplierInquiry.php', 'source_param' => 'SupplierID',
        );

        $definitions['cash_movement'] = array(
            'title' => 'Authorized bank net movement', 'unit' => 'base', 'kind' => 'flow', 'status' => 'actual',
            'source' => 'banktrans; bankaccounts; bankaccountusers',
            'definition' => 'Signed bank transaction amount / exrate / functionalexrate, matching DailyBankTransactions. Positive amounts are inflows and negative amounts outflows. Accounts are always restricted to bankaccountusers for the signed-in user.',
            'caveat' => 'Movement in authorized bank accounts, not reconciled cash balance, free cash flow or a statutory cash-flow statement. Inter-account transfers can contribute to gross inflow/outflow and incomplete account authorization can affect net transfer cancellation. Both source rates must be positive; invalid rates exclude the amount.',
            'route' => 'DailyBankTransactions.php',
            'dimensions' => array('bank' => 'Authorized bank account', 'transaction_type' => 'Transaction type', 'category' => 'Bank transaction method', 'currency' => 'Transaction currency', 'clearance' => 'Cleared status'),
            'sql' => "SELECT t.banktransid AS id, t.transdate AS date,
                CONCAT(t.type, '-', t.transno, ' / ', t.ref) AS reference,
                CASE WHEN t.exrate > 0 AND t.functionalexrate > 0
                    THEN t.amount / t.exrate / t.functionalexrate ELSE NULL END AS amount,
                1 AS quantity, t.bankact AS source_key,
                CONCAT(t.bankact, ' · ', b.bankaccountname) AS bank,
                CAST(t.type AS CHAR) AS transaction_type,
                COALESCE(NULLIF(t.banktranstype, ''), 'Unspecified') AS category,
                COALESCE(NULLIF(t.currcode, ''), 'Unknown') AS currency,
                CASE WHEN ABS(t.amount - t.amountcleared) < 0.000001 THEN 'Cleared' ELSE 'Uncleared / partial' END AS clearance,
                'actual' AS status, t.type AS source_type, t.transno AS transaction_number
                FROM banktrans t INNER JOIN bankaccounts b ON b.accountcode = t.bankact
                WHERE EXISTS (SELECT 1 FROM bankaccountusers bu
                    WHERE bu.accountcode = t.bankact AND bu.userid = ?)",
            'params' => array((string) $userId), 'types' => 's',
            'source_route' => 'GLTransInquiry.php', 'source_param' => 'TransNo',
        );

        $definitions['gl_budget'] = array(
            'title' => 'Recorded P&L budget / planned profit', 'unit' => 'base', 'kind' => 'budget', 'status' => 'estimated',
            'source' => 'chartdetails.budget; periods; chartmaster; accountgroups',
            'definition' => 'Negative sum of recorded P&L account budgets for complete accounting periods wholly contained in the selected range. Zero-only rows do not establish that a budget was set or approved.',
            'caveat' => 'Unversioned recorded budget, not an approved forecast. No daily proration. Comparison with journal-date actual is only indicative because journals can be posted into different accounting periods; use the legacy period P&L for formal reconciliation. The first period without a known predecessor is excluded because its start date cannot be established. Missing budgets remain unavailable.',
            'route' => 'GLBudgets.php',
            'dimensions' => array('account' => 'GL account', 'category' => 'Account group', 'section' => 'Account section'),
            'sql' => "SELECT CONCAT(cd.accountcode, ':', cd.period) AS id,
                p.lastdate_in_period AS date,
                (SELECT DATE_ADD(MAX(previous.lastdate_in_period), INTERVAL 1 DAY)
                    FROM periods previous WHERE previous.lastdate_in_period < p.lastdate_in_period) AS period_start,
                CONCAT('Budget ', cd.period, ' / ', cd.accountcode) AS reference,
                -cd.budget AS amount, 1 AS quantity, cd.accountcode AS source_key,
                CONCAT(cd.accountcode, ' · ', cm.accountname) AS account,
                ag.groupname AS category,
                COALESCE(ac.sectionname, CONCAT('Section ', ag.sectioninaccounts)) AS section,
                cd.period AS accounting_period, 'estimated' AS status
                FROM chartdetails cd
                INNER JOIN periods p ON p.periodno = cd.period
                INNER JOIN chartmaster cm ON cm.accountcode = cd.accountcode
                INNER JOIN accountgroups ag ON ag.groupname = cm.group_
                LEFT JOIN accountsection ac ON ac.sectionid = ag.sectioninaccounts
                WHERE ag.pandl = 1",
            'params' => array(), 'types' => '',
            'source_route' => 'GLBudgets.php', 'source_param' => 'SelectedAccount',
        );

        $definitions['gl_budget_actual'] = $definitions['gl_budget'];
        $definitions['gl_budget_actual']['title'] = 'Recorded period P&L actual';
        $definitions['gl_budget_actual']['status'] = 'actual';
        $definitions['gl_budget_actual']['source'] = 'chartdetails.actual; periods; chartmaster; accountgroups';
        $definitions['gl_budget_actual']['definition'] = 'Negative sum of recorded P&L actuals for complete accounting periods wholly contained in the selected range; compare with recorded budgets on the identical period basis.';
        $definitions['gl_budget_actual']['caveat'] = 'Accounting-period actual from chartdetails, matching the legacy period P&L. The daily journal-date profit measure may differ when posting periods shift. Partial periods and the first period without a known predecessor are excluded. Formal reconciliation depends on the operational GL posting process having updated chartdetails.';
        $definitions['gl_budget_actual']['route'] = 'GLProfit_Loss.php';
        $definitions['gl_budget_actual']['sql'] = str_replace(
            array('-cd.budget AS amount', "'estimated' AS status", "CONCAT('Budget ', cd.period"),
            array('-cd.actual AS amount', "'actual' AS status", "CONCAT('Actual ', cd.period"),
            $definitions['gl_budget']['sql']
        );
        $definitions['gl_budget_actual']['source_route'] = 'GLProfit_Loss.php';
        $definitions['gl_budget_actual']['source_param'] = '';

        return $definitions;
    }

    private static function dueStatusSql($due, $balance)
    {
        return "CASE WHEN " . $balance . " < 0 THEN 'credit'
            WHEN (" . $due . ") IS NULL THEN 'unknown_due'
            WHEN (" . $due . ") < CURRENT_DATE THEN 'overdue' ELSE 'open' END";
    }

    private static function ageBucketSql($due, $balance)
    {
        return "CASE WHEN " . $balance . " < 0 THEN 'Credit / unapplied'
            WHEN (" . $due . ") IS NULL THEN 'Unknown due date'
            WHEN (" . $due . ") >= CURRENT_DATE THEN 'Not overdue'
            WHEN DATEDIFF(CURRENT_DATE, (" . $due . ")) <= 30 THEN '1–30 days'
            WHEN DATEDIFF(CURRENT_DATE, (" . $due . ")) <= 60 THEN '31–60 days'
            WHEN DATEDIFF(CURRENT_DATE, (" . $due . ")) <= 90 THEN '61–90 days'
            ELSE 'Over 90 days' END";
    }
}
