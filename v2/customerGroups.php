<?php 

	$active = "dashboard";

	include_once("config.php");
	
	if(isset($_POST['findByBranch'])){
		
		$SQL = "SELECT * FROM cgdetails WHERE branchcode='".$_POST['branchCode']."'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) == 1){
			
			$row = mysqli_fetch_assoc($res);
			echo json_encode(['status' => 'success', 'id' => $row['cgid']]);
			
		}else{
			echo json_encode(['status' => 'error','id' => 'none']);
		}
		
		return;
		
	}
	if(isset($_POST['findByBranchName'])){
		
	$SQL = "SELECT * FROM cgdetails 
				INNER JOIN custbranch ON( cgdetails.branchcode = custbranch.branchcode
				AND custbranch.brname LIKE '%".str_replace(" ","%",$_POST['branchName'])."%')";
		$res = mysqli_query($db, $SQL);
			
		if(mysqli_num_rows($res) >= 1){
			$arr=[];
			while($row = mysqli_fetch_assoc($res))
				$arr[] = $row['cgid'];
		
			echo json_encode(['status' => 'success', 'id' => $arr]);
			
		}else{
			echo json_encode(['status' => 'error','id' => 'none']);
		}
		
		return;
		
	}
	
	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	$SQL = "SELECT customergroups.id,alias,salesman.salesmanname,cgassignments.target,cgassignments.salesman 
			FROM customergroups 
			LEFT OUTER JOIN cgassignments ON (customergroups.id = cgassignments.cgid
				AND cgassignments.year = '".date('Y')."')
			LEFT OUTER JOIN salesman ON salesman.salesmancode=cgassignments.salesman";
	$groups = mysqli_query($db,$SQL);
	
	$SQL = "SELECT * FROM salesman";
	$salesmen = mysqli_query($db, $SQL);

?>
<link rel="stylesheet" href="assets/customerGroups.css">
<input type="hidden" id="FormID" value="<?php ec($_SESSION['FormID']); ?>">
<div class="content-wrapper main-body">

	<section class="content-header">
		<h1 class="text-center">
			<i class="fa fa-edit"></i>
			Manage Customer Groups
			<button class="btn btn-success" id="newCustomerGroup">
				<i class="fa fa-plus"></i>
				New Group
			</button>
		</h1>
		<h3 class="text-center">
			Search 
			<input class="filter-group-input" placeholder="Group Alias"/>
			<label class="search-result-count"></label>
		</h3>
    </section>
    <section class="content">
		<div class="row"> 
			<div class="col-md-12">
				<section class="group-container">
					<?php while($row = mysqli_fetch_assoc($groups)){ ?>
					<div id="group-<?php ec($row['id']); ?>" class="customer-group">
						<div class="group-display-view">
							<div class="group-name-display"><?php ec($row['alias']); ?></div>
							<div class="group-salesman" data-code="<?php ec($row['salesman']); ?>">
								<?php ec($row['salesmanname']); ?>
							</div>
							<div class="group-target"><?php ec($row['target']); ?></div>
							<section class="pull-right" style="font-size:9px">
								<button class="btn btn-warning edit-group"><i class="fa fa-pencil"></i></button>
								<button class="btn btn-info group-details-display"><i class="fa fa-plus"></i></button>
								<button class="btn btn-danger delete-customer-group">&times;</button>
							</section>
						</div>
						<div class="group-edit-view display-hidden">
							<input type="text" class="group-name-input">
							<section class="pull-right" style="font-size:9px">
								<button class="btn btn-success update-group-alias"><i class="fa fa-check"></i></button>
								<button class="btn btn-danger cancel-group-edit">&times;</button>
							</section>
						</div>
					</div>
					<?php } ?>
				</section>
			</div>	
		</div>
    </section>
	
</div>

<div class="content-wrapper overlay-container display-hidden">

	<section class="content" style="padding:0">
		<div class="row overlay">
			<div class="col-md-12">
				<div class="overlay-parent">
					<section class="overlay-close"> 
						<button class="overlay-close-button">&times;</button>
					</section>
					<section class="overlay-loading-icon">
						<i class="fa fa-spinner fa-spin" style="font-size:5em"></i>
					</section>
					<section class="overlay-body display-hidden">
						<input type="hidden" id="selected-group-id"/>
						<h2 class="text-center selected-group-name"></h2>
						<h2 class="text-center"><input type="number" id="selected-group-target"></input></h2>
						<div class="attach-branch-container">
							<select class="branch-assign" id="select-salesman">
								<option value="">Select Salesman</option>
								<?php while($salesman = mysqli_fetch_assoc($salesmen)){ ?>
									<option value="<?php ec($salesman['salesmancode']); ?>">
										<?php ec($salesman['salesmanname']); ?>
									</option>
								<?php } ?>
							</select>
							<select class="branch-assign" id="select-debtorno"></select>
							<select class="branch-assign" id="select-branch-code"></select>
							<button class="btn btn-success branch-assign" id="assign-branch">Attach</button>			
						</div>
						<div class="branches-container">
						</div>
					</section>
				</div>
			</div>
		</div>
	</section>
	
</div>

<?php
	include_once("includes/footer.php");
?>

<script src="assets/customerGroups.js?v=<?php echo rand(1,99999); ?>"></script>

<?php
	include_once("includes/foot.php");
?>