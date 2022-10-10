<?php 

	$active = "reports";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	if(isset($_GET['ajax'])){
		
		$code = $_POST['code'];
		$target = $_POST['target'];
		
		$SQL = "UPDATE salesman SET target='".$target."' WHERE salesmancode='".$code."'";
		DB_query($SQL, $db);
		
		return;
	}

	$SQL = "SELECT * FROM salesman";
	$salesPersons = mysqli_query($db, $SQL);

?>

<div class="content-wrapper">
    <section class="content-header">
		<h3 style="font-family: initial; margin:0">Update Sale Person Sales Target</h3>
    </section>

    <section class="content">
	    <div class="row">

			<div class="col-md-12">
			
				<table class="table table-striped" style="width:100%">
			
					<thead>
						<tr style="background:#222d32; color:white">
							<th>#</th>
							<th>Code</th>
							<th>Name</th>
							<th>Target</th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach($salesPersons as $salesPerson){ ?>
							<tr>
								<td><?php ec($count++); ?></td>
								<td class="code"><?php ec($salesPerson['salesmancode']); ?></td>
								<td><?php ec($salesPerson['salesmanname']); ?></td>
								<td style="width:1%">
									<input type="number" class="target" style="width:120px" value="<?php ec($salesPerson['target']); ?>"/>
								</td>
							</tr>
						<?php } ?>
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

	$(".target").on("change",function(){
		
		let ref = $(this);
		let val = $(this).val();
		let code = $(this).parent().parent().find(".code").html();
		
		$.post("updateSalesTargets.php?ajax=true",{
			target: val,
			FormID: '<?php ec($_SESSION['FormID']); ?>',
			code: code
		}, function(res, status, something){
			ref.css("border","2px solid green");
		});
		
	});

</script>

<?php
	include_once("includes/foot.php");
?>