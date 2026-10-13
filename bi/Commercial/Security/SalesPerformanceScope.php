<?php

namespace SAHamid\BI\Commercial\Security;

use SAHamid\BI\Exception\BIException;

/** Resolves user filters only within explicit grants; client filters never create authority. */
class SalesPerformanceScope
{
	public static function resolve($userName, $viewAll, $viewTeams, $viewSelf, array $grantedTeamIds, array $teamRows, array $knownSalespeople, $requestedTeamIds = null, $requestedSalespeople = null)
	{
		if (!$viewAll && !$viewTeams && !$viewSelf) {
			throw new BIException('forbidden', 'A sales performance viewing capability has not been granted.', 403);
		}
		$allIds = array();
		$allMembers = array();
		$teamBySalesperson = array();
		$teamMembershipCount = array();
		foreach ($teamRows as $team) {
			$id = (int) $team['id'];
			$allIds[$id] = true;
			foreach (explode(',', (string) $team['members']) as $member) {
				$member = trim($member);
				if ($member === '') continue;
				$teamMembershipCount[$member] = isset($teamMembershipCount[$member]) ? $teamMembershipCount[$member] + 1 : 1;
				if (!isset($teamBySalesperson[$member])) $teamBySalesperson[$member] = 't' . $id;
				$allMembers[$member] = true;
			}
		}
		foreach ($teamMembershipCount as $member => $count) {
			if ($count > 1) $teamBySalesperson[$member] = 'unassigned';
		}

		if ($viewAll) {
			$authorizedTeamIds = array_map('intval', array_keys($allIds));
			$authorizedNames = array_values(array_unique($knownSalespeople));
			$kind = 'erp_company';
		} else {
			$authorizedTeamIds = $viewTeams ? array_values(array_unique(array_map('intval', $grantedTeamIds))) : array();
			foreach ($authorizedTeamIds as $teamId) {
				if (!isset($allIds[$teamId])) throw new BIException('scope_unavailable', 'A granted sales team no longer exists. Contact the ERP administrator.', 503);
			}
			$authorizedNames = array();
			if ($viewTeams) {
				foreach ($teamRows as $team) {
					if (!in_array((int) $team['id'], $authorizedTeamIds, true)) continue;
					foreach (explode(',', (string) $team['members']) as $member) {
						$member = trim($member);
						if ($member !== '' && in_array($member, $knownSalespeople, true)) $authorizedNames[$member] = $member;
					}
				}
				$authorizedNames = array_values($authorizedNames);
			}
			if ($viewSelf && trim((string) $userName) !== '' && in_array($userName, $knownSalespeople, true)) {
				$authorizedNames[] = $userName;
			}
			$authorizedNames = array_values(array_unique($authorizedNames));
			$kind = $viewTeams ? 'granted_teams' : 'self';
			if (count($authorizedNames) === 0) {
				throw new BIException('scope_unavailable', 'No active salesperson or team scope is configured for this account.', 403);
			}
		}

		$visibleTeamIds = $authorizedTeamIds;
		if ($requestedTeamIds !== null && count($requestedTeamIds) > 0) {
			foreach ($requestedTeamIds as $teamId) {
				if (!isset($allIds[(int) $teamId])) throw new BIException('invalid_filter', 'A selected reporting team does not exist.', 400);
				if (!in_array((int) $teamId, $authorizedTeamIds, true)) throw new BIException('forbidden', 'A selected team is outside your authorized reporting scope.', 403);
			}
			$visibleTeamIds = array_values(array_unique(array_map('intval', $requestedTeamIds)));
		}

		$scopeNames = $authorizedNames;
		if ($requestedTeamIds !== null && count($requestedTeamIds) > 0) {
			$namesInSelectedTeams = array();
			foreach ($teamRows as $team) {
				if (!in_array((int) $team['id'], $visibleTeamIds, true)) continue;
				foreach (explode(',', (string) $team['members']) as $member) {
					$member = trim($member);
					if ($member !== '' && in_array($member, $authorizedNames, true)) $namesInSelectedTeams[$member] = $member;
				}
			}
			$scopeNames = array_values(array_intersect($scopeNames, array_values($namesInSelectedTeams)));
		}
		if ($requestedSalespeople !== null && count($requestedSalespeople) > 0) {
			foreach ($requestedSalespeople as $name) {
				if (!in_array($name, $knownSalespeople, true)) throw new BIException('invalid_filter', 'A selected salesperson is unknown.', 400);
				if (!in_array($name, $scopeNames, true)) throw new BIException('forbidden', 'A selected salesperson is outside your authorized reporting scope.', 403);
			}
			$scopeNames = array_values(array_intersect($scopeNames, $requestedSalespeople));
		}

		$visibleTeams = array();
		foreach ($teamRows as $team) {
			if (in_array((int) $team['id'], $visibleTeamIds, true)) $visibleTeams[] = $team;
		}
		$visibleTeamBySalesperson = array();
		foreach ($teamBySalesperson as $member => $teamKey) {
			if (in_array($member, $scopeNames, true)) {
				if ($teamKey === 'unassigned' || in_array((int) substr($teamKey, 1), $visibleTeamIds, true)) {
					$visibleTeamBySalesperson[$member] = $teamKey;
				}
			}
		}

		return array(
			'kind' => $kind,
			'team_ids' => $visibleTeamIds,
			'salespeople' => $scopeNames,
			'team_rows' => $visibleTeams,
			'team_by_salesperson' => $visibleTeamBySalesperson
		);
	}
}
