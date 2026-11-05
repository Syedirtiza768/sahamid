<?php
/** Pure tests for the record evidence presenter and the back-link guard; no database. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';
use SAHamid\BI\Directors\RecordPresenter;

$checks = 0;
function recordCheck($condition, $message) { global $checks; $checks++; if (!$condition) { throw new RuntimeException($message); } echo 'PASS: ' . $message . "\n"; }
function recordField(array $groups, $label) { foreach ($groups as $g) { foreach ($g['fields'] as $f) { if ($f['label'] === $label) { return $f; } } } return null; }
try {
    $groups = RecordPresenter::present(array('id' => '7', 'reference' => '7 / fuel', 'date' => '2026-09-03', 'due_date' => '0000-00-00', 'amount' => '-1234.5',
        'quantity' => '2.5000', 'customer' => 'C1 · <b>Evil</b>', 'quality_issue' => '1', 'receipt' => null, 'odd_field' => 'x'), 'PKR');
    $titles = array_column($groups, 'title');
    recordCheck($titles === array('Document', 'Who and where', 'Amounts', 'Status and quality', 'Other source fields'), 'fields are grouped in a fixed order, unknown fields last');
    recordCheck(recordField($groups, 'Date')['value'] === '3 Sep 2026' && recordField($groups, 'Date')['kind'] === 'date', 'dates are formatted');
    recordCheck(recordField($groups, 'Due date')['kind'] === 'missing' && recordField($groups, 'Due date')['value'] === 'Not recorded', 'a zero date is "Not recorded", not a date');
    recordCheck(recordField($groups, 'Amount (PKR)')['value'] === '-1,234.50', 'money is formatted with its unit and sign');
    recordCheck(recordField($groups, 'Quantity')['value'] === '2.5', 'quantity drops trailing zeros');
    recordCheck(recordField($groups, 'Receipt evidence')['value'] === 'Not recorded', 'a missing value is never shown as blank or zero');
    recordCheck(strpos(recordField($groups, 'Needs review')['value'], 'Yes') === 0, 'quality flag is explained in words');
    recordCheck(recordField($groups, 'Customer')['value'] === 'C1 · <b>Evil</b>', 'values stay raw for the page to escape (the presenter does no HTML)');
    recordCheck(recordField($groups, 'Odd field')['value'] === 'x', 'unknown fields are kept with a readable label');
    $path = '/sahamid/v2/bi/directors.php';
    recordCheck(RecordPresenter::safeReturn($path . '?view=explore&dataset=petty_cash', $path) !== null, 'return link to the dashboard is accepted');
    foreach (array('https://evil.example/sahamid/v2/bi/directors.php', '//evil.example/sahamid/v2/bi/directors.php', '/other.php?x=1', 'javascript:alert(1)', $path . "\r\nSet-Cookie: a=b", "\\evil", '') as $bad) {
        recordCheck(RecordPresenter::safeReturn($bad, $path) === null, 'return link rejected: ' . json_encode($bad));
    }
    echo 'PASS: ' . $checks . " record presenter checks.\n";
} catch (Throwable $e) { fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n"); exit(1); }
