<?
require '../../../db.php';

$reg_id = mysql_real_escape_string($_POST['id']);
$grade = mysql_real_escape_string($_POST['grade']);
$size = mysql_real_escape_string($_POST['size']);
$fname = mysql_real_escape_string($_POST['fname']);
$lname = mysql_real_escape_string($_POST['lname']);
$hname = mysql_real_escape_string($_POST['hname']);
$pname = mysql_real_escape_string($_POST['pname']);
$pemail = mysql_real_escape_string($_POST['pemail']);
$fcell = mysql_real_escape_string($_POST['fcell']);
$mcell = mysql_real_escape_string($_POST['mcell']);
$pic = mysql_real_escape_string($_POST['pic']);
$whatsapp = mysql_real_escape_string($_POST['whatsapp']);
$walkAlone = mysql_real_escape_string($_POST['walkAlone']);
$help = mysql_real_escape_string($_POST['help']);
$chfamily = mysql_real_escape_string($_POST['chfamily']);
$chaddress = mysql_real_escape_string($_POST['chaddress']);
$chphone = mysql_real_escape_string($_POST['chphone']);
$allergy = mysql_real_escape_string($_POST['allergy']);
$shoeSize = mysql_real_escape_string($_POST['shoeSize']);
$streets = mysql_real_escape_string($_POST['streets']);

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

$sql = "update chidon_reg 
		set grade = '" . $grade . "', 
		name = '" . $fname . "', 
		last_name = '" . $lname . "', 
		hname = '" . $hname . "', 
		book = '" . $book . "', 
		parent_name = '" . $pname . "', 
		parent_email = '" . $pemail . "', 
		parent_cell = '" . $fcell . "', 
		parent_cell2 = '" . $mcell . "', 
		help = " . $help . ", 
		family = '" . $chfamily . "', 
		address = '" . $chaddress . "', 
		phone = '" . $chphone . "', 
		whatsapp = " . $whatsapp . ", 
		walk_alone = " . $walkAlone . ", 
		allergies = '" . $allergy . "', 
		file = '" . $pic . "', 
		shoe_size = '" . $shoeSize . "', 
		between_streets = '" . $streets . "'";
//if ($_POST['free']) {
//	$sql .= ", paid = 1";
//}
$sql .= " where chidon_reg_id = " . $reg_id;
if (@mysql_query($sql)) {
	echo 1;
} else {
	//echo $sql . "<br />" . mysql_error();
	@mail('naftolir@gmail.com', 'Error in chidon mobile site', $sql . "<br />" . mysql_error());
	echo 0;
}
?>