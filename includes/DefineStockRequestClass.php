<?php

Class StockRequest {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $DispatchDate;
	var $SourceLocation;
	var $DestinationLocation;
	var $Department;
	var $Narrative;
	var $salesperson;
	
	var $storemanager;
	var $authorizer;
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
					$UOM,
					$DecimalPlaces,
					$LineNumber=-1) {

		if ($LineNumber==-1){
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber]=new LineDetails($StockID,
												$ItemDescription,
												$Quantity,
												$Comments,
												$UOM,
												$DecimalPlaces,
												$LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

Class LineDetails {
	var $StockID;
	var $ItemDescription;
	var $Quantity;
	var $Comments;
	var $UOM;
	var $LineNumber;

	function LineDetails($StockID,
						$ItemDescription,
						$Quantity,
						$Comments,
						$UOM,
						$DecimalPlaces,
						$LineNumber) {

		$this->LineNumber=$LineNumber;
		$this->StockID=$StockID;
		$this->ItemDescription=$ItemDescription;
		$this->Quantity=$Quantity;
		$this->Comments = $Comments;
		$this->DecimalPlaces=$DecimalPlaces;
		$this->UOM=$UOM;
	}

}

?>