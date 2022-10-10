<?php 

	$active = "tasks";

	include_once("config.php");

	if(isset($_POST['saveNewTask'])){

		if(!isset($_POST['title']) || !isset($_POST['description']) || 
			!isset($_POST['from']) || !isset($_POST['to'])){

			echo json_encode([

					'status' => 'error',
					'message' => 'Missing Parms.'

				]);
			return;

		}

		$title = htmlentities(trim($_POST['title']));
		$desc = htmlentities(trim($_POST['description']));
		$from_date = $_POST['from'];
		$to_date = $_POST['to'];

		if($from_date == ""){

			$from_date = date("Y-m-d H:i:s");

		}

		if($to_date == ""){
			$to_date = date("Y-m-d H:i:s", strtotime($from_date));
		}

		$from = $from_date;
		$to = $to_date;

		$aT = $_POST['assignedTo'];
		$cU = $_SESSION['UserID'];

		$SQL = "INSERT INTO tasks(title,description,from_date,to_date,assigned_by,assigned_to,created_by,created_at)
				VALUES('".$title."','".$desc."','".$from."','".$to."','".$cU."','".$aT."','".$cU."','".date('Y-m-d H:i:s')."')";
		DB_query($SQL, $db);

		echo json_encode([
				'status' => 'success'
			]);
		return;

	}

	if(isset($_POST['updateStatus'])){

		if(!isset($_POST['id']) || !isset($_POST['status'])) {

			echo json_encode([

					'status' => 'error',
					'message' => 'Missing Parms.'

				]);
			return;

		}

		$SQL = "UPDATE tasks SET status = '".$_POST['status']."'
				WHERE id='".$_POST['id']."'";
		DB_query($SQL, $db);

		echo json_encode([
				'status' => 'success'
			]);
		return;

	}

	if(isset($_POST['updateAssignTo'])){

		if(!isset($_POST['id']) || !isset($_POST['assignTo'])) {

			echo json_encode([

					'status' => 'error',
					'message' => 'Missing Parms.'

				]);
			return;

		}

		$SQL = "UPDATE tasks SET assigned_by = '".$_SESSION['UserID']."',assigned_to='".$_POST['assignTo']."'
				WHERE id='".$_POST['id']."'";
		DB_query($SQL, $db);

		echo json_encode([
				'status' => 'success'
			]);
		return;

	}

	if(isset($_POST['removeTask'])){

		if(!isset($_POST['id'])) {

			echo json_encode([

					'status' => 'error',
					'message' => 'Missing Parms.'

				]);
			return;

		}

		$SQL = "UPDATE tasks SET deleted_at = '".date("Y-m-d H:i:s")."'
				WHERE id='".$_POST['id']."' AND created_by='".$_SESSION['UserID']."'";
		DB_query($SQL, $db);

		echo json_encode([
				'status' => 'success'
			]);
		return;

	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

	$SQL = "SELECT userid,realname FROM www_users";
	$res = mysqli_query($db, $SQL);

	$users = [];

	while($row = mysqli_fetch_assoc($res)){
		$users[$row['userid']] = $row['realname'];
	}

	// unset($users[$_SESSION['UserID']]);

	$SQL = "SELECT * FROM tasks 
			WHERE deleted_at IS NULL 
			AND (created_by = '".$_SESSION['UserID']."'
					OR assigned_by = '".$_SESSION['UserID']."'
					OR assigned_to = '".$_SESSION['UserID']."')";
	$res = mysqli_query($db, $SQL);

	$tasks = [];

	while($row = mysqli_fetch_assoc($res)){

		$task = [];

		$task['title'] = html_entity_decode($row['title']);
		$task['desc']  = html_entity_decode($row['description']);
		$task['from']  = date("Y/m/d",strtotime($row['from_date']));
		$task['to']    = date("Y/m/d",strtotime($row['to_date']));
		$task['aB']	   = ($users[$row['assigned_by']]);
		$task['status']= $row['status'];
		$task['id']	   = $row['id'];

		if($row['assigned_to'] != ""){
			$task['aT']	   = ($users[$row['assigned_to']]);
		}else{
			$task['aB'] = "";
		}
		
		$task['cB'] = $row['created_by'];

		$tasks[] = $task;

	}

	unset($users[$_SESSION['UserID']]);

?>
<style>
	.table-head{position: relative; top:60px; background: #424242; left: 0; color: white;} .table-head th{border: 1px solid white;} .center-fit{width: 1%; white-space: nowrap; text-align: center;} .modal{display:none;position:fixed;z-index:1000;left:0;top: 0;width: 100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{background-color:#fefefe;margin: 5% auto;padding: 20px;border: 1px solid #888;width: 80%;}.close {color: #aaa;float: right;font-size: 28px;font-weight: bold;}.close:hover,.close:focus{color: black;text-decoration: none;cursor: pointer;}.tasktitle{border:1px solid #424242; border-radius: 7px; padding: 5px; width: 100%}.taskdescription{border:1px solid #424242; border-radius: 7px;padding: 5px; width: 100%; max-width: 100%; min-width: 100%;min-height: 100px;max-height: 100px;}#saveNewTask{margin-top:15px;}.warningsbox{display:none; margin-top: 15px;}.dontFBreak{width: 1%; white-space: nowrap;}td{vertical-align: middle !important;}.tooltip {position: relative;display: inline-block;border-bottom: 1px dotted black;visibility: visible !important;opacity: 1 !important;z-index: 998 !important;}.tooltip .tooltiptext {visibility: hidden;width: 400px;background-color: black;color: #fff;text-align: center;border-radius: 6px;padding: 10px;white-space: pre-wrap;position: absolute;top: -17px;left: 105%;}.tooltip:hover .tooltiptext {visibility: visible;background: #424242;}
</style>

<div class="content-wrapper">
    <section class="content-header">
      
    </section>

    <section class="content">
	    
		<div class="row">
			<div class="col-md-12">
				<div class="box box-primary">
		            <div class="box-header ui-sortable-handle" style="cursor: move;">
		              	<i class="ion ion-clipboard"></i>
		              	<h3 class="box-title">Task Management <a href="tasksCalender.php" class="btn btn-info">Calender View</a></h3>
		              	<button type="button" class="btn btn-default pull-right" id="addNewTask">
		              		<i class="fa fa-plus"></i> Add Task
		              	</button>
		            </div>
	            	<div class="box-body" style="min-height: calc(100vh - 230px); max-height: calc(100vh - 230px); overflow-y: scroll;">
		              	<table class="table table-striped">
		              		<thead class="table-head">
		              			<tr>
		              				<th>Title</th>
		              				<th class="center-fit">From</th>
		              				<th class="center-fit">To</th>
		              				<th class="center-fit">Assigned By</th>
		              				<th class="center-fit">Assigned To</th>
		              				<th class="center-fit">Status</th>
		              				<th class="center-fit">Assign To</th>
		              				<th class="center-fit"></th>
		              			</tr>
		              		</thead>
		              		<tbody>
		              			<?php foreach($tasks as $task) { ?>
		              			<tr id="task-<?php echo ($task['id']); ?>">
		              				<td>
		              					<span class="tooltip"><?php echo ($task['title']); ?>
		              						<span class="tooltiptext"><?php echo ($task['desc']); ?></span>
		              					</span>
		              				</td>
		              				<td class="dontFBreak"><?php echo ($task['from']); ?></td>
		              				<td class="dontFBreak"><?php echo ($task['to']); ?></td>
		              				<td class="dontFBreak"><?php echo ($task['aB']); ?></td>
		              				<td class="dontFBreak"><?php echo ($task['aT']); ?></td>
		              				<td class="dontFBreak">
		              					<select style="width: 100px" class="taskStatusChange">
		              						<option value="Due" <?php ecif($task['status'] == "Due","selected"); ?>>
		              							Due
		              						</option>
		              						<option value="InProgress" <?php ecif($task['status'] == "InProgress","selected"); ?>>
		              							InProgress
		              						</option>
		              						<option value="Done" <?php ecif($task['status'] == "Done","selected"); ?>>
		              							Done
		              						</option>
		              					</select>
		              				</td>
		              				<td>
		              					<select style="width: 100px" class="assign_to">
		              						<option value="">Assign To</option>
		              						<option value="">Self</option>
		              						<?php foreach($users as $id => $name){ ?>
		              						<option value="<?php echo $id; ?>"><?php echo $name; ?></option>
		              						<?php } ?>
		              					</select>
		              				</td>
		              				<td>
										<?php if($task['cB'] == $_SESSION['UserID']){ ?>
		              					<button class="btn btn-danger removeTask">&times;</button>
										<?php } ?>
		              				</td>
		              			</tr>
		              			<?php } ?>
		              		</tbody>
		              	</table>
	            	</div>
	        	</div>
			</div>
		</div>

    </section>

    <div id="myModal" class="modal">

		<!-- Modal content -->
		<div class="modal-content">
		    <span class="close" style="color: white">&times;</span>
		    <p style="font-size: 2rem;
					  margin: -15px;
					  background: #424242;
					  padding: 15px;
					  color: white;">
				Add New Task
			</p>
			<div class="row" style="margin-top: 20px">
				<div class="col-md-12" style="padding-top: 10px">
					<div class="col-md-6">
						<h4>Title</h4>
						<input type="text" class="tasktitle newTaskTitle">
						<h4>Description</h4>
						<textarea type="text" class="taskdescription newTaskDescription"></textarea>
					</div>
					<div class="col-md-6">
						<h4>Assign To</h4>
						<select name="" class="tasktitle newTaskAssignedTo">
							<option value="">Self</option>
							<?php foreach($users as $id => $name){ ?>
							<option value="<?php echo $id; ?>"><?php echo $name; ?></option>
							<?php } ?>
						</select>
						<h4>From Date</h4>
						<input type="date" class="tasktitle newTaskFrom">
						<h4>To Date</h4>
						<input type="date" class="tasktitle newTaskTo">
					</div>
					<div style="clear: both;"></div>
					<div class="callout callout-warning warningsbox"></div>
					<button class="btn btn-success tasktitle" id="saveNewTask">Save Task</button>
				</div>
			</div>
		</div>

	</div>
  
</div>

<?php
	include_once("includes/footer.php");
?>
<script>
	
	var modal = document.getElementById('myModal');

	// Get the button that opens the modal
	var btn = document.getElementById("addNewTask");

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName("close")[0];

	// When the user clicks on the button, open the modal 
	btn.onclick = function() {
	    modal.style.display = "block";
	};

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
	    modal.style.display = "none";
	};

	// When the user clicks anywhere outside of the modal, close it
	// window.onclick = function(event) {
	//     if (event.target == modal) {
	//         modal.style.display = "none";
	//     }
	// }
	$("#saveNewTask").on("click", function(){
		let ref = $(this);
		ref.prop("disabled",true);
		$(".warningsbox").css("display","none");

		let title = $(".newTaskTitle").val();
		let description = $(".newTaskDescription").val();
		let assignedTo = $(".newTaskAssignedTo").val();
		let from = $(".newTaskFrom").val();
		let to = $(".newTaskTo").val();
		let saveNewTask = true;

		if(title.trim() == ""){
			$(".warningsbox").html("Title is required...");
			$(".warningsbox").css("display","block");
			ref.prop("disabled",false);
			return;
		}

		if(description.trim() == ""){
			$(".warningsbox").html("Description is required...");
			$(".warningsbox").css("display","block");
			ref.prop("disabled",false);
			return;
		}

		$.post("tasks.php?saveNewTask", {
			FormID: '<?php echo $_SESSION['FormID']; ?>',
			title,
			description,
			assignedTo,
			from,
			to,
			saveNewTask
		}, function(res, status, something){

			window.location.reload();

		});

	});

	var previousVal = "";

	$(".taskStatusChange").on("change",function(){

		let updateStatus = true;
		let id = $(this).parent().parent().attr("id").split("-")[1];
		let status = $(this).val();
		let ref = $(this);

		if(!confirm("Are You Sure you want to update status of this task to "+$(":selected", this).text().trim()+"?")){
			return;
		}


		$.post("tasks.php", {
			FormID: '<?php echo $_SESSION['FormID']; ?>',
			id,
			status,
			updateStatus
		}, function(res, status, something){

			res = JSON.parse(res);

			if(res.status == "success"){
				ref.css("border","2px solid green")
			}

		});
	});

	$(".assign_to").on("change",function(){

		let updateAssignTo = true;
		let id = $(this).parent().parent().attr("id").split("-")[1];
		let assignTo = $(this).val();
		let ref = $(this);

		if(!confirm("Are You Sure you want to assign this task to "+$(":selected", this).text()+"?")){
			$(this).val("");
			return;
		}


		$.post("tasks.php", {
			FormID: '<?php echo $_SESSION['FormID']; ?>',
			id,
			assignTo,
			updateAssignTo
		}, function(res, status, something){

			res = JSON.parse(res);

			if(res.status == "success"){
				ref.css("border","2px solid green")
			}

		});
	});

	$(".removeTask").on("click",function(){

		if(!confirm("Are You Sure you want to delete this task?")){
			return;
		}

		let removeTask = true;
		let id = $(this).parent().parent().attr("id").split("-")[1];
		let ref = $(this);

		$.post("tasks.php", {
			FormID: '<?php echo $_SESSION['FormID']; ?>',
			id,
			removeTask
		}, function(res, status, something){

			res = JSON.parse(res);

			if(res.status == "success"){
				ref.parent().parent().remove();
			}

		});
	});

</script>
<?php
	include_once("includes/foot.php");
?>