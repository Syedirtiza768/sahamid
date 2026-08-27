<?php

/**
 * Read-only production verification command for the invoice BI boundary.
 *
 * Credentials and the target are supplied only through BI_PROD_* environment
 * variables. The command never writes to the database and does not require
 * the ERP web session, but it uses the same reconciliation service as the BI
 * endpoint with an administrator-shaped audit context.
 */

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "This command is available only from the CLI.\n");
	exit(2);
}

require_once dirname(__DIR__) . '/bootstrap.php';

function biProductionEnv($name, $default = null)
{
	$value = getenv($name);
	return $value === false || $value === '' ? $default : $value;
}

function biProductionArguments(array $arguments)
{
	$options = array(
		'start' => biProductionEnv('BI_RECON_START', date('Y-01-01')),
		'end' => biProductionEnv('BI_RECON_END', date('Y-m-d')),
	);
	foreach (array_slice($arguments, 1) as $argument) {
		if (strpos($argument, '--start=') === 0) {
			$options['start'] = substr($argument, 8);
		} elseif (strpos($argument, '--end=') === 0) {
			$options['end'] = substr($argument, 6);
		} else {
			fwrite(STDERR, "Unknown option: {$argument}\nUse --start=YYYY-MM-DD and --end=YYYY-MM-DD.\n");
			exit(2);
		}
	}
	return $options;
}

function biProductionSchema(mysqli $db)
{
	$required = array(
		'invoice' => array('invoiceno', 'invoicesdate', 'returned', 'inprogress', 'gst', 'services', 'salescaseref'),
		'invoiceoptions' => array('invoiceno', 'invoicelineno', 'invoiceoptionno', 'quantity'),
		'invoicedetails' => array('invoiceno', 'invoicelineno', 'invoiceoptionno', 'stkcode', 'unitprice', 'discountpercent', 'quantity', 'narrative'),
		'debtortrans' => array('transno', 'type', 'reversed', 'ovamount'),
		'salescase' => array('salescaseref', 'salesman'),
		'salesman' => array('salesmanname', 'salesmancode'),
	);
	$tables = array();
	$allPresent = true;
	foreach ($required as $table => $columns) {
		$presentColumns = array();
		$result = @mysqli_query($db, 'SHOW COLUMNS FROM `' . $table . '`');
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) {
				$presentColumns[] = $row['Field'];
			}
			mysqli_free_result($result);
		}
		$missing = array_values(array_diff($columns, $presentColumns));
		if ($missing) {
			$allPresent = false;
		}
		$tables[$table] = array(
			'present' => count($missing) === 0,
			'required_columns' => $columns,
			'missing_columns' => $missing,
		);
	}
	return array('compatible' => $allPresent, 'tables' => $tables);
}

$target = array(
	'host' => biProductionEnv('BI_PROD_DB_HOST'),
	'port' => (int) biProductionEnv('BI_PROD_DB_PORT', 3306),
	'user' => biProductionEnv('BI_PROD_DB_USER'),
	'database' => biProductionEnv('BI_PROD_DB_NAME'),
);
$password = biProductionEnv('BI_PROD_DB_PASSWORD', '');
foreach (array('host', 'user', 'database') as $requiredTargetValue) {
	if ($target[$requiredTargetValue] === null) {
		fwrite(STDERR, 'Missing BI_PROD_DB_' . strtoupper($requiredTargetValue) . ".\n");
		exit(2);
	}
}

$options = biProductionArguments($argv);
$db = mysqli_init();
if (!$db || !@mysqli_real_connect($db, $target['host'], $target['user'], $password, $target['database'], $target['port'])) {
	fwrite(STDERR, "The configured production database could not be reached.\n");
	exit(3);
}
mysqli_set_charset($db, 'utf8');

$payload = array(
	'ok' => false,
	'read_only' => true,
	'target' => $target,
	'date_range' => array('start' => $options['start'], 'end' => $options['end']),
	'generated_at_utc' => gmdate('Y-m-d H:i:s'),
	'schema' => biProductionSchema($db),
);

try {
	if (!$payload['schema']['compatible']) {
		$payload['error'] = array('code' => 'schema_incompatible', 'message' => 'The production database is missing one or more required invoice BI columns.');
		echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
		mysqli_close($db);
		exit(4);
	}

	$request = \SAHamid\BI\Query\QueryRequest::fromArray(array(
		'metricIds' => array('sales.invoice_value'),
		'dateRange' => array('start' => $options['start'], 'end' => $options['end']),
		'limit' => 100,
	));
	$context = new \SAHamid\BI\Security\AuthorizationContext(
		'production-read-only-audit',
		$target['database'],
		'Production',
		10,
		true,
		array('*'),
		array()
	);
	$result = (new \SAHamid\BI\Reconciliation\InvoiceReconciliationService($db))->reconcile($request, $context);
	$payload['ok'] = true;
	$payload['data'] = $result->toArray();
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
	mysqli_close($db);
	exit(0);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	$payload['error'] = array('code' => $exception->getErrorCode(), 'message' => $exception->getMessage());
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
	mysqli_close($db);
	exit(5);
} catch (\Throwable $exception) {
	$payload['error'] = array('code' => 'verification_failed', 'message' => 'The production verification could not be completed.');
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
	mysqli_close($db);
	exit(5);
}
