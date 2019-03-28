<?
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

$sql = "select school_id from users where user_id = " . $user_id;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc($result);
$school_id = $row['school_id'];

$amount = 50;
$jd = unixtojd();
if ($jd > 2458011) $amount = 55; //September 14, 2017

/*
switch ($school_id) {
	case 61: 
		if ($jd > $chaiElul) 
			$amount = 50;
		else 
			$amount = 45;
		break;
	case 5:
	case 42:
	case 63:
	case 263:
		if ($jd > 2457269) 
			$amount = 50;
		break;
	case 3:
	case 49:
	case 81:
	case 185:
	case 192:
		if ($jd > 2457273) 
			$amount = 50;
		break;
	case 7:
	case 9:
	case 30:
	case 45:
	case 60:
	case 89:
		if ($jd > 2457274) 
			$amount = 50;
		break;
	case 2:
		if ($jd > 2457275) 
			$amount = 50;
		break;
	case 58:
	case 106:
	case 194:
		if ($jd > 2457276) 
			$amount = 50;
		break;
	case 89:
	case 176:
		if ($jd > 2457277) 
			$amount = 50;
		break;
	case 4:
	case 84:
	case 21:
		if ($jd > 2457278) 
			$amount = 50;
		break;	
	case 162: 
		if ($jd > 2457304) 
			$amount = 50;
		break;
	case 54:
		if ($jd > 2457309) 
			$amount = 50;
		break;
	case 80:
		if ($jd > 2457320)
			$amount = 50;
		break;
	case 55:
		$amount = 40;
		if ($jd > 2457443)
			$amount = 50;
		break;
	default: 
		if ($jd > $chaiElul) 
			$amount = 50;
		break;
}
*/
echo $amount;
?>