<?php

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

$PathPrefix = "../../../../";

try {
    include("../../../../includes/session.inc");
    include('../../../../includes/SQL_CommonFunctions.inc');
} catch (Throwable $exception) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => true,
        'message' => 'Unable to initialise the inward bazar parchi list.'
    ]);
    exit;
}

/**
 * Bind parameters in a PHP 7.3-compatible way. mysqli_stmt_bind_param()
 * requires references, which cannot be reliably supplied through argument
 * unpacking on the PHP versions supported by this application.
 */
function bindInwardParchiParams($statement, $types, &$params)
{
    if ($types === '') {
        return true;
    }

    $arguments = [$statement, $types];
    foreach ($params as $key => &$value) {
        $arguments[] = &$value;
    }

    return call_user_func_array('mysqli_stmt_bind_param', $arguments);
}

function sendInwardParchiJson($data, $statusCode = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    // Company data is historically stored in mixed encodings. Substitute an
    // invalid byte instead of returning an empty response that breaks JSON.parse.
    $json = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        $json = json_encode([
            'error' => true,
            'message' => 'The inward bazar parchi response could not be encoded.'
        ]);
    }

    echo $json;
    exit;
}

if (!function_exists('userHasPermission') || !userHasPermission($db, "list_inward_parchi")) {
    sendInwardParchiJson([]);
}

try {
    $useFilters = isset($_GET['filters']) && $_GET['filters'] === 'yes';
    $sessionFilter = isset($_SESSION['filter']) ? trim((string) $_SESSION['filter']) : '';

    /*
     * Aggregate each one-to-many table before joining it to bazar_parchi.
     * This avoids duplicate parchi rows and removes the N+1 queries that were
     * previously executed for advance, item totals and allocations.
     */
    $sql = "SELECT
                bp.parchino,
                bp.transno,
                bp.svid,
                bp.temp_vendor,
                bp.gstinvoice,
                bp.created_at,
                bp.inprogress,
                bp.settled AS bazar_settled,
                bp.discarded,
                bp.igp_created,
                bp.igp_id,
                COALESCE(st.settled, bp.settled, 0) AS settled2,
                u.realname,
                COALESCE(NULLIF(s.suppname, ''), bp.temp_vendor) AS name,
                COALESCE(ledger.advance, 0) AS advance,
                COALESCE(item_totals.amount, 0) AS amount,
                COALESCE(allocations.last_date, '') AS last_date,
                COALESCE(allocations.total_alloc, 0) AS total_alloc
            FROM bazar_parchi bp
            INNER JOIN www_users u
                ON u.userid = bp.user_id
            LEFT JOIN suppliers s
                ON s.supplierid = bp.svid
            LEFT JOIN (
                SELECT transno, MAX(id) AS id, MAX(settled) AS settled
                FROM supptrans
                WHERE type = 601
                GROUP BY transno
            ) st
                ON st.transno = bp.transno
            LEFT JOIN (
                SELECT parchino, SUM(amount) AS advance
                FROM bpledger
                GROUP BY parchino
            ) ledger
                ON ledger.parchino = bp.parchino
            LEFT JOIN (
                SELECT parchino, SUM(quantity_received * price) AS amount
                FROM bpitems
                WHERE stockid <> ''
                  AND deleted_by = ''
                GROUP BY parchino
            ) item_totals
                ON item_totals.parchino = bp.parchino
            LEFT JOIN (
                SELECT transid_allocto,
                       MAX(datealloc) AS last_date,
                       SUM(amt) AS total_alloc
                FROM suppallocs
                GROUP BY transid_allocto
            ) allocations
                ON allocations.transid_allocto = st.id
            WHERE bp.discarded = 0
              AND bp.type = 601";

    $conditions = [];
    $params = [];
    $types = '';

    if ($useFilters) {
        if (!empty($_GET['from'])) {
            $conditions[] = 'bp.created_at >= ?';
            $params[] = (string) $_GET['from'];
            $types .= 's';
        }

        if (!empty($_GET['to'])) {
            $conditions[] = 'bp.created_at <= ?';
            $params[] = (string) $_GET['to'] . ' 23:59:59';
            $types .= 's';
        }

        if (!empty($_GET['state'])) {
            switch ($_GET['state']) {
                case 'saved':
                    $conditions[] = 'bp.inprogress = 0';
                    $conditions[] = 'COALESCE(st.settled, bp.settled, 0) = 0';
                    break;
                case 'settled':
                    $conditions[] = 'COALESCE(st.settled, bp.settled, 0) = 1';
                    break;
                case 'inprogress':
                    $conditions[] = 'bp.inprogress = 1';
                    break;
            }
        }

        if (!empty($_GET['item'])) {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM bpitems item_filter
                WHERE item_filter.parchino = bp.parchino
                  AND item_filter.stockid LIKE CONCAT('%', ?, '%')
            )";
            $params[] = (string) $_GET['item'];
            $types .= 's';
        }

        if (!empty($_GET['brand']) && $_GET['brand'] !== 'All') {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM bpitems brand_items
                INNER JOIN stockmaster brand_stock
                    ON brand_stock.stockid = brand_items.stockid
                WHERE brand_items.parchino = bp.parchino
                  AND brand_stock.brand = ?
            )";
            $params[] = (string) $_GET['brand'];
            $types .= 's';
        }
    } else {
        switch ($sessionFilter) {
            case 'none':
                $conditions[] = 'bp.svid = ?';
                $params[] = isset($_SESSION['svid']) ? (string) $_SESSION['svid'] : '';
                $types .= 's';
                break;
            case 'saved':
                $conditions[] = 'bp.inprogress = 0';
                $conditions[] = 'COALESCE(st.settled, bp.settled, 0) = 0';
                $conditions[] = 'bp.svid = ?';
                $params[] = isset($_SESSION['svid']) ? (string) $_SESSION['svid'] : '';
                $types .= 's';
                break;
            case 'inprogress':
                $conditions[] = 'bp.inprogress = 1';
                $conditions[] = 'bp.svid = ?';
                $params[] = isset($_SESSION['svid']) ? (string) $_SESSION['svid'] : '';
                $types .= 's';
                break;
            case 'settled':
                $conditions[] = 'COALESCE(st.settled, bp.settled, 0) = 1';
                $conditions[] = 'bp.svid = ?';
                $params[] = isset($_SESSION['svid']) ? (string) $_SESSION['svid'] : '';
                $types .= 's';
                break;
        }
    }

    if (!empty($conditions)) {
        $sql .= ' AND ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY bp.created_at DESC, bp.id DESC';

    $statement = mysqli_prepare($db, $sql);
    if (!$statement) {
        throw new Exception('Query preparation failed: ' . mysqli_error($db));
    }

    if (!bindInwardParchiParams($statement, $types, $params)) {
        throw new Exception('Query parameter binding failed: ' . mysqli_stmt_error($statement));
    }

    if (!mysqli_stmt_execute($statement)) {
        throw new Exception('Query execution failed: ' . mysqli_stmt_error($statement));
    }

    $result = mysqli_stmt_get_result($statement);
    if (!$result) {
        throw new Exception('Failed to read the inward bazar parchi result: ' . mysqli_stmt_error($statement));
    }

    $canEdit = userHasPermission($db, 'edit_inward_parchi');
    $canPrintInternal = userHasPermission($db, 'inward_parchi_internal');

    // Load vendor permissions once instead of querying once per returned row.
    $vendorPermissions = [];
    if (isset($_SESSION['UserID'])) {
        $vendorStatement = mysqli_prepare(
            $db,
            'SELECT permission FROM vendor_permission WHERE userid = ?'
        );

        if ($vendorStatement) {
            $vendorUserId = (string) $_SESSION['UserID'];
            $vendorParams = [$vendorUserId];
            if (bindInwardParchiParams($vendorStatement, 's', $vendorParams)
                && mysqli_stmt_execute($vendorStatement)
            ) {
                $vendorResult = mysqli_stmt_get_result($vendorStatement);
                if ($vendorResult) {
                    while ($vendorRow = mysqli_fetch_assoc($vendorResult)) {
                        $vendorPermissions[(string) $vendorRow['permission']] = true;
                    }
                }
            }
            mysqli_stmt_close($vendorStatement);
        }
    }

    $canViewAllVendors = isset($vendorPermissions['*']);
    $formatNumber = function ($value) {
        return function_exists('locale_number_format')
            ? locale_number_format($value, 2)
            : number_format($value, 2);
    };

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $parchino = (string) $row['parchino'];
        $amount = (float) $row['amount'];
        $advance = (float) $row['advance'];
        $canViewVendor = $canViewAllVendors || isset($vendorPermissions[(string) $row['svid']]);

        $r = [];
        $parchinoParts = explode('-', $parchino);
        $r[] = isset($parchinoParts[1]) ? $parchinoParts[1] : '';
        $r[] = $parchino;
        $r[] = $row['svid'];
        $r[] = $row['name'];
        $r[] = date('d/m/Y', strtotime($row['created_at']));

        if ($canViewVendor) {
            $amountFormatted = $formatNumber($amount);
            if ($amount <= 10000) {
                $r[] = '<span style="background-color:yellow;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 50000) {
                $r[] = '<span style="background-color:blue;color: white;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 100000) {
                $r[] = '<span style="background-color:darkblue;color: white;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 200000) {
                $r[] = '<span style="background-color:deeppink;color: white;">' . $amountFormatted . '</span>';
            } else {
                $r[] = '<span style="background-color:red;color: white;">' . $amountFormatted . '</span>';
            }
        } else {
            $r[] = '';
        }

        switch ($row['gstinvoice']) {
            case 'e':
                $r[] = 'Exclusive';
                break;
            case 'i':
                $r[] = 'Inclusive';
                break;
            default:
                $r[] = '';
        }

        $r[] = $canViewVendor ? $advance : '';
        $r[] = $row['last_date'];

        $status = 'Saved';
        if ((int) $row['discarded'] === 1) {
            $status = 'Discarded';
        } elseif ((int) $row['settled2'] === 1) {
            $status = 'Settled';
        } elseif ((int) $row['inprogress'] === 1) {
            $status = 'InProgress';
        } elseif ((float) $row['total_alloc'] > 0) {
            $status = 'Saved ' . $row['total_alloc'];
        }
        $r[] = $status;
        $r[] = $row['realname'];

        if ($canEdit) {
            $r[] = ((int) $row['inprogress'] === 1 && (int) $row['settled2'] === 0)
                ? "<a class='btn btn-warning' target='_blank' href='editInwardParchi.php?parchi=" . urlencode($parchino) . "'>Edit</a>"
                : "<a class='btn btn-success' target='_blank' href='addfilesInwardParchi.php?parchi=" . urlencode($parchino) . "'>Edit Aux.</a>";
        }

        $r[] = ((int) $row['igp_created'] === 0)
            ? ''
            : "<a class='btn btn-info' target='_blank' href='../../../PDFIGP.php?RequestNo=" . urlencode($row['igp_id']) . "'>IGP</a>";

        $r[] = ((int) $row['discarded'] === 1)
            ? ''
            : "<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=" . urlencode($parchino) . "'>External</a>";

        if ($canPrintInternal) {
            $r[] = ((int) $row['discarded'] === 1)
                ? ''
                : "<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=" . urlencode($parchino) . "&internal'>Internal</a>";
        }

        $data[] = $r;
    }

    mysqli_stmt_close($statement);
    sendInwardParchiJson($data);
} catch (Throwable $exception) {
    error_log('Inward bazar parchi list failed: ' . $exception->getMessage());
    sendInwardParchiJson([
        'error' => true,
        'message' => 'Unable to load inward bazar parchi data.'
    ], 500);
}
