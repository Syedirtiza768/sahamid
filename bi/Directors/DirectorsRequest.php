<?php

namespace SAHamid\BI\Directors;

use SAHamid\BI\Exception\BIException;

class DirectorsRequest
{
    public $action;
    /** '' = whole overview; finance / sales / operations = one independent slice of it. */
    public $part;
    public $dataset;
    public $period;
    public $filters;
    public $groupBy;
    public $search;
    public $sort;
    public $direction;
    public $page;
    public $pageSize;

    public static function fromArray(array $input, $yearEnd = 12, $today = null)
    {
        $r = new self();
        $now = self::date($today === null ? date('Y-m-d') : $today);
        $fiscalMonth = ((int) $yearEnd % 12) + 1;
        $fiscal = new \DateTimeImmutable($now->format('Y') . '-' . sprintf('%02d', $fiscalMonth) . '-01');
        if ($fiscal > $now) { $fiscal = $fiscal->modify('-1 year'); }
        $r->action = self::choice($input, 'action', array('overview', 'explore', 'export', 'export_summary'), 'overview');
        $r->part = self::scalar($input, 'part', 20, '');
        if ($r->part !== '' && !in_array($r->part, array('finance', 'sales', 'operations'), true)) { self::invalid('Unsupported part.'); }
        $r->dataset = self::scalar($input, 'dataset', 50, 'invoice_value');
        if (!preg_match('/^[a-z_]+$/', $r->dataset)) { self::invalid('Invalid dataset.'); }
        $preset = self::choice($input, 'preset', array('custom', 'month', 'quarter', 'ytd', 'last_month', 'last_quarter', 'last_year'), 'custom');
        $start = $fiscal;
        $end = $now;
        $quarter = $fiscal->modify('+' . (intdiv((int) $fiscal->diff($now)->m + (int) $fiscal->diff($now)->y * 12, 3) * 3) . ' months');
        switch ($preset) {
            case 'month': $start = $now->modify('first day of this month'); break;
            case 'quarter': $start = $quarter; break;
            case 'last_month': $start = $now->modify('first day of previous month'); $end = $start->modify('last day of this month'); break;
            case 'last_quarter': $start = $quarter->modify('-3 months'); $end = $quarter->modify('-1 day'); break;
            case 'last_year': $start = $fiscal->modify('-1 year'); $end = $fiscal->modify('-1 day'); break;
            case 'custom':
                $start = self::date(self::scalar($input, 'start', 10, $fiscal->format('Y-m-d')));
                $end = self::date(self::scalar($input, 'end', 10, $now->format('Y-m-d')));
                break;
        }
        self::range($start, $end, $now);
        $comparison = self::choice($input, 'comparison', array('previous', 'mom', 'qoq', 'yoy', 'custom', 'none'), 'yoy');
        $cs = $ce = null;
        $days = (int) $start->diff($end)->days + 1;
        if ($comparison === 'previous') {
            $ce = $start->modify('-1 day');
            $cs = $ce->modify('-' . ($days - 1) . ' days');
        } elseif (in_array($comparison, array('mom', 'qoq', 'yoy'), true)) {
            $months = array('mom' => -1, 'qoq' => -3, 'yoy' => -12);
            $cs = self::shiftMonths($start, $months[$comparison]);
            $ce = self::shiftMonths($end, $months[$comparison]);
            // A complete calendar/fiscal month or quarter compares to the complete
            // prior block. An incomplete block compares the same elapsed days.
            $block = $comparison === 'qoq' ? 3 : ($comparison === 'yoy' ? 12 : 1);
            if ($start->format('d') === '01' && $end <= $start->modify('+' . $block . ' months')->modify('-1 day')) {
                $cap = $cs->modify('+' . $block . ' months')->modify('-1 day');
                $complete = $end == $start->modify('+' . $block . ' months')->modify('-1 day');
                $ce = $complete ? $cap : min($cs->modify('+' . ($days - 1) . ' days'), $cap);
            }
        } elseif ($comparison === 'custom') {
            $cs = self::date(self::scalar($input, 'comparison_start', 10, ''));
            $ce = self::date(self::scalar($input, 'comparison_end', 10, ''));
        }
        if ($cs !== null) { self::range($cs, $ce, $now); }
        $r->period = array('start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'),
            'comparison_start' => $cs ? $cs->format('Y-m-d') : null, 'comparison_end' => $ce ? $ce->format('Y-m-d') : null,
            'comparison' => $comparison, 'days' => $days, 'comparison_days' => $cs ? (int) $cs->diff($ce)->days + 1 : null,
            'fiscal_start_month' => $fiscalMonth, 'today' => $now->format('Y-m-d'), 'preset' => $preset);
        $filters = isset($input['filters']) ? $input['filters'] : array();
        if (is_string($filters)) { $filters = json_decode($filters, true); }
        if (!is_array($filters) || count($filters) > 10) { self::invalid('Filters must be a dimension-to-value object (maximum 10).'); }
        $r->filters = array();
        foreach ($filters as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-z_]+$/', $key) || !is_scalar($value) || strlen((string) $value) > 150) { self::invalid('Invalid dimension filter.'); }
            $r->filters[$key] = (string) $value;
        }
        $r->groupBy = self::scalar($input, 'group_by', 40, '');
        $r->search = self::scalar($input, 'search', 100, '');
        $r->sort = self::choice($input, 'sort', array('date', 'amount', 'reference'), 'amount');
        $r->direction = self::choice($input, 'direction', array('asc', 'desc'), 'desc');
        $r->page = self::integer($input, 'page', 1, 1, 100000);
        $r->pageSize = self::integer($input, 'page_size', 50, 1, 100);
        return $r;
    }

    public static function change($current, $previous)
    {
        if ($current === null || $previous === null) { return array('delta' => null, 'percent' => null); }
        return array('delta' => $current - $previous, 'percent' => abs($previous) < 0.0000001 ? null : ($current - $previous) / abs($previous) * 100);
    }

    private static function shiftMonths(\DateTimeImmutable $date, $months)
    {
        $first = $date->modify('first day of this month')->modify(($months >= 0 ? '+' : '') . $months . ' months');
        return $first->setDate((int) $first->format('Y'), (int) $first->format('m'), min((int) $date->format('d'), (int) $first->format('t')));
    }

    private static function range($start, $end, $today)
    {
        if ($start > $end || $end > $today || $start->format('Y') < '1900' || $start->diff($end)->days > 1826) {
            self::invalid('Use an ordered date range of at most five years ending today or earlier.');
        }
    }

    private static function date($value)
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        $errors = \DateTimeImmutable::getLastErrors();
        if (!$d || ($errors && ($errors['warning_count'] || $errors['error_count'])) || $d->format('Y-m-d') !== $value) { self::invalid('Dates must be valid YYYY-MM-DD dates.'); }
        return $d;
    }

    private static function scalar(array $input, $key, $max, $default)
    {
        if (!isset($input[$key])) { return $default; }
        if (!is_scalar($input[$key]) || strlen((string) $input[$key]) > $max) { self::invalid('Invalid ' . $key . '.'); }
        return trim((string) $input[$key]);
    }

    private static function choice(array $input, $key, array $choices, $default)
    {
        $v = self::scalar($input, $key, 40, $default);
        if (!in_array($v, $choices, true)) { self::invalid('Unsupported ' . $key . '.'); }
        return $v;
    }

    private static function integer(array $input, $key, $default, $min, $max)
    {
        $v = self::scalar($input, $key, 10, (string) $default);
        if (!ctype_digit($v) || (int) $v < $min || (int) $v > $max) { self::invalid('Invalid ' . $key . '.'); }
        return (int) $v;
    }

    private static function invalid($message) { throw new BIException('invalid_request', $message, 400); }
}
