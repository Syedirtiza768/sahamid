<?php

namespace SAHamid\BI\Commercial;

use SAHamid\BI\Commercial\Source\QuotationSource;

/** Shared source-to-fact and quotation aggregation service for the first slice. */
class ReportingService
{
	private $quotationSource;
	private $definitions;

	public function __construct(QuotationSource $quotationSource = null, CommercialDefinitionRegistry $definitions = null)
	{
		$this->quotationSource = $quotationSource === null ? new QuotationSource() : $quotationSource;
		$this->definitions = $definitions === null ? new CommercialDefinitionRegistry() : $definitions;
	}

	public function quotationFacts($db, ReportingRequest $request)
	{
		$salespeople = $request->getSalespeople();
		$legacyFacts = $this->quotationSource->fetch($db, $request->getFrom(), $request->getThrough(), $salespeople);
		$issuedDocumentIds = $this->quotationSource->issuedDocumentIds($db);
		if (count($issuedDocumentIds) > 0) {
			$legacyFacts = array_values(array_filter($legacyFacts, function($fact) use ($issuedDocumentIds) {
				return !isset($issuedDocumentIds[(string) $fact['source_key']]);
			}));
		}
		$issueFacts = $this->quotationSource->fetchIssuedSnapshots($db, $request->getFrom(), $request->getThrough(), $salespeople);
		$facts = array_merge($legacyFacts, $issueFacts);
		usort($facts, function($left, $right) {
			$dateOrder = strcmp($left['business_at'], $right['business_at']);
			return $dateOrder !== 0 ? $dateOrder : strcmp($left['fact_id'], $right['fact_id']);
		});
		return $facts;
	}

	public function getDefinitions()
	{
		return $this->definitions->all();
	}

	public function aggregateQuotationFacts(array $facts)
	{
		$groups = array();
		foreach ($facts as $fact) {
			$date = substr($fact['business_at'], 0, 10);
			$name = $fact['credited_salesperson'];
			$attributionSnapshot = isset($fact['source_type']) && $fact['source_type'] === 'frozen_quotation_issue';
			$eventTeamId = $attributionSnapshot ? $fact['credited_team'] : null;
			$key = $date . "\0" . $name . "\0" . ($attributionSnapshot ? (string) $eventTeamId : 'legacy');
			if (!isset($groups[$key])) {
				$groups[$key] = array(
					'report_date' => $date,
					'salesman' => $name,
					'event_team_id' => $eventTeamId,
					'attribution_snapshot' => $attributionSnapshot,
					'record_count' => 0,
					'raw_value' => '0.00',
					'net_value' => '0.00',
					'normalized_value' => '0.00',
					'net_known_count' => 0,
					'net_unknown_count' => 0,
					'normalized_unknown_count' => 0,
					'raw_unknown_count' => 0,
					'adjusted_count' => 0,
					'value_status' => 'complete',
					'initial_value_status' => 'complete',
					'revised_count' => 0,
					'revised_raw_value' => '0.00',
					'revised_net_value' => '0.00',
					'revised_normalized_value' => '0.00',
					'revised_net_known_count' => 0,
					'revised_net_unknown_count' => 0,
					'revised_normalized_unknown_count' => 0,
					'revised_raw_unknown_count' => 0,
					'revised_value_status' => 'complete',
					'revised_adjusted_count' => 0
				);
			}
			if ($fact['is_revision']) {
				$groups[$key]['revised_count']++;
			} else {
				$groups[$key]['record_count']++;
			}
			$reportingRaw = array_key_exists('reporting_raw_amount', $fact) ? $fact['reporting_raw_amount'] : $fact['raw_amount'];
			if ($reportingRaw !== null) {
				if ($fact['is_revision']) {
					$groups[$key]['revised_raw_value'] = DecimalAmount::add($groups[$key]['revised_raw_value'], $reportingRaw);
				} else {
					$groups[$key]['raw_value'] = DecimalAmount::add($groups[$key]['raw_value'], $reportingRaw);
				}
			} else {
				if ($fact['is_revision']) {
					$groups[$key]['revised_raw_unknown_count']++;
					$groups[$key]['revised_value_status'] = 'partial';
				} else {
					$groups[$key]['raw_unknown_count']++;
					$groups[$key]['initial_value_status'] = 'partial';
				}
			}
			if ($fact['net_amount'] === null) {
				if ($fact['is_revision']) {
					$groups[$key]['revised_net_unknown_count']++;
					$groups[$key]['revised_value_status'] = $groups[$key]['revised_net_known_count'] > 0 ? 'partial' : 'unavailable';
				} else {
					$groups[$key]['net_unknown_count']++;
					$groups[$key]['initial_value_status'] = $groups[$key]['net_known_count'] > 0 ? 'partial' : 'unavailable';
				}
			} else {
				if ($fact['is_revision']) {
					$groups[$key]['revised_net_known_count']++;
					$groups[$key]['revised_net_value'] = DecimalAmount::add($groups[$key]['revised_net_value'], $fact['net_amount']);
					if ($fact['normalized_amount'] === null) {
						$groups[$key]['revised_normalized_unknown_count']++;
						$groups[$key]['revised_value_status'] = 'partial';
					} else {
						$groups[$key]['revised_normalized_value'] = DecimalAmount::add($groups[$key]['revised_normalized_value'], $fact['normalized_amount']);
					}
				} else {
					$groups[$key]['net_known_count']++;
					$groups[$key]['net_value'] = DecimalAmount::add($groups[$key]['net_value'], $fact['net_amount']);
					if ($fact['normalized_amount'] === null) {
						$groups[$key]['normalized_unknown_count']++;
						$groups[$key]['initial_value_status'] = 'partial';
					} else {
						$groups[$key]['normalized_value'] = DecimalAmount::add($groups[$key]['normalized_value'], $fact['normalized_amount']);
					}
				}
			}
		}
		foreach ($groups as &$group) {
			$group['value_status'] = ($group['initial_value_status'] === 'unavailable' || $group['revised_value_status'] === 'unavailable')
				? 'partial' : (($group['initial_value_status'] === 'partial' || $group['revised_value_status'] === 'partial') ? 'partial' : 'complete');
		}
		unset($group);
		return array_values($groups);
	}
}
