<?php

namespace SAHamid\BI\Security;

use SAHamid\BI\Reports\ReportDefinition;

/**
 * Applies the existing ERP page-security mapping to live-source BI reports.
 * Reports with dedicated BI routes use the BI permission already represented
 * by AuthorizationContext. Routes absent from the legacy scripts map retain
 * their own internal/custom authorization checks.
 */
class ReportAuthorization
{
	private $pageSecurity;

	public function __construct(array $session)
	{
		$this->pageSecurity = isset($session['PageSecurityArray']) && is_array($session['PageSecurityArray'])
			? $session['PageSecurityArray'] : array();
	}

	public function isAllowed(ReportDefinition $report, AuthorizationContext $context)
	{
		$definition = $report->toArray();
		if (!empty($definition['bi_route'])) {
			return $context->canUseSalesAnalytics();
		}
		$route = isset($definition['legacy_route']) ? (string) $definition['legacy_route'] : '';
		$path = parse_url($route, PHP_URL_PATH);
		$script = $path ? basename($path) : '';
		if ($script === '' || !array_key_exists($script, $this->pageSecurity)) {
			return true;
		}
		return in_array((int) $this->pageSecurity[$script], $context->getPageSecurityTokens(), true);
	}
}
