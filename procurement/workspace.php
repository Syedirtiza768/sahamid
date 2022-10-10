<?php 
	$PathPrefix='../';
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	include('api/stages.php');

	if(!userHasPermission($db,'procurement_workspace')){
		echo "Permission Denied.";
		return;
	}

	$SQL = "SELECT supplierid, suppname FROM suppliers WHERE suppliers.supptype=1";
	$suppliers = mysqli_query($db, $SQL);

	$SQL = "SELECT categoryid,categorydescription FROM stockcategory ORDER BY categorydescription";
	$stockCategory = mysqli_query($db, $SQL);

	$SQL = "SELECT manufacturers_id AS m_id,manufacturers_name AS m_name FROM manufacturers";
	$brands = mysqli_query($db, $SQL);

	$createProcurementDocument = userHasPermission($db,'create_procurement_document');
	$openProcurementDocument   = userHasPermission($db,'open_procurement_document');
	$printProcurementDocument  = userHasPermission($db,'print_procurement_document');
	$canUpdateItemPrice 	   = userHasPermission($db,'update_procurement_document_item_price');
	$canUpdateItemQuantity 	   = userHasPermission($db,'update_procurement_document_item_quantity');
	$canInsertItem 			   = userHasPermission($db,'insert_item_procurement_document');
	$updateDocumentStage 	   = userHasPermission($db,'update_procurement_document_stage');
	$cancelDocument 		   = userHasPermission($db,'cancel_procurement_document');
	$discardDocument 		   = userHasPermission($db,'discard_procurement_document');
	$publishDocument 		   = userHasPermission($db,'publish_procurement_document');
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />
	
		<style>
			body{
				overflow: hidden;
			}
			.dataTables_filter label{
				width: 100% !important;
			}
			.body{
				height: 100vh;
				width: 100vw;
				display: flex;
			}
			.right-side{
				width: 30vw;
				height: 100vh;
				background: #C5BD99;
				border: 2px solid #424242;
				border-bottom: 2px solid #424242;
				display: flex;
				flex-direction: column;
				transition: width .8s ease-in-out;
			}
			.left-side{
				width: 70vw;
				transition: width .8s ease-in-out;
			}
			.main-header{
				padding: 10px;
				font-size: 3rem;
				display: flex;
				align-items: center;
				background: #424242;
				color: white;
			}
			.back-button{
				margin-right: 10px;
				padding-right: 10px;
				border-right: 2px #ccc solid;
				cursor: pointer;
			}
			.main-title{
				flex: 1;
				text-align: center;
			}
			.inner-body-fixed{
				width: 100%;
				height: calc(100vh - 50px);
				padding: 7px;
				overflow: hidden;
				display: flex;
				flex-direction: column;
			}
			.filters{
				border-bottom: 1px solid #424242;
			}
			.date-filter{
				display: flex;
				padding: 10px;
			}
			.date-filter input{
				display: flex;
				flex: 1;
				border: 1px solid #424242;
				border-radius: 10px;
			}
			.date-filter span{
				display: flex;
				padding: 0 10px;
				color: #701112;
				align-items: center;
			}
			.stage-filter{
				display: flex;
				padding: 10px;
				padding-top: 0;
			}
			.stage-filter select{
				flex: 1;
			}
			.supplier-filter{
				display: flex;
				padding: 10px;
				padding-top: 0;
			}
			.supplier-filter input{
				border: 1px solid #424242;
				border-radius: 10px;
				padding: 5px;
				flex: 1;
			}
			.value-filter{
				display: flex;
				padding: 10px;
				padding-top: 0;
			}
			.value-filter span{
				color: #424242;
				flex: 1;
				margin:0 5px;
				background: white;
				border:1px solid #424242;
				padding: 5px;
				border-radius: 10px;
				text-align: center;
				margin-left: 0;
			}
			.value-filter select{
				height: auto;
			}
			.value-filter input{
				flex: 1;
				border:1px solid #424242;
				padding: 5px;
				border-radius: 10px;
				margin:0 5px;
				margin-right: 0;
			}
			.submit-filter{
				padding: 10px;
				padding-top: 0;
				display: flex;
			}
			.submit-filter button{
				flex: 1;
			}
			.basic-document-details{
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				position: relative;
			}
			.basic-document-details h3{
				text-align: center;
				color: #701112;
			}
			.basic-document-details{
				color: #701112;
			}
			.document-actions{
				display: flex;
				flex-direction: column;
				border-top: 1px solid #424242;
			}
			.document-actions .when-is-draft,.document-actions .when-is-saved{
				display: flex;
			}
			.document-actions .when-is-draft button,.document-actions .when-is-saved button{
				flex: 1;
				margin: 10px;
			}
			.tabs-headings{
				display: flex;
			}
			.tabs-headings span{
				flex:1;
				background: #ccc;
				border-radius: 0px;
			}
			.tabs-headings .selected{
				background: white;
				border: 1px solid #424242;
				border-bottom: 0px;
				color: #701112;
				font-weight: bolder;
				box-shadow: 0px 7px 15px grey;
			    z-index: 9;
			    text-shadow: 3px 3px 23px;
			}
			.tabs-body{
				height: calc(100vh - 50px - 34px - 14px);
				overflow-y: scroll;
				overflow-x: hidden;
				border: 1px solid #424242;
				border-top: 0;
				background: white;
			}
			.searchTabBody, .documentItemsTable{
				padding: 10px;
			}
			.searchTabBody thead tr, .documentItemsTable thead tr{
				background: #ccc;
				color: #701112;
			}
			.searchTabBody tfoot tr, .documentItemsTable tfoot tr{
				background: #ccc;
				color: #701112;
			}
			.documentTabBody{
				display: none;
				padding: 10px;
				flex-direction: column;
			}
			.documentTabBody .tabs-area{
				display: flex;
				flex: 1
			}
			.documentTabBody .tabs-area button{
				flex: 1;
				padding: 5px;
				background: #ccc;
				border-radius: 0;
			}
			.documentTabBody .tabs-area .selected{
				background: white;
				border: 1px solid #424242;
				border-bottom: 0px;
				color: #701112;
				font-weight: bolder;
			}
			.documentSearchTable{
				width:100%;
			}
			.hamburger{
				cursor: pointer;
			}
			td.details-control {
			    background: url('../resources/details_open.png') no-repeat center center;
			    cursor: pointer;
			}
			tr.shown td.details-control {
			    background: url('../resources/details_close.png') no-repeat center center;
			}
			.dataTables_wrapper{
				width: 100%;
			}
			.itemDetails div{
				border-left: 2px solid #ccc;
			}
			.dt-buttons{
				display: inline-block;
			}
			.dt-buttons .dt-button{
				padding: 10px;
				margin-left: 10px;
			}
			.dataTableInput{
				width: 80px;
				border: 1px solid #424242;
				border-radius: 7px;
				padding: 5px;
			}
			.dataTableInputQTY{
				width: 50px !important;
			}
			.fit{
				width: 1%;
			}
			.hidden{
				display: none;
			}
			.detailsTimeLine{
				display: flex;
				overflow: hidden;
				overflow-x: scroll;
				font-size: 2rem;
				padding: 15px;
				font-size: .9em;
			}
			.detailsTimeLine div{
				border:1px solid #424242;
				border-radius: 7px;
				padding: 10px;
				background: white;
				color: #424242;
				margin: 10px;
				position: relative;
				white-space: nowrap;
			}
			.detailsTimeLine div .name{
				position: absolute;
				top: 25px;
			}
			.itemSearchFilters{
				display: flex; 
				flex-direction: column; 
				align-items: center; 
				justify-content: center;
			}
			.itemSearchFilters select{
				width: 100%;
			}
			.searchInput{
			    height: 46px;
			    border: 1px solid #ccc;
			    border-radius: 5px;
			    background: rgb(248, 248, 248);
			    width: 100%;
			}
			#brandFromCat{
				width: 100%;
			}
			#newItemModal{
				background: rgba(0,0,0,.5);
			}
			#newItemModal .modal-backdrop{
				height: 0 !important;
			}
			.addnewitemcls{
				white-space: nowrap !important;
			}
			.canceled{
				position: absolute;
			    left: 20%;
			    transform: rotate(-45deg);
			    font-size: 3em;
			    top: 34%;
			    border: 5px solid red;
			    padding: 14px;
			    color: red;
			    border-radius: 10px;
			    text-shadow: 3px 2px 3px;
			}
			.printArea{
				display: none;
			}
			.doNotBreak{
				display: flex;
			}
			.deliveryEstimate{
				text-align: center;
			}
			.deliveryEstimate input{
				padding: 5px; 
				border: 1px solid #424242;
				border-radius: 7px;
			}
			@media print {
				.doNotPrint{
					display: none;
				}   
				.printArea{
					display: block;
				}
			}
		</style>

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>
	</head>
	<body>

		<div class="body doNotPrint">

			<div class="left-side">
				<div class="main-header">
					<div class="back-button">
						<a href="/sahamid/index.php" style="color: white;"><i class="fa fa-arrow-left"></i></a>
					</div>
					<div class="main-title">
						Procurement Workspace
					</div>
					<div class="hamburger" data-open="abcd">
						<i class="fa fa-bars"></i>
					</div>
				</div>
				<div class="inner-body-fixed">
					<div class="tabs-headings">
						<span class="tabs-head btn selected searchTabHead">Search Results</span>
						<span class="tabs-head btn documentTabHead hidden">
							Opened Document
							<span style="float: right; padding: 4px 8px;" onclick="closeOpenDocument();">×</span>
						</span>
					</div>
					<div class="tabs-body">
						<div class="searchTabBody">
							<table class="table table-responsive table-striped documentSearchTable">
								<thead>
									<tr>
										<th>Sr#</th>
										<th>Order#</th>
										<th>Supplier</th>
										<th>Date</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody></tbody>
								<tfoot>
									<tr>
										<th>Sr#</th>
										<th>Order#</th>
										<th>Supplier</th>
										<th>Date</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="documentTabBody">
							<table class="table table-responsive table-striped documentItemsTable">
								<thead>
									<tr>
										<th></th>
										<th>MNFCode</th>
										<th>MNFPNo</th>
										<th class="text-center">QOH</th>
										<th class="text-center">QOH-IS</th>
										<th class="text-center">Ack</th>
										<th class="text-center">QTY</th>
										<th class="text-center">Price</th>
										<th class="text-center">SubTotal</th>
										<th></th>
									</tr>
								</thead>
								<tbody></tbody>
								<tfoot>
									<tr>
										<th></th>
										<th>MNFCode</th>
										<th>MNFPNo</th>
										<th class="text-center">QOH</th>
										<th class="text-center">QOH-IS</th>
										<th class="text-center">Ack</th>
										<th class="text-center">QTY</th>
										<th class="text-center">Price</th>
										<th class="text-center">SubTotal</th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="right-side right-block-container">
				<div class="filters">
					<div class="date-filter">
						<input type="date">
						<span>TO</span>
						<input type="date">
					</div>
					<div class="stage-filter">
						<select>
						<option>All</option>
						<option>Canceled</option>
						<?php $st = "Commencement"; while($stages[$st]){ ?>
							<option><?php echo $st; ?></option>
						<?php $st = $stages[$st]['next']; } ?>
						</select>
					</div>
					<div class="supplier-filter">
						<input type="text" placeholder="Supplier Name">
					</div>
					<div class="value-filter">
						<span>Value</span>
						<select>
							<option>>=</option>
							<option><=</option>
							<option>=</option>
							<option>></option>
							<option><</option>
						</select>
						<input type="number" placeholder="Value">
					</div>
					<div class="submit-filter">
						<button class="btn btn-success">Search</button>
					</div>
				</div>
				<div class="basic-document-details"></div>
				<div class="document-actions"></div>
			</div>
			
		</div>

		<div id="newDocumentModal" class="modal fade doNotPrint" role="dialog">
  			<div class="modal-dialog">
    			<div class="modal-content">
      				<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal">&times;</button>
        				<h4 class="modal-title text-center">Create New Doucment</h4>
      				</div>
      				<div class="modal-body">
      					<h4 class="text-center">Select Supplier</h4>
        				<select name="" id="selectedSupplierForNewDocument" class="form-control">
        					<option value="select">Select Supplier</option>
        				<?php while($row = mysqli_fetch_assoc($suppliers)){ ?>
        					<option value="<?php echo $row['supplierid']; ?>"><?php echo $row['suppname']; ?></option>
        				<?php } ?>
        				</select>
      				</div>
      				<div class="modal-footer">
        				<button type="button" class="btn btn-success form-control" id="addNewDocumentClick">
        					Create New Document
        				</button>
      				</div>
    			</div>
  			</div>
		</div>

		<div id="newItemModal" class="modal fade doNotPrint" role="dialog">
  			<div class="modal-dialog modal-lg" style="width: 95%;">
    			<div class="modal-content">
      				<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal">&times;</button>
        				<h4 class="modal-title text-center">Add Item</h4>
      				</div>
      				<div class="modal-body">
      					<div class="row">
      						<div class="col-md-12">
	      						<div class="col-md-3">
	      							<div class="itemSearchFilters">
	      								<span>Category</span>
	      								<select id="scat" class="search" name="scategory">
							        		<option value="ALL">ALL</option>
								        	<?php while ($r = mysqli_fetch_assoc($stockCategory)) { ?>
												<option value="<?php echo $r['categoryid']; ?>">
													<?php echo $r['categorydescription']; ?>
												</option>
								        	<?php } ?>
							        	</select>
	      							</div>
	      						</div>
	      						<div class="col-md-3">
	      							<div class="itemSearchFilters">
	      								<span>Brands</span>
	      								<div id="brandFromCat">
							        		<select class="search" id="searchbrand">
							        			<option value="ALL">ALL</option>
							        			<?php while($r = mysqli_fetch_assoc($brands)) { ?>
							        				<option value="<?php echo $r['m_id']; ?>">
							        					<?php echo $r['m_name']; ?>
							        				</option>
							        			<?php } ?>
							        		</select>
							        	</div>
	      							</div>
	      						</div>
	      						<div class="col-md-3">
	      							<div class="itemSearchFilters">
	      								<span>Stockid/MNFCode/MNFPNo</span>
	      								<input type="text" id="stockIdSearch" class="searchInput">
	      							</div>
	      						</div>
	      						<div class="col-md-3">
	      							<div class="itemSearchFilters">
	      								<span>Description</span>
	      								<input type="text" id="searchDescription" class="searchInput">
	      							</div>
	      						</div>
	      					</div>
	      					<div class="col-md-12" style="margin-top: 10px">
	      						<div class="col-md-6">
								    <input class="btn btn-warning" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bcr" value="Clear Results">
								</div>
								<div class="col-md-6">
							      	<input class="btn btn-primary" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bss" value="Search">
								</div>
								<div class="alert alert-danger"  
									style="text-align:center; clear: both; margin: 20px 15px">
									<strong id="errormessage"></strong>
								</div>
	      					</div>
	      					<div class="col-md-12" style="margin-top: 10px;">
	      						<table id="itemSearchTable" width="100%" class="responsive table table-responsive table-striped">
									<thead>
										<tr style="background:#424242; color:white">
											<th>Item Code</th>
											<th>MNFCode</th>
											<th>MNFPNo</th>
											<th>Description</th>
											<th>QOH</th>
											<th>IS-QOH</th>
											<th>IGP-HO</th>
											<th>OGP-HO</th>
											<th>IGP-SR</th>
											<th>OGP-SR</th>
											<th>DC</th>
											<th>ACK</th>
											<th>Unit</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody id="srb" style="color: black">
										
									</tbody>
									<tfoot>
										<tr style="background:#424242; color:white">
											<th>Item Code</th>
											<th>MNFCode</th>
											<th>MNFPNo</th>
											<th>Description</th>
											<th>QOH</th>
											<th>IS-QOH</th>
											<th>IGP-HO</th>
											<th>OGP-HO</th>
											<th>IGP-SR</th>
											<th>OGP-SR</th>
											<th>DC</th>
											<th>ACK</th>
											<th>Unit</th>
											<th>Action</th>
										</tr>
									</tfoot>
								</table>
	      					</div>
      					</div>
      				</div>
    			</div>
  			</div>
		</div>

		<div class="printArea">asdasda</div>

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../v2/assets/datatables/datatables.buttons.min.js"></script>
		<script src="../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../quotation/assets/javascripts/theme.js"></script>

		<script>
			var FormID = '<?php echo $_SESSION['FormID']; ?>';
			var table;
			var documentTable;
			var searchTable;
			var openDocument = null;
			var permissions = [];
			permissions['createProcurementDocument'] = <?php echo ($createProcurementDocument) ? "true":"false"; ?>;
			permissions['canUpdateItemQuantity'] 	 = <?php echo ($canUpdateItemQuantity) ? "true":"false"; ?>;
			permissions['canUpdateItemPrice'] 	 	 = <?php echo ($canUpdateItemPrice) ? "true":"false"; ?>;
			permissions['canInsertItem'] 	 	 	 = <?php echo ($canInsertItem) ? "true":"false"; ?>;
			permissions['openProcurementDocument'] 	 = <?php echo ($openProcurementDocument) ? "true":"false"; ?>;
			permissions['printProcurementDocument']  = <?php echo ($printProcurementDocument) ? "true":"false"; ?>;
			permissions['updateDocumentStage']  	 = <?php echo ($updateDocumentStage) ? "true":"false"; ?>;
			permissions['cancelDocument']  	 		 = <?php echo ($cancelDocument) ? "true":"false"; ?>;
			permissions['discardDocument']  	 	 = <?php echo ($discardDocument) ? "true":"false"; ?>;
			permissions['publishDocument']  	  	 = <?php echo ($publishDocument) ? "true":"false"; ?>;

			$(document).ready(()=>{

				table = $(".documentSearchTable").DataTable({


					"columns": [
			            {"data": "sr"},
			            { "data": "id" },
			            { "data": "supplier" },
			            { "data": "startdate" },
			            { "data": "stage" },
			            { "data": "id" }
			        ],
			        createdRow: function (row, data, index) {
			        	if(data['received']){
			        		$(row).find("td:eq(0)").addClass("btn-success");
			        	}else if(data['canceled']){
			        		$(row).find("td:eq(0)").addClass("btn-danger");
			        	}
				        
				    },
			        "columnDefs": [
			        	{
	                		"render": function ( data, type, row ) {
	                    		let html = ``;
	                    		if(permissions['openProcurementDocument']){
	                    			html += `
										<button class="btn btn-info btn-sm openSelectedDocument" data-id="${data}">
		                    				Open
		                    			</button>&nbsp;
	                    			`;
	                    		}
	                    		if(permissions['printProcurementDocument']){
	                    			html += `
	                    				<button class="btn btn-success btn-sm printSelectedDocument" data-id="${data}">
		                    				<i class="fa fa-print"></i>
		                    			</button>
	                    			`;
	                    		}
	                    		return html;
	                		},
	                		className: 'text-center doNotBreak',
	                		"targets": 5
	            		},
	            	]
				});
				documentTable = $(".documentItemsTable").DataTable({
					"columns": [
			            {
			                "className":      'details-control',
			                "orderable":      false,
			                "data":           null,
			                "defaultContent": ''
			            },
			            { "data": "mnfcode" },
			            { "data": "mnfpno" },
			            { "data": "qoh" },
			            { "data": "is" },
			            { "data": "ack" },
			            { "data": "quantity" },
			            { "data": "price" },
			        ],
			        "columnDefs": [
			        	{
	                		"render": function ( data, type, row ) {
	                    		return data;
	                		},
	                		className: 'text-center',
	                		"targets": 3
	            		},
			        	{
	                		"render": function ( data, type, row ) {
	                    		return data;
	                		},
	                		className: 'text-center',
	                		"targets": 4
	            		},
			        	{
	                		"render": function ( data, type, row ) {
	                			if(openDocument['received'] || openDocument['canceled']){
	                				return `<span>${data}</span>`;
	                			}
	                    		return `<input  class="dataTableInput" 
	                    						type="number" 
	                    						value="${data}"
	                    						data-stockid="${row['stockid']}"
	                    						data-name="price"
	                    						data-oldval="${data}"></input>`;
	                		},
	                		className: 'text-center',
	                		"targets": 7
	            		},
			        	{
	                		"render": function ( data, type, row ) {
	                			return `<span id='${row['stockid']}'>${parseInt(row['client_required'])+parseInt(row['safety_inventory'])+parseInt(row['stock'])}</span>`;
	                			if(openDocument['received'] || openDocument['canceled']){
	                				return `<span>${data}</span>`;
	                			}
	                    		return `<input  class="dataTableInput" 
	                    						type="number" 
	                    						value="${data}"
	                    						data-stockid="${row['stockid']}"
	                    						data-name="quantity"
	                    						data-oldval="${data}"></input>`;
	                		},
	                		className: 'text-center',
	                		"targets": 6
	            		},
	            		{
	                		"render": function ( data, type, row ) {
	                			return `<span id='${row['stockid']}'>${(parseInt(row['client_required'])+parseInt(row['safety_inventory'])+parseInt(row['stock']))*row['price']}</span>`;
	                		},
	                		className: 'text-center fit',
	                		"targets": 8
	            		},
	            		{
	                		"render": function ( data, type, row ) {
	                			if(openDocument['received'] || openDocument['canceled']){
	                				return ``;
	                			}
	                    		return `<button class="btn btn-danger btn-xs removeItemFromDocument"
	                    						data-stockid="${row['stockid']}">
	                    					&times;
	                    				</button>`;
	                		},
	                		className: 'text-center fit',
	                		"targets": 9
	            		}
	        		],
			        "order": [[1, 'asc']],
			        dom: 'fBlrtip',
	        		buttons: [
	            		{
	                		text: '<i class="fa fa-plus"></i> Add New Item',
	                		action: function ( e, dt, node, config ) {
	                			if(openDocument['canceled']){
	                				generateNotification("error","Error","Document has been canceled.");
	                				return;
	                			}
	                			if(openDocument['received']){
	                				generateNotification("error","Error","Items have been received.");
	                				return;
	                			}
	                			if(permissions['canInsertItem']){
	                    			addNewItemPopUp();
	                			}else{
	                				generateNotification("error","Error","User does not have permission to add item.");
	                			}
	                		}
	            		},
	            		{
	                		text: '<i class="fa fa-download"></i> CSV Download',
	                		action: function ( e, dt, node, config ) {
	                			if(openDocument == null){
	                				generateNotification("error","error","no open document found");
	                				return;
	                			}
	                			let rows = documentTable.rows().data();
	                			let csv = [];
	                			let csvRow = [];
	                			csvRow.push('sr');
	                			csvRow.push('stockid');
	                			csvRow.push('mnfcode');
	                			csvRow.push('mnfpno');
	                			csvRow.push('description');
	                			csvRow.push('igp');
	                			csvRow.push('ogp');
	                			csvRow.push('igp-sr');
	                			csvRow.push('ogp-sr');
	                			csvRow.push('QOH');
	                			csvRow.push('IS-QOH');
	                			csvRow.push('ACK');
	                			csvRow.push('Client Required');
	                			csvRow.push('Safety Inventory');
	                			csvRow.push('Stock');
	                			csvRow.push('Total Order Quantity');
	                			csvRow.push('Unit Price');
	                			csvRow.push('Total Price');
	                			csv.push(csvRow);
	                			let count = 1;
	                			rows.each(function(rowArray){
	                				csvRow = [];
	                				csvRow.push(count);
	                				csvRow.push('"'+rowArray['stockid']+'"');
	                				csvRow.push('"'+rowArray['mnfcode']+'"');
	                				csvRow.push('"'+rowArray['mnfpno']+'"');
	                				csvRow.push('"'+rowArray['description']+'"');
	                				csvRow.push(rowArray['igp']);
	                				csvRow.push(rowArray['ogp']);
	                				csvRow.push(rowArray['igp-sr']);
	                				csvRow.push(rowArray['ogp-sr']);
	                				csvRow.push(rowArray['qoh']);
	                				csvRow.push(rowArray['is']);
	                				csvRow.push(rowArray['ack']);
	                				csvRow.push(rowArray['client_required']);
	                				csvRow.push(rowArray['safety_inventory']);
	                				csvRow.push(rowArray['stock']);
	                				csvRow.push((parseInt(rowArray['client_required'])+parseInt(rowArray['safety_inventory'])+parseInt(rowArray['stock'])));
	                				csvRow.push(rowArray['price']);
	                				csvRow.push((parseInt(rowArray['client_required'])+parseInt(rowArray['safety_inventory'])+parseInt(rowArray['stock']))*parseInt(rowArray['price']));
								   	csv.push(csvRow);
								   	count++;
								}); 
								let csvContent = "data:text/csv;charset=utf-8,";
								csv.forEach(function(rowArray){
								   let row = rowArray.join(",");
								   csvContent += row + "\r\n";
								}); 
								let encodedUri = encodeURI(csvContent);
								var link = document.createElement("a");
								link.setAttribute("href", encodedUri);
								link.setAttribute("download", "procurementDocument"+openDocument['id']+".csv");
								link.style.display = "none";
								link.innerHTML= "Click Here to download";
								document.body.appendChild(link);
								link.click();
	                		}
	            		}
	        		]
				});
				searchTable = $('#itemSearchTable').DataTable({
                    "lengthMenu": [[10, 25, 50,1000, -1], [10, 25, 50,1000, "All"]],
					"columns": [
			            { "data": "stockid" },
			            { "data": "mnfcode" },
			            { "data": "mnfpno" },
			            { "data": "description" },
			            { "data": "qoh" },
			            { "data": "is" },
			            { "data": "igp" },
			            { "data": "ogp" },
			            { "data": "igp-sr" },
			            { "data": "ogp-sr" },
			            { "data": "dc" },
			            { "data": "ack" },
			            { "data": "units" },
			            { "data": "action" }
			    	],
			    	"columnDefs": [
					    { className: "itemcodes", "targets": [ 0 ] },
					    { className: "itemdescs", "targets": [ 1 ] },
					    { className: "itemqohs", "targets": [ 2 ] },
					]
				});
				$('#itemSearchTable tfoot th').each( function (i) {
			        let title = $('#itemSearchTable thead th').eq( $(this).index() ).text(); 
			        if(title != "Action" && title != "QOH" && title != "IS-QOH" && title != "IGP-HO" && title != "OGP-HO" && title != "IGP-SR" && title != "OGP-SR" && title != "DC" && title != "ACK" && title != "Unit"){
			        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
			        } 
			    });

			    searchTable.columns().every( function () {
			        var that = this;
			        $('input', this.footer()).on('keyup change', function (){
			            if(that.search() !== this.value){
			                that.search(this.value).draw();
			            }
			        });
			    });
				$(".dt-button").addClass("btn btn-success");
				$(".dt-button").data("toggle","modal");
				$(".dt-button").data("target","#newDocumentModal");

				$('.documentItemsTable tbody').on('click', 'td.details-control', function () {
			        let tr = $(this).closest('tr');
			        let row = documentTable.row( tr );

			        if ( row.child.isShown() ) {
			            row.child.hide();
			            tr.removeClass('shown');
			        }
			        else {
			            row.child(format(row.data())).show();
			            tr.addClass('shown');
			        }
			    } );

				addNewDoucmentButton();

			});
			$(".searchTabHead").on("click",() => {
				if($(".searchTabHead").hasClass("selected"))
					return;
				$(".documentTabHead").removeClass("selected");
				$(".searchTabHead").addClass("selected");
				$(".documentTabBody").css("display","none");
				$(".searchTabBody").css("display","flex");
			});
			$(".documentTabHead").on("click",() => {
				if($(".documentTabHead").hasClass("selected"))
					return;
				$(".searchTabHead").removeClass("selected");
				$(".documentTabHead").addClass("selected");
				$(".searchTabBody").css("display","none");
				$(".documentTabBody").css("display","flex");
			});
			$(".hamburger").on("click", () => {
				let ref = $(this);
				let isOpen = ref.attr("data-open");

				if(isOpen == "abcd" || typeof isOpen == "undefined"){
					ref.attr("data-open","false");
					$(".right-side").css("width","0px");
					$(".left-side").css("width","100vw");
				}else{
					ref.attr("data-open","abcd");
					$(".right-side").css("width","30vw");
					$(".left-side").css("width","70vw");
				}
			});
			$(".fa-arrow-left").on("click", function(e){
				e.preventDefault();
				let href = $(this).closest("a").attr("href");
				if(openDocument != null){
					swal({
	  					title: "Are you sure?",
	  					text: "There is a document open are you sure you want to go back!",
	  					type: "warning",
	  					showCancelButton: true,
	  					confirmButtonColor: "#DD6B55",
	  					confirmButtonText: "Yes!",
	  					cancelButtonText: "Cancel!",
	  					closeOnConfirm: true,
	  					closeOnCancel: false
					}, function(isConfirm){
	  					if (isConfirm) {
	    					window.location = href;
	  					} else {
	  						swal.close();
	    					//swal("Cancelled", "Operation was canceled.", "error");
	  					}
					});
				}else{
					window.location = href;
				}
			});
			$("#addNewDocumentClick").on("click", () => {
				swal({
					title: "Are you sure?",
					text: "Are you sure you want to create new document?",
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "green",
					confirmButtonText: "Yes!",
					cancelButtonText: "Cancel!",
					closeOnConfirm: false,
					closeOnCancel: false
				}, function(isConfirm){
					if (isConfirm) {
						swal.disableButtons();

						let supplier = $("#selectedSupplierForNewDocument").val();

						if(supplier == "select"){
							swal.enableButtons();
							swal("Warning","Supplier Needs to be selected in order to create new document.","warning");
							return;
						}

						$.post("api/createProcurementDocument.php", {FormID, supplier}, (res, status) => {
							res = JSON.parse(res);
							if(res.status == "success"){
								openDocumentProcess(res['document']);
								$('#newDocumentModal').modal('hide');
								swal.close();
							}else{
								swal("Error",res.message,"error");
							}
						});

					} else {
						swal("Cancelled", "Operation was canceled.", "error");
					}
				});
			});
			$(".submit-filter button").on("click", () => {

				if($(".submit-filter button").attr("data-inprogress") == "true"){
					generateNotification("warning","Alert","Request Already In Progress");
					return;
				}

				if(!$(".submit-filter button").attr("data-inprogress")){
					$(".submit-filter button").attr("data-inprogress","true");
				}else{
					$(".submit-filter button").attr("data-inprogress","true");
				}

				table.clear().draw();
				$.get("api/searchProcurementDocuments.php",(res, status) => {
					res = JSON.parse(res);
					table.rows.add(res).draw();
					$(".submit-filter button").attr("data-inprogress","false");
				});
			});
			$(document.body).on("click",".openSelectedDocument",function(){
				let id = $(this).attr("data-id");

				if(openDocument != null){
					swal({
	  					title: "Are you sure?",
	  					text: "There is a document open are you sure you want to close and open another!",
	  					type: "warning",
	  					showCancelButton: true,
	  					confirmButtonColor: "#DD6B55",
	  					confirmButtonText: "Yes!",
	  					cancelButtonText: "Cancel!",
	  					closeOnConfirm: false,
	  					closeOnCancel: false
					}, function(isConfirm){
	  					if (isConfirm) {
	    					swal.disableButtons();
	  						$.post("api/openProcurementDocument.php",{FormID,id}, (res, status) => {
	  							res = JSON.parse(res);

	  							if(res.status == "success"){
	  								let doc = res['document'];
	  								openDocumentProcess(doc);
	  							}else{
	  								swal("Error",res.message,"error");
	  							}
	  							
	  							swal.enableButtons();
	    						swal.close();
	  						});
	  					} else {
	    					swal("Cancelled", "Operation was canceled.", "error");
	  					}
					});
				}else{
					$.post("api/openProcurementDocument.php",{FormID,id},(res, status) => {
						res = JSON.parse(res);
						
				  		if(res.status == "success"){
							let doc = res['document'];
							openDocumentProcess(doc);
						}else{
							swal("Error",res.message,"error");
						}
					});
				}
			});

			$(document.body).on("click",".updateDocumentStage",function(){
				let id = $(this).attr("data-id");

				swal({
  					title: "Are you sure?",
  					text: "Are you sure you want to update document stage!",
  					type: "warning",
  					showCancelButton: true,
  					confirmButtonColor: "#DD6B55",
  					confirmButtonText: "Yes!",
  					cancelButtonText: "Cancel!",
  					closeOnConfirm: false,
  					closeOnCancel: false
				}, function(isConfirm){
  					if (isConfirm) {
    					swal.disableButtons();
  						$.post("api/updateDocumentStage.php",{FormID,id}, (res, status) => {
  							res = JSON.parse(res);

  							if(res.status == "success"){
  								$(document.body).find(".currentStage").html(res['data']['current']);
  								$(document.body).find(".nextStageName").html(res['data']['next']);

  								let timelineNode = `
									<div>
										<span class="name">${res['data']['date']}</span>
										${res['data']['current']}
									</div>
  								`;

  								openDocument['spermission'] = res['data']['permissions'];

  								$(document.body).find(".detailsTimeLine").append(timelineNode);

  								if(res['data']['isfinal']){
  									$(document.body).find(".updateDocumentStage").remove();
  									$(document.body).find(".dataTableInput").each(function(){
  										$(this).prop("disabled",true);
  									});
  								}

  								if(!res['data']['permissions']['cancelable']){
									if($(document.body).find(".cancelDocument"))
										$(document.body).find(".cancelDocument").addClass("hidden");
  								}else{
  									if($(document.body).find(".cancelDocument"))
										$(document.body).find(".cancelDocument").removeClass("hidden");
  								}

  								if(!res['data']['permissions']['discardable']){
  									if($(document.body).find(".discardDocument"))
										$(document.body).find(".discardDocument").addClass("hidden");
  								}else{
  									if($(document.body).find(".discardDocument"))
										$(document.body).find(".discardDocument").removeClass("hidden");
  								}

  								swal.enableButtons();
    							swal.close();
  							}else{
  								swal("Error",res.message,"error");
  							}
  						});
  					} else {
    					swal("Cancelled", "Operation was canceled.", "error");
  					}

  				});
			});
			$(document.body).on("change","#scat",function(){
				let val = $(this).val();

				$.post("api/getBrand.php",{cat: val},function(data, status){
					$("#brandFromCat").html(data);
			    });
			});
			$(document.body).on("click","#bss", function(){

				if(openDocument == null){
					$("#errormessage").html('No Open Doucment Found...');	
					return;
				}
				
				$("#errormessage").html('Searching...');

				let docID   = openDocument['id'];
				let cat 	= $("#scat").val();
				let brand 	= $("#searchbrand").val();
				let stockid = $("#stockIdSearch").val();
				let desc 	= $("#searchDescription").val();

				$.post("api/itemSearch.php",
					{
						cat: cat,
						brand: brand,
						stockid: stockid,
						desc: desc,
						document:docID
					},
					function(data, status){
						try{
							data = JSON.parse(data);
							if(data.status == "error"){
								$("#errormessage").html(data.message);
							}else if(data.status == "success"){
								searchTable.clear().draw();	
			    				searchTable.rows.add(data.data).draw();
								$("#errormessage").html("");
							}
						}catch(e){

						}
			    	}
			    );
			});

			$("#bcr").on("click",function(){
				searchTable.clear().draw();	
			})

			function format ( d ) {
			    return `<div class="col-md-12 itemDetails">
							<div class="col-md-6">	
								<h4>StockID:</h4>
								<h6>${d.stockid}</h6>
								<h4>Brand: </h4>
								<h6>${d.mname}</h6>
								<h4>Description: </h4>
								${d.description}
							</div>
							<div class="col-md-6">	
								<h4>Details (IGP/OGP/IGP-SR/OGP-SR): </h4>
								<h6>${d['igp']}/${d['ogp']}/${d['igp-sr']}/${d['ogp-sr']}</h6>
								<h4>DC Quantity: </h4>
								<h6>${d['dc']}</h6>
								<h4>Unit: </h4>
								<h6>${d.units}</h6>
							</div>
							<div class="col-md-12">
								<div class="col-md-4">
									<h4>Client Required</h4>
									<h6>
										<input  class="dataTableInputQTY dataTableInput" 
	                						type="number" 
	                						value="${d['client_required']}"
	                						data-stockid="${d['stockid']}"
	                						data-name="client_required"
	                						data-oldval="${d['client_required']}"></input>
									</h6>
								</div>
								<div class="col-md-4">
									<h4>Safety Inventory</h4>
									<h6>
										<input  class="dataTableInputQTY dataTableInput" 
	                						type="number" 
	                						value="${d['safety_inventory']}"
	                						data-stockid="${d['stockid']}"
	                						data-name="safety_inventory"
	                						data-oldval="${d['safety_inventory']}"></input>
									</h6>
								</div>
								<div class="col-md-4">
									<h4>Stock</h4>
									<h6>
										<input  class="dataTableInputQTY dataTableInput" 
	                						type="number" 
	                						value="${d['stock']}"
	                						data-stockid="${d['stockid']}"
	                						data-name="stock"
	                						data-oldval="${d['stock']}"></input>
									</h6>
								</div>
							</div>
						</div>
			    		`;
			}
			function openDocumentProcess(dc){
				openDocument = dc;
				documentTable.clear().draw();
				enableOpenDocumentSection();
				addOpenDocumentDetails(openDocument);
				updateDocumentActions(openDocument);
				documentTable.rows.add(dc.items).draw();

				if(openDocument['canceled']){
					$(document.body).find(".canceled").removeClass("hidden");
				}

			}
			function enableOpenDocumentSection(){
				$(".documentTabHead").removeClass("hidden");
				if($(".documentTabHead").hasClass("selected"))
					return;
				$(".searchTabHead").removeClass("selected");
				$(".documentTabHead").addClass("selected");
				$(".searchTabBody").css("display","none");
				$(".documentTabBody").css("display","flex");
			}
			function addNewItemPopUp(){
				if(!openDocument['spermission']['addItem']){
					swal("Alert","Items Cannot be added at this stage.","warning");
					return;
				}

				$("#newItemModal").modal("show");
			}
			function closeOpenDocument(){
				swal({
  					title: "Are you sure?",
  					text: "Are you sure you want to close this document?",
  					type: "warning",
  					showCancelButton: true,
  					confirmButtonColor: "#DD6B55",
  					confirmButtonText: "Yes, Close it!",
  					cancelButtonText: "No, Keep it open!",
  					closeOnConfirm: false,
  					closeOnCancel: false
				}, function(isConfirm){
  					if (isConfirm) {
    					swal("Closed!", "Document has been closed.", "success");
    					closeOpenDocumentProcess();
  					} else {
    					swal("Cancelled", "Operation was canceled.", "error");
  					}
				});
			}
			function closeOpenDocumentProcess(){
				$(".documentTabHead").addClass("hidden");
				$(".documentTabHead").removeClass("selected");
				$(".searchTabHead").addClass("selected");
				$(".documentTabBody").css("display","none");
				$(".searchTabBody").css("display","flex");
				addNewDoucmentButton();
				clearBasicDetailsSection();
				documentTable.clear().draw();
				searchTable.clear().draw();	
				openDocument = null;
			}
			function clearBasicDetailsSection(){
				$(".basic-document-details").html("");
			}
			function addNewDoucmentButton(){
				let html = `
					<div class="create-new-document">
						<button class="btn btn-success form-control" data-toggle="modal" data-target="#newDocumentModal">
								<i class="fa fa-plus"></i>
								Create New Document
						</button>
					</div>
				`;
				if(!permissions['createProcurementDocument']){
					html = ``;
				}
				$(".document-actions").html(html);
			}
			function updateDocumentActions(dc){
				let html = `<div><div class="when-is-draft">`;
				
				if(dc['spermission']['cancelable'] == true && !dc['canceled'] && permissions['cancelDocument']){
					html += `
						<button class="btn btn-warning cancelDocument documentAction" 
								style="margin:0; padding: 10px; font-size:2em"
								data-status="Cancel">
							<i class="fa fa-times"></i>
							Cancel
						</button>
					`;
				}

				if(dc['spermission']['isfinal'] == false && !dc['canceled'] && permissions['updateDocumentStage']){
					html += `
						<button class="btn btn-success updateDocumentStage" 
								style="margin:0; padding: 10px; font-size:1em; display: flex; align-items: center"
								data-id="${dc['id']}">
							<div>
								<span style="font-size:1.3em; display:block">Next Stage</span>
								<span class="nextStageName"> (${dc['spermission']['next']}) </span>
							</div>
							<span style="flex:1; font-size:1.4em">
								<i class="fa fa-arrow-right"></i>
							</span>
						</button>
					`;
				}

				html += `</div><div class="when-is-draft">`;

				if(dc['spermission']['discardable'] == true && !dc['canceled'] && permissions['discardDocument']){
					html += `
						<button class="btn btn-danger discardDocument documentAction" 
								style="margin:0; padding:10px; font-size:2em"
								data-status="Discard">
							<i class="fa fa-trash"></i>
							Discard
						</button>
					`;
				}
					
				if(!dc['published'] && !dc['canceled'] && permissions['publishDocument']){
					html += `
						<button class="btn btn-info documentAction" 
								style="margin:0; padding:10px; font-size:2em"
								data-status="Publish">
							<i class="fa fa-check"></i>
							Publish
						</button>
					`;
				}		
				
				html += `</div></div>`;

				$(".document-actions").html(html);
			}

			function addOpenDocumentDetails(dc){
				let timelineData = dc['timeline'];
				let timelineHtml = "";

				for(point in timelineData){
					timelineHtml += `<div>
										<span class="name">${timelineData[point]['date']}</span>
										${timelineData[point]['stage']}
									</div>`;
				}

				let html = `
						<h3>
							Basic Details
							<button class="btn btn-info btn-sm printSelectedDocument" data-id="${dc['id']}">
                				<i class="fa fa-print"></i>
                			</button>
						</h3>
						<div class="col-md-12">
							<div style="display:flex; justify-content: space-between;">
								<span class="detailHeading">Document#</span>
								<span>${dc['id']}</span>
							</div>
							<div style="display:flex; justify-content: space-between;">
								<span class="detailHeading">Supplier</span>
								<span>${dc['supplier']}</span>
							</div>
							<div style="display:flex; justify-content: space-between;">
								<span class="detailHeading">Stage</span>
								<span class="currentStage">${dc['stage']}</span>
							</div>
						</div>`;

				
				html += `
					<div class="deliveryEstimate">
						ETA: 
						<input  type="date" 
								class="etainput" 
								value="${dc['eta']}" 
								data-oldETA="${dc['eta']}" 
								data-id="${dc['id']}" 
								${(!dc['canceled']) ? "":"disabled"}/> 
					</div>
				`;

				html +=	`<div class="detailsTimeLine">
							${timelineHtml}
						</div>
						<div class="canceled hidden">
							Canceled
						</div>
				`;
				$(".basic-document-details").html(html);
			}
			$(document.body).on("click", ".addnewitemcls", function(){

				if(openDocument == null)	return;

				let ref = $(this);
				let stockid  = ref.attr("data-stockid");
				let id = openDocument['id'];

				ref.prop("disabled",true);

				$.post("api/addItemToProcurementDocument.php", {FormID, stockid, id}, (res, status) => {
					try{
						res = JSON.parse(res);

						if(res.status == "success"){
							generateNotification("success","Success","Item Was Added Successfully.");
							addItemToDocument(res.data);
							searchTable
								.row(ref.parents('tr'))
						        .remove()
						        .draw();
						}else{
							generateNotification("error","Error",res.message);
						}
					}catch(e){
						generateNotification("error","Error","Refresh Page. Session Expired.");
					}
				}); 
			});
			function generateNotification(type, title, message){
				new PNotify({
						title: title,
						text: message,
						addclass: `notification-${type} stack-topleft`,
						width: "300px",
						delay: 1000,
						type: type
					});
			}
			function addItemToDocument(item){
				documentTable.row.add(item).draw();
			}
			$(document.body).on("click", ".removeItemFromDocument", function(){

				if(openDocument == null){
					generateNotification("error","Alert","No Open Document Found.");
					return;
				}

				if(!openDocument['spermission']['removeItem']){
					generateNotification("error","Alert","Item Cannot Be Removed At This Stage.");
					return;
				}

				let ref = $(this);
				let id  = openDocument['id'];
				let stockid = ref.attr("data-stockid");

				swal({
  					title: "Are you sure?",
  					text: "Are you sure you want to remove this item from document!",
  					type: "warning",
  					showCancelButton: true,
  					confirmButtonColor: "#DD6B55",
  					confirmButtonText: "Yes!",
  					cancelButtonText: "Cancel!",
  					closeOnConfirm: false,
  					closeOnCancel: false
				}, function(isConfirm){
  					if (isConfirm) {
    					swal.disableButtons();
  						$.post("api/removeItemFromProcurementDocument.php", {FormID,id, stockid}, (res, status) => {
  							res = JSON.parse(res);

  							if(res.status == "success"){
  								ref.parents('tr').remove();

  								documentTable
							        .row( ref.parents('tr') )
							        .remove();

  								if(documentTable.page.info().page == 0){
  									documentTable.draw();
  								}

  								generateNotification("success","Item Deleted",res.message);

  							}else{
  								generateNotification("error","Error",res.message);
  							}
  							
  							swal.enableButtons();
    						swal.close();
  						});
  					} else {
    					swal("Cancelled", "Operation was canceled.", "error");
  					}
				});
			});
			$(document.body).on("change", ".dataTableInput", function(){

				if(openDocument == null){
  					generateNotification("error","Error","No Open Document Found.");
					return;
				}

				let ref 	= $(this);
				let id  	= openDocument['id'];
				let stockid = ref.attr("data-stockid");
				let oldval  = ref.attr("data-oldval");
				let name    = ref.attr("data-name");
				let value   = ref.val();

				let tr = ref.parents('tr');

				if((name == "client_required" || name == "safety_inventory" || name == "stock")){
					tr = tr.prev();
				}

				let data = documentTable.row(tr).data();

				if((name == "client_required" || name == "safety_inventory" || name == "stock") && (!openDocument['spermission']['qtychange'] || !permissions['canUpdateItemQuantity'])){
					if(!permissions['canUpdateItemQuantity']){
  						generateNotification("error","Error","User does not have permission to update quantity.");
					}else{
						generateNotification("warning","Error","Quantity Cannot be changed at this stage.");
  					}
  					ref.val(oldval);
  					return;
				}

				if((name == "client_required" || name == "safety_inventory" || name == "stock")){
					if(parseInt(value) < 0){
						generateNotification("warning","Error","Quantity Cannot less the zero.");
						return;
					}
				}

				if(name == "price" && (!openDocument['spermission']['pricechange'] || !permissions['canUpdateItemPrice'])) {
					if(!permissions['canUpdateItemPrice']){
						generateNotification("error","Error","User does not have permission to update Price.");
					}else{
						generateNotification("warning","Error","Price Cannot be changed at this stage.");
					}
					ref.val(oldval);
					return;
				}

				ref.prop("disabled", true);

				$.post("api/updateProcurementDocumentItem.php", {FormID, id, stockid, name, value}, (res, status) => {
					try{
						res = JSON.parse(res);
						if(res.status == "success"){
							ref.css("border","2px solid green");
							generateNotification("success",name+" updated",name+" updated successfully.");
							ref.attr("data-oldval",ref.val());
							data[name] = value;
							documentTable
						        .row(tr)
						        .data(data)
						        .invalidate()
						        .draw();
						}else{
							ref.css("border","2px solid red");
							ref.val(oldval);
							generateNotification("error","Error","Update Failed.");	
						}
					}catch(e){
						generateNotification("warning","Error","Session Expired.");	
					}finally{
						ref.prop("disabled", false);
					}
				});
			});
			$(document.body).on("click", ".documentAction", function(){

				if(openDocument == null){
					generateNotification("error","Error","No Open Document Found.");
					return;
				}
				let ref = $(this);
				let id  = openDocument['id']; 
				let status = ref.attr("data-status");


				if($(this).hasClass("cancelDocument")){
					swal({
	  					title: "Are you sure?",
	  					text: `Are you sure you want to cancel this document!`,
	  					type: "input",
	  					showCancelButton: true,
	  					confirmButtonColor: "#DD6B55",
	  					confirmButtonText: "Yes!",
	  					cancelButtonText: "Cancel!",
	  					closeOnConfirm: false,
	  					closeOnCancel: true,
	  					inputPlaceholder: "Reason"
					}, function(inputVal){

						if(inputVal.trim() == ""){
							swal.close();
							return;
						}

						let reason = inputVal.trim();

						swal.disableButtons();
						ref.prop("disabled",true);
						$.post("api/updateProcurementDocumentStatus.php", {FormID,id,status,reason}, (res, statuss) => {
							try{
								res = JSON.parse(res);
								if(res.status == "success"){
									openDocument['canceled']  = "true";
									openDocument['spermission'] = res.permissions;
									$(document.body).find(".canceled").removeClass("hidden");
									$(document.body).find(".etainput").prop("disabled",true);
									$(document.body).find(".dataTableInput").prop("disabled",true);
									$(document.body).find(".removeItemFromDocument").addClass("hidden");
									
									updateDocumentActions(openDocument);
									generateNotification("success","Success","Document Status updated successfully.");
									swal.close();
								}else{
									generateNotification("error","Error",res.message);
								}
							}catch(e){
								generateNotification("warning","Error","Session Expired.")
							}finally{
								ref.prop("disabled",false);
								swal.enableButtons();
							}
						});
					});
					return;
				}

				swal({
  					title: "Are you sure?",
  					text: `Are you sure you want to ${status} this document!`,
  					type: "warning",
  					showCancelButton: true,
  					confirmButtonColor: "#DD6B55",
  					confirmButtonText: "Yes!",
  					cancelButtonText: "Cancel!",
  					closeOnConfirm: false,
  					closeOnCancel: true
				}, function(isConfirm){

					if(isConfirm){
						swal.disableButtons();
						ref.prop("disabled",true);
						$.post("api/updateProcurementDocumentStatus.php", {FormID,id,status}, (res, statuss) => {
							try{
								res = JSON.parse(res);
								if(res.status == "success"){
									if(status == "Discard"){
										closeOpenDocumentProcess();
										generateNotification("success","Success","Document was discarded!");
										swal.close();
										swal.enableButtons();
										return;
									}
									if(status == "Publish"){
										openDocument['published'] = "true";
									}else{
										openDocument['canceled']  = "true";
										openDocument['spermission'] = res.permissions;
										$(document.body).find(".canceled").removeClass("hidden");
										$(document.body).find(".etainput").prop("disabled",true);
										$(document.body).find(".dataTableInput").prop("disabled",true);
										$(document.body).find(".removeItemFromDocument").addClass("hidden");
									}
									updateDocumentActions(openDocument);
									generateNotification("success","Success","Document Status updated successfully.");
									swal.close();
									swal.enableButtons();
								}else{
									generateNotification("error","Error",res.message);
									swal.enableButtons();
								}
							}catch(e){
								generateNotification("warning","Error","Session Expired.")
							}finally{
								ref.prop("disabled",false);
							}
						});

					}else{
						swal.close();
					}

				});
			});
			$(document.body).on("click", ".printSelectedDocument", function(){
				let ref = $(this);
				let id  = ref.attr("data-id");
				
				ref.prop("disabled",true);
				$.get("api/getPrintRender.php?document="+id, function(res, status){
					$(".printArea").html(res);
					print();
					ref.prop("disabled",false);
				});
			});
			$(document.body).on("change",".etainput",function(){
				let ref = $(this);
				let id = ref.attr("data-id");
				let eta = ref.val();
				let oldETA = ref.attr("data-oldETA");

				ref.prop("disabled",true);
				$.post("api/updateProcurementDocumentETA.php",{id,eta,FormID},(res,status) => {
					res = JSON.parse(res);

					ref.prop("disabled",false);

					if(res.status == "success"){
						ref.css("border","2px solid green");
						ref.attr("data-oldETA",eta);
					}else{
						ref.css("border","2px solid red");
						ref.val(oldETA);
						generateNotification("error","error",res.message);
					}

					setTimeout(function(){
						ref.css("border","1px solid #424242");
					}, 2000);

				});
			});
		</script>

	</body>
</html>