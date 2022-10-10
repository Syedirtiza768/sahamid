<?php

Class StockRequest {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $DispatchDate;
	var $Location;
	var $deliverto;
	
	var $storemanager;
	var $Narrative;
	var $igp;
	var $substore;
	var $LineCounter=0;

	function StockRequest(){
	/*Constructor function initialises a new shopping cart */
		$this->DispatchDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems=array();
	}

	function AddLine($StockID,
					$ItemDescription,
					
					$Quantity,
					$Comments,
					$rate,
					$brand,
					$UOM,
					$DecimalPlaces,
					$LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($StockID,
												$ItemDescription,
												$modelnumber,
												$Quantity,
												$Comments,
												$rate,
												$brand,
												$UOM,
												$DecimalPlaces,
												$LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $StockID;
	var $ItemDescription;
	var $modelnumber;
	var $Quantity;
	var $Comments;
	var $rate;
	var $brand;
	var $UOM;
	var $LineNumber;

	function LineDetails($StockID,
						$ItemDescription,
						$modelnumber,
						$Quantity,
						$Comments,
						$rate,
						$brand,
						$UOM,
						$DecimalPlaces,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->StockID=$StockID;
		$this->ItemDescription=$ItemDescription;
		$this->modelnumber=$modelnumber;
		
		$this->Quantity=$Quantity;
		$this->Comments = $Comments;
		$this->rate = $rate;
		$this->brand = $brand;
		$this->DecimalPlaces=$DecimalPlaces;
		$this->UOM=$UOM;
	}

}

?>