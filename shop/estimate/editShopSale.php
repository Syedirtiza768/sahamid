<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	
	if(!userHasPermission($db, 'shop_sale_internal_view')){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}

	$orderNo = $_GET['orderno'];

	if(!isset($orderNo) || $orderNo == ""){
		header("Location: shopSaleList.php");
		exit;
	}

	$SQL = "SELECT * FROM shopsale WHERE orderno=$orderNo AND complete=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		header("Location: shopSaleList.php");
		exit;
	}

	$shopSale = mysqli_fetch_assoc($res);

	$debtorno = $shopSale['debtorno'];

	$SQL = "SELECT * FROM shopsalelines WHERE orderno=$orderNo";
	$lines = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM debtorsmaster WHERE debtorno='$debtorno'";
	$res = mysqli_query($db, $SQL);

	$client = mysqli_fetch_assoc($res);

	if($shopSale['payment'] == "csv"){
		$client['name'] = $shopSale['crname'];
	}

	$SQL = "SELECT SUM(quantity * price) as total FROM shopsalelines WHERE orderno='$orderNo'";
	$res = mysqli_query($db, $SQL);

	$subTotal = mysqli_fetch_assoc($res)['total'];

	$discount = $shopSale['discount'];
	$discountPKR = $shopSale['discountPKR'];

	$total = $subTotal;

	if($discount != 0){
		$total = $total * (1 - $discount/100);
	}

	if($discountPKR != 0){
		$total -= $discountPKR;
	}

	$advance = $shopSale['advance'];

	$remaining =  $total - $advance;


	$SQL = "SELECT * FROM shopsalecomments WHERE orderno=$orderNo";
	$comments = mysqli_query($db, $SQL);
	
	$canUpdateQuantity 		= userHasPermission($db, 'update_quantity_shopsale');
	$canAttachInternalItems = userHasPermission($db, 'internal_items_shopsale');
	$canDeleteInternal 		= userHasPermission($db, 'delete_internal_item_shopsale');
	$canDeleteLine	   		= userHasPermission($db, 'delete_shopsale_line');
	$canUpdateDescription 	= userHasPermission($db, 'update_shopsale_line_desc');
	$changeLineQuantity 	= userHasPermission($db, 'change_shopsale_line_qty');
	$canUpdateDiscount 		= userHasPermission($db, 'update_shopsale_discount');
	$canUpdateLinePrice 	= userHasPermission($db, 'update_shopsale_line_price');

?>

<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../parchi/inward/assets/searchSelect.css" />

		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style type="text/css">
			.right-side,.tabs-area{border-left:1px solid #424242;display:flex}.details,.main-container,.right-side,.tabs-area{display:flex}.items-container,.main-items-view,.search-container{overflow-y:scroll}.right-side{width:300px;//height:calc(100vh);background:#ccc;order:3;flex-direction:column;zoom:.8}.tabs-area,.tabs-area span{height:calc(100vh - 143px)}.left-side{flex:1;order:1;zoom:.8}.tabs-area{order:2;width:40px;flex-direction:column}.tabs-area span{writing-mode:vertical-rl;font-variant-caps:petite-caps;font-size:20px}.basic-details{min-height:210px;border-bottom:1px solid #424242;background-color:#424242;padding:10px;color:#d4d3d3;box-sizing:border-box}.btn-type1,.btn-type2,.btn-type2:hover{color:#fff}.comments-area{flex:2;display:flex;flex-direction:column}.details{margin:0;justify-content:space-between}.details div{display:inline-block}.comments-container{flex:1;overflow-y:scroll; height: 300px;}.add-comment{height:50px;display:flex}.add-comment input{flex:1;border:1px solid #424242;border-radius:8px}.add-comment button{width:50px;height:50px}.btn-type1{background-color:#C5BD99}.btn-type2{background-color:#701112}.items-container{height:calc(100vh);width:100%}.files-container{height:calc(100vh - 143px);width:100%;background-color:green;display:none}#items-table{width:100%}#attachInternal,.sr{width:1%}#items-table thead{background:#424242;color:#fff}#items-table td,#items-table th{padding:5px}.sr{text-align:center;white-space:nowrap}.search-container{display:none;height:calc(100vh);width:100%}.itemsearchterm,.searchitembtn{width:300px;padding:4px;display:block}.itemsearchterm{border:1px solid #424242;border-radius:7px;margin:auto}.searchitembtn{border:1px solid #424242;border-radius:7px;margin:4px auto}.comment,.comment-input{padding:5px}.dataTables_wrapper .dataTables_filter label{width:100%!important}.internalItemsContainer{display:flex;width:100%;justify-content:space-around;flex-wrap:wrap}.internalItem{width:100%;border:1px solid #424242;border-radius:8px;padding:5px;position:relative;margin:5px}.comment,.itemQuantity{border:1px solid #424242}.internalItemTable{width:100%;margin-left:40px;background:#fff}.descriptionInternal{word-break:break-all}.itemQuantity{border-radius:7px}.description{width:450px}.comment-box{width:100%}.comment{margin:5px;background:#fff;border-radius:7px}.comment .username{font-weight:bolder;display:block}.lineDescriptionInternal{width:100%;border:1px solid #424242;border-radius:7px}.lineQTY, .linePrice{width:80px; border:1px solid #ccc; border-radius: 7px;}.discountInput{border: 1px solid #424242; padding:2px; border-radius: 6px; width: 60px; color: black}
		</style>

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
	      				<a href="<?php echo $RootPath; ?>/../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

	      	<h3 style="text-align: center; font-variant-caps: petite-caps;">
	      		<i class="fa fa-sign-in" aria-hidden="true"></i> 
	      		Attach Internal Items (CSV/CRV) 
	      		<a href="shopSalePrint.php?orderno=<?php echo $_GET['orderno']?>" target="_blank" class="btn btn-success">
	      			<span class="glyphicon glyphicon-print" aria-hidden="true"></span>
	      		</a>
	      		<a href="shopSalePrintInternal.php?orderno=<?php echo $_GET['orderno']?>" target="_blank" class="btn btn-warning">
	      			<span class="glyphicon glyphicon-print" aria-hidden="true"></span>
	      		</a>
	      	</h3>

	      	<div class="col-md-12" style="margin-bottom: 15px; border: 2px solid #424242; padding: 0;">
	      		<div class="main-container">
	      			<div class="right-side">
						<?php if(userHasPermission($db, 'finalize_shopsale')){ ?>
	      				<div class="finalize">
	      					<button class="btn btn-success finalizeShopSale" style="width: 100%">Finalize</button>
	      				</div>
						<?php } ?>
	      				<div class="basic-details">
	      					<p class="details">
	      						<span>Date : </span>
	      						<span><?php echo date('d/m/Y',strtotime($shopSale['orddate'])); ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Salesman : </span>
	      						<span><?php echo $shopSale['salesman']; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Created By : </span>
	      						<span><?php echo $shopSale['created_by']; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Client : </span>
	      						<span><?php echo $client['name']; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Payment : </span>
	      						<span><?php echo $shopSale['payment']; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Sub Total : </span>
	      						<span><?php echo locale_number_format($subTotal,2); ?><sub>PKR<sub></span>
                                <span class="overallSubtotal" hidden><?php echo $subTotal; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Discount : </span>
	      						<?php if($canUpdateDiscount){ ?>
	      						<span>
	      							<input type="number" 
	      									class="discountInput" 
	      									data-name="discount" 
	      									value="<?php echo ($discount); ?>"><sub>%<sub>
	      						</span>
	      						<?php } else { ?>
	      						<span><?php echo locale_number_format($discount,2); ?><sub>%<sub></span>
	      						<?php } ?>
	      					</p>
	      					<p class="details">
	      						<span>Discount PKR : </span>
	      						<?php if($canUpdateDiscount){ ?>
	      						<span>
	      							<input type="number" 
	      									class="discountInput" 
	      									data-name="discountPKR" 
	      									value="<?php echo ($discountPKR); ?>"><sub>PKR<sub>
	      						</span>
	      						<?php } else { ?>
	      						<span><?php echo locale_number_format($discountPKR,2); ?><sub>PKR<sub></span>
	      						<?php } ?>
	      					</p>
	      					<p class="details">
	      						<span>Total : </span>
	      						<span><?php echo locale_number_format($total,2); ?><sub>PKR<sub>
                                            <span class="overallTotal" hidden><?php echo $total; ?></span>
	      					</p>
	      					<p class="details">
	      						<span>Advance : </span>
	      						<span><?php echo locale_number_format($advance,2); ?><sub>PKR<sub></span>
	      					</p>
	      					<p class="details">
	      						<span>Remaining : </span>
	      						<span><?php echo locale_number_format($remaining,2); ?><sub>PKR<sub></span>
	      					</p>
	      				</div>
	      				<div class="comments-area">
	      					<div class="comments-container">
	      						<div class="comments-box">
	      							<?php while($row = mysqli_fetch_assoc($comments)){ ?>
	      							<div class="comment">
	      								<span class="username">
	      									<?php echo $row['username'].
	      											" (".date('d/m/Y h:i A',
	      														strtotime($row['created_at'])).")"; ?>
	      								</span>
	      								<?php echo $row['comment']; ?>
	      							</div>
	      							<?php } ?>
	      						</div>
	      					</div>
	      					<div class="add-comment">
	      						<input type="text" class="comment-input" placeholder="Enter Comment Here">
	      						<button class="btn btn-success addNewComment"><i class="fa fa-send"></i></button>
	      					</div>
	      				</div>
	      			</div>
	      			<div class="tabs-area">
	      				<span class="btn btn-type1 open-items-container">Items</span>
	      				<span class="btn btn-type2 open-files-container" style="display: none">Files</span>
	      			</div>
	      			<div class="left-side">
	      				<div class="items-container">
	      					<div class="main-items-view">
	      						<input type="hidden" id="selecteditemindex">
	      						<table id="items-table" border="1" class="table-striped">
	      							<thead>
	      								<tr>
	      									<th class="sr">SR#</th>
	      									<th>Name</th>
	      									<th>Notes</th>
	      									<th class="sr">Quantity</th>
	      									<th class="sr">Price</th>
	      									<th class="sr">SubTotal</th>
	      									<th style="width: 1%">Attach</th>
	      								</tr>
	      							</thead>
	      							<tbody>
	      							<?php $sr=1; while($row = mysqli_fetch_assoc($lines)){ ?>
	      								<tr data-lineId="<?php echo $row['id']; ?>">
	      									<td class="sr"><?php echo $sr; ?></td>
	      									<td class="description descriptionInternal">
	      										<?php 
	      											if(!$canUpdateDescription){ 
	      												echo html_entity_decode($row['description']); 
	      										 	} else { 
	      										 		echo "<textarea class='lineDescriptionInternal'>".html_entity_decode($row['description'])."</textarea>";
	      										 	} 
	      										 ?>
	      									</td>
	      									<td class="notes descriptionInternal">
	      										<?php echo html_entity_decode($row['notes']); ?>
	      									</td>
	      									<td class="sr">
	      										<?php if($changeLineQuantity){ ?>
													<input type="text" class="lineQTY" value="<?php echo $row['quantity']; ?>">
	      										<?php }else{ 
	      											echo $row['quantity']; 
	      										} ?>
	      										<sub><?php echo $row['uom']; ?></sub>	
	      									</td>
	      									<td class="sr">
	      										<?php if($canUpdateLinePrice){ ?>
	      											<input type="number" class="linePrice" value="<?php echo $row['price']; ?>">
	      										<?php }else{ 
	      											echo '<input type="hidden" class="linePrice" value="'.$row['price'].'">';
	      											echo $row['price']; 
	      										} ?>
	      										<sub>PKR</sub>	
	      									</td>
	      									<td class="sr">
	      										<span class="lineSubTotal">
	      											<?php echo locale_number_format($row['price']*$row['quantity'],2); ?>

	      										</span>
                                                <sub>PKR</sub>
                                                <span class="lineSubTotalHidden" style="visibility: hidden;">
                                                <?php echo $row['price']*$row['quantity']; ?>
                                                </span>

	      									</td>
	      									<td>
												<?php if($canAttachInternalItems){ ?>
												<button class="btn btn-success attachInternal"><i class="fa fa-plus"></i></button>
												<?php } ?>
												<?php if($canDeleteLine){ ?>
												<button class="btn btn-danger deleteLine"><i class="fa fa-times"></i></button>
												<?php } ?>
											</td>
	      								</tr>
	      								<tr class="internalItemsContainerTop internal-<?php echo $row['id']; ?>" >
	      									<td colspan="7" >
		      									<div class="internalItemsContainer">
			      									<?php 
			      										$SQL = "SELECT manufacturers.manufacturers_name as mnfname,
																		stockmaster.stockid,
																		stockmaster.description,
																		stockmaster.units,
																		stockmaster.materialcost as price,
																		stockmaster.mnfcode,
																		stockmaster.mnfpno,
																		shopsalesitems.id,
																		shopsalesitems.quantity,
																		shopsalesitems.discountpercent
			      												FROM shopsalesitems
			      												INNER JOIN stockmaster ON shopsalesitems.stockid = stockmaster.stockid
																INNER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
			      												WHERE lineno='".$row['id']."'"; 
			      									?>
			      									<?php $res = mysqli_query($db, $SQL); ?>
		      										<table class="internalItemTable" border="1">
															<thead style="background-color: #C5BD99; color: black">
																<tr>
																	<th>StockID</th>
																	<th>MNFCode</th>
																	<th>MNFPNo</th>
																	<th>Description</th>
																	<th>UOM</th>
                                                                    <th>Price<span class="manualPrice"></span>Price<span class="adjPrice"></span></th>
                                                                    <th>Discount<input type="hidden" class="manualDiscountLine" value="<?php echo $row['id']; ?>">
                                                                        <button data-discountlineid="<?php echo $row['id']; ?>" class="btn btn-success manualDiscount" style="width: 50%">Manual</button></th>
																	<th>Quantity</th>
																	<th></th>
																</tr>
															</thead>
		      												<tbody class="internal-cont-<?php echo $row['id']; ?>">
					      									<?php while($item = mysqli_fetch_assoc($res)){ ?>	
																<tr id="item-<?php echo $item['id']; ?>" class="internalItem">
																	<td class="sr"><?php echo $item['stockid']; ?></td>
																	<td class="sr"><?php echo $item['mnfcode']; ?></td>
																	<td class="sr"><?php echo $item['mnfpno']; ?></td>
																	<td class="descriptionInternal"><?php echo $item['description']; ?></td>
																	<td class="sr"><?php echo $item['units']; ?></td>
																	<td class="sr">
                                                                        <input  type="text"
                                                                                class="internalPrice"
                                                                                value="<?php echo $item['price']; ?>"
                                                                                data-id="<?php echo $item['id']; ?>"

                                                                                disabled>

                                                                    </td>
                                                                    <td class="sr"><input  type="text"
                                                                                                 class="internalDiscount"
                                                                                                 value="<?php echo $item['discountpercent']; ?>"
                                                                                                 data-externallineiddisc="<?php echo $row['id']; ?>"
                                                                                                 data-id="<?php echo $item['id']; ?>" disabled>
                                                                    </td>
																	<td class="sr">
																		<?php if($canUpdateQuantity){ ?>
																		<input  type="number" 
																				class="itemQuantity" 
																				value="<?php echo $item['quantity']; ?>"
                                                                                data-externallineid="<?php echo $row['id']; ?>"
																				data-id="<?php echo $item['id']; ?>">

																		<?php }else{ ?>
																		<input  type="number" 
																				class="itemQuantity" 
																				value="<?php echo $item['quantity']; ?>"
                                                                                data-externallineid="<?php echo $row['id']; ?>"
																				data-id="<?php echo $item['id']; ?>" disabled>
																		<?php } ?>
																	</td>
																	<td class="sr">
																		<?php if($canDeleteInternal){ ?>
																		<button class="btn btn-danger deleteInternalItem" 
																				style="float: right;"
                                                                                data-externallineid="<?php echo $row['id']; ?>"
																				data-id="<?php echo $item['id']; ?>">
																			X
																		</button>
																		<?php } ?>
																	</td>
																</tr>
					      									<?php } ?>
					      									</tbody>
		      										</table>
		      									</div>
	      									</td>
	      								</tr>
	      							<?php $sr++; }?>
                                    <script>var numLines=<?php echo $sr-1; ?>;</script>
	      							</tbody>
	      						</table>
	      					</div>
	      				</div>
	      				<div class="files-container">
	      					
	      				</div>
	      				<div class="search-container">
	      					<div class="searchbody">
	      						<table style="width: 100%; margin-top: 0px;">
	      							<tr style="border-bottom: 1px solid #424242; ">
	      								<td style="text-align: right; padding: 15px;">
	      									<button class="btn btn-danger closeoverlayitem">X</button>
	      								</td>
	      							</tr>
	      							<tr style="border-bottom: 1px solid #424242; ">
	      								<td>
	      									<div class="col-md-6" style="border-right: 1px solid black">
	      										<h5>Name : </h5>
	      										<span class="lineNameHere"></span>
	      									</div>
	      									<div class="col-md-6" style="border-left: 1px solid black">
	      										<h5>Notes : </h5>
	      										<span class="lineNotesHere"></span>
	      									</div>
	      								</td>
	      							</tr>
	      							<tr>
	      								<td>
	      									<div class="col-md-12">
	      										<h5 style="display: block; text-align: center;">Stockid/Desc</h5>
	      										<input type="text" class="itemsearchterm" placeholder="Search Item">
	      										<button class="btn btn-success searchitembtn">Search</button>
	      									</div>
	      								</td>
	      							</tr>
	      							<tr>
	      								<td style="border-bottom: 0">
	      									<div class="col-md-12" style="padding-top: 10px;">
	      										<table id="searchresultsdatatab" width="100%" class="responsive table table-striped">
													<thead>
														<tr style="background:#424242; color:white">
															<th>Item Code</th>
															<th>Model #</th>
															<th>Part #</th>
															<th>QOH</th>
															<th>Manufacturer</th>
															<th>Description</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody id="srb" style="color: black">
														
													</tbody>
													<tfoot>
														<tr style="background:#424242; color:white">
															<th>Item Code</th>
															<th>Model #</th>
															<th>Part #</th>
															<th>QOH</th>
															<th>Manufacturer</th>
															<th>Description</th>
															<th>Action</th>
														</tr>
													</tfoot>
												</table>
	      									</div>
	      								</td>
	      							</tr>
	      						</table>
	      					</div>
	      				</div>
	      			</div>
	      		</div>
	      	</div>
	      	
			<div style="clear: both;"></div>

	      	<footer style="background:#424242; bottom:0; width:100%; text-align:center; padding: 5px">
      			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
      		</footer>
	      	
		</section>
      	
		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../../quotation/assets/javascripts/theme.js"></script>
		<script src="../parchi/inward/assets/searchSelect.js"></script>

		<script>

			var table;
			
			$(document).ready(function(){

				table = $('#searchresultsdatatab').DataTable({
					"columns": [
			            { "data": "stockid" },
			            { "data": "mnfcode" },
			            { "data": "mnfpno" },
			            { "data": "qoh" },
			            { "data": "mname" },
			            { "data": "description" },
			            { "data": "action" },
		        	]
				});

				$('#searchresultsdatatab tfoot th').each( function (i) {
			        var title = $('#searchresultsdatatab thead th').eq( $(this).index() ).text();
			        if($(this).html() != "Action"){
			        	$(this).html( '<input type="text" style="width:100%; padding:4px; text-align:center; color:black; border:1px solid #424242; border-radius:7px;" placeholder="Search '+title+'" data-index="'+i+'" />' );
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

				$(".open-items-container").on("click",function(){
					$(".items-container").css("display","block");
					$(".files-container").css("display","none");
					$(".search-container").css("display","none");
				});
                $(".manualDiscount").on("click",function(){
                    let externalLineID=$(this).parent().find(".manualDiscountLine").val();
                    let internalAmount=0;
                    $(this).parent().parent().parent().parent().find(".internalPrice").each(function(){
                        internalAmount += +$(this).val()*$(this).parent().parent().find(".itemQuantity").val()*
                            (1-$(this).parent().parent().find(".internalDiscount").val()/100);
                    });

                    console.log(externalLineID);
                    console.log(internalAmount);
                    let str="[data-externallineiddisc="+externalLineID+"]";
                    $(str).prop("disabled",false);
                    $(this).parent().parent().find('.manualPrice').html(" "+Math.round(internalAmount));
                    $(this).parent().parent().find('.adjPrice').html(" "+Math.round(internalAmount));

                });

				$(".open-files-container").on("click",function(){
					$(".items-container").css("display","none");
					$(".files-container").css("display","block");
					$(".search-container").css("display","none");
				});

				$(".attachInternal").on("click",function(){

                    $(".linePrice").prop("disabled",true);
                    $(".discountInput").prop("disabled",true);
					$(".items-container").css("display","none");
					$(".search-container").css("display","block");

					$("#selecteditemindex").val($(this).parent().parent().attr("data-lineId"));

					$(".lineNameHere").html($(this).parent().parent().find(".description").html());
					$(".lineNotesHere").html($(this).parent().parent().find(".notes").html());
				});

				$(".searchitembtn").on("click", function(){
				
					let term = $(".itemsearchterm").val();
					let itemIndex = $("#selecteditemindex").val();
					let orderno = '<?php echo $_GET['orderno']; ?>';

					$.post("api/itemSearch.php", {
						FormID: '<?php echo $_SESSION['FormID']; ?>',
						term: term,
						itemIndex: itemIndex,
						orderno:orderno
					}, function(res, status, something){
						res = JSON.parse(res);
						table.clear().draw();	
			    		table.rows.add(res.data).draw();
					});

				});

				$(".closeoverlayitem").on("click", function(){
					$(".search-container").css("display","none");
					$(".items-container").css("display","block");
					table.clear().draw();
				});

			});

			function attachSKU(line,stockid){

				swal({
		  			title: "Attach "+stockid,
			  		text: "Are you sure you want to attach this?",
			   		type: "info",
			   		showCancelButton: true,
			  		closeOnConfirm: false,
		  			showLoaderOnConfirm: true,
				}, function(){
		  			
					$.post("api/attachOrignalSKU.php",{
						FormID: '<?php echo $_SESSION['FormID']; ?>',
						line,
						stockid
					},function(res, status, something){

						res = JSON.parse(res);

						if(res.status == "success"){
							let item = res.item;
							addInternalItem(item.id,item.line,item.stockid,item.mnfcode,item.mnfpno,item.description,item.units,item.price,0);
							swal("Success","Item attached successfully.","success");
						}else{
							swal("Error",res.message,"error");
						}

					});

				});

			}

			function addInternalItem(id,line,stockid,mnfcode,mnfpno,description,units,price,discount){
                let externallineid=$("#selecteditemindex").val();
				html = `
					<tr id="item-${id}" class="internalItem">
						<td class="sr">${stockid}</td>
						<td class="sr">${mnfcode}</td>
						<td class="sr">${mnfpno}</td>
						<td class="descriptionInternal">${description}</td>
						<td class="sr">${units}</td>
						<td class="sr">
                        <input  type="text"
                         class="internalPrice"
                         value="${price}"
                         data-id="${id}" disabled>
                        </td>
                        <td class="sr">
                        <input  type="text"
                         class="internalDiscount"
                         value="${discount}"
                         data-externallineiddisc="${externallineid}"
                         data-id="${id}" disabled>

                        </td>
						<td class="sr">
							<input  type="number" 
									class="itemQuantity" 
									value="0"

                                    data-externallineid="${externallineid}"
									data-id="${id}">
						</td>
						<td class="sr">
							<button class="btn btn-danger deleteInternalItem" 
									style="float: right;"
                                     data-externallineid="${externallineid}"
									data-id="${id}">
								X
							</button>
						</td>
					</tr>
				`;

				$(".internal-cont-"+line).append(html);

			}

			$(document.body).on("click",".deleteInternalItem",function(){

				let ref = $(this);
				let itemID = ref.attr("data-id");
                let externalLineID = ref.attr("data-externallineid");
                let quantity = ref.val();

                $(this).parent().parent().find(".itemQuantity").val(0);
                let internalAmount=0;
                $(this).parent().parent().parent().find(".internalPrice").each(function(){
                    internalAmount += +$(this).val()*$(this).parent().parent().find(".itemQuantity").val();
                });
                let str="[data-lineid="+externalLineID+"]";
                console.log($(str).find(".linePrice").val());
                $(str).find(".linePrice").prop("disabled",false);

                let discountPerLine=(parseFloat($(".overallSubtotal").html())-parseFloat($(".overallTotal").html()))/numLines;
                let externalLineTotal= ($(str).find(".linePrice").val()*$(str).find(".lineQTY").val())- discountPerLine;
                internalAmount=internalAmount*$(str).find(".lineQTY").val();
                console.log(internalAmount);
                console.log(externalLineTotal);
                console.log(discountPerLine);
                let discount=0;
                if (internalAmount==0)
                    discount=0;
                else
                    discount=(1-externalLineTotal/internalAmount)*100;

                if (discount === undefined || discount === null)
                    discount=0;
                console.log(discount);
                $(this).parent().parent().parent().find(".internalDiscount").val(discount);
                if(quantity == "")
                    quantity = 0;

                ref.prop("disabled",true);

				$.post("api/removeInternalItem.php",{
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					itemid:itemID,
                    discount,
                    externalLineID
				},function(res, status, something){

					res = JSON.parse(res);

					if(res.status == "success"){
                        $("#item-"+itemID).remove();
						
					}else{
						swal("Error",res.message,"error");
						ref.prop("disabled",false);
					}


				});



			});

			$(document.body).on("change",".itemQuantity",function(){
            $(".itemQuantity").each(function(){
				let ref = $(this);
				let itemID = ref.attr("data-id");
                let externalLineID = ref.attr("data-externallineid");
				let quantity = ref.val();

                let internalAmount=0;
                $(this).parent().parent().parent().find(".internalPrice").each(function(){
                    internalAmount += +$(this).val()*$(this).parent().parent().find(".itemQuantity").val();
                });



                let str="[data-lineid="+externalLineID+"]";
                console.log(parseFloat($(str).find(".lineSubTotalHidden").html()));
                let discountOverall=(parseFloat($(".overallSubtotal").html())-parseFloat($(".overallTotal").html()));
                console.log(discountOverall);
                let externalLineSubTotal= ($(str).find(".linePrice").val()*$(str).find(".lineQTY").val());
                console.log(externalLineSubTotal);
                let externalLineTotal= (externalLineSubTotal)- (discountOverall*externalLineSubTotal/parseFloat($(".overallSubtotal").html()));

                console.log(externalLineTotal);
                internalAmount=internalAmount*$(str).find(".lineQTY").val();
                console.log(internalAmount);
                let discount=(1-externalLineTotal/internalAmount)*100;
                console.log(discount);
                if (discount === undefined || discount === null)
                    discount=0;
                $(this).parent().parent().parent().find(".internalDiscount").val(discount);
               if(quantity == "")
					quantity = 0;

				$.post("api/updateInternalItemPrice.php",{
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					itemid:itemID,
					quantity,
                    discount
				},function(res, status, something){

					res = JSON.parse(res);

					if(res.status == "success"){
						ref.css("border","2px solid green");	
					}else{
						swal("Error",res.message,"error");
					}

				});
            });

			});

			$(document.body).on("change", ".discountInput", function(){
				let orderno = '<?php echo $_GET['orderno']; ?>';
				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let ref = $(this);
				let discount = ref.val();
				let type = ref.attr("data-name");

				ref.prop("disabled", true);

				$.post("api/updateShopSaleDiscount.php",{
					FormID, orderno, type, discount
				}, function(res, status){
					res = JSON.parse(res);
					if(res.status == "success"){
						ref.css("border","2px solid green");
					}else{
						ref.css("border","2px solid red");
					}
					ref.prop("disabled", false);
				});
			});

			$(".addNewComment").on("click", function(){

				let ref = $(this);

				let comment = $(".comment-input").val().trim();
				let orderno = '<?php echo $_GET['orderno']; ?>';

				if(comment == "")
					return;

				ref.prop("disabled", true);

				$.post("api/addShopSaleComment.php",{
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					orderno,
					comment
				}, function(res, status, something){

					res = JSON.parse(res);

					if(res.status == "success"){

						let comment = res.comment;

						let html = `
							<div class="comment">
  								<span class="username">${comment.username} (${comment.time})</span>
  								${comment.comment}
  							</div>
						`;

						$(".comments-box").append(html);

						$(".comment-input").val("");

					}

					ref.prop("disabled",false);

				});

			});

			$(".finalizeShopSale").on("click", function(){

				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let orderno = '<?php echo $_GET['orderno']; ?>';

				swal({
				  title: "Confirm!!",
				  text: "Are you sure you want to save this shop sale?",
				  type: "info",
				  showCancelButton: true,
				  closeOnConfirm: false,
				  showLoaderOnConfirm: true,
				},
				function(){
				  
					$.post("api/finalizeShopSale.php",{FormID, orderno},function(res, status, something){

						res = JSON.parse(res);
						console.log(res);
						if(res.status == "success"){
							window.location.href = "shopSaleList.php";
						}else{
							swal("Error",res.message,"error");
						}

					});

				});

			});

			$(".lineDescriptionInternal").on("change", function(){
				
				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let orderno = '<?php echo $_GET['orderno']; ?>';
				let line = $(this).closest("tr").attr("data-lineId");
				let description = $(this).val();
				let ref = $(this);
				
				$.post("api/updateShopSaleLineDescription.php",{
					FormID,orderno,line,description
				}, function(res, status){
					res = JSON.parse(res);
					if(res.status == "success"){
						ref.css("border","2px solid green");
					}else{
						ref.css("border","2px solid red");
					}
				});

			});

			$(".lineQTY").on("change", function(){
				
				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let orderno = '<?php echo $_GET['orderno']; ?>';
				let line = $(this).closest("tr").attr("data-lineId");
				let quantity = $(this).val();
				let ref = $(this);
                /*let internalAmount=0;
                let str="[data-externallineiddisc="+line+"]";
                $(str).each(function(){
                    internalAmount += +$(this).parent().parent().find(".internalPrice").val()*$(this).parent().parent().find(".itemQuantity").val();
                });
                str="[data-lineid="+line+"]";

                let discountPerLine=(parseFloat($(".overallSubtotal").html())-parseFloat($(".overallTotal").html()))/numLines;
                let externalLineTotal= ($(this).val()*$(str).find(".linePrice").val())- discountPerLine;

                internalAmount=internalAmount*$(str).find(".linePrice").val();

                let discount=(1-externalLineTotal/internalAmount)*100;

                console.log(discount);
                str="[data-externallineiddisc="+line+"]";
                $(str).val(discount);
*/
                $.post("api/updateShopSaleLineQTY.php",{
					FormID,orderno,line,quantity
				}, function(res, status){
					res = JSON.parse(res);
					if(res.status == "success"){
						ref.css("border","2px solid green");
						let price = ref.closest("tr").find(".linePrice").val();
						ref.closest("tr").find(".lineSubTotal").html(price*quantity);
                        ref.closest("tr").find(".lineSubTotalHidden").html(price*quantity);
					}else{
						ref.css("border","2px solid red");
					}
				});

			});
            $(".internalDiscount").on("change", function(){


                let externalLineID=$(this).parent().parent().parent().parent().find(".manualDiscountLine").val();
                let internalAmount=0;
                $(this).parent().parent().parent().find(".internalPrice").each(function(){
                    internalAmount += +$(this).val()*$(this).parent().parent().find(".itemQuantity").val()*
                        (1-$(this).parent().parent().find(".internalDiscount").val()/100);
                });

                console.log(externalLineID);
                console.log(internalAmount);
                let str="[data-externallineiddisc="+externalLineID+"]";
                $(str).prop("disabled",false);

                $(this).parent().parent().parent().parent().find('.adjPrice').html(" "+Math.round(internalAmount));
                console.log("changed");


                //ref.prop("disabled",true);
                if($(this).parent().parent().parent().parent().find('.adjPrice').html()==
                    $(this).parent().parent().parent().parent().find('.manualPrice').html()){

                    $(this).parent().parent().parent().find(".internalDiscount").each(function() {
                        let ref=$(this);
                        let FormID = '<?php echo $_SESSION['FormID']; ?>';
                        let orderno = '<?php echo $_GET['orderno']; ?>';
                        let itemID = ref.attr("data-id");
                        let line = $(this).closest("tr").attr("data-lineId");
                        let discount = $(this).val();
                        $.post("api/updateInternalDiscount.php", {
                            FormID,
                            itemID,
                            discount
                        }, function (res, status, something) {

                            res = JSON.parse(res);

                            if (res.status == "success") {
                                ref.css("border", "2px solid green");
                            } else {
                                swal("Error", res.message, "error");
                            }

                        });
                    })}

            });


                $(".linePrice").on("change", function(){

				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let orderno = '<?php echo $_GET['orderno']; ?>';
				let line = $(this).closest("tr").attr("data-lineId");
				let price = $(this).val();
				let ref = $(this);
				ref.prop("disabled",true);
                    $.post("api/updateShopSaleLinePrice.php",{
                        FormID,orderno,line,price
                    }, function(res, status){
                        res = JSON.parse(res);
                        if(res.status == "success"){
                            ref.css("border","2px solid green");
                            let quantity = ref.closest("tr").find(".lineQTY").val();
                            ref.closest("tr").find(".lineSubTotal").html(price*quantity);
                            ref.closest("tr").find(".lineSubTotalHidden").html(price*quantity);
                        }else{
                            ref.css("border","2px solid red");
                        }
                        ref.prop("disabled",false);
                    });
                    let internalAmount=0;
                    let str="[data-externallineiddisc="+line+"]";
                    $(str).each(function(){
                        internalAmount += +$(this).parent().parent().find(".internalPrice").val()*$(this).parent().parent().find(".itemQuantity").val();
                    });

                    str="[data-lineid="+line+"]";
                    internalAmount=internalAmount*$(str).find(".lineQTY").val();
                    let discountOverall=(parseFloat($(".overallSubtotal").html())-parseFloat($(".overallTotal").html()));
                    let externalLineSubTotal= ($(this).val()*$(str).find(".lineQTY").val());
                    let externalLineTotal=  externalLineSubTotal- (discountOverall*externalLineSubTotal/(parseFloat($(".overallSubtotal").html())));



                    let discount=(1-externalLineTotal/internalAmount)*100;

                    console.log(discount);
                    str="[data-externallineiddisc="+line+"]";
                    $(str).val(discount);

                    $.post("api/updateShopSaleLinePrice.php",{
					FormID,orderno,line,price,discount
				}, function(res, status){
					res = JSON.parse(res);
					if(res.status == "success"){
						ref.css("border","2px solid green");
						let quantity = ref.closest("tr").find(".lineQTY").val();
						ref.closest("tr").find(".lineSubTotal").html(price*quantity);
                        ref.closest("tr").find(".lineSubTotalHidden").html(price*quantity);
					}else{
						ref.css("border","2px solid red");
					}
					ref.prop("disabled",false);
				});

			});

			$(".deleteLine").on("click", function(){

				let FormID = '<?php echo $_SESSION['FormID']; ?>';
				let orderno = '<?php echo $_GET['orderno']; ?>';
				let line = $(this).closest("tr").attr("data-lineId");
				let ref = $(this);
				
				if(confirm("Are you sure you want to remove this line?")){

					ref.prop("disabled",true);
					$.post("api/deleteShopSaleLine.php", {
						FormID,orderno,line
					}, function(res, status){
						res = JSON.parse(res);
						if(res.status == "success"){
							ref.closest("tr").remove();
							$(".internal-"+line).remove();
						}else{
							swal("Error",res.message,"error");
						}
						ref.prop("disabled",false);
                        let internalAmount=0;
                        $(this).parent().parent().parent().find(".internalPrice").each(function(){
                            internalAmount += +$(this).val()*$(this).parent().parent().find(".itemQuantity").val();
                        });


                        console.log(externalLineID);
                        console.log(numLines);



                    });

				}
                			});

		</script>

	</body>
</html>