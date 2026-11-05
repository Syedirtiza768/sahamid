<?php

// Read-only company-scoped endpoint shared by overview, investigation and XLSX.
$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
require_once $BiRootPath . '/config.php';
if (isset($SessionSavePath)) { session_save_path($SessionSavePath); }
session_start();
require_once $BiRootPath . '/bi/bootstrap.php';
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

function directorsResponse(array $payload, $status = 200)
{
    if (function_exists('ob_gzhandler') && !ini_get('zlib.output_compression') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) { ob_start('ob_gzhandler'); }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
    directorsResponse(array('ok' => false, 'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')), 401);
}
if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'), true)) {
    header('Allow: GET, POST');
    directorsResponse(array('ok' => false, 'error' => array('code' => 'method_not_allowed', 'message' => 'Use GET or POST for reporting.')), 405);
}
session_write_close();
$AllowAnyone = true;
require_once $BiRootPath . '/includes/session.inc';

try {
    $input = $_GET;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = file_get_contents('php://input', false, null, 0, 65537);
        if (strlen($body) > 65536) { throw new \SAHamid\BI\Exception\BIException('invalid_request', 'The report request is too large.', 400); }
        $input = json_decode($body, true);
        if (!is_array($input)) { throw new \SAHamid\BI\Exception\BIException('invalid_request', 'Use a JSON object for reporting filters.', 400); }
    }
    $context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
    (new \SAHamid\BI\Directors\DirectorsAuthorization($context, $_SESSION))->assertAccess();
    $request = \SAHamid\BI\Directors\DirectorsRequest::fromArray($input, isset($_SESSION['YearEnd']) ? $_SESSION['YearEnd'] : 12);
    $currency = isset($_SESSION['CompanyRecord']['currencydefault']) ? $_SESSION['CompanyRecord']['currencydefault'] : 'Unknown';
    $reportRoot = substr($_SERVER['SCRIPT_NAME'], 0, -strlen('/v2/bi/api/directors.php')) . '/';
    $service = new \SAHamid\BI\Directors\DirectorsService($db, $context, $_SESSION, $currency, null, $reportRoot);
    $service->useCache(new \SAHamid\BI\Directors\ResultCache(), !empty($input['refresh']));

    if ($request->action === 'export' || $request->action === 'export_summary') {
        $isSummary = $request->action === 'export_summary';
        $data = $isSummary ? $service->overview($request) : $service->explore($request, true);
        $period = $data['metadata']['period'];
        $metadata = array('Company' => $context->getCompanyName(), 'Generated UTC' => $data['metadata']['generated_at_utc'],
            'Period' => $period['start'] . ' through ' . $period['end'],
            'Comparison' => ($period['comparison_start'] ?: 'None') . ' through ' . ($period['comparison_end'] ?: 'None'),
            'Scope' => $data['metadata']['scope'],
            'Active filters' => json_encode($request->filters, JSON_UNESCAPED_UNICODE), 'Search' => $request->search,
            'Definitions' => $isSummary ? 'See individual metric definitions and caveats below.' : $data['metric']['definition'],
            'Limitations' => $isSummary ? implode(' ', $data['limitations']) : $data['metric']['caveat']);
        if ($isSummary) {
            $columns = array('section' => 'Reporting area', 'title' => 'Metric', 'value' => 'Value', 'unit' => 'Unit', 'previous' => 'Comparison',
                'delta' => 'Absolute change', 'percent' => 'Change %', 'kind' => 'Period / snapshot / budget', 'status' => 'Actual / estimated',
                'rows' => 'Source rows', 'invalid_amounts' => 'Excluded amounts', 'overdue' => 'Overdue exposure', 'error' => 'Unavailable reason',
                'definition' => 'Definition', 'caveat' => 'Caveat');
            $rows = \SAHamid\BI\Directors\ExportRows::summary($data);
        } else {
            $columns = array('id' => 'Source row ID', 'date' => 'Source date', 'reference' => 'Reference', 'amount' => 'Amount (' . $data['metric']['unit'] . ')', 'quantity' => 'Source quantity', 'status' => 'Source status');
            $columns = array_merge($columns, $data['dimensions'], array('source_url' => 'Source record'));
            $rows = \SAHamid\BI\Directors\ExportRows::records($data);
            foreach (array('due_date' => 'Due date', 'accounting_period' => 'Accounting period', 'transaction_number' => 'Transaction number', 'quality_issue' => 'Quality exception', 'product_label' => 'Product description') as $field => $label) {
                if ($rows && array_key_exists($field, $rows[0])) { $columns[$field] = $label; }
            }
            $metadata['Value completeness'] = $data['metric']['completeness'] . '; excluded amounts: ' . $data['summary']['invalid_amounts'];
            $metadata['Business source dates'] = ($data['summary']['first_record_date'] ?: 'Unavailable') . ' through ' . ($data['summary']['last_record_date'] ?: 'Unavailable');
            if ($data['metric']['kind'] === 'snapshot') { $metadata['Period applicability'] = 'Current snapshot; flow dates do not constrain these records.'; }
            if ($data['metric']['kind'] === 'budget') { $metadata['Period applicability'] = 'Complete accounting periods only; no daily proration.'; }
        }
        (new \SAHamid\BI\Export\XlsxExporter())->download('directors-' . ($isSummary ? 'summary' : $request->dataset) . '-' . $period['end'],
            'Directors Reporting — ' . ($isSummary ? 'Filtered summary' : $data['metric']['title']), $metadata, $columns, $rows);
    }
    $data = $request->action === 'overview' ? $service->overview($request) : $service->explore($request);
    directorsResponse(array('ok' => true, 'data' => $data));
} catch (\SAHamid\BI\Exception\BIException $e) {
    directorsResponse(array('ok' => false, 'error' => array('code' => $e->getErrorCode(), 'message' => $e->getMessage())), $e->getHttpStatus());
} catch (\Throwable $e) {
    error_log('[directors-api] ' . get_class($e) . ': ' . $e->getMessage());
    directorsResponse(array('ok' => false, 'error' => array('code' => 'report_unavailable', 'message' => 'The reporting source is temporarily unavailable. Refresh or contact an administrator.')), 503);
}
