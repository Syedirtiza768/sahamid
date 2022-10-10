<?php 
	
  	$PathPrefix='../';

    include('misc.php');
  	include('../includes/session.inc');
  	include('../includes/CountriesArray.php');

    $db = createDBConnection();

    if($_POST['abc'] == "create"){

      $SQL = "SELECT * FROM salescase WHERE salescaseref='".$_POST['salescaseref']."'";
      $result = mysqli_query($db, $SQL);

      if(mysqli_num_rows($result) > 0){
        
        echo json_encode([

          'status' => 'error',
          'message' => 'salescase already exists'

        ]);
        return;
      
      }
      
      $SQL = "INSERT INTO salescase (salescaseref,salescasedescription,salesman,
              debtorno,branchcode,commencementdate,enquiryvalue)
              VALUES('".$_POST['salescaseref']."','".$_POST['desc']."','".$_POST['salesman']."',
              '".$_POST['customer']."','".$_POST['branch']."','".date('Y-m-d H:i:s')."',0)";

      DB_query($SQL,$db);

      foreach ($_POST['contacts'] as $contact) {
      
          $SQL = "INSERT INTO salescasecontacts(salescaseref,contid) 
          values ('".$_POST['salescaseref']."','".$contact."')";
          
          DB_query($SQL,$db);
      
      }

      echo json_encode([

          'status' => 'success',
          'salescaseref' => $_POST['salescaseref']

        ]);
      return;
    
    }

    $SelectedCustomer = "";
    $SelectedBranch   = "";

    foreach ($_POST as $key => $value) {

        if (strpos($key, 'Submit') === 0) {
            
            $index = explode("Submit",$key)[1];
            $SelectedCustomer = $_POST['SelectedCustomer'.$index];
            $SelectedBranch   = $_POST['SelectedBranch'.$index];

        }

    }

    if($SelectedCustomer == "" || $SelectedBranch == ""){

      echo "<script>";
      echo "window.location = '".$RootPath."/../salescase.php'";
      echo "</script>";

      return;

    }

    $customerInfo = [];

    $SQL = "SELECT debtorsmaster.name,
                custbranch.brname,
                debtorsmaster.currcode,
                debtorsmaster.dba,
                debtorsmaster.holdreason,
                holdreasons.dissallowinvoices,
                currencies.rate
            FROM debtorsmaster INNER JOIN currencies
            ON debtorsmaster.currcode=currencies.currabrev
            INNER JOIN custbranch
            ON debtorsmaster.debtorno=custbranch.debtorno
            INNER JOIN holdreasons
            ON debtorsmaster.holdreason=holdreasons.reasoncode
            WHERE debtorsmaster.debtorno='$SelectedCustomer'
            AND custbranch.branchcode='$SelectedBranch'";

    $result = mysqli_query($db, $SQL);

    if(!$result || mysqli_num_rows($result) == 0){

      echo "<script>";
      echo "window.location = '".$RootPath."/../salescase.php';";
      echo "</script>";

      return;

    }

    $row = mysqli_fetch_assoc($result);

    $customerInfo['name'] = $row['name'];
    $customerInfo['dba']  = $row['dba'];

    $SQL = "SELECT * FROM custcontacts 
            INNER JOIN debtorsmaster 
            ON custcontacts.debtorno = debtorsmaster.debtorno 
            WHERE debtorsmaster.debtorno = '$SelectedCustomer'";

    $result = mysqli_query($db, $SQL);

    if(!$result){
      
      echo "<script>";
      echo "window.location = '".$RootPath."/../salescase.php';";
      echo "</script>";

      return;

    }

    $customerInfo['contacts'] = [];

    while($row = mysqli_fetch_assoc($result)){

      $contact['id']      = $row['contid'];
      $contact['name']    = $row['contactname'];
      $contact['role']    = $row['role'];
      $contact['number']  = $row['phoneno'];

      $customerInfo['contacts'][] = $contact;

    }

    $SQL = "SELECT salesman.salesmanname AS salesman, custbranch.brname FROM custbranch 
            INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode 
            WHERE custbranch.branchcode = '$SelectedBranch'";

    $result = mysqli_query($db, $SQL);

    if(!$result || mysqli_num_rows($result) == 0){
      
      echo "<script>";
      echo "window.location = ".$RootPath."/salescase.php;";
      echo "</script>";

      return;

    }

    $row = mysqli_fetch_assoc($result);

    $customerInfo['salesman'] = $row['salesman'];

    $salescaseref = $SelectedBranch.date('-Y-m-d--his');

?>

<!DOCTYPE html>
<html class="">
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style>

      .parent{
      	display: flex;
      	justify-content: center;
      	align-items: center;
      	width: 100vw;
      }

      .child{
      	 background: #f9f9f9;
      	 border: 2px black solid;
      	 padding: 20px;
    	   overflow-y: auto;
    	   height: calc(100vh - 58px);
         min-width: 50vw;
      }

      p{
      	margin-bottom: 0px;
      }

      textarea{
        border: 2px grey solid;
      }
		</style>

	</head>
	<body style="overflow: hidden;">
		<section class="body" style="overflow: auto">

	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
		      		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
		      		&nbsp;|&nbsp;
		      		<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      	</header>

	      	<div class="parent">
	      		<div class="child">
	      			<h1 style="text-align: center; margin-top: 10px;">New Sales Case</h1>
	      			<p><strong>Ref #</strong> </p>
              <span><?php echo $salescaseref; ?></span>
	      			<p><strong>Customer:</strong></p>
	      			<span><?php echo $customerInfo['name']; ?></span>
              <p><strong>DBA:</strong></p>
              <span><?php echo $customerInfo['dba']; ?></span>
	      			<p><strong>Customer Contacts:</strong></p>
	      			<select id="contactss" multiple="" required="required" name="custcontacts[]" size="5" style="width: 100%">
              <?php 
                
                foreach ($customerInfo['contacts'] as $contact) {
                  
                  echo '<option value="'.$contact['id'].'">'.$contact['name'].' &nbsp;['.$contact['role'].']&nbsp;('.$contact['number'].')</option>';

                }
            
              ?>
              </select>
	      			<p><strong>Sales Person: </strong></p>
	      			<span><?php echo $customerInfo['salesman']; ?></span>
	      			<p><strong>Description: </strong></p>
	      			<span><textarea style="width: 100%; height: 100px" id="description"></textarea></span>
	      			<button class="btn btn-success" style="width: 100%" id="savesalescase">Submit</button>
	      		</div>
	      	</div>
     		

		  	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center">
		  		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
		  	</footer>
		</section>

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
    <script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/javascripts/theme.js"></script>

    <script>
      $(document).ready(function(){
        
        $("#savesalescase").on("click",function(e){

          e.preventDefault();

          var contacts = $('#contactss').val();

          if(contacts == null){
            swal('Alert',"No Contacts Selected.","warning");
            return;
          }

          var desc = $("#description").val();
          desc = desc.replace(/\s\s+/g, ' ');

          if(desc == "" || desc == " " || desc == null){
            swal('Alert',"Description is required.","warning");
            return;
          }

          swal({
            title: "Are you sure?",
            text: "It will create new salescase.!",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Create Salescase!",
            showLoaderOnConfirm: true,
            closeOnConfirm: false
          },
          function(){
            var valu = "create";
            var formid = "<?php echo $_SESSION['FormID']; ?>";
            var salescaseref = "<?php echo $salescaseref; ?>";
            var customer = "<?php echo $SelectedCustomer; ?>";
            var branch = "<?php echo $SelectedBranch; ?>";
            var salesman = "<?php echo $customerInfo['salesman']; ?>";
            $.ajax({
              type: 'POST',
              url: "<?php echo $RootPath; ?>"+"/salescasecreate.php",
              data: {abc: valu, FormID: formid,salescaseref: salescaseref, customer:customer, branch:branch,desc,desc,salesman:salesman,contacts:contacts},
              dataType: "json",
              success: function(response){
                  if(response.status == "success"){
                    window.location = "<?php echo $RootPath; ?>"+"/salescaseview.php?salescaseref="+response.salescaseref;
                  }else{
                    swal("Oops","Salescase with same referance already exists.","warning");
                  }
              },
              error: function(){

              }
            });

          });

        });

      });
    </script>

	</body>
</html>
