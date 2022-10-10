<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	$type=$_GET['type'];
	
	if($type==604 && !userHasPermission($db,"list_receipt_voucher")){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}
    if($type==605 && !userHasPermission($db,"list_payment_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }


if(isset($_POST['to'])) {
    $SQL = "SELECT * FROM dcs";
    $res = mysqli_query($db, $SQL);
    $data = [];

//	$canEdit = userHasPermission($db,"edit_receipt_voucher")&&userHasPermission($db,"edit_payment_voucher");

    while($row = mysqli_fetch_assoc($res)){


        $r = [];
        $r[] = explode("-",$row['voucherno'])[1];
        $r[] = $row['voucherno'];
        $r[] = $row['pid'];
        $r[] = $row['partyname'];
        $r[] = date("d/m/Y",strtotime($row['created_at']));
        $r[] = locale_number_format($row['amount'],2);
        $r[] = $row['salesman'];
        $r[] = $row['user_name'];
        $voucherFilePath = glob("../../../".$_SESSION['part_pics_dir'] . '/' .'voucher_'.$row['id'].'*.pdf');
        $vouchers="";
        $ind=0;
        foreach($voucherFilePath as $voucherFile) {
            $ind++;
            $vouchers.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$voucherFile.'">Attachment'.$ind.'</a>';
        }
        $r[]= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
        <input type='file' id='attachFile".$row['id']."' data-orderno='".$row['id']."' class='attachFile' name='voucher'>
        <input type='button' id='uploadFile' data-orderno='".$row['id']."' class='uploadFile' name='uploadFile' value='upload'>
        </form>".$vouchers;

        $checked = (($row['booked']==1) ? "checked":"");

        $r[] = (($checked=="")?"
        <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
        
        ":"Booked");
        //	if($canEdit){
        if ($row['type']==604)
            $type="rv";
        if ($row['type']==605)
            $type="pv";
        $r[] =   "<a class='btn btn-warning' target='_blank' href='editVoucher.php?orderno=".$row['id']."&type=".$type."'>Edit</a>";

        //	}

        $r[] = "<a class='btn btn-info' target='_blank' href='voucherPrint.php?orderno=".$row['id']."'>Print</a>";
        $r[] = "<a class='btn btn-info' target='_blank' href='voucherPrint.php?orderno=".$row['id']."&duplicate=1"."'>Duplicate</a>";



        $data[] = $r;


    }

    echo json_encode($data);
    return;
}


?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>List Vouchers</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>
		<script>
			var datatable = null;
		</script>
		<style>
			.dataTables_filter label{
				width: 100% !important;
			}

			.dataTables_filter input{
			    border: 1px #ccc solid;
    			border-radius: 5px;
			}

			th{
				text-align: center;
			}

		</style>

	</head>
	<body>
   
		<section class="body">
	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
				<span style="color:white">
					<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
					&nbsp;|&nbsp;
					<span style="color:#ccc">
						<?php echo stripslashes($_SESSION['UsersRealName']); ?>
				  	</span>
					<span class="pull-right" style="background:#424242; padding: 0 10px;">
						<a href="<?php echo $RootPath; ?>/../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>

	      	<div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 30px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
							<th>SR#</th>
	      					<th>Voucher #</th>
	      					<th>Party ID</th>
	      					<th>Name</th>
	      					<th>Date Created</th>
	      					<th>Amount</th>
                            <th>Salesman</th>
                            <th>Created By</th>
                            <th>Attachment</th>
                            <th>Booked</th><?php if(userHasPermission($db,"edit_payment_voucher")){ ?>
								<th>Edit</th>
							<?php } ?>
	      					<th>Print</th>
                            <th>Duplicate</th>

	      				</tr>
	      			</thead>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index: 20">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
		</section>

		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>


	</body>
	<script>
		
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});

				$('#datatable_length')
					.find("label")
					.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Voucher List </h3>");

			};

			$(function() {
				datatableInit();
				$("tbody tr td").html("Loading...");
				
				$.ajax({
					type: 'GET',
					url: "api/voucherList.php?type=<?php echo $type?>",
                    dataType: "json",
					success: function(response) { 
						datatable.rows.add(response).draw(false);
					},
					error: function(){
						$("tbody tr td").html("Error...");
					}
				});
			});

		}).apply( this, [ jQuery ]);
        $('#datatable').on('change','.booked',function(){
            let orderno = $(this).attr('data-orderno');
            let value = $(this).val();
            $.post("api/voucherListUpdate.php",{orderno,value,name:'booked'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });
        });
        $('#datatable').on('change','.booked',function(){
            let orderno = $(this).attr('data-orderno');
            let value = $(this).val();
            $.post("api/voucherListUpdate.php",{orderno,value,name:'booked'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });
        });

        $('#datatable').on('click','.uploadFile',function(){
                var ref=$(this);
                var orderno = $(this).attr('data-orderno');
                var fd = new FormData();


                var files = $('#attachFile'+orderno)[0].files[0];

                fd.append('voucher', files);
                fd.append('orderno',orderno);

                $.ajax({
                    url: 'api/uploadFile.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        if(res){
                            ref.parent().html(res);
                        }
                        else{
                            alert('file not uploaded');
                        }
                    },
                });
            });



    </script>
</html>
