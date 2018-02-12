<?
require '../db.php';

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

if (isset($_POST['action'])) {
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
	$file = $_POST['filePic'];
	if ($file == '/mobile/reg/images/addphoto.png') $file = '';
}

if ($_POST['action'] == 'edit') {
	$regID 		=	$_POST['regID'];
	$sql = "update chidon_reg 
			set grade = '$grade', 
			type = '$type', 
			name = '$name', 
			last_name = '$lname', 
			hname = '$hname', 
			book = '$book', 
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
		$sql .= ", file = '$file'";
	}
	$sql .= " where chidon_reg_id = $regID";
	
	if (!@mysql_query($sql)) {
		$error .= "Error updating information.";
		header("Location: register_" . $gender . ".php?id=" . $regID . "&edit=1&error=" . urlencode($error));
		exit;
	} else {
		if (!empty($error)) $error .= "<br />Everything else was ";
		$error .= "Successfully updated.";
		header("Location: register_" . $gender . ".php?id=" . $regID . "&edit=1&error=" . urlencode($error));
		exit;
	}	
} else if ($_POST['action'] == 'add') {
	$error = '';
	$id = $_POST['schoolID'];
	$year = 5776;
	$fee = 115;
	$paid = false;
	$gender = $_POST['gender'];
	
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
	
	if (!@mysql_query($sql)) {
		//echo $sql . "<br />" . mysql_error(); exit;
		$error = "There was an error adding the participant. Please try again.";
		header("Location: register_" . $gender . ".php?id=" . $id . "&error=" . urlencode($error));
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
		
		$str = "Location: register_" . $gender . ".php?id=" . $id . "&success=1";
		header($str);
		exit;
	}
}
