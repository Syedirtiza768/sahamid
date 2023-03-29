<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "scm_report")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['brand'])){

		$brand 	= $_POST['brand'];
        $brands=implode(",",$brand);
		/*$duration = $_POST['duration'];
		*/
        $from = $_POST['from'];
        $to = $_POST['to'];
		$SQL1 = "SELECT stockmaster.stockid,stockmaster.mnfCode,stockmaster.mnfpno,stockmaster.description,
                stockmaster.brand,stockmaster.materialcost,stockmaster.scmrecommended, stockmaster.minimumqty 
                from stockmaster WHERE stockmaster.brand IN ($brands)";

		$res1 = mysqli_query($db, $SQL1);

		$response1 = [];
		$availablekeys = [];
		while($row1 = mysqli_fetch_assoc($res1)){
            $stockid=$row1['stockid'];
			$response1[$stockid] = $row1;
           $value = unpack('H*', "'".$stockid."'");

            //echo $ak= base_convert($value[1], 16, 2);

            //echo "<br/>";
            $availablekeys[]="'".$stockid."'";
			//$ak=intval("'".$stockid."'");
          //$availablekeys[]=decbin($ak);
		}
        $SQL2 = "SELECT manufacturers.manufacturers_id, manufacturers.manufacturers_name 
                from manufacturers WHERE manufacturers_id IN ($brands)";

        $res2 = mysqli_query($db, $SQL2);

        $response2 = [];
        while($row2 = mysqli_fetch_assoc($res2)){
            $brand=$row2['manufacturers_id'];
            $response2[$brand] = $row2;
        }

        $response=[];
        $stockids=implode(",",$availablekeys);
        //QOH
        $SQL3 = "SELECT locstock.stockid,SUM(locstock.quantity) as sumqty FROM locstock WHERE locstock.loccode
                  IN ('HO','MT','SR','OS') 
                  AND  locstock.stockid IN ($stockids) GROUP BY stockid ";

        $res3 = mysqli_query($db, $SQL3);

        $response3 = [];
        while($row3 = mysqli_fetch_assoc($res3)){
            $stockid=$row3['stockid'];
            $response3[$stockid] = $row3;
        }
// Invoice Quantity
//        $SQL4 = "SELECT invoice.invoiceno FROM invoice
//                 WHERE invoice.returned=0 AND invoice.inprogress=0
//                 AND invoice.invoicesdate >= DATE_SUB(CURDATE(), INTERVAL $duration MONTH)";
//        $res4 = mysqli_query($db, $SQL4);

        $SQL4 = "SELECT invoice.invoiceno FROM invoice 
                 WHERE invoice.returned=0 AND invoice.inprogress=0
                 AND invoice.invoicesdate >='$from'
                 AND invoice.invoicesdate <='$to'";
        $res4 = mysqli_query($db, $SQL4);

        $response4= [];
        $availableinvoices = [];
        while($row4 = mysqli_fetch_assoc($res4)){
            $invoiceno=$row4['invoiceno'];
            $response4[$invoiceno] = $row4;
            $availableinvoices[]=$invoiceno;
        }
        $invoices=implode(",",$availableinvoices);
        $SQL4a = "SELECT invoicedetails.stkcode,invoicedetails.invoiceno,
                  invoicedetails.invoicelineno,invoicedetails.quantity as itemQty
                from invoicedetails   
		        WHERE  invoicedetails.stkcode IN ($stockids)
		        AND invoicedetails.invoiceno IN ($invoices)";

        $res4a = mysqli_query($db, $SQL4a);

        $response4a = [];
        while($row4a = mysqli_fetch_assoc($res4a)){
            $stockid=$row4a['stkcode'];
            $response4a[$stockid][] = $row4a;
        }
        $SQL4b = "SELECT invoiceoptions.invoiceno,invoiceoptions.invoicelineno, invoiceoptions.quantity
                from invoiceoptions   
		        WHERE invoiceoptions.invoiceno IN ($invoices)";

        $res4b = mysqli_query($db, $SQL4b);

        $response4b = [];
        while($row4b = mysqli_fetch_assoc($res4b)){
            $invoiceno=$row4b['invoiceno'];
            $invoicelineno=$row4b['invoicelineno'];

            $response4b[$invoiceno][$invoicelineno] = $row4b;

        }

        foreach($response4a as $key=>$value)
        {
            $temparr=$response4a[$key];
            $count=sizeof($temparr);
            $response4a[$key]['sumQty']=0;
            for ($i=0;$i<$count;$i++)
            {
                $invoiceno=$response4a[$key][$i]['invoiceno'];
                $invoicelineno=$response4a[$key][$i]['invoicelineno'];
                $response4a[$key]['sumQty']+=
                    $response4a[$key][$i]['itemQty']*$response4b[$invoiceno][$invoicelineno]['quantity'];
            }
        }

        // CRV Quantity
//        $SQL10 = "SELECT shopsale.orderno FROM shopsale
//                 WHERE shopsale.payment='crv'
//                 AND shopsale.orddate >= DATE_SUB(CURDATE(), INTERVAL $duration MONTH)";
//        $res10 = mysqli_query($db, $SQL10);

        $SQL10 = "SELECT shopsale.orderno FROM shopsale 
                 WHERE shopsale.payment='crv'
                 AND shopsale.orddate >= '$from'
                 AND shopsale.orddate <= '$to'";
        $res10 = mysqli_query($db, $SQL10);


        $response10= [];
        $availableshopsales = [];
        while($row10 = mysqli_fetch_assoc($res10)){
            $shopsaleno=$row10['orderno'];
            $response10[$shopsaleno] = $row10;
            $availableshopsales[]=$shopsaleno;
        }
        $shopsales=implode(",",$availableshopsales);
        $SQL10a = "SELECT shopsalesitems.stockid,shopsalesitems.orderno,
                  shopsalesitems.lineno,shopsalesitems.quantity as itemQty
                from shopsalesitems   
		        WHERE  BINARY shopsalesitems.stockid IN ($stockids)
		        AND shopsalesitems.orderno IN ($shopsales)";

        $res10a = mysqli_query($db, $SQL10a);

        $response10a = [];
        while($row10a = mysqli_fetch_assoc($res10a)){
            $stockid=$row10a['stockid'];
            $response10a[$stockid][] = $row10a;
        }
        $SQL10b = "SELECT shopsalelines.orderno,shopsalelines.id, shopsalelines.quantity
                from shopsalelines   
		        WHERE shopsalelines.orderno IN ($shopsales)";

        $res10b = mysqli_query($db, $SQL10b);

        $response10b = [];
        while($row10b = mysqli_fetch_assoc($res10b)){
            $shopsaleno=$row10b['orderno'];
            $shopsalelineno=$row10b['id'];

            $response10b[$shopsaleno][$shopsalelineno] = $row10b;

        }

        foreach($response10a as $key=>$value)
        {
            $temparr=$response10a[$key];
            $count=sizeof($temparr);
            $response10a[$key]['sumQty']=0;
            for ($i=0;$i<$count;$i++)
            {
                $shopsaleno=$response10a[$key][$i]['orderno'];
                $shopsalelineno=$response10a[$key][$i]['lineno'];
                $response10a[$key]['sumQty']+=
                    $response10a[$key][$i]['itemQty']*$response10b[$shopsaleno][$shopsalelineno]['quantity'];
            }
        }


        // CSV Quantity
        /*$SQL11 = "SELECT shopsale.orderno FROM shopsale
                 WHERE shopsale.payment='csv'
                 AND shopsale.orddate >= DATE_SUB(CURDATE(), INTERVAL $duration MONTH)";
        $res11 = mysqli_query($db, $SQL11);*/

        $SQL11 = "SELECT shopsale.orderno FROM shopsale 
                 WHERE shopsale.payment='csv'
                 AND shopsale.orddate >= '$from'
                 AND shopsale.orddate <= '$to'";
        $res11 = mysqli_query($db, $SQL11);
        $response11= [];
        $availableshopsalescsv = [];
        while($row11 = mysqli_fetch_assoc($res11)){
            $shopsaleno=$row11['orderno'];
            $response11[$shopsaleno] = $row11;
            $availableshopsalescsv[]=$shopsaleno;
        }
        $shopsalescsv=implode(",",$availableshopsalescsv);
        $SQL11a = "SELECT shopsalesitems.stockid,shopsalesitems.orderno,
                  shopsalesitems.lineno,shopsalesitems.quantity as itemQty
                from shopsalesitems   
		        WHERE  BINARY shopsalesitems.stockid IN ($stockids)
		        AND shopsalesitems.orderno IN ($shopsalescsv)";

        $res11a = mysqli_query($db, $SQL11a);

        $response11a = [];
        while($row11a = mysqli_fetch_assoc($res11a)){
            $stockid=$row11a['stockid'];
            $response11a[$stockid][] = $row11a;
        }
        $SQL11b = "SELECT shopsalelines.orderno,shopsalelines.id, shopsalelines.quantity
                from shopsalelines   
		        WHERE shopsalelines.orderno IN ($shopsalescsv)";

        $res11b = mysqli_query($db, $SQL11b);

        $response11b = [];
        while($row11b = mysqli_fetch_assoc($res11b)){
            $shopsaleno=$row11b['orderno'];
            $shopsalelineno=$row11b['id'];

            $response11b[$shopsaleno][$shopsalelineno] = $row11b;

        }

        foreach($response11a as $key=>$value)
        {
            $temparr=$response11a[$key];
            $count=sizeof($temparr);
            $response11a[$key]['sumQty']=0;
            for ($i=0;$i<$count;$i++)
            {
                $shopsaleno=$response11a[$key][$i]['orderno'];
                $shopsalelineno=$response11a[$key][$i]['lineno'];
                $response11a[$key]['sumQty']+=
                    $response11a[$key][$i]['itemQty']*$response11b[$shopsaleno][$shopsalelineno]['quantity'];
            }
        }





// No. Of Invoices
        $SQL5 = "SELECT stkcode,invoiceno FROM invoicedetails
                  WHERE invoicedetails.invoiceno IN ($invoices)
                  AND  invoicedetails.stkcode IN ($stockids)
                  GROUP BY  stkcode,invoiceno  
                
		        ";

        $res5 = mysqli_query($db, $SQL5);

        $response5 = [];
        while($row5 = mysqli_fetch_assoc($res5)){
            $stockid=$row5['stkcode'];
            $response5[$stockid][] = $row5;
        }


       foreach($response5 as $key=>$value)
        {
            $temparr=[];
            $temparr=$response5[$key];
            $response5[$key]['invoiceCount']=sizeof($temparr);

        }

//No. Of CRVs
/*        $SQL6 = "SELECT shopsale.orderno FROM shopsale
                 WHERE shopsale.payment='crv'
                 AND shopsale.orddate >= DATE_SUB(CURDATE(), INTERVAL $duration MONTH)";
        $res6 = mysqli_query($db, $SQL6);*/

        $SQL6 = "SELECT shopsale.orderno FROM shopsale 
                 WHERE shopsale.payment='crv'
                 AND shopsale.orddate >= '$from'
                 AND shopsale.orddate <= '$to'";
        $res6 = mysqli_query($db, $SQL6);

        $response6= [];
        $availableshopsales = [];
        while($row6 = mysqli_fetch_assoc($res6)){
            $shopsaleno=$row6['orderno'];
            $response6[$shopsaleno] = $row6;
            $availableshopsales[]=$shopsaleno;
        }
        $shopsales=implode(",",$availableshopsales);

        $SQL7 = "SELECT shopsalesitems.stockid,shopsalesitems.orderno FROM shopsalesitems
                  WHERE shopsalesitems.orderno IN ($shopsales)
                  AND  BINARY shopsalesitems.stockid IN ($stockids)
                  GROUP BY  shopsalesitems.stockid,shopsalesitems.orderno  
                
		        ";

        $res7= mysqli_query($db, $SQL7);

        $response7 = [];
        while($row7 = mysqli_fetch_assoc($res7)){
            $stockid=$row7['stockid'];
            $response7[$stockid][] = $row7;
        }

        foreach($response7 as $key=>$value)
        {
            $temparr=[];
            $temparr=$response7[$key];
            $response7[$key]['shopsaleCount']=sizeof($temparr);


        }

//No. Of CSVs
        /*$SQL8 = "SELECT shopsale.orderno FROM shopsale
                 WHERE shopsale.payment='csv'
                 AND shopsale.orddate >= DATE_SUB(CURDATE(), INTERVAL $duration MONTH)";
        $res8 = mysqli_query($db, $SQL8);*/

        $SQL8 = "SELECT shopsale.orderno FROM shopsale 
                 WHERE shopsale.payment='csv'
                 AND shopsale.orddate >= '$from'
                 AND shopsale.orddate <= '$to'";
        $res8 = mysqli_query($db, $SQL8);
        $response8= [];
        $availableshopsalescsv = [];
        while($row8 = mysqli_fetch_assoc($res8)){
            $shopsaleno=$row8['orderno'];
            $response8[$shopsaleno] = $row8;
            $availableshopsalescsv[]=$shopsaleno;
        }
        $shopsales=implode(",",$availableshopsalescsv);

        $SQL9 = "SELECT shopsalesitems.stockid,shopsalesitems.orderno FROM shopsalesitems
                  WHERE shopsalesitems.orderno IN ($shopsales)
                  AND  BINARY shopsalesitems.stockid IN ($stockids)
                  GROUP BY  shopsalesitems.stockid,shopsalesitems.orderno  
                
		        ";

        $res9= mysqli_query($db, $SQL9);

        $response9 = [];
        while($row9 = mysqli_fetch_assoc($res9)){
            $stockid=$row9['stockid'];
            $response9[$stockid][] = $row9;
        }

        foreach($response9 as $key=>$value)
        {
            $temparr=[];
            $temparr=$response9[$key];
            $response9[$key]['shopsalecsvCount']=sizeof($temparr);


        }

        //Available MPIs
        $SQL12 = "SELECT bazar_parchi.id,bazar_parchi.parchino, bazar_parchi.created_at FROM bazar_parchi 
                 WHERE bazar_parchi.type=601";
        $res12 = mysqli_query($db, $SQL12);

        $response12= [];
        $availablempis = [];
        while($row12 = mysqli_fetch_assoc($res12)){
            $mpino=$row12['id'];
            $response12[$mpino] = $row12;
            $availablempis[]=$mpino;
        }
        $mpis=implode(",",$availablempis);
        //Last MPI
        $SQL13 = "SELECT bpitems.stockid,bpitems.parchino,bpitems.price FROM bpitems 
                 WHERE bpitems.parchino LIKE 'MPIW-%'
                 and  BINARY bpitems.stockid IN ($stockids)
                 ";
        $res13 = mysqli_query($db, $SQL13);

        $response13= [];
        while($row13 = mysqli_fetch_assoc($res13)){
            $stockid=$row13['stockid'];
            $response13[$stockid][] = $row13;

        }
        $response14=[];
        foreach($response13 as $key=>$value)
        {
            $temparr=$response13[$key];

            $count=sizeof($temparr);
            if ($count==0)
            {
                $response14[$key] = null;
            }
            for($i=1; $i<=$count; $i++){
                $response14[$key] = $response13[$key][$count - $i];
                if ($response13[$key][$count - $i]['price']>0)
                   break;
                else
                    $response14[$key] = null;
            }
                $mpino=$response13[$key][$count-1]['parchino'];
            $mpino=preg_replace('[MPIW-]','',$mpino);
                if($response14[$key] != null)
                    $response14[$key]['lastPurchaseDate']=$response12[$mpino]['created_at'];
        }
        // Cart status
        $SQL30 = "SELECT stockid,SUM(issued) as cartqty
                  FROM stockissuance INNER JOIN www_users
                  ON stockissuance.salesperson=www_users.realname
                  WHERE www_users.defaultlocation IN ('HO','MT','SR','OS')
                  
                   GROUP BY stockid
		        ";

        $res30 = mysqli_query($db, $SQL30);

        $response30 = [];
        while($row30 = mysqli_fetch_assoc($res30)){
            $stockid=$row30['stockid'];
            $response30[$stockid]['cartqty'] = $row30['cartqty'];
        }


        /*foreach($response30 as $key=>$value)
        {
            $temparr=[];
            $temparr=$response30[$key];
            //$response30[$key]['cartqty']=sizeof($temparr);

        }*/



////////////////////////////////


        foreach($response1 as $key=>$value)
        {
            $brand=$response1[$key]['brand'];
            $qty=$response3[$key]['sumqty'];
            $response[$key]=$value;
            $response[$key]['brand']=$response2[$brand]['manufacturers_name'];
            $response[$key]['QOH']=$response3[$key]['sumqty'];
            $response[$key]['cartqty']=$response30[$key]['cartqty'];
            $response[$key]['invoiceCount']=$response5[$key]['invoiceCount'];
            $response[$key]['sumQty']=$response4a[$key]['sumQty'];
            $response[$key]['shopsaleCount']=$response7[$key]['shopsaleCount'];
            $response[$key]['shopsalecsvCount']=$response9[$key]['shopsalecsvCount'];
            $response[$key]['sumQtyCRV']=$response10a[$key]['sumQty'];
            $response[$key]['sumQtyCSV']=$response11a[$key]['sumQty'];
            $response[$key]['lastPurchasePrice']=$response14[$key]['price'];
            $response[$key]['lastPurchaseDate']=$response14[$key]['lastPurchaseDate'];
        }

        $finalresponse=[];
        foreach ($response as $key => $value)
        {
            $finalresponse[]=$value;
        }

        echo json_encode($finalresponse);
        //echo json_encode($response14);
        //echo json_encode($temparr);
		return;

	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<style>
	.date{
		padding:10px;
		border-radius: 7px;
	}
	thead tr, tfoot tr{
		background-color: #424242;
		color:white;
	}
</style>

<div class="content-wrapper">
    
	<section class="content-header">
		<div class="col-md-12">
			<h1>SCM Report <label id="loading">  </label></h1>
		</div>


    	<!--<label style="margin-top: 15px;" >Duration </label>
	      				<select name="Duration"  class="duration" id="duration">
                            <option value="">...</option>
	      					<option value="3">3 Months</option>
	      					<option value="12">1 Year</option>
	      					<option value="24">2 Years</option>
	      					
	      				</select>
        -->
        <label style="margin-top: 15px;" >From </label>
        <input type="date" class="from" id="from">
        <label style="margin-top: 15px;" >To </label>
        <input type="date" class="to" id="to">

        <label style="margin-top: 15px;">Brand </label>
        <select name="brand" class="brand" id="brand" multiple>
            <?php
            $sql = "SELECT manufacturers_id, manufacturers_name FROM manufacturers";
            $ErrMsg = _('The stock categories could not be retrieved because');
            $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
            echo '<option value="">Select Brand</option>';
            while ($myrow=DB_fetch_array($result)) {

                    echo '<option value="' . $myrow['manufacturers_id'] . '">' . $myrow['manufacturers_name'] . '</option>';

            }
            ?>
        </select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="searchresults">
			<thead>
				<tr>
                    <th>Stock ID</th>
                    <th>manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>QOH</th>
                    <th>Cart Qty</th>
                    <th>No. Of Invoices</th>
                    <th>Sold Invoice Qty</th>
                    <th>No. Of CRVs</th>
                    <th>Sold CRV Qty</th>
                    <th>No. Of CSVs</th>
                    <th>Sold CSV Qty</th>
                    <th>Total Qty Sold</th>
                    <th>Average Unit Per Sale</th>
                    <th>Last Purchase Price</th>
                    <th>Last Purchase Date</th>
                    <th>List Price</th>
                    <th>ERP Recommended</th>
                    <th>SCM Recommended</th>
                    <th>SCM Recommended</th>
                    <th>Minimum Qty</th>
                    <th>Total Value ERP Rec.</th>
                    <th>Total Value SCM Rec.</th>
                    <th>Currency</th>
                    <th>Forex Price</th>
                    <th>Override</th>
                    <th>ListPriceAfterParity</th>

                </tr>
			</thead>
			<tfoot>
				<tr>
                    <th>Stock ID</th>
                    <th>manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>QOH</th>
                    <th>Cart Qty</th>
                    <th>No. Of Invoices</th>
                    <th>Sold Invoice Qty</th>
                    <th>No. Of CRVs</th>
                    <th>Sold CRV Qty</th>
                    <th>No. Of CSVs</th>
                    <th>Sold CSV Qty</th>
                    <th>Total Qty Sold</th>
                    <th>Average Unit Per Sale</th>
                    <th>Last Purchase Price</th>
                    <th>Last Purchase Date</th>
                    <th>List Price</th>
                    <th>ERP Recommended</th>
                    <th>SCM Recommended</th>
                    <th>SCM Recommended</th>
                    <th>Minimum Qty</th>
                    <th>Total Value ERP Rec.</th>
                    <th>Total Value SCM Rec.</th>
                    <th>Currency</th>
                    <th>Forex Price</th>
                    <th>Override</th>
                    <th>ListPriceAfterParity</th>


                </tr>
			</tfoot>
		</table>
	
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>
<script>
	$(document).ready(function(){
        var buttonCommon = {
            exportOptions: {
                columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,19,20,21,22,23]
            }

        };

        let brandListSelect = $("#brand").select2();
        let durationListSelect = $("#duration").select2();
		let table = $('#searchresults').DataTable({
            "scrollY": "500px",
            "scrollX": true,
            responsive: true,
			dom: 'Bflrtip',
			"lengthMenu": [[ 10, 25, 50, 75, 100,500,1000 ], [10, 25, 50, 75, 100,500,1000 ]],
            buttons: [
                $.extend( true, {}, buttonCommon, {
                    extend: 'copyHtml5'
                } ),
                $.extend( true, {}, buttonCommon, {
                    extend: 'excelHtml5'
                } ),
                $.extend( true, {}, buttonCommon, {
                    extend: 'csvHtml5'
                } ),
                $.extend( true, {}, buttonCommon, {
                    extend: 'pdfHtml5'
                } )
            ],
			language: {
		        search: "_INPUT_",
		        searchPlaceholder: "Search..."
		    },
		    				
			
		    columns:[
				{"data":"stockid"},
                {"data":"mnfCode"},
                {"data":"mnfpno"},
                {"data":"description"},
                {"data":"brand"},
                {"data":"QOH"},
                {"data":"cartqty"},
                {"data":"invoiceCount"},
                {"data":"sumQty"},
                {"data":"shopsaleCount"},
                {"data":"sumQtyCRV"},
                {"data":"shopsalecsvCount"},
                {"data":"sumQtyCSV"},
                {"data":"sumQtyCSV"},
                {"data":"sumQtyCSV"},
                {"data":"lastPurchasePrice"},
                {"data":"lastPurchaseDate"},
                {"data":"materialcost"},
                {"data":"stockid"},
                {"data":"scmrecommended"},
                {"data":"scmrecommended"},
                {"data":"minimumqty"},
                {"data":"stockid"},
                {"data":"stockid"},
                {"data":"stockid"},
                {"data":"stockid"},
                {"data":"stockid"},
                {"data":"stockid"},



            ],
		"columnDefs": [
            {
                "targets": [ 19 ],
                "visible": false,
                "searchable": false
            },


            {
                    "render": function ( data, type, row ) {
                       return data > 0  ?
                           data:
                           0;
                    },
                    className: 'text-center',
                    "targets": [6,7,8,9,10,11,12]
                },


	        	{
            		"render": function ( data, type, row ) {
            		    let stockid=row.stockid;
                        let listprice=row.materialcost;
                		let html="<input  value='"+data+"' class='scmRecommended' data-stockid='"+stockid+"' data-listprice='"+listprice+"'>";
                		return html;
            		},
            		className: 'text-center',
            		"targets": 18
        		},

                {
                    "render": function ( data, type, row ) {
                        let avg=parseInt((row.sumQty+row.sumQtyCRV+row.sumQtyCSV));
                        return avg;

                    },
                    className: 'text-center',
                    "targets": 13
                },
                {
                    "render": function ( data, type, row ) {
                        let avg=parseInt((row.sumQty+row.sumQtyCRV+row.sumQtyCSV)/(row.invoiceCount+row.shopsaleCount+row.shopsalecsvCount));
                        if (avg>0)
                            return avg;
                        else
                            return 0;
                    },
                    className: 'text-center',
                    "targets": 14
                },


            {
                    "render": function ( data, type, row ) {
                        let html="<input value='"+data+"'>";
                        return parseInt((row.sumQty+row.sumQtyCRV+row.sumQtyCSV)/durationListSelect.val());
                    },
                    className: 'text-center',
                    "targets": 18
                },
            {
                "render": function ( data, type, row ) {
                    let stockid=row.stockid;
                    let minimumqty=row.minimumqty;
                    let html="<input  value='"+data+"' class='minimumqty' data-stockid='"+stockid+"' data-minimumqty='"+minimumqty+"'>";
                    return html;
                },
                className: 'text-center',
                "targets": 21
            },

            {
                    "render": function ( data, type, row ) {
                        let html="<input value='"+data+"'>";
                        return parseInt((row.sumQty+row.sumQtyCRV+row.sumQtyCSV)/durationListSelect.val())*row.materialcost;
                    },
                    className: 'text-center',
                    "targets": 22
                },
                {
                    "render": function ( data, type, row ) {
                        let html="<input value='"+data+"'>";
                        return parseInt(row.scmrecommended)*row.materialcost;
                    },
                    className: 'text-center',
                    "targets": 23
                },

            {
                "render": function ( data, type, row ) {
                    let html="<input value='"+data+"'>";
                    return html;
                },
                className: 'text-center',
                "targets": 24
            },
            {
                "render": function ( data, type, row ) {
                    let html="<input>";
                    return html;
                },
                className: 'text-center',
                "targets": 25
            },
            {
                "render": function ( data, type, row ) {
                    let html="<input type='checkbox'>";
                    return html;
                },
                className: 'text-center',
                "targets": 26
            },
            {
                "render": function ( data, type, row ) {
                    let html="<input>";
                    return html;
                },
                className: 'text-center',
                "targets": 27
            },


        ]
	        
		});

		$('#datatable tfoot th').each( function (i) {
	        var title = $('#datatable thead th').eq( $(this).index() ).text(); 
	        if(title != "Amount" && title != "Statement"){
	        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
	        } 
	    });

	    table.columns().every( function () {
	        var that = this;
	        $('input', this.footer()).on('keyup change', function (){
	            if(that.search() !== this.value){
	                that.search(this.value).draw();
	            }
	        });
	    });

	    $(".searchData").on("click", function(){
	    	let brand = $(".brand").val();
	    	/*let duration  = $(".duration").val();*/
            let from  = $(".from").val();
            let to  = $(".to").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';
            $('#loading').html("Loading Data");

	    	table.clear().draw();
	    	$.post("scmreport.php",{brand,from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
                $('#loading').html("");
	    	});
	    });
        $('#searchresults').on('change','.scmRecommended',function(){
            let stockid = $(this).attr('data-stockid');
            let listprice = $(this).attr('data-listprice');
            let value = $(this).val();
            $.post("api/updateScmRecommended.php",{stockid,value,name:'scmrecommended'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });
            $(this).parent().next().next().html(listprice*$(this).val());
        });
        $('#searchresults').on('change','.minimumqty',function(){
            let stockid = $(this).attr('data-stockid');

            let value = $(this).val();
            $.post("api/updateminimumqty.php",{stockid,value,name:'minimumqty'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });

        });



    });
</script>
<?php
	include_once("includes/foot.php");
?>