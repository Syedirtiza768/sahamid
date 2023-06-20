<?php
session_start(); 
include 'config.php';
?>

!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../sidebarStyles.css">
    <link rel="stylesheet" href="./links/icons.css">
	<link rel="stylesheet" href="./links/bootstrap.css">
	 <link rel="stylesheet" type="text/css" href="datatable/dt.css">
	<script src="../js/jquery.min.js"></script>
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
                <ul>
                    <li style="font-size: 100%;"><a class="show-category">Category</a></li>
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
					<b><h1 style="font-size: 23px;">Selected Users Can Add New Files</b></h1><br>
				</div>
			</div>
		</div>
		<table id= "myTable" class="table table-striped table-hover">
			<thead>
				<tr>
					<th>
						<!-- <span class="custom-checkbox">
							<input type="checkbox" id="selectAll">
							<label for="selectAll"></label>
						</span> -->
					</th>
					<th>Real Name</th>
					<th>User ID</th>
				</tr>
			</thead>
			<tbody>
                
			<?php
			$result = mysqli_query($conn,"SELECT * FROM www_users");
				$i=1;
				while($row = mysqli_fetch_array($result)) {
                    $addPerm="";

                    $user_id = $_SESSION['UserID'];
                    $result1 = mysqli_query($conn,"SELECT permission FROM addPerm WHERE userid='".$row["userid"]."' ");
				while($row1 = mysqli_fetch_array($result1)) {
                    $addPerm = $row1['permission'];
                    
                }
			?>
			<tr id="<?php echo $row["userid"]; ?>">
            <?php 
            
             if($addPerm==1){
            ?>
			        <td>
						<span class="custom-checkbox">
							<input type="checkbox" class="user_checkbox" id ="1" user-id="<?php echo $row["userid"]; ?>" checked>
							<label for="checkbox2"></label>
						</span>
					</td>
            <?php
                }
                else{
            ?>
                    <td>
						<span class="custom-checkbox">
							<input type="checkbox" class="user_checkbox" id ="0" user-id="<?php echo $row["userid"];?>" >
							<label for="checkbox2"></label>
						</span>
					</td>
              <?php
              }
              ?>      
				<td><?php echo $row["realname"]; ?></td>
				<td><?php echo $row["userid"]; ?></td>
			</tr>
			<?php
			}
			?>
			</tbody>
		</table>
		
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
            window.location.replace('../category/category.php');
        });
    </script>


    <!-- Show editPerm page-->-->
    <script>
        $(document).on('click', '.edit-perm-page', function() {
            window.location.replace('../editPerm/editPerm.php');
        });
    </script>

     <!-- Show addPerm page-->-->
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
        $(document).on("click", ".user_checkbox", function() {
        var user_id = $(this).attr("user-id");
        var id = $(this).attr("id");
        $.ajax({
            data: {'user_id': user_id, 'id':id},
            type: "post",
            url: "assignPerm.php",
            success: function(dataResult) {
                console.log(dataResult);
                

            }
        });

    });
    </script>


        <script type="text/javascript" src="datatable/dt.js"></script>
    
</body>

</html>