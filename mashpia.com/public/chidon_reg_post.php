<?
//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
require 'db.php';

function clean(&$value) {
	if (is_array($value)) {
		foreach ($value as $k => &$v) {
			clean($v);
		}
	} else {
		$value = mysql_real_escape_string($value);
	}
}

//sanitize values
clean($_POST);
$error = '';

$id = $_POST['schoolID'];
$year = 5776;
$fee = 115;
$paid = false;
$gender = $_POST['gender'];
/*
//if we are adding a participant
if (isset($_POST['name'])) {
	$grade 		= 	$_POST['grade'];
	$type 		= 	$_POST['type'];
	$name 		= 	$_POST['name'];
	$lname		=	$_POST['lname'];
	$hfname		= 	$_POST['hname'];
	$hlname		=	$_POST['hname_last'];
	//$hname 		= 	$_POST['hname'] . ' ' . $_POST['hname_last']; 
	$mark1 		= 	($_POST['mark1'] == '' ? 0 : $_POST['mark1']);
	$mark2 		= 	($_POST['mark2'] == '' ? 0 : $_POST['mark2']);
	$mark3		=	($_POST['mark3'] == '' ? 0 : $_POST['mark3']);
	$bonus		=	($_POST['bonus'] == '' ? 0 : $_POST['bonus']); 
	$help 		= 	($_POST['help'] == 'n' ? 0 : 1);
	$family		=	$_POST['family'];
	$address	=	$_POST['address'];
	$phone		= 	$_POST['phone'];
	$notes 		=	$_POST['notes'];
	$parentName	=	$_POST['parentName'];
	$parentEmail=	$_POST['parentEmail'];
	$fatherCell	=	$_POST['parentCell'];
	$motherCell = 	$_POST['motherCell'];
	$size 		=	$_POST['size'];
	$walk 		=	$_POST['walk'];
	$allergy	=	$_POST['allergy'];
	$streets	=	$_POST['streets'];
	$shoeSize	=	$_POST['shoeSize'];
	
	switch ($grade) {
		case '4':
			$book = 1;
			break;
		case '5':
			$book = 2;
			break;
		case '6':
			$book = 3;
			break;
		case '7':
			$book = 4;
			break;
		case '8':
			$book = 1;
			break;
	}
	
	/*
	$arrAirport =	$_POST['arrAirport'];
	$arrNumber 	= 	$_POST['arrNumber'];
	$arrTime 	= 	$_POST['arrTime'];
	$depAirport = 	$_POST['depAirport'];
	$depNumber 	=	$_POST['depNumber'];
	$depTime 	= 	$_POST['depTime'];
	*/
	
	//add file
	/*
	if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
		chdir('chidon/photos');
		if (move_uploaded_file($_FILES['photo']['tmp_name'], $_FILES['photo']['name'])) {
			$file = $_FILES['photo']['name'];
		}
		chdir('../../');
	}
	 * 
	 */
	 /*
	$file = $_POST['filePic'];
	if ($file == '/mobile/reg/images/addphoto.png') $file = '';
	
	$sql = "insert into chidon_reg 
			set chidon_schools_id = $id, 
			grade = '$grade', 
			type = '$type', 
			name = '$name', 
			last_name = '$lname', 
			hfname = '$hfname', 
			hlname = '$hlname',  
			book = '$book', 
			fee = $fee, 
			help = $help, 
			family = '$family', 
			address = '$address', 
			phone = '$phone', 
			mark = 0, 
			mark1 = $mark1, 
			mark2 = $mark2, 
			mark3 = $mark3, 
			bonus = $bonus, 
			notes = '$notes', 
			parent_name = '$parentName', 
			parent_email = '$parentEmail', 
			parent_cell = '$fatherCell', 
			parent_cell2 = '$motherCell', 
			size = '$size', 
			arr_airport = '$arrAirport', 
			arr_number = '$arrNumber', 
			arr_time = '$arrTime', 
			dep_airport = '$arrAirport', 
			dep_number = '$depNumber', 
			dep_time = '$depTime', 
			walk_alone = $walk, 
			allergies = '$allergy', 
			between_streets = '$streets', 
			shoe_size = '$shoeSize'";
	if (isset($file) && !empty($file)) {
		$sql .= ",file = '$file'";
	}
	
	//echo $sql; exit;
	if (!@mysql_query($sql)) {
		//echo $sql . "<br />" . mysql_error(); exit;
		$error = "There was an error adding the participant. Please try again.";
		header("Location: chidon/register_" . $gender . ".php?id=" . $id . "&error=" . urlencode($error));
		exit;
	} else {
		//mail to th chidon office
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Chidon Tzivos Hashem <chidon@tzivoshashem.org>' . "\r\n";
		
		$to = "chidon@tzivoshashem.org";
		$subject = "Participant added to Chidon";
		$msg = "<b>$name</b> has just been added as a <b>$type</b> to the Chidon.";
		@mail($to, $subject, $msg, $headers);
		
		$str = "Location: chidon/register_" . $gender . ".php?id=" . $id . "&success=1";
		header($str);
		exit;
	}
} else if (isset($_POST['total']) && $_POST['total'] > 0) {
*/
if ($_POST['action'] == 'pay') {
	//we are paying for one or many participants
	$card_num = $_POST['ccnum'];
	$exp_date = $_POST['mm'] . $_POST['yy'];
	$amount = $_POST['total'];
	$description = "Chidon " . $year . " payment from " . $school;
	$first_name = '';
	$last_name = $_POST['ccname'];
	$address = '';
	$state = '';
	$zip = $_POST['zcode'];
	
	if (empty($card_num) || empty($exp_date) || empty($amount) || empty($last_name) || empty($zip)) {
		$error = "You have not entered all information needed to process the credit card. Please try again.";
	}
	
	if (empty($error)) {				
		//check for spammers    
		require 'check_for_spammers.php';
		
		//send through authorize.net
		require_once 'authorize.php';
		
		if (isset($response_array) && !empty($response_array)) {
			if ($response_array[0] == 1) {	//success
				
				$paid = true;
				$approval = $response_array[3] . ':' . 
							$response_array[4] . ':' . 
							$response_array[6] . ':' . 
							$response_array[9];
			
				$to = $_POST['email'];
				$subject = "Your purchase for the Chidon 5776";
				$message = "Thank you for your payment of $" . $response_array[9] . ". 
					Your credit card transaction # is: " . $response_array[6];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: Chidon Tzivos Hashem <chidon@tzivoshashem.org>' . "\r\n";
				$headers .= 'CC: chidon@tzivoshashem.org' . "\r\n";
				$headers .= 'Reply to: chidon@tzivoshashem.org' . "\r\n";
				@mail($to, $subject, $message, $headers);
				
				//save to database
				if (isset($_POST['chapReg'])) {
					$sql = "update chidon_schools set 
							full_program = 1 
							where chidon_schools_id = " . $id;
					@mysql_query($sql);
				}
				if (isset($_POST['chapShirt'])) {
					$sql = "update chidon_schools set 
							sweater = 1, 
							s_size = '" . $_POST['chapSize'] . "' 
							where chidon_schools_id = " . $id;
					@mysql_query($sql);
				}

				foreach ($_POST['pay'] as $rid => $val) {
					$sql = "update chidon_reg set paid = 1, 
							approval = '" . $approval . "' 
							where chidon_reg_id = " . $rid;
					if (!@mysql_query($sql)) {
						$error = "There was an error applying your payment to our system. Please contact Tzivos Hashem.";
					}
				}
			} else {
				$error = $response_array[3];
			}
		} else {
			$error = "There was an error processing your payment.";
		}							
	}
	if (empty($error)) {
		$str = "Location: chidon/register_" . $gender . ".php?id=" . $id . "&success=1&paid=1";
		header($str);
		exit;
	} else {
		header("Location: chidon/register_" . $gender . ".php?id=" . $id . "&error=" . urlencode($error));
		exit;
	}
}
?>