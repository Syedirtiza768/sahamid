<?php

namespace SAHamid\BI\Query;

use SAHamid\BI\Security\AuthorizationContext;

class CacheKey
{
	public static function forRequest(QueryRequest $request, AuthorizationContext $context, array $metricVersions)
	{
		$payload = array(
			'database_name' => $context->getDatabaseName(),
			'scope_fingerprint' => $context->getScopeFingerprint(),
			'metric_versions' => $metricVersions,
			'request' => $request->getCanonicalPayload(),
			'currency_context' => 'source_currency',
			'unit_context' => 'source_units',
		);
		return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
}
