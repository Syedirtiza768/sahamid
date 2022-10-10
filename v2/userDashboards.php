<?php 

	$active = "dashboard";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, 'assign_user_dashboard')){
		header("Location: /sahamid");
		exit();
	}
	
	if(isset($_POST['getUserPermissions'])){
		
		if(!isset($_POST['UserID'])){
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parameters...'
			]);
			return;
		}
		
		$UserID = $_POST['UserID'];
		
		$SQL = "SELECT dashboards.id,
						dashboards.name,
						dashboards.description
				FROM user_dashboards
				INNER JOIN dashboards ON dashboards.id = user_dashboards.dashboard_id
				WHERE user_dashboards.user_id='$UserID'";
		$res = mysqli_query($db,$SQL);
		
		$data = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$id = $row['id'];
			
			$row['action'] = "<a class='btn btn-warning' href='/sahamid/v2/dashboard-UD.php?Dashboard=$id' >Edit</a>&nbsp;";
			$row['action'] .= "<button class='btn btn-danger removeUserPermission' data-id='$id' >&times;</button>";
			
			$data[] = $row;
			
		}
		
		echo json_encode([
			'status' => 'success',
			'data'	 => $data
		]);
		return;

	}
	
	if(isset($_POST['removeUserPermission'])){
		
		if(!isset($_POST['id'])){
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parameters...'
			]);
			return;
		}
		
		$id = $_POST['id'];
		
		$SQL = "DELETE FROM user_dashboards WHERE id=$id";
		DB_query($SQL, $db);

		echo json_encode([
			'status' => 'success',
			'id'	 => $id
		]);
		return;

	}
	
	if(isset($_POST['assignUserPermission'])){
		
		if(!isset($_POST['UserID']) || !isset($_POST['permission'])){
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parameters...'
			]);
			return;
		}
		
		$UserID 	= $_POST['UserID'];
		$permission = $_POST['permission'];
		
		$SQL = "SELECT * FROM user_dashboards WHERE user_id='$UserID' AND dashboard_id='$permission'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) >= 1){
			echo json_encode([
				'status' 	=> 'warning',
				'message' 	=> 'Already Assigned!'
			]);
			return;
		} 
		
		$SQL = "INSERT INTO user_dashboards (user_id, dashboard_id)
				VALUES('$UserID','$permission')";
		DB_query($SQL, $db);
		
		$SQL = "SELECT name,description FROM dashboards WHERE id='$permission'";
		$res = mysqli_fetch_assoc(mysqli_query($db, $SQL));
		
		$id = $_SESSION['LastInsertId'];
		$name = $res['name'];
		$description = $res['description'];
		
		$row = [];
		$row['name'] 		= $name;
		$row['description'] = $description;
		$row['action']		= "<button class='btn btn-danger removeUserPermission' data-id='".$id."' >&times;</button>";
		
		echo json_encode([
			'status' => 'success',
			'row'	 => $row
		]);
		return;

	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	$SQL = "SELECT userid,realname FROM www_users";
	$users = mysqli_query($db,$SQL);
	
	$SQL = "SELECT * FROM dashboards";
	$dashboards = mysqli_query($db,$SQL);
	
	

?>
<link rel="stylesheet" href="assets/userPermissions.css?v=<?php echo rand(0,99999); ?>">
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">

<div class="content-wrapper">
    
	<section class="content-header">
		<h2 class="text-center">
			User Dashboards 	
			<select id="selectedUser">
				<option value="">Select User</option>
				<?php while($row=mysqli_fetch_assoc($users)){ ?>
					<option value="<?php ec($row['userid']);?>"><?php ec($row['realname']);?></option>
				<?php } ?>
			</select>
		</h2>
		
    </section>

    <section class="content">
		<div class="row">
			<div class="newPermissionContainer col-md-12">
				<div class="closeButtonContainer">
					<button class="btn btn-danger closePermissionContainer">&times;</button>
				</div>
				<select id="selectedPermission">
					<option value="">Select Dashboard</option>
						<?php while($row=mysqli_fetch_assoc($dashboards)){?>
							<option value="<?php ec($row['id']); ?>"><?php ec($row['name']); ?></option>
						<?php } ?>
				</select>
				<button class="btn btn-success assignSelectedPermission">Assign</button>
			</div>
			<div class="userPermissions col-md-12">
				<table id="userPermissions" class="table table-striped table-responsive">
					<thead>
						<tr>
							<th>Dashboard Name</th>
							<th>Dashboard Description</th>
							<th class="fit no-wrap">Action</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script>
	$(document).ready(function(){

		var permissionsTable = $("#userPermissions").DataTable({
			"columns" : [
				{"data" : "name"},
				{"data" : "description"},
				{"data" : "action"},
			]
		});
		
		$("#userPermissions_length").html("<h3 class='no-margin'>Assisned Dashboards <button class='btn btn-success' id='assignNewPermission'>Assign New Dashboard</button> </h3>");

		$(".closePermissionContainer").on("click",function(){
			
			$(".newPermissionContainer").removeClass("containerVisible");
			
		});
		
		$("#assignNewPermission").on("click",function(){
			
			if ($("#selectedUser").val()=="") return;
			
			$(".newPermissionContainer").addClass("containerVisible");
			
		});
		
		$("#selectedUser").on("change",function(){
			 
			let UserID = $(this).val();
			
			if(UserID.trim() == ""){
				return;
			}
			
			$(".newPermissionContainer").removeClass("containerVisible");
			
			permissionsTable.clear().draw();
			
			let FormID = $("#FormID").val();
			let getUserPermissions = true;
			
			$.post("userDashboards.php",{
				FormID,getUserPermissions,UserID
			},function(res,status,something){
				res = JSON.parse(res);
				
				if(res.status == "success"){
					permissionsTable.rows.add(res.data).draw();
				}
				
			});
		
		});
		
		$(document.body).on("click", ".removeUserPermission", function(){
			
			let FormID = $("#FormID").val();
			let removeUserPermission = true;
			let ref = $(this);
			
			ref.prop("disabled",true);
			
			let id = ref.attr("data-id");
			
			$.post("userDashboards.php",{
				FormID,removeUserPermission,id
			},function(res,status,something){
				res = JSON.parse(res);
				
				if(res.status == "success"){
					permissionsTable.row(ref.parent().parent()).remove().draw();
				}
				
			});
			
		});
		
		$(".assignSelectedPermission").on("click", function(){
			
			let FormID = $("#FormID").val();
			let permission = $("#selectedPermission").val();
			let assignUserPermission = true;
			
			let ref = $(this);
			ref.prop("disabled",true);
			
			if(permission.trim() == ""){
				ref.prop("disabled",false);
				return;
			}
			
			let UserID = $("#selectedUser").val();
			
			if(UserID.trim() == ""){
				ref.prop("disabled",false);
				return;
			}
			
			$.post("userDashboards.php",{
				FormID,assignUserPermission,UserID,permission
			},function(res,status,something){
				res = JSON.parse(res);
				
				if(res.status == "success"){
					permissionsTable.row.add(res.row).draw();
				}
				
				$("#selectedPermission").val("");
				ref.prop("disabled",false);
				
			});
			
		});
		
		$(".viewAllAssigned").on("click", function(){
			
			let FormID = $("#FormID").val();
			let viewAllAssigned = true;
			
			permissionsTable.clear().draw();
			
			$("#selectedUser").val("");
			$(".newPermissionContainer").removeClass("containerVisible");
			
			$.post("userDashboards.php",{
				FormID,viewAllAssigned
			},function(res,status,something){
				
				res = JSON.parse(res);
				
				if(res.status == "success"){
					permissionsTable.rows.add(res.data).draw();
				}
				
			});
			
		});
		
	});
</script> 

<?php
	include_once("includes/foot.php");
?>