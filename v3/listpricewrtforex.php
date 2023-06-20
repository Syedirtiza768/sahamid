<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	/*if(!userHasPermission($db, "add_new_lead")){
		header("Location: /");
		return;
	}*/

	if(isset($_POST['to'])){

        $SQL = "SELECT * FROM stockmaster INNER JOIN manufacturers ON stockmaster.brand=manufacturers.manufacturers_id";


        $res = mysqli_query($db,$SQL);
        $response=[];


       while($row=mysqli_fetch_assoc($res))
        {
            $response[]=$row;
        }



        echo json_encode($response);

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
		<div class="col-md-12">
			<h1>List Price wrt Forex <label id="loading"> LOADING DATA </label></h1>
		</div>
		<!-- <label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> -->
    	<input type="hidden" class="date toDate">
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="datatable">

            <thead>
				<tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Model No</th>
                    <th>Brand</th>
                    <th>Current List Price</th>
                    <th>Exchange Currency</th>
                    <th>Foreign Exchange Amount</th>
                    <th>Check Box</th>
				</tr>
			</thead>
			<tfoot>
            <tr>
                <th>Item Code</th>
                <th>Description</th>
                <th>Category</th>
                <th>Model No</th>
                <th>Brand</th>
                <th>Current List Price</th>
                <th>Exchange Currency</th>
                <th>Foreign Exchange Amount</th>
                <th>Check Box</th>
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
            "scrollY": "500px",
            "scrollX": true,
			dom: 'Blfrtip',
            "order": [[ 0, "desc" ]],
            colReorder: true,
            "autoWidth": true,
            "lengthMenu": [ [50, 100, 200, -1], [50, 100, 200, "All"] ],

			buttons: [
	            'csvHtml5'

	        ],

			language: {
		        search: "_INPUT_",
		        searchPlaceholder: "Search..."
		    },
            columns:[
                {"data":"stockid"},
				{"data":"description"},
				{"data":"categoryid"},
                {"data":"mnfCode"},
				{"data":"manufacturers_name"},
				{"data":"materialcost"},
                {"data":"exchcurr"},
				{"data":"exchamt"},
                {"data":"chk"}
			],
            "columnDefs": [
                {
                    "render": function ( data, type, row ) {
                        let html='<select >' +
                            '<option>USD</option>' +
                            '<option>Euro</option> ' +
                            '</select>'
                        return html;


                    },
                    "targets":[6]
                },
                {
                    "render": function ( data, type, row ) {
                        let html='<input type="number" step="any">'
                        return html;


                    },
                    "targets":[7]
                },

                {
                    "render": function ( data, type, row ) {
                        let html='<input type="checkbox">'
                        return html;


                    },
                    "targets":[8]
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

	    	let from  = $(".fromDate").val();
	    	let to  = $(".toDate").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();

	    	$.post("listpricewrtforex.php",{from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    		$('#loading').html("");
	    	});


	});
    $(document).on('change','.attachFile',function(){

        $(".uploadFile").attr('disabled',false);
    });
    $(document).on('change','.input-yay',function(){
        let leadid = $(this).attr('data-lead');
        let name = $(this).attr('data-name');
        let value = $(this).val();

        $(".uploadFile").attr('disabled',false);
        $.post("api/leadupdate.php",{leadid,value,name},function(res,data, status){
            console.log("Data: " + data + "\nStatus: " + status);
            $(`.updatehistory${leadid}`).html(res);
        });

    });
    $(document).on('click','.input-yaz',function(){
        let ref=$(this);
        let leadid = $(this).attr('data-lead');
        let name = $(this).attr('data-name');
        let value = $(this).val();


        $(".uploadFile").attr('disabled',false);
                $.post("api/leadupdate.php", {leadid, value, name}, function (res, data, status) {
                    console.log("Data: " + data + "\nStatus: " + status);
                    $(`.updatehistory${leadid}`).html(res);
                    ref.html("Deleted")
                    ref.removeClass();
                    ref.parent().prev().html("Deleted")
                    ref.parent().parent().remove();



            });

    });
    $(document).on('click','.convert',function(){
        let ref=$(this);
        let leadid = $(this).attr('data-lead');
        let name = $(this).attr('data-name');
        let value = $(this).val();


        $(".uploadFile").attr('disabled',false);
        swal({
                title: "Are you sure?",
                text: `Are you sure you have checked the lead name phone and email before converting
                    `,

                showCancelButton: true,
                confirmButtonColor: "green",
                cancelButtonColor: "red",
                confirmButtonText: "Continue!",
                cancelButtonText: "Cancel!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function() {
                let url = "../salescase/salescaseview.php?salescaseref="+leadid+"";
                window.open(url, '_blank').focus();
                swal.close();
                return;
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
            url: 'api/removeleadfiles.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res){

                    ref.prev().remove();
                    ref.remove();
                }
                else{
                    alert('file not uploaded');
                }
            },
        });
    });
    $('#datatable').on('click','.uploadFile',function(){
        $(".uploadFile").attr('disabled',true);
        var ref=$(this);
        var leadid = $(this).attr('data-lead');
        var name = $(this).attr('data-name');
        var lastname = $(this).attr('data-lastname');
        var fd = new FormData();



        if (name == "bill")
            var files = $('#attachBillFile_'+leadid)[0].files[0];
        if (name == "photo")
            var files = $('#attachPhotoFile_'+leadid)[0].files[0];
        if (name == "quote")
            var files = $('#attachQuoteFile_'+leadid)[0].files[0];


        fd.append('uploadedfile', files);
        fd.append('leadid',leadid);
        fd.append('name',name);
        fd.append('lastname',lastname);

        $.ajax({
            url: 'api/uploadFile.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                if(res){
                    ref.parent().append(res);
                }
                else{
                    alert('file not uploaded');
                }
            },
        });

    });
    $('#datatable tfoot th').each( function (i) {
        var title = $('#datatable thead th').eq( $(this).index() ).text();
        if(title != "Action"){
            $(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );
        }
    });
    $('table tbody tr').each( function (i) {
        console.log("hgs");
        //$(this).css('background-color','red');
    });

</script>
<?php
	include_once("includes/foot.php");
?>