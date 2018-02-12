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
//print_r($_POST);
//print_r($_FILES);
echo "</pre>";
//exit;

$year = 5777;
$school = 61;
/*
if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
	chdir('images/staff');
	if (move_uploaded_file($_FILES['photo']['tmp_name'], $_FILES['photo']['name'])) {
		$_POST['file'] = $_FILES['photo']['name'];
	}
	chdir('../../');
}
*/
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
	if ($_SESSION['type'] == 'new') $p->sendConfEmail(); 
	$toRegister = array();
	$extraShipping = array();
	$processPayment = true;
	
	if (isset($_POST['addChildren']) && $_POST['addChildren'] > 0) {
		
		$sql = "select * from admins where admin_id = " . $p->getAdminID();
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		
		//create admin object
		require 'classes/admin.php';
		$parent = new Admin($row);
		
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
			$firsth = mysql_real_escape_string($_POST['fnameh'][$i]);
			$lasth	= mysql_real_escape_string($_POST['lnameh'][$i]);
			if (isset($_POST['pp'][$i])) $pushka = (int)mysql_real_escape_string($_POST['pp'][$i]);
			
			$user = new NewSoldier($parent, $first, $last, $dob, $gender, $school, $grade, $firsth, $lasth);
			if ($user->create()) {
				$userID = $user->getUserID();
				$toRegister[] = $userID;
				if (isset($_POST['sup' . ($i+1)])) $extraShipping[] = $userID;
				//update user photo
				if (isset($_FILES['photo']['name'][$i])) {
					$photo['name'] = $_FILES['photo']['name'][$i];
					$photo['type'] = $_FILES['photo']['type'][$i];
					$photo['tmp_name'] = $_FILES['photo']['tmp_name'][$i];
					$photo['error'] = $_FILES['photo']['error'][$i];
					$photo['size'] = $_FILES['photo']['size'][$i];
					$fileID = addFile($photo);
					$sql = "update users set user_photo_id = $fileID where user_id = $userID";
					mysql_query($sql);
				}
				if (isset($pushka)) {
					$sql = "update users set pushka = " . $pushka . " where user_id = " . $userID;
					mysql_query($sql);
				}
			} else {
				$processPayment = false;
				break;
			}
		}
	}
	
	if (isset($_POST['children'])) {
		foreach ($_POST['children'] as $child) {
			$toRegister[] = $child;
			if (isset($_POST['pp'][$child])) {
				$pushka = $_POST['pp'][$child];
				$sql = "udate users set pushka = " . $pushka . " where user_id = " . $child;
				mysql_query($sql);
			}
		}
	}
	
	if ($processPayment) {
		//put through payment
		$card_num = $_POST['ccnum'];
		$exp_date = $_POST['ccmm'] . $_POST['ccyy'];
		$amount = $_POST['total'];
		$description = "MyShliach registration for " . $year;
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
			include 'check_for_spammers.php';
			
			//send through authorize.net
			require_once 'authorize.php';
			
			if (isset($response_array) && !empty($response_array)) {
				if ($response_array[0] == 1) {	//success
					$approval = $response_array[3] . ':' . 
								$response_array[4] . ':' . 
								$response_array[6] . ':' . 
								$response_array[9];	
					//$approval = 'approved';			
					//update registration table
					$whatsapp = $_POST['whatsapp'];
					$tutorial = $_POST['tutorial'];
					$chavrusaHe = isset($_POST['chavrusaHe']) ? 1 : 0;
					$chavrusaEn = isset($_POST['chavrusaEn']) ? 1 : 0;
					$library = isset($_POST['library']) ? 1 : 0;
					$birthday = isset($_POST['birthday']) ? 1 : 0;
					$mishmor = isset($_POST['mishmor']) ? 1 : 0;
					
					//$sql = "update users set user_registered = now() where user_id in (" . implode(',', $toRegister) . ")";
					//@mysql_query($sql);
					
					//update registration table
					$sql = "insert into registration 
							set description = '" . $description . "', 
							approval = '" . $approval . "', 
							year = " . $year . ", 
							school_id = $school, 
							admin_id = " . $p->getAdminID() . ", 
							whatsapp = $whatsapp, 
							tutorial = $tutorial, 
							chavrusaHe = $chavrusaHe, 
							chavrusaEn = $chavrusaEn, 
							library = $library, 
							birthday = $birthday, 
							mishmor = $mishmor, 
							users = '" . implode(',', $toRegister) . "', 
							extra_shipping = '" . implode(',', $extraShipping) . "'";
					@mysql_query($sql);	
								
					//update transactions table
					$sql = "insert into transactions 
							set response = '" . $approval . "', 
							trans_date = now(), 
							description = '" . $description . '->' . implode(',', $toRegister) . "', 
							amount = " . $amount . ", 
							last = '" . $last_name . "', 
							zip = '" . $zip . "'";
					@mysql_query($sql);	
					
					$registered = true;
					require_once 'class.userRegister.php';
					$u = new UserRegister($toRegister, $year, $p->getAdminID());
					$registered = $u->register();
					if ($registered) {
						$msg = "Your registration has been successful. You should be receiving an email confirmation shortly.";
					} else {
						$msg = "There was an error registering your children. Please contact Tzivos Hashem.";
					}
					 
					$to = $data['admin_email'];
					$subject = "Chayolei Tzivos Hashem Registration.";
					$message = "Thank you for your payment of $" . $response_array[9] . ". 
						Your credit card transaction # is: " . $response_array[6];
					if ($registered) {
						$message .= ". Your child(ren) have been successfully setup and registered for " . $year . ".";
					} else {
						$message .= ". However, there seems to have been a problem registering your children. Please contact Tzivos Hashem ASAP.";
					} 
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= 'From: Tzivos Hashem <cth@tzivoshashem.org>' . "\r\n";
					$headers .= 'CC: cth@tzivoshashem.org' . "\r\n";
					$headers .= 'Reply to: cth@tzivoshashem.org' . "\r\n";
					@mail($to, $subject, $message, $headers);
					 						
				} else {
					$error = '';
					if ($_POST['addChildren'] > 0) $error .= "Your child(ren) have been successfully setup however they were not registered because ";
					$error .= $response_array[3];
				}
			} else {
				$error = "There was an error processing your payment.";
			}
		}
	} else {
		$error = "Your admin account was created successfully. However, there was an error creating your child(ren)'s account(s). You can login at mashpia.com to manage your account as well as add / edit children.";
	}	
} else {
	$error = "There was an error creating your admin account.";
}
if (isset($error)) {
	header("Location: https://www.mashpia.com/shliachRegister.php?load=1&error=" . urlencode($error));
	exit;
} else if (isset($msg)) {
	header("Location: https://www.mashpia.com/shliachRegister.php?load=1&msg=" . urlencode($msg));
	exit;
} else {
	header("Location: https://www.mashpia.com/shliachRegister.php?load=1");
	exit;
}
?>