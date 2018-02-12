<?
 ini_set(upload_max_filesize,'10MB');
//$admin_auth = array('school'); 
require('../db.php');

function clean(&$value) {
	if (is_array($value)) {
		foreach ($value as $k => &$v) {
			clean($v);
		}
	} else {
		$value = mysql_real_escape_string($value);
	}
}

if (isset($_POST['submit'])) {
	//sanitize values
	clean($_POST);
	$error = '';
	
	$regID 		=	$_POST['regID'];
	$gender 	=	$_POST['gender'];
	$grade 		= 	$_POST['grade'];
	$type 		= 	$_POST['type'];
	$name 		= 	$_POST['name'];
	$lname		=	$_POST['lname'];
	$hname 		= 	trim($_POST['hname']) . ' ' . trim($_POST['hname_last']);
	$mark1 		= 	($_POST['mark1'] == '' ? 0 : $_POST['mark1']);
	$mark2 		= 	($_POST['mark2'] == '' ? 0 : $_POST['mark2']);
	$mark3 		= 	($_POST['mark3'] == '' ? 0 : $_POST['mark3']);
	$bonus 		= 	($_POST['bonus'] == '' ? 0 : $_POST['bonus']);
	$help 		= 	($_POST['help'] == 'n' ? 0 : 1);
	$family		=	$_POST['family'];
	$address	=	$_POST['address'];
	$phone		= 	$_POST['phone'];
	$notes 		=	$_POST['notes'];
	$parentName = 	$_POST['parentName'];
	$parentEmail=	$_POST['parentEmail'];
	$fatherCell	=	$_POST['parentCell'];
	$motherCell = 	$_POST['motherCell'];
	$size 		=	$_POST['size'];
	$arrAirport =	$_POST['arrAirport'];
	$arrNumber 	= 	$_POST['arrNumber'];
	$arrTime 	= 	$_POST['arrTime'];
	$depAirport = 	$_POST['depAirport'];
	$depNumber 	=	$_POST['depNumber'];
	$depTime 	= 	$_POST['depTime'];
	$walk 		= 	$_POST['walk'];
	$allergy 	=	$_POST['allergy'];
	$streets 	=	$_POST['streets'];
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
	
	//update file
	/*
	if (isset($_FILES['photo']['error'])) {
		if ($_FILES['photo']['error'] == 1) {
			$error .= "Your photo could not be uploaded because the file size is too big. Please reduce the size of your file and try again.";
		}
	}
	if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
		chdir('photos');
		if (move_uploaded_file($_FILES['photo']['tmp_name'], $_FILES['photo']['name'])) {
			$file = $_FILES['photo']['name'];
		}
		chdir('../');
	}
	 * 
	 */
	$file = $_POST['filePic'];
	if ($file == '/mobile/reg/images/addphoto.png') $file = '';
	
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
	//echo $sql; exit;
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
}
?>
