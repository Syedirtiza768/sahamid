<?php
/* $Id:  $*/
/* definition of the SalesCase class */

Class SalesCase {

	var $SalesCaseRef; /*the SalesCase short description used for stockid when SalesCase submitted for quotation */
	var $SalesCaseDescription; /*the description of the SalesCase */
    var $DebtorNo; /*the customer that the SalesCase is for */
    var $CustomerName;
    var $BranchCode;
    var $BranchName;
    var $commencementdate;
	var $salesman;
  	var $enquiryfile; /*a link to the SalesCase enquiryfile*/
    var $enquirydate; /*a link to the SalesCase enquirydate*/
    var $pofile; /*a link to the SalesCase pofile*/
    var $podate; /*a link to the SalesCase podate*/
    var $ocdocumentfile; /*a link to the SalesCase ocdocumentfile*/
    var $ocdocumentdate; /*a link to the SalesCase ocdocumentdate*/
    var $salescasevalue;
	var $salescaseprospects;
    var $BOMComponentCounter;
    var $RequirementsCounter;

	var $SalesCaseBOM; /*array of stockid components  required for the SalesCase */
	var $SalesCaseReqts; /*array of other items required for the SalesCase */

	function SalesCase(){
	/*Constructor function initialises a new Payment batch */
		$this->SalesCaseBOM = array();
		$this->SalesCaseReqts = array();
		$this->BOMComponentCounter=0;
		$this->RequirementsCounter=0;
		$this->Status = 0;
	}

	function Add_To_SalesCaseBOM($StockID,
							    $ItemDescription,
							    $WorkCentre,
							    $Quantity,
							    $ItemCost,
							    $UOM,
							    $DecimalPlaces){

		if (isset($StockID) AND $Quantity!=0){
			$this->SalesCaseBOM[$this->BOMComponentCounter] = new SalesCaseComponent($this->BOMComponentCounter,
																	$StockID,
																	$ItemDescription,
																	$WorkCentre,
																	$Quantity,
																	$ItemCost,
																	$UOM,
																	$DecimalPlaces);
			$this->BOMComponentCounter++;
			Return 1;
		}
		Return 0;
	}

	function Remove_SalesCaseComponent($SalesCaseComponent_ID){
		global $db;
		$result = DB_query("DELETE FROM SalesCasebom
											WHERE SalesCaseref='" . $this->SalesCaseRef . "'
											AND stockid='" . $this->SalesCaseBOM[$SalesCaseComponent_ID]->StockID . "'",
											$db);
		unset($this->SalesCaseBOM[$SalesCaseComponent_ID]);
	}


/*Requirments Methods */

function Add_To_SalesCaseRequirements($Requirement,
									$Quantity,
									$CostPerUnit,
									$SalesCaseReqID=0){

		if (isset($Requirement) AND $Quantity!=0 AND $CostPerUnit!=0){
			$this->SalesCaseReqts[$this->RequirementsCounter] = new SalesCaseRequirement($Requirement, $Quantity, $CostPerUnit,$SalesCaseReqID);
			$this->RequirementsCounter++;
			Return 1;
		}
		Return 0;
	}

	function Remove_SalesCaseRequirement($SalesCaseRequirementID){
		global $db;
		$result = DB_query("DELETE FROM SalesCasereqts WHERE SalesCasereqid='" . $this->SalesCaseReqts[$SalesCaseRequirementID]->SalesCaseReqID . "'",$db);
		unset($this->SalesCaseReqts[$SalesCaseRequirementID]);
	}

} /* end of class defintion */

Class SalesCaseComponent {
	var $ComponentID;
	var $StockID;
	var $ItemDescription;
	var $WorkCentre;
	var $Quantity;
	var $ItemCost;
	var $UOM;
	var $DecimalPlaces;

	function SalesCaseComponent ($ComponentID,
								$StockID,
								$ItemDescription,
								$WorkCentre,
								$Quantity,
								$ItemCost,
								$UOM,
								$DecimalPlaces=0){

/* Constructor function to add a new SalesCase Component object with passed params */
		$this->ComponentID = $ComponentID;
		$this->StockID = $StockID;
		$this->ItemDescription = $ItemDescription;
		$this->WorkCentre = $WorkCentre;
		$this->Quantity = $Quantity;
		$this->ItemCost= $ItemCost;
		$this->UOM = $UOM;
		$this->DecimalPlaces = $DecimalPlaces;
	}
}

Class SalesCaseRequirement {

	var $SalesCaseReqID; /*Used to hold the database ID of the SalesCasereqtID  - if an existing SalesCase*/
	var $Requirement; /*The description of the requirement for the SalesCase */
	var $Quantity;
	var $CostPerUnit;

	function SalesCaseRequirement ($Requirement,
									$Quantity,
									$CostPerUnit,
									$SalesCaseReqID=0){

/* Constructor function to add a new SalesCase Component object with passed params */
		$this->Requirement = $Requirement;
		$this->Quantity = $Quantity;
		$this->CostPerUnit = $CostPerUnit;
		$this->SalesCaseReqID = $SalesCaseReqID;
	}
}
?>