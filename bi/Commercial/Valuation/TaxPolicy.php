<?php

namespace SAHamid\BI\Commercial\Valuation;

use SAHamid\BI\Commercial\DecimalAmount;

/** Provisional extraction of the current quotation PDF tax-display rules. */
class TaxPolicy
{
	const VERSION = 'erp-quotation-gst-v1-provisional';

	public function valueWithoutGst($sourceAmount, $gstMode, $isService, $documentDate, $currency)
	{
		if ($sourceAmount === null || $sourceAmount === '') {
			return array('net_amount' => null, 'tax_basis' => 'unknown', 'tax_rate' => null, 'status' => 'missing_source_amount');
		}
		if (strtoupper((string) $currency) !== 'PKR') {
			return array('net_amount' => null, 'tax_basis' => 'currency_conversion_required', 'tax_rate' => null, 'status' => 'unsupported_currency');
		}

		$mode = strtolower(trim((string) $gstMode));
		if ($mode === 'exclusive' || $mode === 'update') {
			return array('net_amount' => DecimalAmount::normalize($sourceAmount), 'tax_basis' => $mode, 'tax_rate' => null, 'status' => 'known_source_subtotal');
		}
		if ($mode !== 'inclusive' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $documentDate)) {
			return array('net_amount' => null, 'tax_basis' => 'unresolved_gst_classification', 'tax_rate' => null, 'status' => 'unknown_tax_treatment');
		}

		$services = (int) $isService === 1;
		$taxRate = $services ? 16 : ((string) $documentDate < '2023-02-14' ? 17 : 18);
		return array(
			'net_amount' => DecimalAmount::divideInclusiveTax($sourceAmount, $taxRate),
			'tax_basis' => 'inclusive_gst_removed',
			'tax_rate' => $taxRate,
			'status' => 'provisional_tax_rule_applied'
		);
	}
}
