<?php

namespace SAHamid\BI\Query;

class CacheStore
{
	private $db;
	private $available = null;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function isAvailable()
	{
		if ($this->available !== null) {
			return $this->available;
		}
		$result = @mysqli_query($this->db, "SHOW TABLES LIKE 'bi_query_cache'");
		$this->available = $result && mysqli_num_rows($result) > 0;
		if ($result) {
			mysqli_free_result($result);
		}
		return $this->available;
	}

	public function read($cacheKey)
	{
		if (!$this->isAvailable()) {
			return null;
		}
		$stmt = @mysqli_prepare($this->db, 'SELECT payload FROM bi_query_cache WHERE cache_key = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1');
		if (!$stmt) {
			return null;
		}
		mysqli_stmt_bind_param($stmt, 's', $cacheKey);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			return null;
		}
		$result = mysqli_stmt_get_result($stmt);
		$row = $result ? mysqli_fetch_assoc($result) : null;
		if ($result) {
			mysqli_free_result($result);
		}
		mysqli_stmt_close($stmt);
		if (!$row) {
			return null;
		}
		$payload = json_decode($row['payload'], true);
		return is_array($payload) ? $payload : null;
	}

	public function write($cacheKey, array $payload, $ttlSeconds)
	{
		if (!$this->isAvailable()) {
			return false;
		}
		$encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$ttlSeconds = max(1, (int) $ttlSeconds);
		$expiresAt = gmdate('Y-m-d H:i:s', time() + $ttlSeconds);
		$stmt = @mysqli_prepare($this->db, 'INSERT INTO bi_query_cache (cache_key, payload, created_at, expires_at) VALUES (?, ?, UTC_TIMESTAMP(), ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload), created_at = VALUES(created_at), expires_at = VALUES(expires_at)');
		if (!$stmt) {
			return false;
		}
		mysqli_stmt_bind_param($stmt, 'sss', $cacheKey, $encoded, $expiresAt);
		$ok = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		return $ok;
	}
}
