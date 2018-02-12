<?php
die();
include ("db.php");
$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);


function update_users_for_new_registration($parameters) {
// to test: http://www.mashpia.com/admin_users_register_ajax.php?&function_name=update_users_for_new_registration&parameters=82,8253,4,true,true,true,64.00
	
	$school_id = $parameters[0];
	$user_id = $parameters[1];
	$fee_id = $parameters[2];
	$checkbox1 = $parameters[3];
	$checkbox2 = $parameters[4];
	$checkbox3 = $parameters[5];
	$registration_fee = $parameters[6];
	$user_start_date = $parameters[7];
	
	// add on one and two
	$add_on_one = 0;
	$add_on_two = 0;
	if($checkbox2 = 'true')
		$add_on_one = 1;
	if($checkbox3 = 'true')
		$add_on_two = 1;
	
	// if user_start_date is NULL then use today
	if ($user_start_date == NULL)
		$user_start_date  = unixtojd();
		
	$sql = 	" UPDATE users SET  " .
			" user_registered = NOW(), " .			
			" user_start_date = " . $user_start_date . ", " .
			" user_registration_fee = " . $registration_fee . ", " .
			" add_on_one = " . $add_on_one . ", " .
			" add_on_two = " . $add_on_two . ", " .
			" fee_id = 4 "  .
			" WHERE user_id= " . $user_id .
			" and school_id= " . $school_id ;
	 
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 1;
}	

// Create Invoice record
// to test: http://www.mashpia.com/admin_users_register_ajax.php?&function_name=insert_invoice_for_new_registration&parameters=82,50.00,school_packages,4,Registration- undefined- undefined 
function insert_invoice_for_new_registration($parameters) {
	$school_id = $parameters[0];
	$item_price = $parameters[1];
	$item_ref_type = $parameters[2];
	$item_ref_id = $parameters[3];
	$item_description  = $parameters[4];		
	
	$sql = 	" INSERT INTO invoice_items (
			school_id ,			
			item_price ,
			item_date ,
			item_ref_type ,
			item_ref_id ,
			item_description)
		VALUES ('$school_id', '$item_price',  CURRENT_TIMESTAMP ,  '$item_ref_type', '$item_ref_id', '$item_description' ) ";

		$query = mysql_query($sql);
		if (!$query)
		{	$error_code = 1; }
}	
 
