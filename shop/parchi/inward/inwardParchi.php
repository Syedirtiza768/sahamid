<?php

$PathPrefix = "../../../";
include("../../../includes/session.inc");

if (!userHasPermission($db, "create_inward_parchi")) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
}

$SQL = "SELECT * FROM shop_vendors";
$svs = mysqli_query($db, $SQL);

?>

<!DOCTYPE html>
<html>

<head>
	<title>ERP-SAHamid Bazar Parchi</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
	<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
	<link rel="stylesheet" href="../../../quotation/assets/vendor/pnotify/pnotify.custom.css">
	<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="assets/searchSelect.css" />

	<script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style type="text/css">
		table {
			margin-top: 20px;
			margin-bottom: 20px;
		}

		.inputstyle {
			height: 25px;
			border: 1px solid #424242;
			border-radius: 5px;
			margin-left: 5px;
		}

		.itemrow {
			margin-bottom: 10px
		}

		.modelno {
			width: 150px;
			text-align: center;
		}

		.itemname {
			width: 400px;
			padding: 5px;
		}

		.itemquantity,
		.discount {
			width: 80px;
			text-align: center;
		}

		.actualprice,
		.listprice,
		.itemprice {
			width: 90px;
			text-align: center;
		}

		.removeitem {
			width: 100px
		}
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
					<a href="<?php echo $RootPath; ?>/../../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
					<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
				</span>
			</span>
			<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
		</header>

		<h3 style="text-align: center; font-variant-caps: petite-caps;">
			<i class="fa fa-sign-in" aria-hidden="true"></i>
			Inwards Bazar Parchi
		</h3>

		<div style="display: flex; justify-content: center; width: 100%; margin-bottom: 50px">
			<div style="min-width: 60vw;  background-color: white">
				<div class="col-md-12" style="border:1px solid #424242; border-radius: 7px; min-width: 60vw">

					<table style="width: 100%">
						<tr>
							<td></td>
							<td style="text-align: right;">Date: <?php echo date("d/m/Y h:i:s"); ?></td>
						</tr>
						<tr>
							<td>For Vendor: &nbsp;&nbsp;&nbsp;
								<input id="vendordropdown" type="text" style="max-width: 300px; border: 1px solid black; border-radius: 7px;" placeholder="Vendor Name Here">
								<!-- <select id="vendordropdown" style="max-width: 300px; border: 1px solid black">
										<option value="">Select Vendor</option>
										<option value="new">Add New</option>
										<?php while ($row = mysqli_fetch_assoc($svs)) { ?>

											<option value="<?php echo $row['vid']; ?>"><?php echo $row['name']; ?></option>

										<?php } ?>
									</select> -->
							</td>
							<td>
								<div style="display: none;" id="addnewvendor">
									<input type="text" id="newvendorname" class="inputstyle" placeholder="Vendor Name">
									<button class="btn btn-success inputstyle" style="padding-top: 2px" id="savevendor">
										Save Vendor
									</button>
								</div>
							</td>
						</tr>
						<tr>
							<td><br>On Behalf Of:
								<input id="obo" type="text" style="max-width: 300px; border: 1px solid black; border-radius: 7px;" placeholder="On Behalf Of">
							</td>
						</tr>
						<tr>
							<td colspan="2" id="errormessage" style="text-align: center;"></td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								<h3 style="font-variant-caps:petite-caps;">
									Items
									<button class="btn btn-success" style="display: inline-block;" id="addmoreitems">
										Add More Items
									</button>
								</h3>
							</td>
						</tr>
						<tr>
							<td colspan="3" style="text-align: center; " id="itemscontainer">
								<div>
									<div class="itemname" style="text-align: center; display: inline-block;">Item Name</div>
									<div class="modelno" style="text-align: center; display: inline-block;">Model No</div>
									<div class="listprice" style="text-align: center; display: inline-block;">List Price</div>
									<div class="discount" style="text-align: center; display: inline-block;">Discount</div>
									<div class="itemprice" style="text-align: center; display: inline-block;"> Actual Price</div>
									<div class="itemquantity" style="text-align: center; display: inline-block;">Quantity</div>
									<div class="removeitem" style="text-align: center; display: inline-block;">Action</div>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<button class="btn btn-success" style="width: 100%; margin-top: 50px" id="saveparchi">
									Save
								</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px">
			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
		</footer>

	</section>

	<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
	<script src="../../../quotation/assets/javascripts/theme.js"></script>
	<script type="text/javascript" src="assets/searchSelect.js"></script>

	<script>
		$("#vendordropdown").on("change", function() {
			if ($(this).val() == "new") {
				$("#addnewvendor").css("display", "block");
			} else {
				$("#addnewvendor").css("display", "none");
			}
		});
		$("#addmoreitems").on("click", function() {

			addNewItem();

		});

		function addNewItem() {

			var html = '<div class="itemrow">';
			html += '<input type="text" class="inputstyle itemname" placeholder="Item Name">';
			html += '<input type="text" class="inputstyle modelno" placeholder="Model No">';
			html += '<input type="number" class="inputstyle listprice" placeholder=" List Price" value="0">';
			html += '<input type="number" class="inputstyle discount" placeholder="Discount" value="0">';
			html += '<input type="number" class="inputstyle itemprice" placeholder="Actual Price" value="0">';
			html += '<input type="number" class="inputstyle itemquantity" placeholder="Quantity" value="0">';
			html += '<button class="btn btn-danger inputstyle removeitem" style="padding-top: 2px">Remove</button>';
			html += '</div>';

			$("#itemscontainer").append(html);

		}

		$(document).ready(function() {

			addNewItem();

		});

		$(document.body).on("click", ".removeitem", function() {
			$(this).parent().remove();
		});

		$("#newvendorname").keypress(function(event) {
			var inputValue = event.which;
			// allow letters and whitespaces only.
			if (!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)) {
				event.preventDefault();
			}
		});

		$("#savevendor").on("click", function() {

			$("#errormessage").html("");

			let name = $("#newvendorname").val();
			$.post('api/insert_shop_vendor.php', {
				FormID: '<?php echo $_SESSION["FormID"]; ?>',
				name: name
			}, function(res, status) {
				try {
					let data = JSON.parse(res);
					if (data.status == "success") {
						let html = "<option value='" + data.vid + "'>" + data.name + "</option>";
						$("#vendordropdown").append(html);
						$("#newvendorname").val("");
						$("#vendordropdown").val(data.vid);
						$("#addnewvendor").css("display", "none");
					} else {
						$("#errormessage").html(data.message);
					}
				} catch (e) {
					$("#errormessage").html("Something went wrong!");
				}
			});
		});

		$("#saveparchi").on("click", function() {
			const saveButton = $(this); // Store reference to button

			if ($("#vendordropdown").val() == "" || $("#vendordropdown").val() == "new") {
				swal("Error", "Inward Bazar Parchi Cannot be created without vendor.", "error");
				return;
			}

			if ($("#obo").val().trim() == "") {
				swal("Error", "Inward Bazar Parchi Cannot be created without On Behalf of.", "error");
				return;
			}

			let items = {};
			let count = 0;
			let missingQuantity = false;
			let missingModel = false;

			$("#itemscontainer").find(".itemrow").each(function() {
				if ($(this).find(".itemname").val().trim() != "") {
					if ($(this).find(".itemquantity").val() <= 0)
						missingQuantity = true;

					if ($(this).find(".modelno").val().trim() == "")
						missingModel = true;

					items[count] = {};
					items[count]['name'] = $(this).find(".itemname").val().trim();
					items[count]['model'] = $(this).find(".modelno").val().trim();
					items[count]['quantity'] = $(this).find(".itemquantity").val().trim();
					items[count]['price'] = $(this).find(".itemprice").val().trim();
					items[count]['discount'] = $(this).find(".discount").val().trim();
					items[count]['listprice'] = $(this).find(".listprice").val().trim();

					count++;
				}
			});

			if (missingQuantity) {
				swal("Error", "One or more items found with 0 or less quantity", "error");
				return;
			}

			if (missingModel) {
				swal("Error", "Missing Model No...", "error");
				return;
			}

			if (Object.keys(items).length <= 0) {
				swal("Error", "Bazar Parchi cannot be created without items", "error");
				return;
			}

			saveButton.prop("disabled", true);

			$.post("api/saveInwardBazarParchi.php", {
				FormID: '<?php echo $_SESSION['FormID']; ?>',
				items: items,
				vendor: $("#vendordropdown").val().trim(),
				obo: $("#obo").val().trim()
			}, function(res, status) {
				try {
					let data = JSON.parse(res);
					if (data.status == "success") {
						swal({
							title: "Success",
							text: "Bazar Parchi Created Successfully.",
							type: "success",
							confirmButtonText: "OK"
						}).then(function() {
							location.href = "editInwardParchi.php?parchi=" + data.parchino;
						});
						return;
					} else {
						swal("Error", data.message, "error");
						saveButton.prop("disabled", false);
					}
				} catch (e) {
					console.error("JSON Parse Error:", e);
					console.error("Response:", res);
					swal("Error", "Something Went wrong. Response: " + res.substring(0, 100), "error");
					saveButton.prop("disabled", false);
				}
			}).fail(function(jqXHR, textStatus, errorThrown) {
				console.error("AJAX Error:", textStatus, errorThrown);
				swal("Error", "Network error occurred. Please try again.", "error");
				saveButton.prop("disabled", false);
			});
		});

		$(document.body).on("change", ".listprice", function() {
			$(this).parent().find(".itemprice").val((100 - $(this).parent().find(".discount").val()) * ($(this).parent().find(".listprice").val() / 100));
		});
		$(document.body).on("change", ".discount", function() {
			$(this).parent().find(".itemprice").val((100 - $(this).parent().find(".discount").val()) * ($(this).parent().find(".listprice").val() / 100));
		});
		$(document.body).on("change", ".itemprice", function() {
			$(this).parent().find(".discount").val(0);
			$(this).parent().find(".listprice").val($(this).val());
		});

		$(document).ready(function() {
			//$("#vendordropdown").select2();
		});
	</script>


</body>

</html>