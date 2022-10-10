<?php 

	$AllowAnyone = true;

	$active = "dashboard";
	
	include_once("config.php");
	include_once("dashboard/availableBadges.php");
	include_once("dashboard/availableWidgets.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

	$id = 0;

	if(isset($_GET['Dashboard'])){
		$id = $_GET['Dashboard'];

		$SQL = "SELECT * FROM user_dashboards WHERE dashboard_id='$id' AND user_id='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		if(mysqli_num_rows($res) != 1){
			$id = -1;
		}
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
	.badges,.grid{position:relative}.item{display:block;position:absolute;z-index:1}.item.muuri-item-dragging{z-index:3}.item.muuri-item-releasing{z-index:2}.item.muuri-item-hidden{z-index:0}.item-content{position:relative;width:100%;height:100%}.grid .item{margin-top:10px}.dashboard-configuration{background:#222d32;width:400px;height:calc(100vh - 14px);position:fixed;right:-400px;top:51px;z-index:100;transition:right .4s ease-out}.fa-gear{position:absolute;left:-39px;background:#fff;font-size:1.4em;padding:10px;box-shadow:0 2px 8px 2px;border-radius:8px 0 0 8px;border:1px solid #424242;cursor:pointer}.available-badges,.available-widgets{width:100%;overflow:hidden}.badges-list,.widget-list{overflow:hidden}.widget{display:inline-block;min-width:10px;font-weight:700;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:middle;background-color:#777}.badge,.widget{border:1px solid #fff;border-radius:7px;padding:7px;margin:5px;font-size:.7em;cursor:pointer}body{zoom:.95!important}.removeBadge,.removeWidget{display:none!important}
</style>

<div class="content-wrapper">
    
    <h1 style="font-variant-caps: all-petite-caps; text-align: center; font-size: 3.5em;">
    	<?php echo $name; ?>
    	<?php if(userHasPermission($db, 'create_new_dashboard') && $id != -1){ ?>
    		<a class="btn btn-warning" href="dashboard-UD.php?Dashboard=<?php echo $id; ?>">Edit Dashboard</a>
    	<?php } ?>	
    	<a class="btn btn-danger" href="api/clearCache.php">Reload With Fresh Data</a>
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
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script src="dashboard/assets/js/muuri.js"></script>
<script>
	var FormID = '<?php echo $_SESSION['FormID']; ?>';
	$(document).ready(function(){
		let widgetsGrid = new Muuri('.grid',{
			dragEnabled: false
		});
		let badgesGrid = new Muuri('.badges',{
			dragEnabled: false
		});
	});
</script>

<?php
	include_once("includes/foot.php");
?>