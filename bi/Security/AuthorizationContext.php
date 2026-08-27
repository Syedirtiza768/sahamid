<?php

namespace SAHamid\BI\Security;

class AuthorizationContext
{
	private $userId;
	private $databaseName;
	private $companyName;
	private $accessLevel;
	private $isAdministrator;
	private $permissions;
	private $pageSecurityTokens;
	private $salespersonCode;

	public function __construct(
		$userId,
		$databaseName,
		$companyName,
		$accessLevel,
		$isAdministrator,
		array $permissions,
		array $pageSecurityTokens,
		$salespersonCode = null
	) {
		$this->userId = (string) $userId;
		$this->databaseName = (string) $databaseName;
		$this->companyName = (string) $companyName;
		$this->accessLevel = (int) $accessLevel;
		$this->isAdministrator = (bool) $isAdministrator;
		$this->permissions = array_values(array_unique(array_map('strval', $permissions)));
		$this->pageSecurityTokens = array_values(array_unique(array_map('intval', $pageSecurityTokens)));
		$this->salespersonCode = $salespersonCode === null ? null : (string) $salespersonCode;
		sort($this->permissions, SORT_STRING);
		sort($this->pageSecurityTokens, SORT_NUMERIC);
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	public function getCompanyName()
	{
		return $this->companyName;
	}

	public function getAccessLevel()
	{
		return $this->accessLevel;
	}

	public function getPermissions()
	{
		return $this->permissions;
	}

	public function getPageSecurityTokens()
	{
		return $this->pageSecurityTokens;
	}

	public function isAdministrator()
	{
		return $this->isAdministrator;
	}

	public function canUseSalesAnalytics()
	{
		return $this->isAdministrator
			|| in_array('sales_dashboard', $this->permissions, true)
			|| in_array('*', $this->permissions, true);
	}

	public function getSalespersonCode()
	{
		return $this->salespersonCode;
	}

	public function hasSalespersonScope()
	{
		return !$this->isAdministrator;
	}

	public function getScopeFingerprint()
	{
		$scope = array(
			'user_id' => $this->userId,
			'database_name' => $this->databaseName,
			'company_name' => $this->companyName,
			'access_level' => $this->accessLevel,
			'is_administrator' => $this->isAdministrator,
			'salesperson_code' => $this->salespersonCode,
			'permissions' => $this->permissions,
			'page_security_tokens' => $this->pageSecurityTokens,
		);
		return hash('sha256', json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
}
