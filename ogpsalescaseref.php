<?php
include('configg.php');

// Handle POST request to update quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = $_POST['data'] ?? [];

    foreach ($updates as $key => $quantity) {
        // Key format is "id|stockid"
        [$id, $stockid] = explode('|', $key);
        $id = intval($id);
        $stockid = intval($stockid);
        $quantity = intval($quantity);

        $sql = "UPDATE ogpsalescaseref SET quantity = $quantity WHERE id = $id AND stockid = $stockid";
        mysqli_query($conn, $sql);
    }

    echo json_encode(['status' => 'success']);
    exit;
}

// Fetch data for table
$sql = "SELECT * FROM ogpsalescaseref WHERE quantity IS NOT NULL";
$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OGP Sales Case Ref Table</title>

    <!-- ✅ Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ✅ DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto bg-white shadow-lg p-6 rounded-lg">
    <h2 class="text-2xl font-bold mb-4 text-gray-700">OGP Sales Case References</h2>

    <div class="overflow-auto">
        <table id="ogpTable" class="stripe hover w-full text-sm">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-2">ID</th>
                    <th class="p-2">Stock ID</th>
                    <th class="p-2">Dispatch ID</th>
                    <th class="p-2">Sales Case Ref</th>
                    <th class="p-2">Requested By</th>
                    <th class="p-2">Salesman</th>
                    <th class="p-2">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr data-key="<?= $row['id'] ?>|<?= $row['stockid'] ?>">
                        <td class="p-2"><?= $row['id'] ?></td>
                        <td class="p-2"><?= $row['stockid'] ?></td>
                        <td class="p-2"><?= $row['dispatchid'] ?></td>
                        <td class="p-2"><?= $row['salescaseref'] ?></td>
                        <td class="p-2"><?= $row['requestedby'] ?></td>
                        <td class="p-2"><?= $row['salesman'] ?></td>
                        <td class="p-2">
                            <input type="number" class="qty-input border border-gray-300 rounded px-2 py-1 w-20" value="<?= $row['quantity'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-right">
        <button id="submitChanges" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded">
            Submit Changes
        </button>
    </div>
</div>

<!-- ✅ jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#ogpTable').DataTable({
        pageLength: 10
    });

    // Handle submit button
    $('#submitChanges').click(function () {
        const updatedData = {};

        $('#ogpTable tbody tr').each(function () {
            const key = $(this).data('key'); // format: "id|stockid"
            const qty = $(this).find('input.qty-input').val();
            if (qty !== undefined && key) {
                updatedData[key] = qty;
            }
        });

        $.post('ogpsalescaseref.php', { data: updatedData }, function (response) {
            if (response.status === 'success') {
                alert('Quantities updated successfully!');
            } else {
                alert('Failed to update quantities.');
            }
        }, 'json');
    });
});
</script>
</body>
</html>
