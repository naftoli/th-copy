<?
session_start();
require 'db.php';
require_once('file_save.php');

function clean(&$value) {
	if (is_array($value)) {
		foreach ($value as $k => &$v) {
			clean($v);
		}
	} else {
		$value = mysql_real_escape_string($value);
	}
}
clean($_POST);

$_SESSION['post'] = $_POST;

echo "<pre>";
print_r( $_POST );
echo "</pre>";

$year = 5776;
$school = 61;

//find out if we are updating existing user or creating new user
if (isset($_POST['password2'])) {
	$_SESSION['type'] = 'new';
	require 'newClasses/newParent.php';
	$p = new NewParent();
	$p->setAsShliach();
} else {
	$_SESSION['type'] = 'update';
	require 'newClasses/updateParent.php';
	$p = new UpdateParent();	
}

if ($p->action($_POST)) {
	$p->sendConfEmail();
	$toRegister = array();
	$extraShipping = array();
	$processPayment = true;
	
	if (isset($_POST['addChildren']) && $_POST['addChildren'] > 0) {
		
		$sql = "select * from admins where admin_id = " . $p->getAdminID();
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		
		//create admin object
		require 'classes/admin.php';
		$parent = new \classes\admin($row);
		
		require 'newClasses/newSoldier.php';
		$add = $_POST['addChildren'];

		for ($i = 0; $i < $add; $i++) {
			$gender	= mysql_real_escape_string($_POST['gender'][$i+1]);
			$first 	= mysql_real_escape_string($_POST['fname'][$i]);
			$last  	= mysql_real_escape_string($_POST['lname'][$i]);
			$mm		= mysql_real_escape_string($_POST['mm'][$i]);
			$dd		= mysql_real_escape_string($_POST['dd'][$i]);
			$yy		= mysql_real_escape_string($_POST['yy'][$i]);
			$dob 	= $yy . '-' . $mm . '-' . $dd;
			$grade 	= mysql_real_escape_string($_POST['grade'][$i]);
			//$firsth = mysql_real_escape_string($_POST['fnameh'][$i]);
			//$lasth	= mysql_real_escape_string($_POST['lnameh'][$i]);
		}
	}
}
?>