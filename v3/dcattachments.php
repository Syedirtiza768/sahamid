<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "show_dc_attachments_view")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];
		$SQL1="SELECT DISTINCT dcno as dcno FROM grb";
		$res1=mysqli_query($db,$SQL1);
        $grbarray=[];
        while($row1 = mysqli_fetch_assoc($res1)){
            $grbarray[$row1['dcno']]="";

        }
        $SQLgrb="SELECT * FROM grb";
        $resgrb = mysqli_query($db, $SQLgrb);

        while($rowgrb = mysqli_fetch_assoc($resgrb)){
            $grbarray[$rowgrb['dcno']].=
                '<a target="_blank" href = "../PDFGRB.php?grbno='.$rowgrb['orderno'].'">'.$rowgrb['orderno'].'</a>'.
                "<br/>";

        }
        $SQL2="SELECT distinct dcs.orderno from dcs inner join invoice on dcs.invoicegroupid=invoice.groupid";
        $res2=mysqli_query($db,$SQL2);
        $invoicearray=[];
        while($row2 = mysqli_fetch_assoc($res2)){
            $invoicearray[$row2['orderno']]="";

        }
        $SQLinvoice="SELECT * from dcs inner join invoice on dcs.invoicegroupid=invoice.groupid  ";
        $resinvoice = mysqli_query($db, $SQLinvoice);

        while($rowinvoice = mysqli_fetch_assoc($resinvoice)){

            $invoicearray[$rowinvoice['orderno']].=
                '<a target="_blank" href = "../PDFInvoice.php?InvoiceNo='.$rowinvoice['invoiceno'].'">'.$rowinvoice['invoiceno'].'</a>'.
                "<br/>";

        }



        if(!userHasPermission($db, "show_all_dcs_attachments")){
        $SQLdcs = 'SELECT dcs.orderno as dcno FROM dcs INNER JOIN salescase
        ON dcs.salescaseref=salescase.salescaseref INNER JOIN salesman
        ON salescase.salesman=salesman.salesmanname
        
		WHERE ( salesman.salesmanname ="'.$_SESSION['UsersRealName'].'"
		OR salesman.salesmancode IN  (SELECT salescase_permissions.user 
		FROM salescase_permissions WHERE salescase_permissions.can_access = "'.$_SESSION['UserID'].'"'.'))
		AND dcs.orddate >= "'.$from.'"'.'
		AND dcs.orddate <= "'.$to.'"'.'
		';
        }
        else{
        $SQLdcs = 'SELECT dcs.orderno as dcno FROM dcs
		WHERE dcs.orddate >= "'.$from.'"'.'
		AND dcs.orddate <= "'.$to.'"'.'
		';}
        $resdcs = mysqli_query($db, $SQLdcs);

        $polist=[];

        $POFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_*.pdf');

        $couriersliplist=[];

        $CourierSlipFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'CourierSlip_*.pdf');

        $grblist=[];

        $GRBFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'GRB_*.pdf');

        $invoicelist=[];

        $InvoiceFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'Invoice_*.pdf');

        $commercialinvoicelist=[];

        $CommercialInvoiceFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'CommercialInvoice_*.pdf');

        while ($rowdcs = mysqli_fetch_assoc($resdcs)) {
        $PO = "";
        $ind = 0;
        foreach($POFilePath as $POFile) {



                if (strpos($POFile, $rowdcs['dcno'])!=False) {

                    $ind++;
                    if (strpos($POFile, "deleted")!=False)
                        $PO .= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $POFile . '">Attachment' . $ind . "</a>";

                    else
                        $PO .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $POFile . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $POFile . "' data-orderno='" . $rowdcs['dcno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";

                }
                $polist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachPOFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachPOFile' name='PO'>
            <input type='button' id='uploadPOFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadPOFile' name='uploadPOFile' value='uploadPO'>
            </form>" . $PO;

            }
            $CourierSlip = "";
            $ind = 0;
            foreach($CourierSlipFilePath as $CourierSlipFile) {



                if (strpos($CourierSlipFile, $rowdcs['dcno'])!=False) {

                    $ind++;
                    if (strpos($CourierSlipFile, "deleted")!=False)
                        $CourierSlip .= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $CourierSlipFile . '">Attachment' . $ind . "</a>";

                    else
                        $CourierSlip .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $CourierSlipFile . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $CourierSlipFile . "'  data-orderno='" . $rowdcs['dcno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";

                }


            }
            $couriersliplist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachCourierSlipFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachCourierSlipFile' name='CourierSlip'>
            <input type='button' id='uploadCourierSlipFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadCourierSlipFile' name='uploadCourierSlipFile' value='uploadCourierSlip'>
            </form>" . $CourierSlip;

            $Invoice = "";
            $ind = 0;
            foreach($InvoiceFilePath as $InvoiceFile) {



                if (strpos($InvoiceFile, $rowdcs['dcno'])!=False) {

                    $ind++;
                    if (strpos($InvoiceFile, "deleted")!=False)
                        $Invoice .= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $InvoiceFile . '">Attachment' . $ind . "</a>";

                    else
                        $Invoice .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $InvoiceFile . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $InvoiceFile . "'  data-orderno='" . $rowdcs['dcno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";

                }

            }
            $invoicelist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachInvoiceFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachInvoiceFile' name='Invoice'>
            <input type='button' id='uploadInvoiceFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadInvoiceFile' name='uploadInvoiceFile' value='uploadInvoice'>
            </form>" . $Invoice;


            $CommercialInvoice = "";
            $ind = 0;
            foreach($CommercialInvoiceFilePath as $CommercialInvoiceFile) {



                if (strpos($CommercialInvoiceFile, $rowdcs['dcno'])!=False) {

                    $ind++;
                    if (strpos($CommercialInvoiceFile, "deleted")!=False)
                        $CommercialInvoice .= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $CommercialInvoiceFile . '">Attachment' . $ind . "</a>";

                    else
                        $CommercialInvoice .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $CommercialInvoiceFile . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $CommercialInvoiceFile . "'  data-orderno='" . $rowdcs['dcno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";

                }
/*                $commercialinvoicelist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachInvoiceFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachInvoiceFile' name='Invoice'>
            <input type='button' id='uploadInvoiceFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadInvoiceFile' name='uploadInvoiceFile' value='uploadInvoice'>
            </form>" . $CommercialInvoice;*/

            }
            $commercialinvoicelist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachCommercialInvoiceFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachCommercialInvoiceFile' name='CommercialInvoice'>
            <input type='button' id='uploadCommercialnvoiceFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadCommercialInvoiceFile' name='uploadCommercialInvoiceFile' value='uploadCommercialInvoice'>
            </form>" . $CommercialInvoice;

            $GRB = "";
            $ind = 0;
            foreach($GRBFilePath as $GRBFile) {



                if (strpos($GRBFile, $rowdcs['dcno'])!=False) {

                    $ind++;
                    if (strpos($GRBFile, "deleted")!=False)
                        $GRB .= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $GRBFile . '">Attachment' . $ind . "</a>";

                    else
                        $GRB .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $GRBFile . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $GRBFile . "'  data-orderno='" . $rowdcs['dcno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";

                }

            }
            $grblist[$rowdcs['dcno']] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
            <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
            <input type='file' id='attachGRBFile" . $rowdcs['dcno'] . "' data-orderno='" . $rowdcs['dcno'] . "' class='attachGRBFile' name='GRB'>
            <input type='button' id='uploadGRBFile' data-orderno='" . $rowdcs['dcno'] . "' class='uploadGRBFile' name='uploadGRBFile' value='uploadGRB'>
            </form>" . $GRB;

        }


        if(!userHasPermission($db, "show_all_dcs_attachments")){
        $SQL = 'SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client
        ,debtorsmaster.dba,dcs.invoicegroupid,
        dcs.dcstatus FROM dcs INNER JOIN debtorsmaster on debtorsmaster.debtorno=dcs.debtorno
		WHERE dcs.orderno IN (SELECT dcs.orderno as dcno FROM dcs 
		INNER JOIN salescase ON dcs.salescaseref=salescase.salescaseref
		 INNER JOIN www_users ON salescase.salesman = www_users.realname
		  WHERE www_users.userid
		   IN (SELECT salescase_permissions.can_access FROM salescase_permissions
		    WHERE salescase_permissions.user = "'.$_SESSION['UserID'].'"'.')) AND
		dcs.orddate >= "'.$from.'"'.'
		AND dcs.orddate <= "'.$to.'"'.'
		';
        }
        else {
            $SQL = 'SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,debtorsmaster.dba,dcs.invoicegroupid,
        dcs.dcstatus FROM dcs INNER JOIN debtorsmaster on debtorsmaster.debtorno=dcs.debtorno
		WHERE dcs.orddate >= "' . $from . '"' . '
		AND dcs.orddate <= "' . $to . '"' . '
		';
        }
		$res = mysqli_query($db, $SQL);
        $response = [];


        while($row = mysqli_fetch_assoc($res)){

                $response[$row['dcno']] = $row;


                $response[$row['dcno']]['grblist']=$grbarray[$row['dcno']];
                $response[$row['dcno']]['invoicelist'] = $invoicearray[$row['dcno']];

                $checked = (($row['dcstatus']!="") ? $row['dcstatus']:"");

                $response[$row['dcno']]['sendtoinvoice'] = (($checked=="")?"
                <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
            
                ":$row['dcstatus']);

                $checked = (($row['dcstatus']!="") ? $row['dcstatus']:"");

                $response[$row['dcno']]['dcinvoiced'] = (($checked=="")?"
                <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
                
                ":$row['dcstatus']);
                $response[$row['dcno']]['polist']=$polist[$row['dcno']];
                $response[$row['dcno']]['couriersliplist']=$couriersliplist[$row['dcno']];
                $response[$row['dcno']]['oldgrblist']=$grblist[$row['dcno']];
                $response[$row['dcno']]['oldinvoicelist']=$invoicelist[$row['dcno']];
                $response[$row['dcno']]['oldcommercialinvoicelist']=$commercialinvoicelist[$row['dcno']];



        }
        $data = [];
       foreach ($response as $key => $value)
       {
           $data[]=$value;
       }

		echo json_encode($data);
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

<div class="content-wrapper" style="overflow: scroll;">
    
	<section class="content-header">
		<div class="col-md-12">
			<h1>Manage DC Attachments</h1>
		</div>
		<!-- <label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> -->
    	FROM <input type="date" class="date fromDate">
    	TO <input type="date" class="date toDate">
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>
					<th>DC No</th>
					<th>Date</th>
					<th>Salescaseref</th>
                    <th>Customer Name</th>
                    <th>DBA</th>
                    <th>DC Internal</th>
                    <th>DC External</th>
                   <!-- <th>Send For Invoice</th>
					<th>Invoice DC</th>-->
					<th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Tax Invoice</th>
                    <th>Commercial Invoice</th>
                    <th>GRB</th>
                    <th>System Invoice</th>
                    <th>System GRB</th>

				</tr>
			</thead>
			<tfoot>
				<tr>
                    <th>DC No</th>
                    <th>Date</th>
                    <th>Salescaseref</th>
                    <th>Customer Name</th>
                    <th>DBA</th>
                    <th>DC Internal</th>
                    <th>DC External</th>
                   <!-- <th>Send For Invoice</th>
                    <th>Invoice DC</th>-->
                    <th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Tax Invoice</th>
                    <th>Commercial Invoice</th>
                    <th>GRB</th>
                    <th>System Invoice</th>
                    <th>System GRB</th>
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
        let table = $('#datatable').DataTable({
        <?php
            if(userHasPermission($db, "show_dc_attachments_csv")){
            echo "dom: 'Bflrtip',
        buttons: [
                'excelHtml5',
                'csvHtml5',
            ],";}?>
           // dom: 'Bflrtip',
            /*buttons: [
                'excelHtml5',
                'csvHtml5',
            ],*/
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            },
            columns:[
                {"data":"dcno"},
                {"data":"date"},
                {"data":"salescaseref"},
                {"data":"client"},
                {"data":"dba"},
                {"data":"dcno"},
                {"data":"dcno"},
               /* {"data":"sendtoinvoice"},
                {"data":"dcinvoiced"},*/
                {"data":"polist"},
                {"data":"couriersliplist"},
                {"data":"oldinvoicelist"},
                {"data":"oldcommercialinvoicelist"},
                {"data":"oldgrblist"},
                {"data":"invoicelist"},
                {"data":"grblist"},
            ],
            "columnDefs": [
                {
                    "render": function ( data, type, row ) {
                       let html='<a href="../salescase/salescaseview.php?salescaseref='+data+'" target="_blank">'+data+'</a>';

                        return html;


                    },
                    "targets":[2]
                },

                {
                    "render": function ( data, type, row ) {
                        let html='<a target="_blank" href = "../PDFDCh.php?DCNo='+data+'">'+data+'</a>';
                        return html;

                    },
                    "targets":[5]
                },
                {
                    "render": function ( data, type, row ) {
                        let html='<a target="_blank" href = "../PDFDChExternal.php?DCNo='+data+'">'+data+'</a>';
                        return html;

                    },
                    "targets":[6]
                },

            ]
        });

        $('#datatable tfoot th').each( function (i) {
            var title = $('#datatable thead th').eq( $(this).index() ).text();

            $(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );

        });

        table.columns().every( function () {
            var that = this;
            $('input', this.footer()).on('keyup change', function (){
                if(that.search() !== this.value){
                    that.search(this.value).draw();
                }
            });
        });
        $('#datatable').on('click','.uploadPOFile',function(){
            var ref=$(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachPOFile'+orderno)[0].files[0];

            fd.append('PO', files);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/uploadPOFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){


                        ref.parent().parent().html(res);
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click','.uploadCourierSlipFile',function(){
            var ref=$(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachCourierSlipFile'+orderno)[0].files[0];

            fd.append('CourierSlip', files);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/uploadCourierSlipFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){

                        ref.parent().parent().html(res);
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });

        $('#datatable').on('click','.uploadInvoiceFile',function(){
            var ref=$(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachInvoiceFile'+orderno)[0].files[0];

            fd.append('Invoice', files);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/uploadInvoiceFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){

                        ref.parent().parent().html(res);
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click','.uploadCommercialInvoiceFile',function(){
            var ref=$(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachCommercialInvoiceFile'+orderno)[0].files[0];

            fd.append('CommercialInvoice', files);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/uploadCommercialInvoiceFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){

                        ref.parent().parent().html(res);
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });

        $('#datatable').on('click','.uploadGRBFile',function(){
            var ref=$(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachGRBFile'+orderno)[0].files[0];

            fd.append('GRB', files);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/uploadGRBFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){

                        ref.parent().parent().html(res);
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click','.removeFile',function(){
            var ref=$(this);
            var basepath = $(this).attr('data-basepath');
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();
            fd.append('basepath',basepath);
            fd.append('orderno',orderno);

            $.ajax({
                url: 'api/removeFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res){
                    if(res){

                        ref.prev().css("background-color", "red");
                        ref.remove();
                    }
                    else{
                        alert('file not uploaded');
                    }
                },
            });
        });

        $(".searchData").on("click", function(){
            let from  = $(".fromDate").val();
            let to  = $(".toDate").val();
            let FormID = '<?php echo $_SESSION['FormID']; ?>';

            table.clear().draw();
            $("tbody tr td").html("<h3>Loading ... (This may take a few minutes) </h3>");
            $.post("dcattachments.php",{from,to,FormID},function(res, status){
                res = JSON.parse(res);
                table.rows.add(res).draw();
            });
        });

    });

</script>
<?php
	include_once("includes/foot.php");
?>