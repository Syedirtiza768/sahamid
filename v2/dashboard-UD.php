<?php 

	$active = "dashboard";
	
	include_once("config.php");
	include_once("dashboard/availableBadges.php");
	include_once("dashboard/availableWidgets.php");

	if(!userHasPermission($db, 'create_new_dashboard')){
		header("Location: /sahamid");
		exit();
	}

	if(isset($_POST['updateDashboard'])){
		$badges = ($_POST['badges'] != "" ? implode(",", $_POST['badges']):"");
		$widgets = ($_POST['widgets'] != "" ? implode(",", $_POST['widgets']):"");
		
		$id 	= $_POST['id'];
		$name 	= htmlentities($_POST['name']);

		if($id == -1){
			$SQL = "INSERT INTO dashboards (name, widgets, badges) VALUES('$name', '$widgets', '$badges')";
		}else{
			$SQL = "UPDATE dashboards SET widgets='$widgets',badges='$badges',name='$name' WHERE id='$id'";
		}

		mysqli_query($db, $SQL);

		echo json_encode([
				'status' => 'success'
			]);
		return;
	}

	if(isset($_POST['deleteDashboard'])){
		$id = $_POST['id'];
		$SQL = "DELETE FROM dashboards WHERE id='$id'";
		mysqli_query($db, $SQL);
		$SQL = "DELETE FROM user_dashboards WHERE dashboard_id='$id'";
		mysqli_query($db, $SQL);
		echo json_encode([
				'status' => 'success'
			]);
		return;
	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

	$id = -1;

	if(isset($_GET['Dashboard'])){
		$id = $_GET['Dashboard'];
	}

	$SQL = "SELECT * FROM dashboards WHERE id='$id'";
	$res = mysqli_query($db, $SQL);

	$badges = [];
	$widgets = [];
	if(mysqli_num_rows($res) != 1){
	
		$name = "";
		$badges = [];
		$widgets = [];
	
	}else{

		$res = mysqli_fetch_assoc($res);
		$name = $res['name'];
		$badges = explode(",", $res['badges']);
		$widgets = explode(",", $res['widgets']);

	}

?>

<style>
	.grid,.badges{position: relative;}.item{display: block;position: absolute;z-index: 1;}.item.muuri-item-dragging{z-index: 3;}.item.muuri-item-releasing{z-index: 2;}.item.muuri-item-hidden{z-index: 0;}.item-content{position: relative;width: 100%;height: 100%;}
	.grid .item{
		margin-top:10px;
	}
	.dashboard-configuration{
		background: #222d32; 
		width: 400px;
		height: calc(100vh - 14px);
		position: fixed;
		right: -400px;
		top: 51px;
		z-index: 100;
		transition: right .4s ease-out;
	}
	.fa-gear{
		position: absolute; 
		left: -39px; 
		background: white; 
		font-size: 1.4em;
		padding: 10px;
		box-shadow: 0px 2px 8px 2px;
		border-radius: 8px 0 0 8px;
		border: 1px solid #424242;
		cursor: pointer;
	}
	.available-badges, .available-widgets{
		width: 100%;
		overflow: hidden;
	}
	.badges-list, .widget-list{
		overflow: hidden;
	}
	.widget{
		display: inline-block;
	    min-width: 10px;
	    padding: 3px 7px;
	    font-size: 12px;
	    font-weight: 700;
	    line-height: 1;
	    color: #fff;
	    text-align: center;
	    white-space: nowrap;
	    vertical-align: middle;
	    background-color: #777;
	    border-radius: 10px;
	}
	.badge, .widget{
		border: 1px solid white;
		border-radius: 7px;
		padding: 7px;
		margin: 5px 5px; 
		font-size: .7em;
		cursor: pointer;
	}
	body{
		zoom:0.95 !important;
	}
</style>

<div class="content-wrapper">
    
	<h1 style="text-align: center;">
		<input type="hidden" id="dashboard-id" value="<?php echo $id; ?>">
		<input type="text" id="dashboard-name" style="border: 1px solid #424242; border-radius: 7px;" value="<?php echo $name; ?>">
		<?php if($id == -1){ ?>
		<button class="btn btn-success" id="save-dashboard">Save Dashboard</button>
		<?php }else{ ?>
		<button class="btn btn-success" id="update-dashboard">Update Dashboard</button>
		<?php } if($id != -1){ ?>
		&nbsp; &nbsp; &nbsp;
		<button class="btn btn-danger" id="delete-dashboard">Delete Dashboard</button>
		<?php } ?>
	</h1>

	<section class="content-header" style="padding: 15px 0 0 0">
		<div class="badges">
			<?php for($i=0; $i < count($badges); $i++){ 
				if($badges[$i] == "" || 
					!file_exists('dashboard/widgets/badges/'.$badges[$i].'.php') || 
					!canAccessWidget($db,$badges[$i]))
					continue;
				include('dashboard/widgets/badges/'.$badges[$i].'.php');
			} ?>
		</div>
	</section>

    <section class="content" style="padding: 0 0 15px 0">
	    <div class="grid">
		    <?php for($i=0; $i < count($widgets); $i++){ 
				if($widgets[$i] == "" || 
					!file_exists('dashboard/widgets/'.$widgets[$i].'.php') || 
					!canAccessWidget($db,$widgets[$i]))
					continue;
				include('dashboard/widgets/'.$widgets[$i].'.php');
			} ?>
		</div>
    </section>

    <div class="dashboard-configuration">
    	<div class="" style="position: relative;">
    		<i class="fa fa-gear"></i>
    		<div class="col-md-12">
    			<h2 class="text-center" style="color: white; border-bottom: 1px solid white; font-variant-caps: petite-caps;">
    				Available Badges
    			</h2>
    			<section class="available-badges">
    				<div class="badges-list">
    					<?php foreach($availableBadges as $key => $name){ if(!canAccessWidget($db, $key))  continue; ?>
    					<div class="badge" data-badge="<?php echo $key; ?>">
    						<?php echo $name; ?> 
    					</div>
    					<?php } ?>
    				</div>
    			</section>
    			<h2 class="text-center" style="color: white; border-bottom: 1px solid white; border-top: 1px solid white; font-variant-caps: petite-caps;">
    				Available Widgets
    			</h2>
    			<section class="available-widgets">
    				<div class="widget-list">
    					<?php foreach($availableWidgets as $name => $key){ if(!canAccessWidget($db, $key))  continue; ?>
						<div class="widget" data-widget="<?php echo $key; ?>">
							<?php echo $name; ?> 
						</div>
						<?php } ?>
    				</div>
    			</section>
    		</div>
    	</div>
    </div>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script src="dashboard/assets/js/muuri.js"></script>
<script>
	var FormID = '<?php echo $_SESSION['FormID']; ?>';

	$(document).ready(function(){
		let widgetsGrid = new Muuri('.grid',{
			dragEnabled: true
		});
		let badgesGrid = new Muuri('.badges',{
			dragEnabled: true
		});
		/*badgesGrid.on('dragReleaseEnd', function (item) {
			let badges = [];
			let updateBadges = true;
			badgesGrid.getItems().forEach(function(el){
				badges.push(el._element.attributes['data-code'].nodeValue);
			});
			$.post('<?php echo $_SERVER['PHP_SELF']; ?>',{FormID,badges,updateBadges},function(res, status){
				res = JSON.parse(res);
				console.log(res);
			});
		});
		widgetsGrid.on('dragReleaseEnd', function (item) {
			let widgets = [];
			let updateWidgets = true;
			widgetsGrid.getItems().forEach(function(el){
				widgets.push(el._element.attributes['data-code'].nodeValue);
			});
			$.post('<?php echo $_SERVER['PHP_SELF']; ?>',{FormID,widgets,updateWidgets},function(res, status){
				res = JSON.parse(res);
				console.log(res);
			});
		});*/
		$(".fa-gear").on("click", function(){
			let right = $(".dashboard-configuration").css("right");
			if(right == "0px"){
				right = "-400px";
			}else{
				right = "0px";
			}
			$(".dashboard-configuration").css("right",right);
		});
		$(".badge").on("click", function(){
			let badge = $(this).attr("data-badge");
			let pass  = true;
			badgesGrid.getItems().forEach(function(el){
				if(el._element.attributes['data-code'].nodeValue == badge){
					pass = false;
				}
			});
			if(!pass){
				return;
			}	
			$.get(`dashboard/widgets/badges/${badge}.php`, function(res, status){
				let elem = createElementFromHTML(res);
				badgesGrid.add([elem]);
			});
		});
		$(".widget").on("click", function(){
			let widget = $(this).attr("data-widget");
			console.log(widget);
			let pass  = true;
			widgetsGrid.getItems().forEach(function(el){
				console.log(el._element.attributes['data-code'].nodeValue);
				if(el._element.attributes['data-code'].nodeValue == widget){
					pass = false;
				}
			});
			if(!pass){
				return;
			}	
			$.get(`dashboard/widgets/${widget}.php`, function(res, status){
				let elem = createElementFromHTML(res);
				eval($(elem).find("script").html());
				widgetsGrid.add([elem]);
			});
		});
		$(document.body).on("click",".removeBadge",function(){
			let elem = $(this).parent().parent()[0];
			badgesGrid.remove(elem, {removeElements: true});
		});
		$(document.body).on("click",".removeWidget",function(){
			let elem = $(this).parent().parent()[0];
			widgetsGrid.remove(elem, {removeElements: true});
		});
		$("#update-dashboard").on("click", function(){
			let id = $("#dashboard-id").val();
			let name = $("#dashboard-name").val().trim();

			if(name == ""){
				alert("Name Cannot Be Empty");
				return;
			}

			updateDashboard(id, name, badgesGrid, widgetsGrid);
		});

		$("#save-dashboard").on("click", function(){
			let id = $("#dashboard-id").val();
			let name = $("#dashboard-name").val().trim();

			if(name == ""){
				alert("Name Cannot Be Empty");
				return;
			}

			updateDashboard(id, name, badgesGrid, widgetsGrid);
		});

		$("#delete-dashboard").on("click", function(){
			let id = $("#dashboard-id").val();
			let deleteDashboard = true;
			if(confirm("Are You Sure?")){
				$.post('<?php echo $_SERVER['PHP_SELF']; ?>',{FormID,id,deleteDashboard},function(res, status){
					res = JSON.parse(res);
					location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
				});
			}
		});
	});
	function updateDashboard(id, name, badgesGrid, widgetsGrid){
		let badges = [];
		let widgets = [];
		let updateDashboard = true;
		badgesGrid.getItems().forEach(function(el){
			badges.push(el._element.attributes['data-code'].nodeValue);
		});
		widgetsGrid.getItems().forEach(function(el){
			widgets.push(el._element.attributes['data-code'].nodeValue);
		});
		$.post('<?php echo $_SERVER['PHP_SELF']; ?>',{FormID,id,name,badges,widgets,updateDashboard},function(res, status){
			res = JSON.parse(res);
			location.reload();
		});
	}
	function createElementFromHTML(htmlString) {
	  	let div = document.createElement('div');
	  	div.innerHTML = htmlString.trim();
	 	return div.firstChild; 
	}
</script>

<?php
	include_once("includes/foot.php");
?>