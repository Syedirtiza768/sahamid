<?php
session_start();
include_once("config1.php");

$userId = $_SESSION['UserID'];
$query = "SELECT * FROM www_users WHERE userid = '$userId'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>DC Attachments</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>
<body>

<h2>DC Attachments</h2>

<!-- Upload Form -->
<form id="uploadForm" enctype="multipart/form-data">
    <label>File Type:</label>
    <select name="fileType" required>
        <option value="PO">PO</option>
        <option value="Courier Slip">Courier Slip</option>
        <option value="Invoice">Invoice</option>
        <option value="Commercial Invoice">Commercial Invoice</option>
        <option value="GRB">GRB</option>
    </select>

    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

<hr>

<!-- DataTable -->
<table id="dcTable" class="display">
    <thead>
        <tr>
            <th>ID</th>
            <th>File Type</th>
            <th>File</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

<script>
$(document).ready(function() {
    // ✅ Initialize DataTable with search, sort, server-side
    let table = $('#dcTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "fetch_attachments.php", // <-- you’ll implement this backend file
            type: "POST"
        },
        columns: [
            { data: "id" },
            { data: "fileType" },
            { data: "file", render: function(data, type, row) {
                return `<a href="uploads/${data}" target="_blank">${data}</a>`;
            }},
            { data: "uploadedBy" },
            { data: "uploadedAt" },
            { data: null, render: function(data, type, row) {
                return `<button class="deleteBtn" data-id="${row.id}">Delete</button>`;
            }}
        ]
    });

    // ✅ Handle upload
    $("#uploadForm").on("submit", function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "upload_attachment.php", // <-- handle file upload
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function() {
                table.ajax.reload();
                alert("File uploaded successfully!");
            }
        });
    });

    // ✅ Handle delete
    $('#dcTable').on('click', '.deleteBtn', function() {
        let id = $(this).data("id");

        if(confirm("Are you sure to delete this file?")) {
            $.post("delete_attachment.php", { id: id }, function() {
                table.ajax.reload();
            });
        }
    });
});
</script>

</body>
</html>
