<?php 

	$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	
	$newStorePostfix 	= "PS";
	$newLocationName	= "Offset";
	$newLocationCode	= "OS";
	$newLocationAdd1	= "Add1";
	$newLocationAdd2	= "Add2";
	$newLocationContact = "dasdas";
	$newLocationSubStoreName = "Offset";

	$defaultSubstores = [];

	// Insert in all 
	$SQL = "INSERT INTO `systypes`(`typeid`, `typename`, `typeno`) 
			VALUES ('111','PSIS', '1')";
	mysqli_query($db,$SQL);

	//Location Division
echo	$SQL = "SELECT * FROM locations WHERE loccode like '%HO%' OR loccode like '%MT%' OR loccode like '%SR%'";
	$res = mysqli_query($db, $SQL);
	while($row = mysqli_fetch_assoc($res)){

		$loccode 	  = $row['loccode'];
		$locationCode = $row['loccode'].$newStorePostfix;
		$locationName = $row['locationname']." ".$newStorePostfix;
		$deladd1	  = $row['deladd1'];
		$deladd2	  = $row['deladd2'];
		$deladd3	  = $row['deladd3'];
		$deladd4	  = $row['deladd4'];
		$deladd5	  = $row['deladd5'];
		$deladd6	  = $row['deladd6'];
		$contact 	  = $row['contact'];

		$defaultSubstoreArray = [
			'subStoreID' => $row['defaultsubstore'],
			'loccode'	 => $row['loccode'],
			'newloccode' => $locationCode
		];

		$SQL = "INSERT INTO `locations`(`loccode`,`locationname`,`deladd1`,`deladd2`,`deladd3`,
							`deladd4`,`deladd5`,`deladd6`,`contact`) 
				VALUES ('$locationCode','$locationName','$deladd1','$deladd2','$deladd3',
						'$deladd4','$deladd5','$deladd6','$contact')";
		mysqli_query($db, $SQL);

		//Add items in newly added location
		$SQL = "SELECT stockid FROM stockmaster";
		$stocks = mysqli_query($db, $SQL);
		while($stock = mysqli_fetch_assoc($stocks)){
			$sID = $stock['stockid'];

			$SQL = "INSERT INTO `locstock`(`loccode`, `stockid`, `quantity`) 
					VALUES ('$locationCode','$sID','0')";
			mysqli_query($db, $SQL);
		}

		//creating substores for newly created locations
echo	$SQL = "SELECT * FROM substores WHERE locid='$loccode' AND (locid like '%MT%' OR locid like '%HO%' OR locid like '%SR%')";
		$subStoresRes = mysqli_query($db, $SQL);
		while($subStore = mysqli_fetch_assoc($subStoresRes)){
			$description = $subStore['description']." ".$newStorePostfix;
			
			$SQL = "INSERT INTO `substores`(`description`, `locid`) 
					VALUES ('$description','$locationCode')";
			mysqli_query($db, $SQL);
			$newSubStoreID = mysqli_insert_id($db);

			if($subStore['substoreid'] == $defaultSubstoreArray['subStoreID']){
				$defaultSubstoreArray['newSubStoreID'] = $newSubStoreID;
			}
		}

		$defaultSubstores[] = $defaultSubstoreArray;

	}

	//creating location for odd quantity Items
	$SQL = "INSERT INTO `locations`(`loccode`,`locationname`,`deladd1`,`deladd2`,`contact`) 
			VALUES ('$newLocationCode','$newLocationName','$newLocationAdd1','$newLocationAdd2','$newLocationContact')";
	mysqli_query($db, $SQL);

	$SQL = "SELECT stockid FROM stockmaster";
	$stocks = mysqli_query($db, $SQL);
	while($stock = mysqli_fetch_assoc($stocks)){
		$sID = $stock['stockid'];

		$SQL = "INSERT INTO `locstock`(`loccode`, `stockid`, `quantity`) 
				VALUES ('$newLocationCode','$sID','0')";
		mysqli_query($db, $SQL);
	}

	//new location substore
	$SQL = "INSERT INTO `substores`(`description`, `locid`) 
			VALUES ('$newLocationSubStoreName','$newLocationCode')";
	mysqli_query($db, $SQL);
	$newSubStoreID = mysqli_insert_id($db);
	$newOddItemsSubstoreID = $newSubStoreID;

	//Update New Location with the substore code for default substore
	$SQL = "UPDATE locations
			SET defaultsubstore='$newSubStoreID'
			WHERE loccode='$newLocationCode'";
	mysqli_query($db, $SQL);

	//update defaultsubstores where available for other newly created locations
	foreach($defaultSubstores as $subStore){
		if(isset($subStore['newSubStoreID'])){
			$subStoreID = $subStore['newSubStoreID'];
			$newLocCode = $subStore['newloccode'];

			$SQL = "UPDATE locations
					SET defaultsubstore='$subStoreID'
					WHERE loccode='$newLocCode'";
			mysqli_query($db, $SQL);
		}
	}

	//MAP new and old substoresid
	$subStoresArray = [];

	$subStoresMapIdToName = [];

echo	$SQL = "SELECT * FROM substores WHERE locid like '%MT%' OR locid like '%HO%' OR locid = '%SR%'";
	$res = mysqli_query($db, $SQL);
	while($row = mysqli_fetch_assoc($res)){
		$subStoresMapIdToName[$row['substoreid']] = $row['description'];
		$subStoresArray[$row['description']] = $row;
	}

	$mappedSubStoreID = [];
	foreach($subStoresArray as $key => $value){
		if(isset($subStoresArray[$key." ".$newStorePostfix])){
			$mappedSubStoreID[$value['substoreid']] = $subStoresArray[$key." ".$newStorePostfix]['substoreid'];
		}
	}
	//------------------------------------------------------------------------------------
	//--------------------DONE WITH THE NEW LOCATIONS AND SUBSTORES-----------------------
	//------------------------------------------------------------------------------------
	
echo	$SQL = "SELECT * FROM substorestock WHERE loccode = '%HO%' OR loccode = '%MT%' OR loccode = '%SR%'";
	$res = mysqli_query($db, $SQL);
	while($row = mysqli_fetch_assoc($res)){

		$stockid 		= $row['stockid'];
		$loccode 		= $row['loccode'];
		$quantity 		= $row['quantity'];
		$index 			= $row['index'];
		$substoreid 	= $row['substoreid'];
		$newloccode 	= $loccode.$newStorePostfix;
		$newsubstoreid 	= $mappedSubStoreID[$substoreid]; 

echo		$SQL = "SELECT * FROM substorestock WHERE stockid='$stockid' 
		AND  (loccode like '%HO%' OR loccode like '%MT%' OR loccode like '%SR%') AND substoreid='$newOddItemsSubstoreID'";
		if(mysqli_num_rows(mysqli_query($db, $SQL)) == 0){
			$SQL = "INSERT INTO `substorestock`(`loccode`, `stockid`, `quantity`, `substoreid`) 
					VALUES ('$newLocationCode','$stockid','0','$newOddItemsSubstoreID')";
			mysqli_query($db, $SQL);
		}

		if($quantity > 0){
			$remaining = 0;
			
			if(($quantity % 2) != 0){
				$remaining = 1;
			}

			$newQuantity = ($quantity-$remaining)/2;
$SQL = "SELECT SUM(quantity) qty 
				FROM substorestock 
				WHERE loccode='$loccode'
				AND stockid='$stockid'
				GROUP BY stockid";
		$qtyRes = mysqli_query($db, $SQL);
		$qtyRow=mysqli_fetch_assoc($qtyRes);
		$qoh=$qtyRow['qty'];
			$SQL = "UPDATE substorestock SET quantity='$newQuantity' WHERE `index`='$index'";
			mysqli_query($db, $SQL);

			$SQL = "INSERT INTO `stockmoves`(`stockid`, `type`, `transno`, `loccode`, `trandate`,  `prd`, 
								`reference`, `qty`, `newqoh`,  `narrative`)
					VALUES ('$stockid','111','1','$loccode', '".date('Y-m-d')."',42,
							'PSIS Transfered To $newloccode Substore ".$subStoresMapIdToName[$newsubstoreid]."',
							'$newQuantity','".($qoh-$newQuantity)."','PSIS')";
			mysqli_query($db,$SQL);
$SQL = "SELECT SUM(quantity) qty 
				FROM substorestock 
				WHERE loccode='$newloccode'
				AND stockid='$stockid'
				GROUP BY stockid";
		$qtyRes = mysqli_query($db, $SQL);
		$qtyRow=mysqli_fetch_assoc($qtyRes);
		$qoh=$qtyRow['qty'];
			$SQL = "INSERT INTO `substorestock`(`loccode`, `stockid`, `quantity`, `substoreid`) 
					VALUES ('$newloccode','$stockid','$newQuantity','$newsubstoreid')";
			mysqli_query($db, $SQL);


			//movement
			$SQL = "INSERT INTO `stockmoves`(`stockid`, `type`, `transno`, `loccode`, `trandate`,  `prd`, 
								`reference`, `qty`, `newqoh`,  `narrative`)
					VALUES ('$stockid','111','1','$newloccode', '".date('Y-m-d')."',42,
							'PSIS From $loccode Substore ".$subStoresMapIdToName[$substoreid]."',
							'$newQuantity','".($qoh+$newQuantity)."','PSIS')";
			mysqli_query($db,$SQL);

			if($remaining == 1){
				$SQL = "SELECT SUM(quantity) qty 
				FROM substorestock 
				WHERE loccode='$newOddItemsSubstoreID'
				AND stockid='$stockid'
				GROUP BY stockid";
		$qtyRes = mysqli_query($db, $SQL);
		$qtyRow=mysqli_fetch_assoc($qtyRes);
		$qoh=$qtyRow['qty'];
				$SQL = "UPDATE substorestock 
						SET quantity = (quantity+1) 
						WHERE substoreid=$newOddItemsSubstoreID 
						AND stockid='$stockid'";
				mysqli_query($db, $SQL);
			
				//movement
				$SQL = "INSERT INTO `stockmoves`(`stockid`, `type`, `transno`, `loccode`, `trandate`,  `prd`, 
									`reference`, `qty`, `newqoh`,  `narrative`)
						VALUES ('$stockid','111','1','$newLocationCode', '".date('Y-m-d')."',42,
								'PSIS From $loccode Substore ".$subStoresMapIdToName[$substoreid]."',
								'1','".($qoh+1)."','PSIS')";
				mysqli_query($db,$SQL);
			$SQL = "SELECT SUM(quantity) qty 
				FROM substorestock 
				WHERE loccode='$loccode'
				AND stockid='$stockid'
				GROUP BY stockid";
		$qtyRes = mysqli_query($db, $SQL);
		$qtyRow=mysqli_fetch_assoc($qtyRes);
		$qoh=$qtyRow['qty'];
		
				$SQL = "INSERT INTO `stockmoves`(`stockid`, `type`, `transno`, `loccode`, `trandate`,  `prd`, 
									`reference`, `qty`, `newqoh`,  `narrative`)
						VALUES ('$stockid','111','1','$loccode', '".date('Y-m-d')."',42,
								'PSIS to $newLocationCode Substore $newLocationSubStoreName',
								'1','".($qoh)."','PSIS')";
				mysqli_query($db,$SQL);
		
			}
		}else{
			$SQL = "INSERT INTO `substorestock`(`loccode`, `stockid`, `quantity`, `substoreid`) 
					VALUES ('$newloccode','$stockid','0','$newsubstoreid')";
			mysqli_query($db, $SQL);
		}
	}

	//Update Location Stock Quantity
echo	$SQL = "SELECT * FROM locstock WHERE loccode like '%HO%' OR loccode = '%MT%' OR loccode = '%SR%'";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){
		$stockid = $row['stockid'];
		$loccode = $row['loccode'];

		$SQL = "SELECT SUM(quantity) qty 
				FROM substorestock 
				WHERE loccode='$loccode'
				AND stockid='$stockid'
				GROUP BY stockid";
		$qtyRes = mysqli_query($db, $SQL);

		if(mysqli_num_rows($qtyRes) == 0)
			continue;

		$quantity = mysqli_fetch_assoc($qtyRes)['qty'];

		$SQL = "UPDATE locstock 
				SET quantity='$quantity' 
				WHERE stockid='$stockid'
				AND loccode='$loccode'";
		mysqli_query($db, $SQL);

	}

	echo "Done";

