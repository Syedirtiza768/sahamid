<?php

namespace SAHamid\BI\Query;

use SAHamid\BI\Security\AuthorizationContext;

class QueryLogger
{
	private $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function log(AuthorizationContext $context, QueryRequest $request, $status, $elapsedMs, $returnedRows, $errorCode = null)
	{
		$probe = @mysqli_query($this->db, "SHOW TABLES LIKE 'bi_query_log'");
		$available = $probe && mysqli_num_rows($probe) > 0;
		if ($probe) {
			mysqli_free_result($probe);
		}
		if (!$available) {
			return false;
		}

		$metricIds = implode(',', $request->getMetricIds());
		$requestFingerprint = hash('sha256', json_encode($request->getCanonicalPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		$stmt = @mysqli_prepare($this->db, 'INSERT INTO bi_query_log (database_name, user_id, metric_ids, request_fingerprint, scope_fingerprint, result_status, error_code, elapsed_ms, returned_rows, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())');
		if (!$stmt) {
			return false;
		}
		$userId = $context->getUserId();
		$databaseName = $context->getDatabaseName();
		$scopeFingerprint = $context->getScopeFingerprint();
		$status = (string) $status;
		$errorCode = $errorCode === null ? null : (string) $errorCode;
		$elapsedMs = $elapsedMs === null ? null : (int) $elapsedMs;
		$returnedRows = $returnedRows === null ? null : (int) $returnedRows;
		mysqli_stmt_bind_param($stmt, 'sssssssii', $databaseName, $userId, $metricIds, $requestFingerprint, $scopeFingerprint, $status, $errorCode, $elapsedMs, $returnedRows);
		$ok = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		return $ok;
	}
}
