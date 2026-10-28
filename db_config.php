<?php
/*
 * Database credentials: the one place the application reads them from.
 *
 * config.php (session/ConnectDB) reads the variables below. Other pages use the
 * SAHAMID_DB_* constants, which are visible inside functions and classes too.
 * Environment overrides: DB_HOST (Docker) and DB_PASSWORD (rotation without an edit).
 */
$host = getenv('DB_HOST') ?: 'localhost';
$DBUser = 'irtiza';
$DBPassword = getenv('DB_PASSWORD') ?: 'netetech321';

if (!defined('SAHAMID_DB_HOST')) {
	define('SAHAMID_DB_HOST', $host);
	define('SAHAMID_DB_USER', $DBUser);
	define('SAHAMID_DB_PASSWORD', $DBPassword);
}
