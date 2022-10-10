<?php 

	if (isset($_POST['Search'])){
		

		
		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.stockid NOT LIKE '%\t%'
						AND stockmaster.discontinued=0
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						AND stockmaster.stockid NOT LIKE '%\t%'
						ORDER BY stockmaster.stockid";
			}

		} elseif (mb_strlen($_POST['StockCode'])>0){

			$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
			$SearchString = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND (stockmaster.mnfCode " . LIKE . " '%" . $SearchString . "%'
					or stockmaster.stockid " . LIKE . " '%" . $SearchString. "%')
						
						AND stockmaster.stockid NOT LIKE '%\t%'
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.stockid NOT LIKE '%\t%'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
			}

		} else {
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.stockid NOT LIKE '%\t%'
						ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.longdescription,stockmaster.materialcost,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L'".$RawMaterialSellable.")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.stockid NOT LIKE '%\t%'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
			 	}
			}

		
	} //end of if search
			while ($myrow=DB_fetch_array($SearchResult)) {

				// Find the quantity in stock at location
				$QOHSQL = "SELECT quantity AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid = stockmaster.stockid
							   WHERE locstock.stockid='" .$myrow['stockid'] . "'";
				$QOHResult =  DB_query($QOHSQL,$db);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				$Available = $QOH;

				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number" tabindex="%s" type="text" size="6" name="OrderQty%s"  ' . ($i==0 ? 'autofocus="autofocus"':'') . ' value="0" min="0"/>
						<input type="hidden" name="StockID%s" value="%s" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['longdescription'],
						$myrow['description'],
						$myrow['units'],
						locale_number_format($QOH,$QOHRow['decimalplaces']),
						locale_number_format($DemandQty,$QOHRow['decimalplaces']),
						locale_number_format($OnOrder,$QOHRow['decimalplaces']),
						locale_number_format($Available,$QOHRow['decimalplaces']),
						strval($j+7),
						$i,
						$i,
						$myrow['stockid'] );
				$i++;
				$j++;
	#end of page full new headings if
			}


	
	
	
	
	
?>