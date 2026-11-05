<?php
/**
 * Read-only access-control test for the Directors' dashboard, using the REAL authorization
 * path (SessionAuthorizationAdapter + DirectorsAuthorization + DirectorsService) with each
 * user's real permissions and role tokens read from the target database.
 *
 * Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME. The session is set to READ ONLY.
 * Five ERP user ids, one per role (names are not kept in the repo):
 *   BI_ROLE_ADMIN     administrator (fullaccess 8/10/22 or the '*' permission)
 *   BI_ROLE_DIRECTOR  NOT an administrator, holds directorreports or directors_dashboard
 *   BI_ROLE_LISTING   holds executive_listing but neither directors permission
 *   BI_ROLE_SALES     a salesperson (name in the salesman table), no directors permission
 *   BI_ROLE_NONE      any other user with no directors permission
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DatasetCatalog;
use SAHamid\BI\Directors\DirectorsAuthorization;
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Security\SessionAuthorizationAdapter;

$failures = array();
function roleCheck($condition, $message, array &$failures)
{
    echo ($condition ? 'PASS: ' : 'FAIL: ') . $message . "\n";
    if (!$condition) { $failures[] = $message; }
}
function roleRejects(callable $call, $status, $message, array &$failures)
{
    $caught = null;
    try { $call(); } catch (BIException $e) { $caught = $e; }
    roleCheck($caught !== null && $caught->getHttpStatus() === $status && $caught->getErrorCode() === 'forbidden', $message, $failures);
}
function roleRows($db, $sql, array $params = array(), $types = '')
{
    $statement = $db->prepare($sql);
    if ($types !== '') { $bind = array($types); foreach ($params as $i => $v) { $bind[] = &$params[$i]; } call_user_func_array(array($statement, 'bind_param'), $bind); }
    $statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}
/** The session the ERP login would build for this user. */
function roleSession($db, $userId, $databaseName)
{
    $user = roleRows($db, 'SELECT userid, realname, fullaccess FROM www_users WHERE userid = ?', array($userId), 's');
    if (!$user) { throw new RuntimeException('Unknown ERP user for a role: ' . $userId); }
    $tokens = array_column(roleRows($db, 'SELECT tokenid FROM securitygroups WHERE secroleid = ?', array((int) $user[0]['fullaccess']), 'i'), 'tokenid');
    $pages = array_column(roleRows($db, 'SELECT script, pagesecurity FROM scripts'), 'pagesecurity', 'script');
    return array('UserID' => $userId, 'DatabaseName' => $databaseName, 'UsersRealName' => $user[0]['realname'], 'AccessLevel' => $user[0]['fullaccess'],
        'CompanyName' => 'Role test', 'AllowedPageSecurityTokens' => $tokens, 'PageSecurityArray' => $pages);
}

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME', 'BI_ROLE_ADMIN', 'BI_ROLE_DIRECTOR', 'BI_ROLE_LISTING', 'BI_ROLE_SALES', 'BI_ROLE_NONE') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $name = getenv('BI_TEST_DB_NAME');
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', $name, (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $today = date('Y-m-d');
    $start = date('Y-m-d', strtotime('-1800 days'));
    $period = array('start' => $start, 'end' => $today, 'comparison' => 'none');
    $resolve = function ($key) use ($db, $name) {
        $session = roleSession($db, getenv($key), $name);
        return array($session, (new SessionAuthorizationAdapter($db, $session))->resolve());
    };

    // ---- principals that must be refused at the page gate AND at the API/service ---------------------
    foreach (array('BI_ROLE_LISTING' => 'executive_listing only', 'BI_ROLE_SALES' => 'salesperson', 'BI_ROLE_NONE' => 'no directors permission') as $key => $label) {
        list($session, $context) = $resolve($key);
        $permissions = $context->getPermissions();
        roleCheck(!$context->isAdministrator() && !in_array('directorreports', $permissions, true) && !in_array('directors_dashboard', $permissions, true), "$label: test user really lacks directors access", $failures);
        roleRejects(function () use ($context, $session) { (new DirectorsAuthorization($context, $session))->assertAccess(); }, 403, "$label: page gate refuses", $failures);
        roleRejects(function () use ($db, $context, $session) { new DirectorsService($db, $context, $session, 'PKR'); }, 403, "$label: API/service refuses (overview, drill-down, record and export all start here)", $failures);
    }
    list($session, $listing) = $resolve('BI_ROLE_LISTING');
    roleCheck(in_array('executive_listing', $listing->getPermissions(), true), 'executive_listing user really holds executive_listing (it does not grant Directors access)', $failures);

    // ---- administrator ---------------------------------------------------------------------------------
    list($session, $context) = $resolve('BI_ROLE_ADMIN');
    roleCheck($context->isAdministrator(), 'administrator principal is an administrator', $failures);
    $service = new DirectorsService($db, $context, $session, 'PKR');
    $overview = $service->overview(DirectorsRequest::fromArray($period));
    $blocked = array_filter($overview['metrics'], function ($m) { return isset($m['error']) && strpos((string) $m['error'], 'does not have access') !== false; });
    roleCheck(count($blocked) === 0, 'administrator: no measure is withheld for access', $failures);

    // ---- delegated director: directors permission, not an administrator ------------------------------------
    list($session, $context) = $resolve('BI_ROLE_DIRECTOR');
    roleCheck(!$context->isAdministrator(), 'director principal is NOT an administrator (otherwise this proves nothing)', $failures);
    (new DirectorsAuthorization($context, $session))->assertAccess();
    roleCheck(true, 'director: page gate admits', $failures);
    $service = new DirectorsService($db, $context, $session, 'PKR');
    $authorization = new DirectorsAuthorization($context, $session);
    $catalog = new DatasetCatalog($context);
    $overview = $service->overview(DirectorsRequest::fromArray($period));
    $byId = array_column($overview['metrics'], null, 'id');
    $tokens = $context->getPageSecurityTokens();
    $withoutToken = 0;
    foreach ($catalog->all() as $id => $definition) {
        $route = basename($definition['route']);
        if (!(isset($session['PageSecurityArray'][$route]) && in_array((int) $session['PageSecurityArray'][$route], $tokens, true))) { $withoutToken++; }
        roleCheck($authorization->canRead($definition), "director: $id is readable on the directors permission alone", $failures);
        roleCheck(!isset($byId[$id]['error']) || strpos((string) $byId[$id]['error'], 'does not have access') === false, "director: $id is not withheld in the overview", $failures);
    }
    roleCheck($withoutToken > 0, "director: the role lacks the page token for $withoutToken source pages, so the result does not depend on role tokens", $failures);
    $unknown = null;
    try { $service->explore(DirectorsRequest::fromArray($period + array('dataset' => 'no_such_measure'))); } catch (BIException $e) { $unknown = $e; }
    roleCheck($unknown !== null && $unknown->getHttpStatus() === 404 && $unknown->getErrorCode() === 'unknown_dataset', 'director: an unknown measure is refused with 404', $failures);

    // ---- user-level scopes still apply, whatever the page gate allows ---------------------------------------------
    $userId = $context->getUserId();
    $range = array($start, date('Y-m-d', strtotime($today . ' +1 day')));
    $accounts = (int) roleRows($db, 'SELECT COUNT(*) n FROM bankaccountusers WHERE userid = ?', array($userId), 's')[0]['n'];
    if ($accounts === 0) {
        roleCheck($byId['cash_movement']['available'] === false && strpos((string) $byId['cash_movement']['error'], 'No bank accounts are assigned') !== false, 'director: no assigned bank accounts shows "unavailable", never a reliable zero', $failures);
    } else {
        $expected = (int) roleRows($db, 'SELECT COUNT(*) n FROM banktrans t INNER JOIN bankaccounts b ON b.accountcode = t.bankact WHERE t.transdate >= ? AND t.transdate < ? AND EXISTS (SELECT 1 FROM bankaccountusers bu WHERE bu.accountcode = t.bankact AND bu.userid = ?)', array($range[0], $range[1], $userId), 'sss')[0]['n'];
        $rows = $service->explore(DirectorsRequest::fromArray($period + array('dataset' => 'cash_movement')))['summary']['rows'];
        roleCheck($rows === $expected, "director: bank movement rows ($rows) equal rows on the user's own bank accounts ($expected)", $failures);
    }
    $expected = (int) roleRows($db, "SELECT COUNT(*) n FROM pcashdetails d WHERE UPPER(TRIM(d.codeexpense)) <> 'ASSIGNCASH' AND d.date >= ? AND d.date < ? AND EXISTS (SELECT 1 FROM expense_listing_access a WHERE a.user = ? AND a.can_access = d.tabcode)", array($range[0], $range[1], $userId), 'sss')[0]['n'];
    $rows = $service->explore(DirectorsRequest::fromArray($period + array('dataset' => 'petty_cash')))['summary']['rows'];
    roleCheck($rows === $expected, "director: petty-cash rows ($rows) equal rows on the user's assigned expense tabs ($expected)", $failures);
    $allRows = (int) roleRows($db, "SELECT COUNT(*) n FROM pcashdetails d WHERE UPPER(TRIM(d.codeexpense)) <> 'ASSIGNCASH' AND d.date >= ? AND d.date < ?", array($range[0], $range[1]), 'ss')[0]['n'];
    roleCheck($rows < $allRows, "director: petty cash is narrower than the company total ($rows of $allRows), so the scope really filters", $failures);
    echo $failures ? 'FAILED: ' . count($failures) . " check(s)\n" : "All role checks pass.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
