<?php

    $PathPrefix = "../../../";
    include("../../../includes/session.inc");
    include('../../../includes/SQL_CommonFunctions.inc');

    header('Content-Type: application/json; charset=utf-8');

    $type = isset($_GET['type']) ? (int) $_GET['type'] : 0;

    if (!isset($_POST['from'], $_POST['to']) || !in_array($type, [604, 605], true)) {
        echo json_encode([]);
        exit;
    }

    $from = trim((string) $_POST['from']);
    $to = trim((string) $_POST['to']);

    // Use a half-open date range so the complete selected end date is included
    // without applying a function to voucher.created_at.
    $fromDate = DateTime::createFromFormat('!Y-m-d', $from);
    $toDate = DateTime::createFromFormat('!Y-m-d', $to);
    if (!$fromDate || !$toDate || $fromDate->format('Y-m-d') !== $from || $toDate->format('Y-m-d') !== $to) {
        http_response_code(422);
        echo json_encode([]);
        exit;
    }
    $toExclusive = $toDate->modify('+1 day')->format('Y-m-d');

    if ($type === 604 && !userHasPermission($db, "list_receipt_voucher")) {
        http_response_code(403);
        echo json_encode([]);
        exit;
    }
    if ($type === 605 && !userHasPermission($db, "list_payment_voucher")) {
        http_response_code(403);
        echo json_encode([]);
        exit;
    }

    $conditions = [
        'voucher.type = ?',
        'voucher.created_at >= ?',
        'voucher.created_at < ?'
    ];
    $params = [$type, $from, $toExclusive];
    $paramTypes = 'iss';

    $salespeople = isset($_POST['salesperson']) ? $_POST['salesperson'] : [];
    if (!is_array($salespeople)) {
        $salespeople = [$salespeople];
    }
    $salespeople = array_values(array_unique(array_filter(array_map(function ($salesperson) {
        // Accept the quoted values sent by older versions of the page as well.
        return trim(trim((string) $salesperson), "'\"");
    }, $salespeople), function ($salesperson) {
        return $salesperson !== '';
    })));

    if (!userHasPermission($db, "executive_listing")) {
        $realName = (string) $_SESSION['UsersRealName'];

        if ($type === 604) {
            $conditions[] = '(voucher.salesman = ? OR voucher.user_name = ?)';
            $params[] = $realName;
            $params[] = $realName;
            $paramTypes .= 'ss';
        } else {
            $conditions[] = '(voucher.user_name = ? OR EXISTS (
                SELECT 1
                FROM vendor_permission AS vp
                WHERE vp.userid = ? AND vp.permission = voucher.pid
            ))';
            $params[] = $realName;
            $params[] = (string) $_SESSION['UserID'];
            $paramTypes .= 'ss';
        }
    } elseif ($salespeople) {
        $placeholders = implode(',', array_fill(0, count($salespeople), '?'));
        $conditions[] = '(voucher.salesman IN (' . $placeholders . ') OR voucher.user_name IN (' . $placeholders . '))';
        foreach ($salespeople as $salesperson) {
            $params[] = $salesperson;
        }
        foreach ($salespeople as $salesperson) {
            $params[] = $salesperson;
        }
        $paramTypes .= str_repeat('s', count($salespeople) * 2);
    }

    $sql = "SELECT
                voucher.id AS voucherid,
                voucher.voucherno,
                voucher.pid,
                voucher.partyname,
                voucher.created_at,
                voucher.amount,
                voucher.salesman,
                voucher.user_name,
                voucher.booked,
                voucher.supptransno
            FROM voucher
            WHERE " . implode(' AND ', $conditions);

    $statement = mysqli_prepare($db, $sql);
    if (!$statement) {
        error_log('Unable to prepare voucher list query: ' . mysqli_error($db));
        http_response_code(500);
        echo json_encode([]);
        exit;
    }

    $bindParams = [$statement, $paramTypes];
    foreach ($params as $paramIndex => $param) {
        $bindParams[] = &$params[$paramIndex];
    }
    if (!call_user_func_array('mysqli_stmt_bind_param', $bindParams)) {
        error_log('Unable to bind voucher list query parameters: ' . mysqli_stmt_error($statement));
        mysqli_stmt_close($statement);
        http_response_code(500);
        echo json_encode([]);
        exit;
    }

    if (!mysqli_stmt_execute($statement)) {
        error_log('Unable to execute voucher list query: ' . mysqli_stmt_error($statement));
        mysqli_stmt_close($statement);
        http_response_code(500);
        echo json_encode([]);
        exit;
    }

    $result = mysqli_stmt_get_result($statement);
    if (!$result) {
        error_log('Unable to read voucher list query result: ' . mysqli_stmt_error($statement));
        mysqli_stmt_close($statement);
        http_response_code(500);
        echo json_encode([]);
        exit;
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    // The old implementation called glob() once for every voucher. Scan the
    // attachment directory once and group files by voucher id instead.
    $attachmentsByVoucher = [];
    if ($rows) {
        $attachmentFiles = glob("../../../" . $_SESSION['part_pics_dir'] . '/voucher_*.pdf') ?: [];
        foreach ($attachmentFiles as $attachmentFile) {
            if (preg_match('/^voucher_(\d+).*\.pdf$/i', basename($attachmentFile), $matches)) {
                $attachmentsByVoucher[(int) $matches[1]][] = $attachmentFile;
            }
        }
    }

    $data = [];
    $escape = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    foreach ($rows as $row) {
        $voucherId = (int) $row['voucherid'];
        $voucherNumberParts = explode('-', (string) $row['voucherno'], 2);
        $voucherNumber = isset($voucherNumberParts[1]) ? $voucherNumberParts[1] : $voucherNumberParts[0];

        $r = [];
        $r[] = $escape($voucherNumber);
        $r[] = $escape($row['voucherno']);
        $r[] = $escape($row['pid']);
        $r[] = $escape($row['partyname']);
        $r[] = date("d/m/Y", strtotime($row['created_at']));
        $r[] = locale_number_format($row['amount'], 2);
        $r[] = $escape($row['salesman']);
        $r[] = $escape($row['user_name']);

        $vouchers = "";
        $ind = 0;
        foreach ($attachmentsByVoucher[$voucherId] ?? [] as $voucherFile) {
            $ind++;
            $vouchers .= '<br /><a target="_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href="' .
                $escape($RootPath . '/' . $voucherFile) . '">Attachment' . $ind . '</a>';
        }
        $r[] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' " . $escape($_SESSION['FormID']) . " ' />
        <input type='file' id='attachFile" . $voucherId . "' data-orderno='" . $voucherId . "' class='attachFile' name='voucher'>
        <input type='button' id='uploadFile' data-orderno='" . $voucherId . "' class='uploadFile' name='uploadFile' value='upload'>
        </form>" . $vouchers;

        $checked = ((int) $row['booked'] === 1) ? "checked" : "";
        $r[] = ($checked === "") ?
            "<input type='checkbox' id='booked" . $voucherId . "' data-orderno='" . $voucherId . "' class='booked' name='booked' value='1'>" :
            "Booked";

        $r[] = "<a class='btn btn-info' target='_blank' href='paymentVoucherPrint.php?orderno=" . $voucherId . "&supptrans=" .
            (int) $row['supptransno'] . "'>Print</a>";
        $r[] = "<a class='btn btn-info' target='_blank' href='paymentVoucherPrint.php?orderno=" . $voucherId . "&supptrans=" .
            (int) $row['supptransno'] . "&duplicate=1'>Duplicate</a>";

        $data[] = $r;
    }

    echo json_encode($data);
