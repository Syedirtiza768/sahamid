<?php

namespace SAHamid\BI\Directors;

/**
 * Turns one authorized source row into labelled, grouped, formatted fields for the
 * evidence page. Pure: no database, no HTML. The page escapes every value it prints.
 *
 * Kinds: text, money, number, date, flag, missing. A missing value is never shown as
 * zero or blank; it is shown as "Not recorded".
 */
class RecordPresenter
{
    private static $labels = array(
        'id' => 'Source row ID', 'reference' => 'Reference', 'date' => 'Date', 'due_date' => 'Due date', 'transaction_number' => 'Transaction number',
        'source_type' => 'Transaction type', 'source_key' => 'Source key', 'status' => 'Status', 'amount' => 'Amount', 'quantity' => 'Quantity',
        'overdue_amount' => 'Overdue amount', 'quality_issue' => 'Needs review', 'customer' => 'Customer', 'branch' => 'Customer branch',
        'supplier' => 'Supplier', 'salesperson' => 'Salesperson', 'project' => 'Sales case / job', 'location' => 'Location', 'bank' => 'Bank account',
        'owner' => 'Owner', 'tab' => 'Petty-cash tab', 'department' => 'Tab type', 'category' => 'Category', 'account' => 'GL account',
        'section' => 'Account section', 'approval' => 'Approval / posting', 'receipt' => 'Receipt evidence', 'currency' => 'Currency',
        'due_status' => 'Due status', 'age_bucket' => 'Age', 'accounting_period' => 'Accounting period', 'period_start' => 'Period start',
        'product' => 'Product', 'product_label' => 'Product description', 'brand' => 'Brand', 'stage' => 'Stage', 'eta' => 'ETA', 'publication' => 'Publication',
    );
    private static $groups = array(
        'Document' => array('id', 'reference', 'date', 'due_date', 'period_start', 'accounting_period', 'transaction_number', 'source_type', 'source_key'),
        'Who and where' => array('customer', 'branch', 'supplier', 'salesperson', 'owner', 'tab', 'department', 'project', 'location', 'bank'),
        'What' => array('product', 'product_label', 'brand', 'category', 'account', 'section', 'stage'),
        'Amounts' => array('amount', 'overdue_amount', 'quantity', 'currency'),
        'Status and quality' => array('status', 'due_status', 'age_bucket', 'approval', 'receipt', 'publication', 'eta', 'quality_issue'),
    );

    /** @return array list of array('title' => string, 'fields' => list of array('label', 'kind', 'value')) */
    public static function present(array $row, $unit)
    {
        $used = array();
        $out = array();
        foreach (self::$groups as $title => $keys) {
            $fields = array();
            foreach ($keys as $key) {
                if (!array_key_exists($key, $row)) { continue; }
                $used[$key] = true;
                $fields[] = self::field($key, $row[$key], $unit);
            }
            if ($fields) { $out[] = array('title' => $title, 'fields' => $fields); }
        }
        $other = array();
        foreach ($row as $key => $value) {
            if (!isset($used[$key])) { $other[] = self::field($key, $value, $unit); }
        }
        if ($other) { $out[] = array('title' => 'Other source fields', 'fields' => $other); }
        return $out;
    }

    private static function field($key, $value, $unit)
    {
        $label = isset(self::$labels[$key]) ? self::$labels[$key] : ucfirst(str_replace('_', ' ', $key));
        if ($value === null || $value === '' || (is_string($value) && strpos($value, '0000-00-00') === 0)) {
            return array('label' => $label, 'kind' => 'missing', 'value' => 'Not recorded');
        }
        if ($key === 'quality_issue') {
            return array('label' => $label, 'kind' => 'flag', 'value' => (int) $value === 1 ? 'Yes: the source record has a data-quality exception' : 'No exception');
        }
        if (is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})( \d{2}:\d{2}:\d{2})?$/', $value, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return array('label' => $label, 'kind' => 'date', 'value' => date('j M Y', mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1])));
        }
        if (in_array($key, array('amount', 'overdue_amount'), true) && is_numeric($value)) {
            return array('label' => $label . ($unit !== '' ? ' (' . $unit . ')' : ''), 'kind' => 'money', 'value' => number_format((float) $value, 2));
        }
        if ($key === 'quantity' && is_numeric($value)) {
            return array('label' => $label, 'kind' => 'number', 'value' => rtrim(rtrim(number_format((float) $value, 4), '0'), '.'));
        }
        return array('label' => $label, 'kind' => 'text', 'value' => (string) $value);
    }

    /**
     * A return link is honoured only when it is a path on the directors dashboard itself
     * (no scheme, host or traversal). Anything else returns null.
     */
    public static function safeReturn($candidate, $dashboardPath)
    {
        if (!is_string($candidate) || $candidate === '' || strlen($candidate) > 2000 || preg_match('/[\x00-\x1f\\\\]/', $candidate)) { return null; }
        if ($candidate[0] !== '/' || (isset($candidate[1]) && $candidate[1] === '/')) { return null; }
        $path = parse_url($candidate, PHP_URL_PATH);
        if ($path !== $dashboardPath || parse_url($candidate, PHP_URL_HOST) !== null || parse_url($candidate, PHP_URL_SCHEME) !== null) { return null; }
        return $candidate;
    }
}
