<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "payments_due_report_crv")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];

	$SQL = 'SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,debtorsmaster.dba,dcs.invoicegroupid,
        dcs.dcstatus FROM dcs INNER JOIN debtorsmaster on debtorsmaster.debtorno=dcs.debtorno
		WHERE dcs.orddate >= "'.$from.'"'.'
		AND dcs.orddate <= "'.$to.'"'.'
		';

		$res = mysqli_query($db, $SQL);
        $response = [];


        while($row = mysqli_fetch_assoc($res)){

                $response[$row['dcno']] = $row;
                $response[$row['dcno']]['grblist'] = "";
                $SQLgrb="SELECT * FROM grb WHERE dcno='".$row['dcno']."'";
            $resgrb = mysqli_query($db, $SQLgrb);
            while($rowgrb = mysqli_fetch_assoc($resgrb)){
                $response[$row['dcno']]['grblist'].= $rowgrb['orderno']."<br/>";
            }

                $response[$row['dcno']]['invoicelist'] = "";
                $SQLinvoice="SELECT * FROM invoice WHERE groupid='".$row['invoicegroupid']."'";
                $resinvoice = mysqli_query($db, $SQLinvoice);
                while($rowinvoice = mysqli_fetch_assoc($resinvoice)){
                    $response[$row['dcno']]['invoicelist'].= $rowinvoice['invoiceno']."<br/>";
                }

                $checked = (($row['dcstatus']!="") ? $row['dcstatus']:"");

                $response[$row['dcno']]['sendtoinvoice'] = (($checked=="")?"
                <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
            
                ":$row['dcstatus']);

                $checked = (($row['dcstatus']!="") ? $row['dcstatus']:"");

                $response[$row['dcno']]['dcinvoiced'] = (($checked=="")?"
                <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
                
                ":$row['dcstatus']);

                $POFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'PurchaseOrder_'.$row['dcno'].'*.pdf');
                $PO="";
                $ind=0;
                foreach($POFilePath as $POFile) {
                    $ind++;
                    $PO.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$POFile.'">Attachment'.$ind.'</a>';
                }
                $response[$row['dcno']]['polist']= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
                <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
                <input type='file' id='attachPOFile".$row['dcno']."' data-orderno='".$row['dcno']."' class='attachPOFile' name='PO'>
                <input type='button' id='uploadPOFile' data-orderno='".$row['dcno']."' class='uploadPOFile' name='uploadPOFile' value='uploadPO'>
                </form>".$PO;

                $CourierSlipFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'CourierSlip_'.$row['dcno'].'*.pdf');
                $CourierSlip="";
                $ind=0;
                foreach($CourierSlipFilePath as $CourierSlipFile) {
                    $ind++;
                    $CourierSlip.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$CourierSlipFile.'">Attachment'.$ind.'</a>';
                }
                $response[$row['dcno']]['couriersliplist']= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
                    <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
                    <input type='file' id='attachCourierSlipFile".$row['dcno']."' data-orderno='".$row['dcno']."' class='attachCourierSlipFile' name='CourierSlip'>
                    <input type='button' id='uploadCourierSlipFile' data-orderno='".$row['dcno']."' class='uploadCourierSlipFile' name='uploadCourierSlipFile' value='uploadCourierSlip'>
                    </form>".$CourierSlip;



                $InvoiceFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'Invoice_'.$row['dcno'].'*.pdf');
                $Invoice="";
                $ind=0;
                foreach($InvoiceFilePath as $InvoiceFile) {
                    $ind++;
                    $Invoice.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$InvoiceFile.'">Attachment'.$ind.'</a>';
                }
                $response[$row['dcno']]['oldinvoicelist']= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
                    <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
                    <input type='file' id='attachInvoiceFile".$row['dcno']."' data-orderno='".$row['dcno']."' class='attachInvoiceFile' name='Invoice'>
                    <input type='button' id='uploadInvoiceFile' data-orderno='".$row['dcno']."' class='uploadInvoiceFile' name='uploadInvoiceFile' value='uploadInvoice'>
                    </form>".$Invoice;

                $GRBFilePath = glob("../".$_SESSION['part_pics_dir'] . '/' .'GRB_'.$row['dcno'].'*.pdf');
                $GRB="";
                $ind=0;
                foreach($GRBFilePath as $GRBFile) {
                    $ind++;
                    $GRB.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$GRBFile.'">Attachment'.$ind.'</a>';
                }
                $response[$row['dcno']]['oldgrblist']= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
                        <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
                        <input type='file' id='attachGRBFile".$row['dcno']."' data-orderno='".$row['dcno']."' class='attachGRBFile' name='GRB'>
                        <input type='button' id='uploadGRBFile' data-orderno='".$row['dcno']."' class='uploadGRBFile' name='uploadGRBFile' value='uploadGRB'>
                        </form>".$GRB;




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
                    <th>Send For Invoice</th>
					<th>Invoice DC</th>
					<th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Invoice</th>
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
                    <th>Send For Invoice</th>
                    <th>Invoice DC</th>
                    <th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Invoice</th>
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
            dom: 'Bflrtip',
            buttons: [
                'excelHtml5',
                'csvHtml5',
            ],
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
                {"data":"sendtoinvoice"},
                {"data":"dcinvoiced"},
                {"data":"polist"},
                {"data":"couriersliplist"},
                {"data":"oldinvoicelist"},
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