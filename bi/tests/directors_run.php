<?php
/** Pure date/comparison and permission boundary tests; no database required. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsAuthorization;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;
use SAHamid\BI\Exception\BIException;
$checks = 0;
function directorsCheck($condition, $message) { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } echo 'PASS: ' . $message . "\n"; }
function directorsReject(callable $call, $code, $message) { $rejected = false; try { $call(); } catch (BIException $e) { $rejected = $e->getErrorCode() === $code; } directorsCheck($rejected, $message); }
function directorsPeriod($start, $end, $comparison) { return DirectorsRequest::fromArray(array('start' => $start, 'end' => $end, 'comparison' => $comparison), 6, '2026-11-05')->period; }
try {
    $p = directorsPeriod('2026-02-01', '2026-02-28', 'mom');
    directorsCheck($p['comparison_start'] === '2026-01-01' && $p['comparison_end'] === '2026-01-31', 'Complete February compares to complete January');
    directorsCheck($p['days'] === 28 && $p['comparison_days'] === 31, 'Unequal full-period day counts are explicit');
    $p = directorsPeriod('2026-03-01', '2026-03-15', 'mom');
    directorsCheck($p['comparison_end'] === '2026-02-15', 'Partial month compares equal elapsed days');
    $p = directorsPeriod('2026-01-01', '2026-03-31', 'qoq');
    directorsCheck($p['comparison_start'] === '2025-10-01' && $p['comparison_end'] === '2025-12-31', 'Complete quarter retains complete previous quarter');
    $p = directorsPeriod('2024-02-29', '2024-02-29', 'yoy');
    directorsCheck($p['comparison_start'] === '2023-02-28' && $p['comparison_end'] === '2023-02-28', 'Leap-day shift clamps to valid prior-year day');
    $p = directorsPeriod('2026-03-01', '2026-03-31', 'previous');
    directorsCheck($p['comparison_start'] === '2026-01-29' && $p['comparison_end'] === '2026-02-28', 'Previous equal period crosses month boundaries by days');
    $p = DirectorsRequest::fromArray(array('preset' => 'ytd', 'comparison' => 'yoy'), 6, '2026-11-05')->period;
    directorsCheck($p['start'] === '2026-07-01' && $p['fiscal_start_month'] === 7, 'Configured June year-end determines fiscal YTD');
    directorsCheck($p['comparison_days'] === $p['days'], 'Fiscal YTD compares elapsed prior-year days');
    $p = DirectorsRequest::fromArray(array('preset' => 'quarter'), 6, '2026-11-05')->period;
    directorsCheck($p['start'] === '2026-10-01', 'Quarter-to-date follows fiscal calendar');
    $p = DirectorsRequest::fromArray(array('preset' => 'last_year'), 6, '2026-11-05')->period;
    directorsCheck($p['start'] === '2025-07-01' && $p['end'] === '2026-06-30', 'Last year means complete fiscal year');
    $p = DirectorsRequest::fromArray(array('start' => '2026-01-01', 'end' => '2026-01-31', 'comparison' => 'custom', 'comparison_start' => '2024-01-01', 'comparison_end' => '2024-01-10'), 12, '2026-11-05')->period;
    directorsCheck($p['comparison_days'] === 10 && $p['days'] === 31, 'Custom comparison retains explicit lengths');
    directorsCheck(DirectorsRequest::change(100, 0)['percent'] === null, 'Zero baseline is not infinite or fabricated percent');
    directorsCheck(DirectorsRequest::change(null, 100)['delta'] === null, 'Missing value remains missing change');
    directorsCheck(DirectorsRequest::change(-50, -100)['percent'] === 50.0, 'Negative baseline uses absolute denominator');
    foreach (array(array('start' => '2026-02-30'), array('start' => '2026-11-06'), array('start' => '2026-02-01','end' => '2026-01-01'), array('start' => '2000-01-01'), array('comparison' => 'custom'), array('page_size' => 100000), array('sort' => 'amount; DROP TABLE'), array('filters' => array('customer' => array('C1')))) as $bad) {
        directorsReject(function () use ($bad) { DirectorsRequest::fromArray($bad, 12, '2026-11-05'); }, 'invalid_request', 'Invalid request rejected: ' . json_encode($bad));
    }
    $regular = new AuthorizationContext('sales', 'company_a', 'Company A', 1, false, array('sales_dashboard'), array(2));
    directorsReject(function () use ($regular) { (new DirectorsAuthorization($regular, array()))->assertAccess(); }, 'forbidden', 'Sales dashboard access cannot grant directors data');
    $director = new AuthorizationContext('director', 'company_a', 'Company A', 1, false, array('directorreports'), array(2));
    $auth = new DirectorsAuthorization($director, array('PageSecurityArray' => array('AgedDebtors.php' => 2, 'GLProfit_Loss.php' => 8)));
    directorsCheck($auth->canRead(array('route' => 'AgedDebtors.php')), 'Delegated director retains allowed module');
    directorsCheck(!$auth->canRead(array('route' => 'GLProfit_Loss.php')), 'Delegated director cannot bypass module token');
    directorsCheck(!$auth->canRead(array('route' => 'Unmapped.php')), 'Missing module security mapping fails closed');
    $service = new DirectorsService(null, $director, array(), 'PKR', function () { throw new RuntimeException('No query should run'); });
    directorsReject(function () use ($service) { $service->explore(DirectorsRequest::fromArray(array('dataset' => 'gl_result'),12,'2026-11-05')); }, 'forbidden', 'Drill-through checks access before querying');
    $admin = new AuthorizationContext('admin', 'company_a', 'Company A', 8, true, array('*'), array());
    $service = new DirectorsService(null, $admin, array(), 'PKR', function () { throw new RuntimeException('No query should run'); });
    directorsReject(function () use ($service) { $service->explore(DirectorsRequest::fromArray(array('dataset' => 'inventory','filters' => array('customer' => 'C1')),12,'2026-11-05')); }, 'invalid_dimension', 'Inapplicable dimension cannot silently filter unrelated source');
    directorsReject(function () use ($service) { $service->explore(DirectorsRequest::fromArray(array('group_by' => 'amount` DESC;--'),12,'2026-11-05')); }, 'invalid_dimension', 'Group identifier is allowlisted before query');
    directorsReject(function () use ($service) { $service->overview(DirectorsRequest::fromArray(array('filters' => array('customer' => 'C1')),12,'2026-11-05')); }, 'invalid_filters', 'Overview cannot pretend a dataset-only filter applied company-wide');
    echo 'PASS: ' . $checks . " date, comparison, validation and access checks.\n";
} catch (Throwable $e) { fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n"); exit(1); }
