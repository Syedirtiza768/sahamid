<?php 

	$active = "dashboard";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<div class="content-wrapper">
    
	<section class="content-header">
      
    </section>

    <section class="content">
	    
		<div class="row input">
			<h1 class="text-center">Permissions Maintenance</h1>
			
		
		<h2 class="text-center" style="display:flex; justify-content:center;"><label class="col-md-2">Permission</label><input id="permission" class="col-md-3" type="text" name="Permission">
		<label class="col-md-2">Slug</label><input id="slug" class="col-md-3" type="text" name="Slug"></h2>
		<h3 class="text-center" style="display:flex; justify-content:center;"><button class ="btn btn-success" id="addPermission">Add Permission</button></h3>
		
		<div class="row list">
			<div class="row" style="margin:25px;">
			
			<div class="col-md-12">
				
				<table class="table table-bordered table-striped mb-none"  id="datatable">
					<thead>
						<tr style="background-color: #424242; color: white">
							<th class="">SNo</th>
							<th class="">Permission</th>
							<th class="">Definition</th>
							<th class="">Slug</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
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
	$(document.body).on("keyup","#permission",function(){
		$("#slug").val($(this).val().replace(/ /g,"_"));
		
	});
	
	$('#datatable').DataTable({
		"processing":true,
		"serverSide":true,
		"ajax":"api/server_processing_permissions.php",
		"columnDefs": [ {
		  "targets": 4,
		  "render": function(data,type,row){
				let html = "<button class='btn btn-warning editpermission' data-id='"+row[0]+"'>Edit</button>";
				return html;
			}
		} ]
	});
});
</script>
<?php
	include_once("includes/foot.php");
?>