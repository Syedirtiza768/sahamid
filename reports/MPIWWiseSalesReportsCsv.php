<?php

/*
 * Stream the MPIW sales-detail CSV directly from the database. Keeping this
 * endpoint separate from the HTML/JSON response avoids building a large JSON
 * payload and a large DataTables DOM just to download a file.
 */

$startDate = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$endDate = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

$startDateObject = DateTime::createFromFormat('!Y-m-d', $startDate);
$endDateObject = DateTime::createFromFormat('!Y-m-d', $endDate);
$dateErrors = DateTime::getLastErrors();

$validDates = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)
	&& preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)
	&& $startDateObject
	&& $endDateObject
	&& ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
	&& $startDateObject->format('Y-m-d') === $startDate
	&& $endDateObject->format('Y-m-d') === $endDate
	&& $startDateObject <= $endDateObject;

if (!$validDates) {
	http_response_code(400);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Please provide a valid date range.']);
	exit;
}

$endDateExclusive = clone $endDateObject;
$endDateExclusive->modify('+1 day');
$startDateTime = $startDateObject->format('Y-m-d') . ' 00:00:00';
$endDateExclusiveTime = $endDateExclusive->format('Y-m-d') . ' 00:00:00';
$endDateExclusiveValue = $endDateExclusive->format('Y-m-d');

/*
 * Each source is reduced to one row per stock before the joins. This replaces
 * the old N+1 pattern (two queries per MPI item) with one database round trip.
 */
$SQL = "SELECT
			mpi.stockid,
			mpi.mnfcode,
			mpi.mnfpno,
			mpi.description,
			mpi.brand,
			mpi.MPIQTY,
			mpi.MPIPrice,
			COALESCE(shop.shopSaleQTY, 0) AS shopSaleQTY,
			COALESCE(shop.shopSaleAvgPrice, 0) AS shopSaleAvgPrice,
			COALESCE(dc.dcQTY, 0) AS dcQTY,
			COALESCE(dc.dcAvgPrice, 0) AS dcAvgPrice,
			COALESCE(dc.SAHdcQTY, 0) AS SAHdcQTY,
			COALESCE(dc.SAHdcAvgPrice, 0) AS SAHdcAvgPrice
		FROM (
			SELECT
				stockmaster.stockid,
				stockmaster.mnfcode,
				stockmaster.mnfpno,
				stockmaster.description,
				manufacturers.manufacturers_name AS brand,
				SUM(bpitems.quantity_received) AS MPIQTY,
				SUM(bpitems.quantity_received * bpitems.listprice * (1 - bpitems.discount / 100))
					/ NULLIF(SUM(bpitems.quantity_received), 0) AS MPIPrice,
				MAX(bpitems.id) AS latestMPIItemId
			FROM bpitems
			INNER JOIN bazar_parchi
				ON bazar_parchi.parchino = bpitems.parchino
			INNER JOIN stockmaster
				ON stockmaster.stockid = bpitems.stockid
			INNER JOIN manufacturers
				ON stockmaster.brand = manufacturers.manufacturers_id
			WHERE bazar_parchi.created_at >= ?
			AND bazar_parchi.created_at < ?
			AND bpitems.listprice > 0
			AND bazar_parchi.type = 601
			GROUP BY
				stockmaster.stockid,
				stockmaster.mnfcode,
				stockmaster.mnfpno,
				stockmaster.description,
				manufacturers.manufacturers_name
		) mpi
		LEFT JOIN (
			SELECT
				shopsalesitems.stockid,
				SUM(shopsalelines.quantity * shopsalesitems.quantity) AS shopSaleQTY,
				SUM(
					shopsalelines.quantity * shopsalesitems.quantity * shopsalelines.price
					* (1 - shopsale.discount / 100)
				) / NULLIF(SUM(shopsalelines.quantity * shopsalesitems.quantity), 0)
					AS shopSaleAvgPrice
			FROM shopsale
			INNER JOIN shopsalelines
				ON shopsalelines.orderno = shopsale.orderno
			INNER JOIN shopsalesitems
				ON shopsalesitems.lineno = shopsalelines.id
				AND shopsalesitems.orderno = shopsalelines.orderno
			WHERE shopsale.orddate >= ?
			AND shopsale.orddate < ?
			AND (
				shopsale.debtorno LIKE 'SR%'
				OR shopsale.debtorno = 'WALKIN01'
			)
			GROUP BY shopsalesitems.stockid
		) shop ON shop.stockid = mpi.stockid
		LEFT JOIN (
			SELECT
				dcdetails.stkcode,
				SUM(CASE WHEN dcs.GSTAdd <> ''
					THEN dcoptions.quantity * dcdetails.quantity ELSE 0 END) AS dcQTY,
				SUM(CASE WHEN dcs.GSTAdd <> '' THEN
					dcoptions.quantity * dcdetails.quantity * dcdetails.unitprice
					* (1 - dcdetails.discountpercent)
					* CASE WHEN LOWER(TRIM(dcs.GSTAdd)) = 'inclusive' THEN 1 / 1.17 ELSE 1 END
					ELSE 0 END)
					/ NULLIF(SUM(CASE WHEN dcs.GSTAdd <> ''
						THEN dcoptions.quantity * dcdetails.quantity ELSE 0 END), 0)
					AS dcAvgPrice,
				SUM(CASE WHEN dcs.GSTAdd = ''
					THEN dcoptions.quantity * dcdetails.quantity ELSE 0 END) AS SAHdcQTY,
				SUM(CASE WHEN dcs.GSTAdd = '' THEN
					dcoptions.quantity * dcdetails.quantity * dcdetails.unitprice
					* (1 - dcdetails.discountpercent)
					ELSE 0 END)
					/ NULLIF(SUM(CASE WHEN dcs.GSTAdd = ''
						THEN dcoptions.quantity * dcdetails.quantity ELSE 0 END), 0)
					AS SAHdcAvgPrice
			FROM dcoptions
			INNER JOIN dcs
				ON dcs.orderno = dcoptions.orderno
			INNER JOIN dcdetails
				ON dcdetails.orderno = dcoptions.orderno
				AND dcdetails.orderlineno = dcoptions.lineno
				AND dcdetails.lineoptionno = dcoptions.optionno
			WHERE dcs.orddate >= ?
			AND dcs.orddate < ?
			AND dcs.debtorno LIKE 'SR%'
			GROUP BY dcdetails.stkcode
		) dc ON dc.stkcode = mpi.stockid
		ORDER BY mpi.latestMPIItemId DESC";

$stmt = mysqli_prepare($db, $SQL);
if (!$stmt) {
	http_response_code(500);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Unable to prepare the MPIW CSV query.']);
	exit;
}

mysqli_stmt_bind_param(
	$stmt,
	'ssssss',
	$startDateTime,
	$endDateExclusiveTime,
	$startDate,
	$endDateExclusiveValue,
	$startDate,
	$endDateExclusiveValue
);

if (!mysqli_stmt_execute($stmt)) {
	http_response_code(500);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Unable to load the MPIW CSV.']);
	mysqli_stmt_close($stmt);
	exit;
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
	http_response_code(500);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Unable to read the MPIW CSV results.']);
	mysqli_stmt_close($stmt);
	exit;
}

$filename = 'MPIW_Sales_Details_' . $startDate . '_to_' . $endDate . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');

echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');
fputcsv($output, [
	'Stockid',
	'Description',
	'Brand',
	'MNFCode',
	'MNFPNo',
	'MPIQTY',
	'MPIPriceAVG',
	'ShopSaleQTY',
	'ShopSalePriceAVG',
	'GSTDCQTY',
	'GSTDCPriceAVG',
	'SAHDCQTY',
	'SAHDCPriceAVG'
]);

while ($row = mysqli_fetch_row($result)) {
	fputcsv($output, $row);
}

fclose($output);
mysqli_free_result($result);
mysqli_stmt_close($stmt);
