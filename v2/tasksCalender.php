<?php 

	$active = "tasks";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

	$SQL = "SELECT userid,realname FROM www_users";
	$res = mysqli_query($db, $SQL);

	$users = [];

	while($row = mysqli_fetch_assoc($res)){
		$users[$row['userid']] = $row['realname'];
	}

	$SQL = "SELECT * FROM tasks 
			WHERE deleted_at IS NULL 
			AND (created_by = '".$_SESSION['userid']."'
					OR assigned_by = '".$_SESSION['UserID']."'
					OR assigned_to = '".$_SESSION['UserID']."')";
	$res = mysqli_query($db, $SQL);

	$tasks = [];

	while($row = mysqli_fetch_assoc($res)){

		$task = [];

		$task['titleb'] 	 = html_entity_decode($row['title']);
		$task['title'] 		 = html_entity_decode($row['title']).
								"\nAssigned To: ".($users[$row['assigned_to']]).
								"\nAssigned By: ".($users[$row['assigned_by']]).
								"\nStatus: ".$row['status'];
		$task['description'] = html_entity_decode($row['description']);
		$task['start']  	 = date("Y-m-d",strtotime($row['from_date']));
		$task['end']    	 = date("Y-m-d",strtotime($row['to_date']));
	
		$tasks[] = $task;

	}

	unset($users[$_SESSION['UserID']]);

?>
<link rel='stylesheet' href='assets/fullcalendar/fullcalendar.min.css'/>
<style>
	.table-head{position: relative; top:60px; background: #424242; left: 0; color: white;} .table-head th{border: 1px solid white;} .center-fit{width: 1%; white-space: nowrap; text-align: center;} .modal{display:none;position:fixed;z-index:1000;left:0;top: 0;width: 100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{background-color:#fefefe;margin: 5% auto;padding: 20px;border: 1px solid #888;width: 80%;}.close {color: #aaa;float: right;font-size: 28px;font-weight: bold;}.close:hover,.close:focus{color: black;text-decoration: none;cursor: pointer;}.tasktitle{border:1px solid #424242; border-radius: 7px; padding: 5px; width: 100%}.taskdescription{border:1px solid #424242; border-radius: 7px;padding: 5px; width: 100%; max-width: 100%; min-width: 100%;min-height: 100px;max-height: 100px;}#saveNewTask{margin-top:15px;}.warningsbox{display:none; margin-top: 15px;}.dontFBreak{width: 1%; white-space: nowrap;}td{vertical-align: middle !important;}
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
		              	<h3 class="box-title">Task Management <a href="tasks.php" class="btn btn-info">List View</a></h3>
		      			<button type="button" class="btn btn-default pull-right" id="addNewTask">
		              		<i class="fa fa-plus"></i> Add Task
		              	</button>
		            </div>
	            	<div class="box-body">
		              	<div id='calendar'></div>
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

	$(function() {

	  	$('#calendar').fullCalendar({
		    header: {
		        left: 'prev,next today',
		        center: 'title',
		        right: 'month,basicWeek,basicDay'
	      	},
	      	eventRender: function(eventObj, $el) {
		        $el.popover({
		          title: eventObj.titleb,
		          content: eventObj.description,
		          trigger: 'hover',
		          placement: 'top',
		          container: 'body'
		        });
		    },
		    defaultDate: '<?php ec(date('Y-m-d')); ?>',
		    navLinks: true, // can click day/week names to navigate views
		    editable: false,
		    eventLimit: true, // allow "more" link when too many events
		    events: <?php echo json_encode($tasks); ?>
		});

	});
</script>
<script src="assets/moment.min.js"></script>
<script src="assets/fullcalendar/fullcalendar.min.js"></script>
<?php
	include_once("includes/foot.php");
?>