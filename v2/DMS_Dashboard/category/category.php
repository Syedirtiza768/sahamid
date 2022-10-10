<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="../sidebarStyles.css">
	<link rel="stylesheet" href="./links/icons.css">
	<link rel="stylesheet" href="./links/bootstrap.css">
	 <link rel="stylesheet" type="text/css" href="datatable/dt.css">
	<script src="../js/jquery.min.js"></script>
	<script src="./links/bootstrap.js"></script>
    <title>DMS</title>
</head>
<body>

<div class="container">  
    <div class="sidebar">
                <nav>
				<h1 style="color: white;
                margin-left: 42px;
                margin-top: 30px;
                margin-bottom: 20px;
                font-size: 12px;
                text-decoration: underline;"> Document Managment System</h1>
                    <ul >
                        <!-- <li class="active"><a href="#">Dashboard</a></li> -->
                        <li style="font-size: 100%;"><a>Category</a></li>
                        <li style="font-size: 100%;"><a class="user-page">Users</a></li>
						<li style="font-size: 100%;"><a class="edit-perm-page">Edit Permission</a></li>
                    	<li style="font-size: 100%;"><a class="add-perm-page">Add Permission</a></li>
                    </ul>
                </nav>
    </div>
    <div class="main-content" style="background-color: white;
    width: 81%;
    height: 100%;
    margin-left: 168px;
    padding: 73px 21px;">
    <p id="success"></p>
	<div class="table-wrapper">
		<div class="table-title">
			<div class="row">
				<div class="col-sm-6">
					<b><h1>Category</b></h1>
				</div>
				<div class="col-sm-6" >
					<a href="#addCategoryModal" class="btn btn-success" data-toggle="modal"><i class="material-icons"></i> <span>Add</span></a>
					<a href="JavaScript:void(0);" class="btn btn-danger" id="delete_multiple"><i class="material-icons"></i> <span>Del</span></a>						
				</div>
			</div>
		</div>
		<table id= "myTable" class="table table-striped table-hover">
			<thead>
				<tr>
					<th>
						<span class="custom-checkbox">
							<input type="checkbox" id="selectAll">
							<label for="selectAll"></label>
						</span>
					</th>
                    <th>SL NO</th>
					<th>Category Name</th>
					<th>Category Code</th>
					<th>Category Action</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			$result = mysqli_query($conn,"SELECT * FROM category");
				$i=1;
				while($row = mysqli_fetch_array($result)) {
			?>
			<tr id="<?php echo $row["id"]; ?>">
			<td>
						<span class="custom-checkbox">
							<input type="checkbox" class="user_checkbox" data-user-id="<?php echo $row["id"]; ?>">
							<label for="checkbox2"></label>
						</span>
					</td>
				<td><?php echo $i; ?></td>
				<td><?php echo $row["cat_name"]; ?></td>
				<td><?php echo $row["cat_code"]; ?></td>
				<td>
					<a href="#editCategoryModal" class="edit" data-toggle="modal">
						<i class="material-icons update" data-toggle="tooltip" 
						data-id="<?php echo $row["id"]; ?>"
						data-name="<?php echo $row["cat_name"]; ?>"
						data-code="<?php echo $row["cat_code"]; ?>"
						title="Edit"></i>
					</a>
					<a href="#deleteCategoryModal" class="delete" data-id="<?php echo $row["id"]; ?>" data-toggle="modal"><i class="material-icons" data-toggle="tooltip" 
						title="Delete"></i></a>
				</td>
			</tr>
			<?php
			$i++;
			}
			?>
			</tbody>
		</table>
		
	</div>
</div>

<!-- Edit Modal HTML -->
<div id="editCategoryModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="update_form">
				<div class="modal-header">						
					<h4 class="modal-title">Edit Category</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="cat_id_u" name="id" class="form-control" required>					
					<div class="form-group">
						<label>Category Name</label>
						<input type="text" id="cat_name_u" name="cat_name" class="form-control" required>
					</div>
					<div class="form-group">
						<label>Category Code</label>
						<input type="email" id="cat_code_u" name="cat_code" class="form-control" required>
					</div>				
				</div>
				<div class="modal-footer">
				<input type="hidden" value="2" name="type">
					<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
					<button type="button" class="btn btn-info" id="update">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- Delete Modal HTML -->
<div id="deleteCategoryModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form>
				<div class="modal-header">						
					<h4 class="modal-title">Delete Category</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="id_d" name="id" class="form-control">					
					<p>Are you sure you want to delete this Record?</p>
					<p class="text-warning"><small>This action cannot be undone.</small></p>
				</div>
				<div class="modal-footer">
					<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
					<button type="button" class="btn btn-danger" id="delete">Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- Add Modal HTML -->
<div id="addCategoryModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="cat_form">
				<div class="modal-header">						
					<h4 class="modal-title">Add Category</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">					
					<div class="form-group">
						<label>Category Name</label>
						<input type="text" id="cat_name" name="cat_name" class="form-control" required>
					</div>
					<div class="form-group">
						<label>Category Code</label>
						<input type="text" id="cat_code" name="cat_code" class="form-control" required>
					</div>
				<div class="modal-footer">
					<input type="hidden" value="1" name="type">
					<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
					<button type="button" class="btn btn-success" id="btn-add">Add</button>
				</div>
			</form>
		</div>
	</div>
</div>
</div>
<!-- Show users page -->-->
<script>
        $(document).on('click', '.user-page', function() {
            window.location.replace('../user/users.php');
        });
</script>

<!-- Show category page-->-->
<script>
        $(document).on('click', '.show-category', function() {
            window.location.replace('category/category.php');
        });
    </script>

    <!-- Show editPerm page-->-->
    <script>
        $(document).on('click', '.edit-perm-page', function() {
            window.location.replace('../editPerm/editPerm.php');
        });
    </script>

	<!-- Show editPerm page-->-->
    <script>
        $(document).on('click', '.add-perm-page', function() {
            window.location.replace('../addPerm/addPerm.php');
        });
    </script>

<script>
		 $(document).ready(function() {
		$('#myTable').DataTable();
		 });
</script>
	
	
<script>
	$(document).on('click', '#btn-add', function(e) {
    var data = $("#cat_form").serialize();
    $.ajax({
        data: data,
        type: "post",
        url: "backend/save.php",
        success: function(dataResult) {
            var dataResult = JSON.parse(dataResult);
            if (dataResult.statusCode == 200) {
                $('#addCategoryModal').modal('hide');
                alert('Data added successfully !');
                location.reload();
            } else if (dataResult.statusCode == 201) {
                alert(dataResult);
            }
        }
    });
});
$(document).on('click', '.update', function(e) {
    var id = $(this).attr("data-id");
    var name = $(this).attr("data-name");
    var email = $(this).attr("data-code");
    $('#cat_id_u').val(id);
    $('#cat_name_u').val(name);
    $('#cat_code_u').val(email);
});

$(document).on('click', '#update', function(e) {
    var data = $("#update_form").serialize();
    $.ajax({
        data: data,
        type: "post",
        url: "backend/save.php",
        success: function(dataResult) {
            $('#editCategoryModal').modal('hide');
            alert('Data updated successfully !');
            location.reload();

        }
    });
});
$(document).on("click", ".delete", function() {
    var id = $(this).attr("data-id");
    $('#id_d').val(id);

});
$(document).on("click", "#delete", function() {
    $.ajax({
        url: "backend/save.php",
        type: "POST",
        cache: false,
        data: {
            type: 3,
            id: $("#id_d").val()
        },
        success: function(dataResult) {
            $('#deleteCategoryModal').modal('hide');
            $("#" + dataResult).remove();

        }
    });
});
$(document).on("click", "#delete_multiple", function() {
    var user = [];
    $(".user_checkbox:checked").each(function() {
        user.push($(this).data('user-id'));
    });
    if (user.length <= 0) {
        alert("Please select records.");
    } else {
        WRN_PROFILE_DELETE = "Are you sure you want to delete " + (user.length > 1 ? "these" : "this") + " row?";
        var checked = confirm(WRN_PROFILE_DELETE);
        if (checked == true) {
            var selected_values = user.join(",");
            console.log(selected_values);
            $.ajax({
                type: "POST",
                url: "backend/save.php",
                cache: false,
                data: {
                    type: 4,
                    id: selected_values
                },
                success: function(response) {
                    var ids = response.split(",");
                    for (var i = 0; i < ids.length; i++) {
                        $("#" + ids[i]).remove();
                    }
                }
            });
        }
    }
});
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
    var checkbox = $('table tbody input[type="checkbox"]');
    $("#selectAll").click(function() {
        if (this.checked) {
            checkbox.each(function() {
                this.checked = true;
            });
        } else {
            checkbox.each(function() {
                this.checked = false;
            });
        }
    });
    checkbox.click(function() {
        if (!this.checked) {
            $("#selectAll").prop("checked", false);
        }
    });
});
	</script>
<script type="text/javascript" src="datatable/dt.js"></script>
</body>

</html>


