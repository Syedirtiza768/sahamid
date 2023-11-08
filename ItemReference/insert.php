<?php
include_once("../v2/config.php");
$old_sales = $_POST['old_sales'];
$old_csv = $_POST['old_csv'];
$old_crv = $_POST['old_crv'];
$old_mpo = $_POST['old_mpo'];
$old_quantity = $_POST['old_quantity'];

$new_sales = $_POST['new_sales'];
$new_sales_q = $_POST['new_sales_q'];
$new_csv = $_POST['new_csv'];
$new_csv_q = $_POST['new_csv_q'];
$new_crv = $_POST['new_crv'];
$new_crv_q = $_POST['new_crv_q'];
$new_mpo = $_POST['new_mpo'];
$new_mpo_q = $_POST['new_mpo_q'];
$item = $_POST['item'];
$salesman = $_POST['salesman'];


if ($new_sales != "") {
	if ($old_sales != "") {
		$sql = "UPDATE ogpsalescaseref SET quantity=quantity-$new_sales_q WHERE salescaseref='$old_sales' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_csv != "") {
		$sql = "UPDATE ogpcsvref SET quantity=quantity-$new_sales_q WHERE csv='$old_csv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_crv != "") {
		$sql = "UPDATE ogpcrvref SET quantity=quantity-$new_sales_q WHERE crv='$old_crv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_mpo != "") {
		$sql = "UPDATE ogpmporef SET quantity=quantity-$new_sales_q WHERE mpo='$old_mpo' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	$check_select = mysqli_query($db, "SELECT * FROM `ogpsalescaseref` WHERE stockid = '$item' AND salescaseref = '$new_sales'
                                    AND salesman = '$salesman'");

	$numrows = mysqli_num_rows($check_select);

	if ($numrows > 0) {

		$sql = "UPDATE ogpsalescaseref SET quantity=quantity+$new_sales_q WHERE stockid='$item' AND salescaseref = '$new_sales' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		}
	} else {
		$sql = "INSERT INTO ogpsalescaseref (salescaseref,
											stockid,
											salesman,
											quantity)
							VALUES ( '$new_sales' ,
								 '$item' ,
								 '$salesman' ,
								 '$new_sales_q' )";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
}

if ($new_csv != "") {

	if ($old_sales != "") {
		$sql = "UPDATE ogpsalescaseref SET quantity=quantity-$new_csv_q WHERE salescaseref='$old_sales' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_csv != "") {
		$sql = "UPDATE ogpcsvref SET quantity=quantity-$new_csv_q WHERE csv='$old_csv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_crv != "") {
		$sql = "UPDATE ogpcrvref SET quantity=quantity-$new_csv_q WHERE crv='$old_crv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_mpo != "") {
		$sql = "UPDATE ogpmporef SET quantity=quantity-$new_csv_q WHERE mpo='$old_mpo' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	$check_select = mysqli_query($db, "SELECT * FROM `ogpcsvref` WHERE stockid = '$item' AND csv = '$new_csv'
                                    AND salesman = '$salesman'");

	$numrows = mysqli_num_rows($check_select);

	if ($numrows > 0) {

		$sql = "UPDATE ogpcsvref SET quantity=quantity+$new_csv_q WHERE stockid='$item' AND csv = '$new_csv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		}
	} else {
		$sql = "INSERT INTO ogpcsvref (csv,
									stockid,
									salesman,
									quantity)
							VALUES ( '$new_csv' ,
								 '$item' ,
								 '$salesman' ,
								 '$new_csv_q' )";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
}

if ($new_crv != "") {
	if ($old_sales != "") {
		$sql = "UPDATE ogpsalescaseref SET quantity=quantity-$new_crv_q WHERE salescaseref='$old_sales' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_csv != "") {
		$sql = "UPDATE ogpcsvref SET quantity=quantity-$new_crv_q WHERE csv='$old_csv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_crv != "") {
		$sql = "UPDATE ogpcrvref SET quantity=quantity-$new_crv_q WHERE crv='$old_crv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_mpo != "") {
		$sql = "UPDATE ogpmporef SET quantity=quantity-$new_crv_q WHERE mpo='$old_mpo' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	$check_select = mysqli_query($db, "SELECT * FROM `ogpcrvref` WHERE stockid = '$item' AND crv = '$new_crv'
                                    AND salesman = '$salesman'");

	$numrows = mysqli_num_rows($check_select);

	if ($numrows > 0) {

		$sql = "UPDATE ogpcrvref SET quantity=quantity+$new_crv_q WHERE stockid='$item' AND crv = '$new_crv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		}
	} else {
		$sql = "INSERT INTO ogpcrvref (crv,
									stockid,
									salesman,
									quantity)
							VALUES ( '$new_crv' ,
								 '$item' ,
								 '$salesman' ,
								 '$new_crv_q' )";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
}

if ($new_mpo != "") {
	if ($old_sales != "") {
		$sql = "UPDATE ogpsalescaseref SET quantity=quantity-$new_mpo_q WHERE salescaseref='$old_sales' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_csv != "") {
		$sql = "UPDATE ogpcsvref SET quantity=quantity-$new_mpo_q WHERE csv='$old_csv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_crv != "") {
		$sql = "UPDATE ogpcrvref SET quantity=quantity-$new_mpo_q WHERE crv='$old_crv' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
	if ($old_mpo != "") {
		$sql = "UPDATE ogpmporef SET quantity=quantity-$new_mpo_q WHERE mpo='$old_mpo' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	$check_select = mysqli_query($db, "SELECT * FROM `ogpmporef` WHERE stockid = '$item' AND mpo = '$new_mpo'
                                    AND salesman = '$salesman'");

	$numrows = mysqli_num_rows($check_select);

	if ($numrows > 0) {

		$sql = "UPDATE ogpmporef SET quantity=quantity+$new_mpo_q WHERE stockid='$item' AND mpo = '$new_mpo' AND salesman = '$salesman'";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		}
	} else {
		$sql = "INSERT INTO ogpmporef (mpo,
									stockid,
									salesman,
									quantity)
							VALUES ( '$new_mpo' ,
								 '$item' ,
								 '$salesman' ,
								 '$new_mpo_q' )";

		if (mysqli_query($db, $sql)) {
			echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}
}
