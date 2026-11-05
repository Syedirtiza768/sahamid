<?php

namespace SAHamid\BI\Directors;

/** Rows for the XLSX exports, built only from already-authorized service output. */
class ExportRows
{
    /** Overview export: every measure, then the attention items and data-health items. */
    public static function summary(array $overview)
    {
        $rows = $overview['metrics'];
        foreach ($overview['attention'] as $item) {
            $rows[] = array('section' => 'Needs attention', 'title' => $item['title'], 'value' => $item['amount'], 'unit' => $item['unit'],
                'kind' => 'Change or exposure above the screening threshold', 'definition' => $item['message'], 'caveat' => 'Severity: ' . $item['severity']);
        }
        foreach ($overview['data_health'] as $item) {
            $rows[] = array('section' => 'Data health', 'title' => $item['title'], 'kind' => 'Records the figures leave out', 'definition' => $item['message']);
        }
        return $rows;
    }

    /** Records export: the filtered rows, then one total row for the whole filtered set (same query as the screen). */
    public static function records(array $exploration)
    {
        $rows = $exploration['details']['rows'];
        $summary = $exploration['summary'];
        $total = array('id' => '', 'reference' => 'TOTAL (' . count($rows) . ' records)', 'amount' => $summary['value'] === null ? 'Unavailable' : $summary['value']);
        if ($summary['invalid_amounts'] > 0) { $total['status'] = $summary['invalid_amounts'] . ' amounts excluded from the total'; }
        $rows[] = $total;
        return $rows;
    }
}
