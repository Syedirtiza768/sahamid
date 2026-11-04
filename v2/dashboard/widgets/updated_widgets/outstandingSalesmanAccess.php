<?php

function dashboardOutstandingSalesmanAccess($db)
{
	$userId = mysqli_real_escape_string($db, (string) $_SESSION['UserID']);
	$permissionResult = mysqli_query($db, "SELECT 1 FROM user_permission WHERE userid = '$userId' AND permission = '*' LIMIT 1");
	$isAdmin = $permissionResult && mysqli_num_rows($permissionResult) > 0;
	$salesmen = [];

	if ($isAdmin) {
		$salesmanResult = mysqli_query($db, "SELECT DISTINCT salesmanname FROM salesman WHERE salesmanname <> '' ORDER BY salesmanname");
		while ($salesmanResult && ($salesmanRow = mysqli_fetch_assoc($salesmanResult))) {
			$salesmen[] = $salesmanRow['salesmanname'];
		}
	} else {
		$ownResult = mysqli_query($db, "SELECT realname FROM www_users WHERE userid = '$userId' LIMIT 1");
		if ($ownResult && ($ownRow = mysqli_fetch_assoc($ownResult)) && trim($ownRow['realname']) !== '') {
			$salesmen[] = $ownRow['realname'];
		}
		if (isset($_SESSION['UsersRealName']) && trim($_SESSION['UsersRealName']) !== '') {
			$salesmen[] = $_SESSION['UsersRealName'];
		}

		$grantResult = mysqli_query(
			$db,
			"SELECT DISTINCT www_users.realname
			 FROM salescase_permissions
			 INNER JOIN www_users ON www_users.userid = salescase_permissions.can_access
			 WHERE salescase_permissions.user = '$userId'
			   AND www_users.realname <> ''"
		);
		while ($grantResult && ($grantRow = mysqli_fetch_assoc($grantResult))) {
			$salesmen[] = $grantRow['realname'];
		}
	}

	$salesmen = array_values(array_unique(array_filter($salesmen, function ($name) {
		return trim((string) $name) !== '';
	})));
	sort($salesmen, SORT_NATURAL | SORT_FLAG_CASE);

	return [
		'is_admin' => $isAdmin,
		'salesmen' => $salesmen,
	];
}

function dashboardOutstandingSalesmanFilter($access)
{
	$requested = null;
	if (isset($_POST['salesmen']) && is_array($_POST['salesmen'])) {
		$requested = array_map('strval', $_POST['salesmen']);
	} elseif (isset($_POST['salesman']) && is_string($_POST['salesman'])) {
		// Retain compatibility with older dashboard widgets that post quoted CSV names.
		preg_match_all("/'((?:\\\\.|[^'])*)'/", $_POST['salesman'], $matches);
		if (!empty($matches[1])) {
			$requested = array_map('stripcslashes', $matches[1]);
		} elseif (trim($_POST['salesman']) !== '') {
			$requested = [trim($_POST['salesman'], "' \t\n\r\0\x0B")];
		}
	}

	if ($access['is_admin'] && (!is_array($requested) || count($requested) === 0)) {
		return null;
	}

	$allowedByLowerName = [];
	foreach ($access['salesmen'] as $name) {
		$allowedByLowerName[strtolower($name)] = $name;
	}

	$filter = $access['is_admin'] ? [] : $access['salesmen'];
	if (is_array($requested) && count($requested) > 0) {
		$filter = [];
		foreach ($requested as $name) {
			$key = strtolower(trim($name));
			if (isset($allowedByLowerName[$key])) {
				$filter[] = $allowedByLowerName[$key];
			}
		}
	}

	return array_values(array_unique($filter));
}

function dashboardOutstandingSalesmanWhere($db, $column, $filter)
{
	if ($filter === null) {
		return '';
	}
	if (count($filter) === 0) {
		return null;
	}

	$values = [];
	foreach ($filter as $name) {
		$values[] = "'" . mysqli_real_escape_string($db, $name) . "'";
	}

	return ' AND ' . $column . ' IN (' . implode(',', $values) . ')';
}
