<?php

function GetStockMovementBalanceMetadata($type)
{
	$type = (int) $type;

	switch ($type) {
		case 750:
			return array('label' => 'Salesperson issued balance', 'is_location_qoh' => false);
		case 602:
			return array('label' => 'Pre-market-slip location balance', 'is_location_qoh' => false);
		case 111:
			return array('label' => 'Substore balance', 'is_location_qoh' => false);
		default:
			return array('label' => 'Location quantity on hand', 'is_location_qoh' => true);
	}
}

