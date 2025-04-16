<?php
$PathPrefix = "";

include("includes/session.inc");

if(isset($_GET['json'])){



    $SQL = 'SELECT stockmaster.stockid,manufacturers_name, lastcost, materialcost,
     lastcostupdate, lastupdatedby, mnfCode, mnfpno,abbreviation,categorydescription
     FROM stockmaster INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
     
     INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid INNER JOIN itemcondition 
     ON stockmaster.conditionID = itemcondition.conditionID
     
     group by stockmaster.stockid';

    $res = mysqli_query($db, $SQL);

//HO quantities
    /*    $SQLHO="SELECT * FROM locstock WHERE loccode='HO'";
        $resHO = mysqli_query($db, $SQLHO);
        while($rowHO = mysqli_fetch_assoc($resHO)) {
            $responseHO[$rowHO['stockid']] = $rowHO['quantity'];
        }*/
//----
//HOPS quantities
    $SQLHOPS="SELECT * FROM locstock WHERE loccode='HOPS'";
    $resHOPS = mysqli_query($db, $SQLHOPS);
    while($rowHOPS = mysqli_fetch_assoc($resHOPS)) {
        $responseHOPS[$rowHOPS['stockid']] = $rowHOPS['quantity'];
    }
//----
//MT quantities
    /*$SQLMT="SELECT * FROM locstock WHERE loccode='MT'";
    $resMT = mysqli_query($db, $SQLMT);
    while($rowMT = mysqli_fetch_assoc($resMT)) {
        $responseMT[$rowMT['stockid']] = $rowMT['quantity'];
    }*/
//----
//MTPS quantities
    $SQLMTPS="SELECT * FROM locstock WHERE loccode='MTPS'";
    $resMTPS = mysqli_query($db, $SQLMTPS);
    while($rowMTPS = mysqli_fetch_assoc($resMTPS)) {
        $responseMTPS[$rowMTPS['stockid']] = $rowMTPS['quantity'];
    }
//----
//OS quantities
    $SQLOS="SELECT * FROM locstock WHERE loccode='OS'";
    $resOS = mysqli_query($db, $SQLOS);
    while($rowOS = mysqli_fetch_assoc($resOS)) {
        $responseOS[$rowOS['stockid']] = $rowOS['quantity'];
    }
//----
//SR quantities
    /*$SQLSR="SELECT * FROM locstock WHERE loccode='SR'";
    $resSR = mysqli_query($db, $SQLSR);
    while($rowSR = mysqli_fetch_assoc($resSR)) {
        $responseSR[$rowSR['stockid']] = $rowSR['quantity'];
    }*/
//----
//SRPS quantities
    $SQLSRPS="SELECT * FROM locstock WHERE loccode='SRPS'";
    $resSRPS = mysqli_query($db, $SQLSRPS);
    while($rowSRPS = mysqli_fetch_assoc($resSRPS)) {
        $responseSRPS[$rowSRPS['stockid']] = $rowSRPS['quantity'];
    }
//----
//VSR quantities
    /*$SQLVSR="SELECT * FROM locstock WHERE loccode='VSR'";
    $resVSR = mysqli_query($db, $SQLVSR);
    while($rowVSR = mysqli_fetch_assoc($resVSR)) {
        $responseVSR[$rowVSR['stockid']] = $rowVSR['quantity'];
    }*/
//----
//WS quantities
    /*$SQLWS="SELECT * FROM locstock WHERE loccode='WS'";
    $resWS = mysqli_query($db, $SQLWS);
    while($rowWS = mysqli_fetch_assoc($resWS)) {
        $responseWS[$rowWS['stockid']] = $rowWS['quantity'];
    }*/
//----


    while($row = mysqli_fetch_assoc($res)){



        $response[$row['stockid']] = $row;
        $response[$row['stockid']]['qtyHO']=$responseHO[$row['stockid']];
        $response[$row['stockid']]['qtyHOPS']=$responseHOPS[$row['stockid']];
        $response[$row['stockid']]['qtyMT']=$responseMT[$row['stockid']];
        $response[$row['stockid']]['qtyMTPS']=$responseMTPS[$row['stockid']];
        $response[$row['stockid']]['qtySR']=$responseSR[$row['stockid']];
        $response[$row['stockid']]['qtySRPS']=$responseSRPS[$row['stockid']];
        $response[$row['stockid']]['qtyOS']=$responseOS[$row['stockid']];
        $response[$row['stockid']]['qtyVSR']=$responseVSR[$row['stockid']];
        $response[$row['stockid']]['qtyWS']=$responseWS[$row['stockid']];

    }
    $finalresponse=[];
    foreach ($response as $key=>$value)
    {
        $finalresponse[]=$value;
    }

    echo json_encode($finalresponse);
    return;

}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ERP-SAHamid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />





    <link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/nanoscroller/nanoscroller.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
    <link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
    <link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="quotation/assets/vendor/pnotify/pnotify.custom.css">
    <link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
    <link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />


    <script src="quotation/assets/vendor/modernizr/modernizr.js"></script>
    <style type="text/css">
        table{
            margin-top: 20px;
            margin-bottom: 20px;
        }

        th{
            text-align: center;
        }

        td{
            padding:4px 6px !important;
            vertical-align: middle !important;
        }


    </style>

    <script>
        var datatable = null;
    </script>
</head>
<body>

<section class="body" style="overflow: auto">

    <header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
	      			<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?>
	      			&nbsp;|&nbsp;
	      			<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
        <input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
    </header>

    <div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 50px;">
        <table class="table table-bordered table-striped mb-none" id="datatable">
            <thead>
                    <th>Stock ID</th>
					<th>Brand</th>
					<th>Category Description</th>
					
						<th>mnfpno</th>
						<th>mnfCode</th>
						<th>Item Condition</th>
						<th>Last Update</th>
						<th>Update Person</th>
						<th>Last Price</th>
						<th>Current Price</th>
						<!--<th>QOH HO</th>
                        --><th>QOH HOPS</th>
<!--                        <th>QOH MT</th>
-->                        <th>QOH MTPS</th>

<!--                        <th>QOH SR</th>
-->
                        <th>QOH SRPS</th>

                        <th>QOH OS</th>

<!--                        <th>QOH VSR</th>

                        <th>QOH WS</th>
-->
						
					
						

				</thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <footer style="background:#424242; bottom:0; width:100%; position: fixed; text-align:center; padding: 5px; z-index: 10">
        Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
    </footer>

</section>

<script src="quotation/assets/vendor/jquery/jquery.js"></script>
<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
<script src="quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
<script src="quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
<script src="quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
<script src="v2/assets/datatables/dataTables.buttons.min.js"></script>
<script src="v2/assets/datatables/buttons.html5.min.js"></script>
<script src="quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
<script src="quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
<script src="quotation/assets/javascripts/theme.js"></script>
<script>

    (function( $ ) {

        'use strict';

        var datatableInit = function() {

            datatable = $('#datatable').DataTable({

                dom: 'Bflrtip',
                lengthMenu: [10, 25, 50, 75, 1000 ],
                buttons: [
                        'copy',
                        {
                            text: 'Download CSV',
                            action: function(e, dt, node, config) {
                                window.location.href = 'export_pricelistb.php';
                            }
                        },
                        'excel'
                    ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                },
                pageLength: 1000,

                columns: [
                    { "data": "stockid" },
                    { "data": "manufacturers_name" },
                    { "data": "categorydescription" },
                    { "data": "mnfCode" },
                    { "data": "mnfpno" },
                    { "data": "abbreviation" },
                    { "data": "lastcostupdate" },
                    { "data": "lastupdatedby" },
                    { "data": "lastcost" },
                    { "data": "materialcost" },
                    /*{ "data": "qtyHO" },
                    */{ "data": "qtyHOPS" },
                    /*{ "data": "qtyMT" },
                    */{ "data": "qtyMTPS" },
                    /*{ "data": "qtySR" },
                    */{ "data": "qtySRPS" },
                    { "data": "qtyOS" },
                    /*{ "data": "qtyVSR" },
                    { "data": "qtyWS" },*/
                ],

            });

/*            $('#datatable_length')
                .find("label")
                .html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Price List</h3>");*/

        };

        $(function() {
            datatableInit();
            $("tbody tr td").html("Loading...");

            $.ajax({
                type: 'GET',
                url: "pricelist.php?json=true",
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

</script>
</body>
</html>