<?php 

	$active = "dashboard";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<div class="content-wrapper">
    
	<section class="content-header">
      
    </section>
	
	<h2 style="text-align:center;">Remove Invoice Group From InProgress</h2>
	<p style="display:block; text-align:center;">Proceed Only if no invoice has been successfully created or returned against the InvoiceGroup.</p>
	
    <section class="content" style="display:flex; flex-direction: column; align-items:center; justify-content:center;">
	    
		<div class="row">
			Enter Invoice Group ID: <br>
			<input type="number" id="invoiceGroupID" style = "border:1px solid #424242; border-radius:7px; width:250px;"/> <br>
			<button class="btn btn-success" style="width:250px; margin-top:5px">Submit</button>
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>


<?php
	include_once("includes/foot.php");
?>