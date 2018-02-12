<?php
	require('db.php');
	
	$intUserSerial = $_GET["s"];
	$intUserSerial = preg_replace("/[^0-9]+/", "", $intUserSerial);
	$intUserSerial = preg_replace("/^3/", "", $intUserSerial);
    
    $strSql = "select year from school_add_ons order by year desc limit 1";
    $objResult = mysql_query($strSql) or die(mysql_error());
    $arrRow = mysql_fetch_assoc($objResult);
    $strYear = $arrRow['year'];
	
	$strSql = "
		SELECT
			school_add_ons.*, user_add_ons.size as item_size
		FROM
			users,
			user_add_ons,
			school_add_ons
		WHERE
			users.user_code = \"" . $intUserSerial . "\"
			AND user_add_ons.user_id = users.user_id
			AND user_add_ons.school_add_on_id = school_add_ons.school_add_on_id 
			AND school_add_ons.year = " . $strYear;
	
	$objResult = mysql_query($strSql) or die(mysql_error());
	$arrResult = array();
	while ($objRow = mysql_fetch_assoc($objResult))
	{
		//$objRow["store_item"] = trim($objRow);
		$arrResult[] = $objRow;
	}
	print serialize($arrResult);
?>