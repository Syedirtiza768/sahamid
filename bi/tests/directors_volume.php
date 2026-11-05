<?php
/** Optional synthetic scale test. Requires an explicitly named disposable fixture. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;
try {
    $database = getenv('BI_FIXTURE_DB_NAME');
    if (!$database || !preg_match('/^codex_directors_fixture_[a-z0-9_]+$/', $database) || !getenv('BI_TEST_DB_HOST') || !getenv('BI_TEST_DB_USER')) {
        throw new RuntimeException('An explicit disposable codex_directors_fixture_* database and server are required.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', $database, (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4'); $db->query("SET SESSION sql_mode=''"); $db->query('SET SESSION FOREIGN_KEY_CHECKS=0');
    if (getenv('BI_DIRECTORS_VOLUME_LOAD') === '1') {
        if ((int) $db->query("SELECT COUNT(*) n FROM stockmaster WHERE stockid LIKE 'SCALE%'")->fetch_assoc()['n']) { throw new RuntimeException('Scale fixture already loaded. Run without BI_DIRECTORS_VOLUME_LOAD=1.'); }
        $db->query('CREATE TEMPORARY TABLE director_digits (n INT PRIMARY KEY)');
        $db->query('INSERT INTO director_digits VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9)');
        $db->query('CREATE TEMPORARY TABLE director_numbers (n INT PRIMARY KEY)');
        $db->query('INSERT INTO director_numbers SELECT a.n+10*b.n+100*c.n+1000*d.n+10000*e.n FROM director_digits a CROSS JOIN director_digits b CROSS JOIN director_digits c CROSS JOIN director_digits d CROSS JOIN director_digits e');
        $statements = array(
            "INSERT INTO stockmaster (stockid,description,brand,categoryid,materialcost) SELECT CONCAT('SCALE',n),'Scale item',1,'CAT1',10 FROM director_numbers WHERE n<50000",
            "INSERT INTO locstock (stockid,loccode,quantity,reorderlevel) SELECT CONCAT('SCALE',n.n),CONCAT('L',d.n),10,5 FROM director_numbers n CROSS JOIN director_digits d WHERE n.n<50000 AND d.n<8",
            "INSERT INTO invoice (invoiceindex,invoiceno,invoicesdate,debtorno,branchcode,salescaseref) SELECT 100000+n,100000+n,DATE_ADD('2024-01-01', INTERVAL MOD(n,365) DAY),'C1','B1','SC1' FROM director_numbers WHERE n<30000",
            "INSERT INTO invoiceoptions (invoiceoptionindex,invoiceno,invoicelineno,invoiceoptionno,quantity) SELECT 100000+n,100000+n,1,1,1 FROM director_numbers WHERE n<30000",
            "INSERT INTO invoicedetails (invoicedetailsindex,invoiceno,invoicelineno,invoiceoptionno,stkcode,unitprice,quantity) SELECT 100000+n,100000+MOD(n,30000),1,1,CONCAT('SCALE',MOD(n,50000)),100,1 FROM director_numbers",
            "INSERT INTO gltrans (counterindex,type,typeno,trandate,periodno,account,narrative,amount,posted) SELECT 100000+n,1,100000+n,DATE_ADD('2024-01-01',INTERVAL MOD(n,365) DAY),2,'6000','Scale journal',10,1 FROM director_numbers",
            "INSERT INTO pcashdetails (counterindex,tabcode,date,codeexpense,amount,authorized,posted,receipt,receiptimage,notes) SELECT 100000+n,'T1',DATE_ADD('2024-01-01',INTERVAL MOD(n,365) DAY),'FUEL',-10,'2024-01-01',1,'SCALE','','Scale claim' FROM director_numbers",
            "INSERT INTO dcs (dcindex,orderno,salescaseref,debtorno,branchcode,orddate,courierslipdate,invoicedate,grbdate) SELECT 100000+n,100000+n,'SC1','C1','B1','2024-01-01','0000-00-00','0000-00-00','0000-00-00' FROM director_numbers WHERE n<30000",
            "INSERT INTO dcgroups (dcnos) SELECT CAST(100000+n AS CHAR) FROM director_numbers WHERE n<15000",
        );
        foreach ($statements as $sql) { $db->query($sql); }
        echo "Loaded synthetic scale: 400k stock positions, 30k invoices, 100k details, 100k GL rows, 100k claims, 30k DCs, 15k group lists.\n";
    }
    if (getenv('BI_DIRECTORS_APPLY_FIXTURE_INDEXES') === '1') {
        $db->multi_query(file_get_contents(dirname(__DIR__, 2) . '/migrations/directors_reporting_indexes.sql'));
        do { if ($result = $db->store_result()) { $result->free(); } } while ($db->more_results() && $db->next_result());
    }
    $context = new AuthorizationContext('fixture-director', $database, 'Synthetic scale fixture', 8, true, array('*'), array());
    $base = array('start' => '2024-01-01', 'end' => '2024-12-31', 'comparison' => 'yoy');
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $start = microtime(true); $overview = $service->overview(DirectorsRequest::fromArray($base,12,'2026-11-05'));
    $ids = array_column($overview['metrics'],'id');
    if (count($ids) !== count(array_unique($ids))) { throw new RuntimeException('Duplicate metric on partial failure'); }
    echo 'Overview: ' . (int) round((microtime(true)-$start)*1000) . " ms\n";
    foreach (array('invoice_value','inventory','pending_deliveries','petty_cash','gl_result') as $id) {
        $request = DirectorsRequest::fromArray($base + array('action'=>'explore','dataset'=>$id,'group_by'=>$id==='inventory'?'product':''),12,'2026-11-05');
        $start = microtime(true); $data=$service->explore($request);
        if (count($data['details']['rows'])>100) { throw new RuntimeException('Detail pagination unbounded'); }
        $sum=array_sum(array_column($data['breakdown'],'value'));
        if ($data['summary']['value']!==null && abs($sum-$data['summary']['value'])>0.001) { throw new RuntimeException('Bounded breakdown / Other groups failed reconciliation'); }
        echo $id . ': ' . (int) round((microtime(true)-$start)*1000) . ' ms, ' . $data['summary']['rows'] . ' facts, ' . count($data['details']['rows']) . ' visible rows, ' . count($data['breakdown']) . " groups\n";
        $refused=false;
        try { $service->explore($request,true); } catch (\SAHamid\BI\Exception\BIException $e) { $refused=$e->getErrorCode()==='export_too_large'; }
        if (!$refused) { throw new RuntimeException('Large export was not refused'); }
    }
    echo "PASS: synthetic scale pagination, bounded groups and export limits. Timings describe this machine and fixture only.\n";
} catch (Throwable $e) { fwrite(STDERR,'FAIL: '.$e->getMessage()."\n"); exit(1); }
