<?php

/**
 * Return the most recent recorded outward movement for each item.
 *
 * The old report read stock_status.latest_trandate, which is only updated by
 * a subset of screens and can therefore remain stale after an invoice, OGP,
 * DC, market slip, or shop sale. stockmoves is the audit trail used by all
 * of those flows, so it is the authoritative activity source for this report.
 */
function getReportLatestOutwardDates($db, $stockIdsSql)
{
    $latestDates = [];
    $sql = "SELECT stockid, MAX(trandate) AS latest_outward_date
            FROM stockmoves
            WHERE stockid IN ($stockIdsSql)
              AND hidemovt = 0
              AND (
                    type IN (511, 512, 513, 602, 750)
                    OR (type = 10 AND qty < 0)
                  )
            GROUP BY stockid";

    $result = mysqli_query($db, $sql);
    if (!$result) {
        throw new Exception('Outward movement query failed: ' . mysqli_error($db));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $latestDates[$row['stockid']] = $row['latest_outward_date'];
    }
    mysqli_free_result($result);

    return $latestDates;
}

/**
 * Return the newest positive BP item price for each stock item.
 *
 * This is a deliberate fallback only when the current IGP cost lots cannot
 * price the on-hand quantity. It prevents a zero-cost IGP row from masking
 * a real MPIW/BP item price while keeping the source visible to the UI.
 */
function getReportFallbackPrices($db, $stockIdsSql)
{
    $fallbackPrices = [];
    $sql = "SELECT stockid, price, parchino, created_at, id
            FROM bpitems
            WHERE stockid IN ($stockIdsSql)
              AND deleted_at IS NULL
              AND price > 0
            ORDER BY stockid, created_at DESC, id DESC";

    $result = mysqli_query($db, $sql);
    if (!$result) {
        throw new Exception('BP item fallback price query failed: ' . mysqli_error($db));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($fallbackPrices[$row['stockid']])) {
            $fallbackPrices[$row['stockid']] = [
                'price' => (float)$row['price'],
                'parchino' => $row['parchino'],
                'created_at' => $row['created_at']
            ];
        }
    }
    mysqli_free_result($result);

    return $fallbackPrices;
}

/**
 * Allocate current on-hand quantity against newest-to-oldest positive-cost
 * IGP lots and expose cost coverage so partial or missing valuation is not
 * silently presented as a fully priced inventory balance.
 */
function calculatePriceForStock($parchinos, $requested_qty, $fallback_unit_price = 0)
{
    $requested_qty = max(0, (float)$requested_qty);
    $fallback_unit_price = max(0, (float)$fallback_unit_price);

    if ($requested_qty <= 0) {
        return [
            'total_bpitems_price' => 0,
            'weighted_unit_price' => 0,
            'total_quantity' => 0,
            'unpriced_quantity' => 0,
            'price_status' => 'NO_STOCK',
            'price_source' => 'NONE',
            'price_coverage_percent' => 100
        ];
    }

    $remaining_qty = $requested_qty;
    $total_allocated_qty = 0;
    $total_weighted_price = 0;
    $usedCostLot = false;

    foreach ($parchinos as $parchino) {
        if ($remaining_qty <= 0) {
            break;
        }

        $available_qty = max(0, (float)($parchino['quantity'] ?? 0));
        $unit_price = (float)($parchino['adjust_unit_price'] ?? 0);
        if ($unit_price <= 0) {
            $unit_price = (float)($parchino['price'] ?? 0);
        }

        // A zero-cost row has no valuation coverage. Do not consume current
        // quantity against it and thereby hide an older valid MPIW cost lot.
        if ($available_qty <= 0 || $unit_price <= 0) {
            continue;
        }

        $landing_factor = (float)($parchino['landing_factor'] ?? 1);
        if ($landing_factor <= 0) {
            $landing_factor = 1;
        }
        $effective_price = $unit_price * $landing_factor;
        $allocated_qty = min($available_qty, $remaining_qty);

        if ($allocated_qty > 0) {
            $total_weighted_price += $allocated_qty * $effective_price;
            $total_allocated_qty += $allocated_qty;
            $remaining_qty -= $allocated_qty;
            $usedCostLot = true;
        }
    }

    $priceSource = $usedCostLot ? 'MPIW_COST_LOT' : 'NONE';
    if ($remaining_qty > 0 && $fallback_unit_price > 0) {
        $total_weighted_price += $remaining_qty * $fallback_unit_price;
        $total_allocated_qty += $remaining_qty;
        $priceSource = $usedCostLot ? 'MIXED_MPIW_BPITEM' : 'BPITEM_FALLBACK';
        $remaining_qty = 0;
    }

    $unpriced_qty = max(0, $requested_qty - $total_allocated_qty);
    $coverage = $requested_qty > 0
        ? round(($total_allocated_qty / $requested_qty) * 100, 2)
        : 100;

    if ($total_allocated_qty <= 0) {
        $priceStatus = 'MISSING_COST';
    } elseif ($unpriced_qty > 0) {
        $priceStatus = 'PARTIAL_COST_COVERAGE';
    } else {
        $priceStatus = 'PRICED';
    }

    return [
        'total_bpitems_price' => round($total_weighted_price, 2),
        'weighted_unit_price' => round($total_allocated_qty > 0 ? $total_weighted_price / $total_allocated_qty : 0, 2),
        'total_quantity' => round($total_allocated_qty, 2),
        'unpriced_quantity' => round($unpriced_qty, 2),
        'price_status' => $priceStatus,
        'price_source' => $priceSource,
        'price_coverage_percent' => $coverage
    ];
}
