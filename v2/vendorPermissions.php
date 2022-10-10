<?php 

	$active = "dashboard";

	include_once("config.php");
	
	if(isset($_POST['getUserPermissions'])){
		
		if(!isset($_POST['UserID'])){
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parameters...'
			]);
			return;
		}
		
		$UserID = $_POST['UserID'];
		
		$SQL = "SELECT vendor_permission.id,
						suppliers.suppname as name,
						suppliers.supplierid as description
				FROM vendor_permission
				INNER JOIN suppliers ON suppliers.supplierid = vendor_permission.permission
				WHERE userid='$UserID'";
		$res = mysqli_query($db,$SQL);
		
		$data = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$id = $row['id'];
			
			$row['action'] = "<button class='btn btn-danger removeUserPermission' data-id='$id' >&times;</button>";
			
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
		
		$SQL = "DELETE FROM vendor_permission WHERE id=$id";
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
		
		$SQL = "SELECT * FROM vendor_permission WHERE userid='$UserID' AND permission='$permission'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) >= 1){
			echo json_encode([
				'status' 	=> 'warning',
				'message' 	=> 'Already Assigned!'
			]);
			return;
		} 
		
		$SQL = "INSERT INTO vendor_permission (userid, permission)
				VALUES('$UserID','$permission')";
		DB_query($SQL, $db);
		$id = $_SESSION['LastInsertId'];

		if($permission == "*"){
			$name = "All Access";
			$description = "All Access";
		}else{
			$SQL = "SELECT suppname as name, supplierid as description FROM suppliers WHERE supplierid='$permission'";
			$res = mysqli_fetch_assoc(mysqli_query($db, $SQL));
			$name = $res['name'];
			$description = $res['description'];
		}
		
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
	
	if(isset($_POST['viewAllAssigned'])){
		
		$SQL = "SELECT  vendor_permission.id,
						vendor_permission.userid,
						suppname as name,
						supplierid as description
				FROM vendor_permission
				INNER JOIN suppliers ON suppliers.supplierid = vendor_permission.permission";
		$res = mysqli_query($db,$SQL);
		
		$data = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$row['action'] = $row['userid'];
			
			$data[] = $row;
		
		}
		
		echo json_encode([
			'status' => 'success',
			'data'	 => $data
		]);
		return;
		
	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	$SQL = "SELECT userid,realname FROM www_users";
	$users = mysqli_query($db,$SQL);
	
	$SQL = "SELECT * FROM suppliers";
	$permissions = mysqli_query($db,$SQL);
	
	

?>
<link rel="stylesheet" href="assets/userPermissions.css?v=<?php echo rand(0,99999); ?>">
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">

<div class="content-wrapper">
    
	<section class="content-header">
		<h2 class="text-center">
			Vendor Permissions 	
			<select id="selectedUser">
				<option value="">Select User</option>
				<?php while($row=mysqli_fetch_assoc($users)){ ?>
					<option value="<?php ec($row['userid']);?>"><?php ec($row['realname']);?></option>
				<?php } ?>
			</select>
			<button class="btn btn-success viewAllAssigned">View All Assigned</button>
		</h2>
		
    </section>

    <section class="content">
		<div class="row">
			<div class="newPermissionContainer col-md-12">
				<div class="closeButtonContainer">
					<button class="btn btn-danger closePermissionContainer">&times;</button>
				</div>
				<select id="selectedPermission">
					<option value="">Select Permission</option>
					<option value="*">All</option>
						<?php while($row=mysqli_fetch_assoc($permissions)){?>
							<option value="<?php ec($row['supplierid']); ?>"><?php ec($row['suppname']); ?></option>
						<?php } ?>
				</select>
				<button class="btn btn-success assignSelectedPermission">Assign</button>
			</div>
			<div class="userPermissions col-md-12">
				<table id="userPermissions" class="table table-striped table-responsive">
					<thead>
						<tr>
							<th>Permission Name</th>
							<th>Permission Description</th>
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

<script src="assets/vendorPermissions.js?v=<?php echo rand(0,99999); ?>"></script> 

<?php
	include_once("includes/foot.php");
?>