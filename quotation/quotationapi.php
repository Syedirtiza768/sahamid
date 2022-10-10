<?php
//Quotation Header


	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);




if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);
		
//End Quotation Header
//Line Items Result
		$LineItemsSQL = "SELECT salesorderdetails.internalitemno,
									salesorderdetails.orderlineno,
									salesorderdetails.lineoptionno,
									salesorderdetails.optionitemno,
									salesorderdetails.stkcode,
									stockmaster.description,
									stockmaster.longdescription,
									stockmaster.materialcost,
									stockmaster.volume,
									stockmaster.grossweight,
									stockmaster.units,
									stockmaster.serialised,
									stockmaster.nextserialno,
									stockmaster.eoq,
									salesorderdetails.unitprice,
									salesorderdetails.quantity,
									salesorderdetails.discountpercent,
									salesorderdetails.actualdispatchdate,
									salesorderdetails.qtyinvoiced,
									salesorderdetails.narrative,
									salesorderdetails.itemdue,
									salesorderdetails.poline,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
									salesorderdetails.completed
								FROM salesorderdetails INNER JOIN stockmaster
								ON salesorderdetails.stkcode = stockmaster.stockid
								INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
								WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
								AND salesorderdetails.orderno ='" . $orderno . "'
								ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg);


//Line Items Result End

//External Line SQL

$externallinessql = "SELECT *
								FROM salesorderlines 
								WHERE  salesorderlines.orderno ='" . $orderno . "'
								";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$externallinessqlresult = DB_query($externallinessql,$db,$ErrMsg);


// External Line SQL End
//Line Options Sql
		$lineoptionsssql = "SELECT *
								FROM salesorderoptions 
								WHERE  salesorderoptions.orderno ='" . $orderno . "'
								
								";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$lineoptionsssqlresult = DB_query($lineoptionsssql,$db,$ErrMsg);
// Line Options SQL End		
	}

		

	
		
			
?>