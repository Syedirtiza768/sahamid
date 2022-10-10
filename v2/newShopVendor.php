<?php 

	$active = "shop";

	include_once("config.php");
	
	if(!userHasPermission($db,"insert_shop_vendor")){ 
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}

	if(isset($_POST['newShopVendor'])){

		if(!isset($_POST['name']) || trim($_POST['name']) == "" || strlen(trim($_POST['name'])) < 3){
		
			echo json_encode([
					
					'status' => 'error',
					'message' => 'Name Should Be minimum 3 characters'

				]);
			return;
		
		}

		$_POST['name'] = trim($_POST['name']);

		$SQL = "SELECT * FROM shop_vendors WHERE name='".$_POST['name']."'";
		$res = mysqli_query($db, $SQL);

		if(mysqli_num_rows($res) >= 1){

			echo json_encode([
					
					'status' => 'error',
					'message' => 'Vendor Already Exists'

				]);
			return;

		}

		$SQL = "INSERT INTO shop_vendors (name) VALUES ('".$_POST['name']."')";
		DB_query($SQL, $db);

		$SQL = "SELECT * FROM shop_vendors WHERE name='".$_POST['name']."'";
		$res = mysqli_query($db, $SQL);
		$res = mysqli_fetch_assoc($res);

		$SQL = "UPDATE shop_vendors SET vid='MV-".$res['id']."' WHERE id='".$res['id']."'";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO suppliers(`supplierid`,`suppname`,`supptype`,`currcode`,`paymentterms`,`suppliersince`,`address1`,`address2`,`address3`,`address4`,`address5`,`address6`,`phn`,`email`)
			VALUES ('MV-".$res['id']."','".$_POST['name']."',2,'PKR','DF','".date('Y-m-d')."','".htmlentities($_POST['address1'])."','".htmlentities($_POST['address2'])."','".htmlentities($_POST['address3'])."','".htmlentities($_POST['address4'])."','".htmlentities($_POST['address5'])."','".htmlentities($_POST['address6'])."','".htmlentities($_POST['phn'])."','".htmlentities($_POST['email'])."')";
		DB_query($SQL, $db);
		$SQL = "INSERT into debtorsmaster(debtorsmaster.debtorno,debtorsmaster.name,debtorsmaster.address1,debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,debtorsmaster.currcode,
			debtorsmaster.typeid,debtorsmaster.salestype,debtorsmaster.holdreason,debtorsmaster.dueDays,debtorsmaster.paymentExpected,debtorsmaster.clientsince,debtorsmaster.dba)
			VALUES ('MV-".$res['id']."','".$_POST['name']."','".htmlentities($_POST['address1'])."','".htmlentities($_POST['address2'])."','".htmlentities($_POST['address3'])."','".htmlentities($_POST['address4'])."','".htmlentities($_POST['address5'])."','".htmlentities($_POST['address6'])."','PKR','28','11','1','30','30','".date('Y-m-d')."','SA HAMID AND COMPANY')";
		DB_query($SQL, $db);

		$SQL = "INSERT into custbranch(custbranch.branchcode,custbranch.debtorno,custbranch.brname,custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,custbranch.braddress4,custbranch.braddress5,custbranch.braddress6,defaultlocation,salesman,area)
			VALUES ('MV-".$res['id']."','MV-".$res['id']."','".$_POST['name']."','".htmlentities($_POST['address1'])."','".htmlentities($_POST['address2'])."','".htmlentities($_POST['address3'])."','".htmlentities($_POST['address4'])."','".htmlentities($_POST['address5'])."','".htmlentities($_POST['address6'])."','SR','SR1','11')";
		DB_query($SQL, $db);


		if(isset($_POST['contacts']) && count($_POST['contacts']) > 0){
			
			foreach($_POST['contacts'] as $name => $number){
			
				$SQL = "INSERT INTO svcontacts (`svid`,`name`,`number`)
						VALUES ('MV-".$res['id']."','".htmlentities($name)."','".$number."')";
				DB_query($SQL, $db);		

			}
			
		}

		echo json_encode([
				
				'status' => 'success',
				'vid' => 'MV-'.$res['id'],
				'name' => $_POST['name']

			]);
		return;

	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<style>
	.input-yay{
		border:1px solid #424242;
		padding: 3px;
		width: 300px;
		border-radius: 7px; 
		display: block;
		margin-top: 5px;
	}
</style>

<div class="content-wrapper">
    <section class="content-header">
    	<h3 style="text-align: center;">Add New Market Place Business</h3>
    </section>

    <section class="content">
	    
		<div class="row">
			<div class="col-md-12" style="text-align: center;">
				<div style="width: 100%; padding: 10px; display: none; text-align: center;" class="reporting"></div>
				<div class="col-md-6">
					<div style="display: inline-block;">
						Vendor/Supplier Name <br>
						<input type="text" class="input-yay suppname" placeholder="Name">
						Telephone:<br>
						<input type="text" class="input-yay telephone" placeholder="Phone No">
						Email:<br>
						<input type="text" class="input-yay email" placeholder="Email Address">
						Address 1 (Street):<br>
						<input type="text" class="input-yay address1" placeholder="Street Address">
						Address 2 (Street):<br>
						<input type="text" class="input-yay address2" placeholder="Street Address Cont.">
						Address 3 (Suburb/City):<br>
						<input type="text" class="input-yay address3" placeholder="City">
						Address 4 (State/Province):	<br>
						<input type="text" class="input-yay address4" placeholder="State/Province">
						Address 5 (Postal Code):<br>
						<input type="text" class="input-yay address5" placeholder="Postal Code">
						Address 6 (Country):<br>
						<input type="text" class="input-yay address6" placeholder="Country">
						<button class="btn btn-success input-yay saveVendor">Save</button>
					</div>
				</div>
				<div class="col-md-6" class="contacts-cont">
					<div class="contact-info" style="text-align: center; display: inline-block;">
						Vendor Contact 1 Name <br>
						<input type="text" class="input-yay contname" placeholder="Contact Name ">
						Vendor Contact 1 Number
						<input type="text" class="input-yay contnum" placeholder="Contact Number ">
					</div>
					<div class="contact-info" style="text-align: center; display: inline-block;">
						Vendor Contact 2 Name <br>
						<input type="text" class="input-yay contname" placeholder="Contact Name ">
						Vendor Contact 2 Number
						<input type="text" class="input-yay contnum" placeholder="Contact Number ">
					</div>
					<div class="contact-info" style="text-align: center; display: inline-block;">
						Vendor Contact 3 Name <br>
						<input type="text" class="input-yay contname" placeholder="Contact Name ">
						Vendor Contact 3 Number
						<input type="text" class="input-yay contnum" placeholder="Contact Number ">
					</div>
					<div class="contact-info" style="text-align: center; display: inline-block;">
						Vendor Contact 4 Name <br>
						<input type="text" class="input-yay contname" placeholder="Contact Name ">
						Vendor Contact 4 Number
						<input type="text" class="input-yay contnum" placeholder="Contact Number ">
					</div>
					<div class="contact-info" style="text-align: center; display: inline-block;">
						Vendor Contact 5 Name <br>
						<input type="text" class="input-yay contname" placeholder="Contact Name ">
						Vendor Contact 5 Number
						<input type="text" class="input-yay contnum" placeholder="Contact Number ">
					</div>
				</div>
			</div>
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script>
	$(".saveVendor").on("click", function(){
		let ref = $(this);
		ref.prop("disabled",true);

		let contacts = {};

		$(".contact-info").each(function(){
			let name = $(this).find(".contname").val().trim();
			let num  = $(this).find(".contnum").val().trim();

			if(name != "" && num != ""){
				contacts[name] = num;
			}

		});

		$.post("newShopVendor.php", {
			newShopVendor: true,
			name: $(".suppname").val(),
			phn: $(".telephone").val(),
			email: $(".email").val(),
			address1: $(".address1").val(),
			address2: $(".address2").val(),
			address3: $(".address3").val(),
			address4: $(".address4").val(),
			address5: $(".address5").val(),
			address6: $(".address6").val(),
			contacts: contacts,
			FormID: '<?php ec($_SESSION['FormID']); ?>'
		}, function(res, status, something){
			res = JSON.parse(res);
			if(res.status == "error"){
				report("Error",res.message,"danger");
				ref.prop("disabled",false);
			}else{
				report("Success","Vendor ("+res.name+") Created successfully.","success");
				setTimeout(function(){
					window.location.reload();
				},2000);
			}
		});
	});

	function report(title,message,type){
		$(".reporting").addClass("btn-"+type);
		$(".reporting").css("display","block");
		$(".reporting").html(message);
	}
</script>

<?php
	include_once("includes/foot.php");
?>