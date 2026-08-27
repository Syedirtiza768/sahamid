<?php

namespace SAHamid\BI\Security;

use SAHamid\BI\Exception\BIException;

class SessionAuthorizationAdapter
{
	private $db;
	private $session;

	public function __construct($db, array $session = null)
	{
		$this->db = $db;
		$this->session = $session === null && isset($_SESSION) && is_array($_SESSION) ? $_SESSION : (array) $session;
	}

	public function resolve()
	{
		$userId = isset($this->session['UserID']) ? trim((string) $this->session['UserID']) : '';
		$databaseName = isset($this->session['DatabaseName']) ? trim((string) $this->session['DatabaseName']) : '';
		if ($userId === '' || $databaseName === '') {
			throw new BIException('unauthorized', 'An authenticated ERP session is required.', 401);
		}

		$permissions = $this->loadPermissions($userId);
		$accessLevel = isset($this->session['AccessLevel']) ? (int) $this->session['AccessLevel'] : 0;
		$isAdministrator = in_array($accessLevel, array(8, 10, 22), true) || in_array('*', $permissions, true);
		$salespersonCode = $this->resolveSalespersonCode($this->session);

		return new AuthorizationContext(
			$userId,
			$databaseName,
			isset($this->session['CompanyName']) ? $this->session['CompanyName'] : '',
			$accessLevel,
			$isAdministrator,
			$permissions,
			isset($this->session['AllowedPageSecurityTokens']) && is_array($this->session['AllowedPageSecurityTokens'])
				? $this->session['AllowedPageSecurityTokens'] : array(),
			$salespersonCode
		);
	}

	private function loadPermissions($userId)
	{
		$stmt = mysqli_prepare($this->db, 'SELECT permission FROM user_permission WHERE userid = ?');
		if (!$stmt) {
			throw new BIException('authorization_unavailable', 'The ERP permission store could not be read.', 503);
		}
		mysqli_stmt_bind_param($stmt, 's', $userId);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('authorization_unavailable', 'The ERP permission store could not be read.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		$permissions = array();
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) {
				$permissions[] = $row['permission'];
			}
			mysqli_free_result($result);
		}
		mysqli_stmt_close($stmt);
		return $permissions;
	}

	private function resolveSalespersonCode(array $session)
	{
		$realName = isset($session['UsersRealName']) ? trim((string) $session['UsersRealName']) : '';
		if ($realName === '') {
			return null;
		}
		$stmt = mysqli_prepare($this->db, 'SELECT salesmancode FROM salesman WHERE TRIM(salesmanname) = TRIM(?) LIMIT 1');
		if (!$stmt) {
			throw new BIException('authorization_unavailable', 'The salesperson scope could not be resolved.', 503);
		}
		mysqli_stmt_bind_param($stmt, 's', $realName);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('authorization_unavailable', 'The salesperson scope could not be resolved.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		$row = $result ? mysqli_fetch_assoc($result) : null;
		if ($result) {
			mysqli_free_result($result);
		}
		mysqli_stmt_close($stmt);
		return $row ? $row['salesmancode'] : null;
	}
}
