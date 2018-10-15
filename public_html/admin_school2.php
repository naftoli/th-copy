<? $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
	//define("AUTHORIZE_NET_SANDBOX", true);
}
// redirect the user to https if they are using http
if (!isset($_SERVER['HTTPS'])) {
	$url = "https://mashpia.com" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['argv'][0];
	header("Location: $url");
}
// only schools can access this page
$admin_auth = array('school'); 
require('header.php'); // loads db.php, and login.php as well as a few other files

// Load the authorize.net api
require_once('classes/authorize/CustomerProfile.php');
use \classes\authorize\CustomerProfile;

require_once('classes/authorize/PaymentProfile.php');
use \classes\authorize\PaymentProfile;

// get the school_store from the Get headers
$school_store = gri('school_store'); // this varable is not used in the next two require_once calls directly

// set the ui type
$ui_type = 'school';
require_once('admin_ui.php');
require_once('file_save.php');

// cannot file source file for this function
$auth_mode = check_id_access();
$school_id = gri('school_id', -1); // get the school id from the GET headers

// if this is a school only allow editing
if ($auth_mode == 'school') 
{
	if (gr('action') != 'edit' && gr('action') != 'edit2') sgr('action', 'edit');
}
// and then load that action
$action = gr('action');
$edit_row = false; // the row that will be edited by the admin

// load the child types from the database
include("camps/includes/classes/child_type.php");
$child_types = array();
$sql = "SELECT * FROM child_types ORDER BY child_type_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$child_type = new child_type($row); // create a new object
	array_push($child_types, $child_type);
}

// if the action is not empty (cant it be?) then switch on the action
if (!empty($action)) { switch($action) {
	case 'add': // if the action is add then load a empty object with some sql and load it into the edit_row
		$result = mq(
			" SELECT -1 school_id, '' school_name, '' school_name_he, -1 inst_id, -1 school_makeup_id, "
		   ." '' school_settings, '' school_gender, NULL school_logo_id, NULL school_logo_kiosk_id, 0 school_no_logo, "
		   ." NULL school_file_id, '' school_address1, '' school_address2, '' school_city, '' school_state, "
		   ." '' school_postal, '' school_country, '' school_phone, 1 kiosk_print, '' shipping_method, '' shipping_first, "
		   ." '' shipping_last, '' shipping_phone, '' shipping_address1, '' shipping_address2, '' shipping_city, "
		   ." '' shipping_state, '' shipping_postal, '' shipping_country");
		$edit_row = mysql_fetch_assoc($result);
		break; // the obligitory break

	case 'add2': // add2 actually adds the institution to the database
		$name = gr('name'); // get the name from the request
		// see if we already have one
		$result = mq('SELECT 1 FROM schools WHERE school_name = ' . ms($name). ' AND inst_id = ' . gri('inst_id'));
		$school_settings = implode(',', gra('school_settings')); // oh, and sperate the school_settings
	
		if(mysql_num_rows($result)) { // if we already have a institution
			$message = T_('Unable to add new institution, this name is already used.'); // let the user know that that name exists
			// run a sql select with the data they provided
			$result = mq('SELECT -1 school_id, ' . ms($name) . ' school_name, '
				. ms(gr('name_he')) . ' school_name_he, ' . gri('inst_id') . ' inst_id, '
				. gri('school_makeup_id') . ' school_makeup_id, ' . ms($school_settings) . ' school_settings, '
				. ms(gr('school_gender')) . ' school_gender, NULL school_logo_id, NULL school_logo_kiosk_id, '
				. gri('school_no_logo', 0) . ' school_no_logo, NULL school_file_id, '
				. ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, '
				. ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, '
				. ms(gr('postal')) . ' school_postal, ' .  ms(gr('country')) . ' school_country, '
				. ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, '
				. ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, '
				. ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, '
				. ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, '
				. ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, '
				. ms(gr('shipping_state')) . ' shipping_state, ' .  ms(gr('shipping_postal')) . ' shipping_postal, '
				. ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . ' shipping_phone');
			$edit_row = mysql_fetch_assoc($result); // set the result to the current row on the editing table
			$action = 'add'; // and revert to add
		} else { // if this is a genuine new organization
			$logo = false; $logo_girls = false;
			if( isset($_FILES['logo']) ) {
				$logo = saveFile($_FILES['logo'], "schoolLogos/", $name . "_logo");
				$logo = $logo ? str_replace("schoolLogos/", "", $logo) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
			}
			
			if( isset($_FILES['logo_girls']) ) {
				$logo_girls = saveFile($_FILES['logo_girls'], "schoolLogos/", $name . "_logo_girls");
				$logo_girls = $logo_girls ? str_replace("schoolLogos/", "", $logo_girls) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
			}
			
			// removed school_logo_kiosk_id and $school_file_id from insert query
			mq('INSERT INTO schools SET school_name = ' . ms($name)
				. ', school_name_he = ' . ms(gr('name_he')) . ', inst_id = ' . gri('inst_id')
				. ', school_makeup_id = ' . gri('school_makeup_id', 0) . ', school_settings = ' . ms($school_settings)
				. ', school_gender = ' . ms(gr('school_gender')) . ", school_number = " . mysql_result(mq("(SELECT IFNULL(MAX(school_number), 0)+1 FROM schools schools_max)"), 0)
				. ", school_no_logo = " . gri('school_no_logo', 0) . ", school_address1 = " . ms(gr('address1'))
				. ', school_address2 = ' . ms(gr('address2')) . ', school_city = ' . ms(gr('city')) . ', school_state = ' . ms(gr('state')) . ', school_postal = ' . ms(gr('postal'))
				. ', school_country = ' . ms(gr('country')) . ', school_phone = ' . ms(gr('phone'))
				. ', kiosk_print = ' . gri('kiosk_print', 0) . ', shipping_method = ' . ms(gr('shipping_method'))
				. ', shipping_first = ' . ms(gr('shipping_first')) . ', shipping_last = ' . ms(gr('shipping_last'))
				. ', shipping_address1 = ' . ms(gr('shipping_address1')) . ', shipping_address2 = ' . ms(gr('shipping_address2'))
				. ', shipping_city = ' . ms(gr('shipping_city')) . ', shipping_state = ' . ms(gr('shipping_state')) . ', shipping_postal = '
				. ms(gr('shipping_postal')) . ', shipping_country = ' . ms(gr('shipping_country')) . ', shipping_phone = ' . ms(gr('shipping_phone')));
			//**************************************AUTHORIZE.NET API INTEGRATION********************************************
			
			// create the billing address info
			$billto = ["address" => gr('billing_address'), "city" => gr('billing_city'), "state" => gr('billing_state'), "zip" => gr('billing_postal')];
			// create the payment profile info
			$payment_profile = PaymentProfile::createBasicArray(gr('cc_number'), gr('cc_exp'), gr('cc_cvv'), $billto, true);
			
			// get the ID from the new school
			$id = mysql_insert_id();
			// get the email from the admin
			$email = mysql_fetch_assoc(mq("SELECT admin_email FROM admins WHERE admin_id = ".$admin_user['admin_id'].";"))['admin_email'];
			// create the payment profile
			$customer_profile = CustomerProfile::create("CTH_$id", $email, $name, $payment_profile);
			// if it is a valid payment profile, update the system. (only bad case is a duplicate which then returns an array)
			if ($customer_profile instanceof CustomerProfile) {
			  mq("UPDATE schools SET authorize_customer_profile_id = ". $customer_profile->customerProfileId . ", authorize_payment_profile_id = " . $customer_profile->paymentProfiles[0]["customerPaymentProfileId"] . " WHERE school_id = $id");
			  $message = T_('Institution added'); // note that it was added.
			} else {
			  $message = T_('Institution added: Payment information invalid'); // note that it was added.
			}
			// note that an institution was added
			
		}
		break;
	case 'delete': // delete an institution
			// delete all the files related to the school
			mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_id) WHERE school_id = $school_id");
			mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_kiosk_id) WHERE school_id = $school_id");
			mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");
			// delete the school row
			mq("DELETE FROM schools WHERE school_id = $school_id");
			// delete the login relationship with the admin
			mq("DELETE FROM admin_auths WHERE auth = 'school' AND id = $school_id");
			// let the user know that the delete commands where run
			$message = T_('Institution deleted');
			break;
	case 'edit':
		// load the information from the database for the given school_id
		// added the authorize.net feilds to be loaded
		$result = mq(
			" SELECT school_id, school_name, school_name_he, school_makeup_id, "
			." inst_id, school_settings, school_gender, logo, logo_girls, school_logo_kiosk_id, "
			." school_no_logo, school_file_id, school_address1, school_address2, school_city, "
			." school_state, school_postal, school_country, school_phone, cc_number, cc_exp, cc_cvv, "
			." authorize_customer_profile_id, authorize_payment_profile_id, kiosk_print, "
			."shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, "
			." shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, "
			." school_store, notes, shipping_requests, store_reset FROM schools WHERE school_id = $school_id"
		);
		// and set it to the edit row
		$edit_row = mysql_fetch_assoc($result);
		if ($edit_row['authorize_customer_profile_id']){
			$customer_profile = new CustomerProfile($edit_row['authorize_customer_profile_id']);
		} else {
			$customer_profile = false;
		}
		break;

	case 'edit2':
		if ($school_id == -1) break; // if there is no school id then quit now while we are ahead
		$name = gr('name'); // get the name from the school
		$inst_id = $admin_user['auth'] == 'super' ? gri('inst_id') : 'inst_id'; // if the user is an admin get the inst_id
		$school_settings = implode(',', gra('school_settings')); // get the school settings from the get/post request
		// get any schools with the new name
		$result = mq('SELECT 1 FROM schools WHERE school_name = ' . ms($name) . " AND inst_id = $inst_id AND school_id != $school_id");
	
		if( mysql_num_rows($result) ) { // if said institution exists
			$message = T_( 'Unable to edit institution, this name is already used.' ); // tell the user that names must be unique
			// added the authorize data to what should be retrived from the database
			$result = mq(
				'SELECT school_id, ' . ms($name) . ' school_name, ' . ms(gr('name_he')) . ' school_name_he, '
				. gri('school_makeup_id', -1) . ' school_makeup_id, ' . $inst_id . ' inst_id, ' . ms($school_settings) . ' school_settings, '
				. ms(gr('school_gender')) . ' school_gender, school_logo_id, school_logo_kiosk_id, ' . gri('school_no_logo', 0)
				. ' school_no_logo, school_file_id, ' .  ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, '
				.  ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, ' .  ms(gr('postal')) . ' school_postal, ' .  ms(gr('country'))
				. ' school_country, ' .  ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, '
				.  ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, ' .   ms(gr('shipping_method')) . ' shipping_method, '
				. ms(gr('shipping_first')) . ' shipping_first, ' .  ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, '
				.  ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, ' .  ms(gr('shipping_state')) . ' shipping_state, '
				.  ms(gr('shipping_postal')) . ' shipping_postal, ' .  ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . " shipping_phone, "
				." authorize_customer_profile_id, authorize_payment_profile_id, school_store, store_reset FROM schools WHERE school_id = $school_id"
			);
			$edit_row = mysql_fetch_assoc($result); // get the generated edit_row
			$action = 'edit'; // set the action to edit
		} else { // the user is not using another institutions name			
			$logo = false; $logo_girls = false;
			if( isset($_FILES['logo']) ) {
				$logo = saveFile($_FILES['logo'], "schoolLogos/", $name . "_logo");
				$logo = $logo ? str_replace("schoolLogos/", "", $logo) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
			}
			
			if( isset($_FILES['logo_girls']) ) {
				$logo_girls = saveFile($_FILES['logo_girls'], "schoolLogos/", $name . "_logo_girls");
				$logo_girls = $logo_girls ? str_replace("schoolLogos/", "", $logo_girls) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
			}
			
			// legacy kiosk logo...
			$school_logo_kiosk_id = gri('logo_kiosk_delete', 0) ? 'NULL' : 'school_logo_kiosk_id';
			if(isset($_FILES['logo_kiosk'])) $school_logo_kiosk_id = addFile($_FILES['logo_kiosk'], $school_logo_kiosk_id);
			if($school_logo_kiosk_id !== 'school_logo_kiosk_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_kiosk_id) WHERE school_id = $school_id");
			
			// and the schools file
			$school_file_id = gri('file_delete', 0) ? 'NULL' : 'school_file_id';
			if(isset($_FILES['file'])) $school_file_id = addFile($_FILES['file'], $school_file_id);
	  
			if($school_file_id !== 'school_file_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");
      
            $store_reset = gr('store_reset');
            $store_reset = $store_reset ? unixtojd( strtotime( $store_reset ) ) : 'NULL';
			// update the school
			mq('UPDATE schools SET school_name = ' . ms($name) . ', school_name_he = ' . ms(gr('name_he')) . ', '
				." school_makeup_id = " . gri('school_makeup_id', -1) 	. ", "
				." inst_id = " 			. $inst_id 						. ", "
				." school_settings = " 	. ms($school_settings) 			. ", "
				." school_gender = " 	. ms(gr('school_gender')) 		. ", "
				." school_logo_kiosk_id = $school_logo_kiosk_id, "
				." school_no_logo = " 	. gri('school_no_logo', 0)		. ", "
				." school_file_id = $school_file_id, "
				." school_address1 = " 	. ms(gr('address1')) 			. ", "
				." school_address2 = " 	. ms(gr('address2')) 			. ", "
				." school_city = " 		. ms(gr('city')) 				. ", "
				." school_state = " 	. ms(gr('state')) 				. ", "
				." school_postal = " 	. ms(gr('postal')) 				. ", "
				." school_country = " 	. ms(gr('country')) 			. ", "
				." school_phone = " 	. ms(gr('phone')) 				. ", "
				." kiosk_print = " 		. gri('kiosk_print', 0) 		. ", "
				." shipping_method = " 	. ms(gr('shipping_method')) 	. ", "
				." shipping_first = " 	. ms(gr('shipping_first')) 		. ", "
				." shipping_last = " 	. ms(gr('shipping_last'))  		. ", "
				." shipping_address1 = ". ms(gr('shipping_address1')) 	. ", "
				." shipping_address2 = ". ms(gr('shipping_address2')) 	. ", "
				." shipping_city = " 	. ms(gr('shipping_city')) 		. ", "
				." shipping_state = " 	. ms(gr('shipping_state')) 		. ", "
				." shipping_postal = " 	. ms(gr('shipping_postal')) 	. ", "
				." shipping_country = " . ms(gr('shipping_country')) 	. ", "
				." shipping_phone = " 	. ms(gr('shipping_phone')) 		. ", "
                ." school_store=" 		. gri('school_store', 0) 		. ", "
                ." store_reset="   . $store_reset             . ", "
				." shipping_requests=" 	. ms(gr('shipping_requests', null)) . ", "
				." notes = " 			. ms(gr('notes', null)) 		. " "
				.($logo ? ", logo = '$logo' " : "")
				.($logo_girls ? ", logo_girls = '$logo_girls' " : "")
				." WHERE school_id = $school_id");
		  
		// if there is a CC update that too.
			if(strlen(ms(gr('cc_number')))>2) {
				if(!!gr('customerProfileId')){ // if there is a customer profile update that
					$payment_profile = new PaymentProfile(gr('paymentProfileId'), gr('customerProfileId'));
					$payment_profile->cardNumber = gr('cc_number');
					$payment_profile->expirationDate = gr('cc_exp');
					$payment_profile->cardCode = gr('cc_cvv');
					
					if (gr('billing_address') && gr('billing_city') && gr('billing_state') && gr('billing_postal')) {
						$billto = ["address" => gr('billing_address'), "city" => gr('billing_city'), "state" => gr('billing_state'), "zip" => gr('billing_postal')];
					} else if (gr('billing_postal')) {
						$billto = ["zip" => gr('billing_postal')];
					} else {
						$billto = null;
					}
					
					$payment_profile->billTo = $billto;
					// submit for update to the api
					$errors = $payment_profile->update();
					
					if ($errors) {
						$errorCode = $errors['messages']['message'][0]['code'];
						$errorText = $errors['messages']['message'][0]['text'];
						// display error
						header("Location: admin_school2.php?school_id=$school_id&action=edit&message=Credit Card Error ($errorCode): $errorText");
						//echo "<script>alert('Please note that your Credit Card has NOT been updated.\\n\\nError ($errorCode): $errorText');</script>";
						$action = "edit";
						
					}
			} else { // if there is no existing customer profile, create one
				// create the billing address info
				if (gr('billing_address') && gr('billing_city') && gr('billing_state') && gr('billing_postal')) {
					$billto = ["address" => gr('billing_address'), "city" => gr('billing_city'), "state" => gr('billing_state'), "zip" => gr('billing_postal')];
				} else if (gr('billing_postal')) {
					$billto = ["zip" => gr('billing_postal')];
				} else {
					$billto = null;
				}
				
				// create the payment profile info
				$payment_profile = PaymentProfile::createBasicArray(gr('cc_number'), gr('cc_exp'), gr('cc_cvv'), $billto, true);

				// get the email from the admin
				$email = mysql_fetch_assoc(mq("SELECT admin_email FROM admins WHERE admin_id = ".$admin_user['admin_id'].";"))['admin_email'];
				// create the payment profile
				$customer_profile = CustomerProfile::create("CTH_$school_id", $email, $name, $payment_profile);
				// if it is a valid payment profile, update the system. (only bad case is a duplicate whic
				mq("UPDATE schools SET "
					."authorize_customer_profile_id = " . $customer_profile->customerProfileId . ", "
					."authorize_payment_profile_id = " . $customer_profile->paymentProfiles[0]["customerPaymentProfileId"] . " "
					."WHERE school_id = $school_id");
			}
		}
		// update child types where required - Form removed from rendered result
//		if ( isset( $_POST['child_type_id'] ) ) {
//			$sql = 'update school_child_types set child_type_id = ' . $_POST['child_type_id'] . ' where school_id = ' . $school_id;
//			mq($sql);
//		}

		//$message = T_('Institution edited');
		//header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
		//exit();
		//header("Location: admin_school.php"); // redirect to the main page with no editing options (boroken);
		//exit;
		}
	break;
	// if the user is attempting to export the school
	case 'export_schools':
		require_once('export.php'); // load the exporting funciton and call it on the sql query
		export('SELECT school_id, school_name, school_name_he, inst_name institution_type, school_number, school_gender, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, school_store FROM schools LEFT JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, inst_id, school_name, school_id', 'schools');
		exit;
		break;
// same for export_teachers
	case 'export_teachers':
		require_once('export.php');
		export('SELECT school_id, school_name, inst_name institution_type, school_number, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, class_id, class_grade, class_sub, class_teacher, school_store FROM schools JOIN classes USING (school_id) LEFT JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, inst_id, school_name, school_id,  class_grade, class_sub, class_id', 'teachers');
		exit;
		break;
// same again for users
	case 'export_users':
		require_once('export.php');
		export('SELECT school_id, school_name, inst_name institution_type, school_number, class_id, class_grade, class_sub, class_teacher, user_id, username, email, first, last, first_he, last_he, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, gender, user_start_date, user_registered, dob FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (class_id, school_id) LEFT JOIN institutions USING (inst_id) WHERE school_id IS NOT NULL' . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name, school_id, class_grade, class_sub, class_id, last, first, username, user_id', 'soldiers');
		exit;
		break;
// if it is something else then just tell the user to stop messing with us
	default:
		user_error('unknown action', E_USER_ERROR);
		break;
	} // end switch
}
// render the UI now that post is handled
?>

<!DOCTYPE html>

<html DIR="<?=$dir?>">
	<head>
		<title><?=($action == 'edit' ? T_('Base Profile') : T_('Bases')), ' - ', T_('Tzivos Hashem Management System')?></title>
		<link href="/admin_styles.css" rel="stylesheet" type="text/css">
		<link href="/styles/admin/forms.css"  rel="stylesheet" type="text/css"/>
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="/js/utils/cc_validate.js"></script>
		<script src="/js/admin/admin_school.php.js"></script>
		<style>
			div#school_logos img {
				display: inline-block;	width: 20%;	margin-top: 15px;
			}
			div#school_logos div.img_options {
				display: inline-block; width: 74%; margin-left: 5%; margin-top: 15px; vertical-align: top;
			}
		</style>
	</head>
	
	<body>
		
		<?php include('admin_header.php');?>
		
		<div>
			<div>
				<div class="sub_menu">
					<?php if(!empty($message)) { ?>
						<h2><?=$message?></h2>
					<?php } ?>
				</div>
				
				<h1><?=($action == 'edit' ? T_('Base Profile') : T_('Bases'))?></h1>

				<div class="ui_body">
					<div class="ui_menu">
						<?ui_menu();?>
					</div>
					
					<div>
						<p><?=mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'base_mission'"), 0);?></p>
						<?php if(!!$edit_row) { // is there a row to edit?
							if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) { ?>
								<a href="admin_school2.php"><?=T_('Cancel')?></a><br/>
							<? } // show a cancel button if the user is a super or has many schools
							if (gr('message')){ ?>
								<h2>Error Messages</h2>
								<p><center><? echo htmlspecialchars(gr('message'))?></center></p>
							<? } // end displaying message from get request ?>

							<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
								<p class="rows">
									<h2><?=T_('Base Information')?></h2>
									<input type="hidden" name="action" value="<?=$action?>2"/>
									<input type="hidden" name="school_id" value="<?=$edit_row['school_id']?>"/>
									<div class="input_group input_half">
										<label><?=T_('Name')?><br/><input type="text" name="name" value="<?=es($edit_row['school_name'])?>"></label>
									</div>
									<div class="input_group input_half">
										<label><?=T_('Hebrew Name')?><br/>
											<input type="text" name="name_he" value="<?=es($edit_row['school_name_he'])?>">
											(<?=T_('This is how it will appear on school banner')?>)
										</label>
									</div>
									<?php if($admin_user['auth'] == 'super') {
										$institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name'); ?>
										<div class="input_group input_half">
											<label><?=T_('Institution type')?><br/>
												<select name="inst_id">
												<? while($row = mysql_fetch_assoc($institution_result)): ?>
													<option VALUE="<?=$row['inst_id']?>" <?=$row['inst_id'] == $edit_row['inst_id'] ? 'SELECTED' : '' ?>/>
														<?=es($row['inst_name'])?>
													</option>
												<? endwhile; ?>
												</select>
											</label>
										</div>
									<?php } ?>
									<? $school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name'); ?>
									<div class="input_group input_half">
										<label><?=T_('Gender')?></label><br/>
										<!-- School Gender Options -->
										<label>
											<input type="radio" name="school_gender" value="M" <?=$edit_row['school_gender'] == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?>
										</label>
										<label>
											<input type="radio" name="school_gender" value="F" <?=$edit_row['school_gender'] == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?>
										</label>
										<label>
											<input type="radio" name="school_gender" value="B" <?=$edit_row['school_gender'] == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?>
										</label>
                                    </div>
                                    <div class="input_group input_half">
                                        <label><?=T_('Store Miles Start From:')?></label>
                                        <?php $store_reset = $edit_row['store_reset'] ? date( 'Y-m-d', jdtounix( $edit_row['store_reset'] ) ) : ""; ?>
                                        <input type='date' id='store_reset' name='store_reset' <?= $store_reset ? "" : "disabled" ?>
                                            value="<?= $store_reset ? $store_reset : "" ?>"/><br/>
                                        <input type='checkbox' id='toggle_store_reset' <?= $store_reset ? "" : "checked" ?> />
                                        <span>Allow soldiers to spend all their miles.</span>
                                    </div>
									<div class="input_group input_full">
											<!-- Address information -->
										<label><?=T_('Address 1')?><br/>
											<input type="text" name="address1" value="<?=es($edit_row['school_address1'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_full">
										<label><?=T_('Address 2')?><br/>
											<input type="text" name="address2" value="<?=es($edit_row['school_address2'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_third">
										<label><?=T_('City')?><br/>
											<input type="text" name="city" value="<?=es($edit_row['school_city'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_third">
										<label>
											<?=T_('State/Province')?><br/>
											<input type="text" name="state" value="<?=es($edit_row['school_state'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_third">
										<label><?=T_('Zip/Postal code')?><br/>
											<input type="text" name="postal" value="<?=es($edit_row['school_postal'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_half">
										<label><?=T_('Country')?><br/>
											<input type="text" name="country" value="<?=es($edit_row['school_country'])?>" maxlength=255>
										</label>
									</div>
									<div class="input_group input_half">
										<label><?=T_('Phone')?><br/>
											<input type="text" name="phone" value="<?=es($edit_row['school_phone'])?>" maxlength=255>
										</label>
									</div>
										<h2>Logo</h2>
										<div id="school_logos">
											<?php $logo = isset($edit_row['logo']) ? $edit_row['logo'] : "TH-Blank%20Logo.gif"?>
											<img src="schoolLogos/<?=$logo?>" alt="logo" id="logo"/>
											<div class="img_options">
												<span class="girls_school_logo" <?=$edit_row['school_gender'] == "B" ? "" : "style='display: none;'"?>>
													<strong>Boys Logo:</strong><br/>
												</span>
												<?=T_('PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.')?><br/><br/>
												<input type="file" name="logo" class="file"><br/>
												<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<br/>
											</div>
											<div class="girls_school_logo" <?=$edit_row['school_gender'] == "B" ? "" : "style='display: none;'"?>>
												<hr style="display: block"/>
												<img src="schoolLogos/<?=isset($edit_row['logo_girls']) ? $edit_row['logo_girls'] : $logo?>" alt="logo_girls" id="logo_girls"/>
												<div class="img_options">
													<strong>Girls Logo:</strong><br/>
													<?=T_('PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.')?><br/><br/>
													<input type="file" name="logo_girls" class="file"><br/>
													<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<br/>
												</div>
											</div>
										</div>
										
										<h2><?=T_('Shipping Info')?></h2>
										<?=T_('Shipping Method')?><br/>
										<label>
											<input type="radio" name="shipping_method" value="pickup" <?=$edit_row['shipping_method'] == 'pickup' ? 'CHECKED' : ''?>><?=T_('Pickup')?>
										</label>
										<label>
											<input type="radio" name="shipping_method" value="deliver" <?=$edit_row['shipping_method'] == 'deliver' ? 'CHECKED' : ''?>><?=T_('Deliver')?>
										</label>
										<br/>
										<div class="input_group input_half">
											<label><?=T_('Shipping First')?><br/>
												<input type="text" name="shipping_first" value="<?=es($edit_row['shipping_first'])?>" maxlength=128>
											</label>
										</div>
										<div class="input_group input_half">
											<label><?=T_('Shipping Last')?><br/>
												<input type="text" name="shipping_last" value="<?=es($edit_row['shipping_last'])?>" maxlength=128>
											</label>
										</div>
										<div class="input_group input_full">
											<label>
												<?=T_('Shipping Address 1')?><br/>
												<input type="text" name="shipping_address1" value="<?=es($edit_row['shipping_address1'])?>" maxlength=255>
											</label>
										</div>
										<div class="input_group input_full">
											<label>
												<?=T_('Shipping Address 2')?><br/>
												<input type="text" name="shipping_address2" value="<?=es($edit_row['shipping_address2'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_third">
											<label>
												<?=T_('Shipping City')?><br/>
												<input type="text" name="shipping_city" value="<?=es($edit_row['shipping_city'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_third">
											<label>
												<?=T_('Shipping State/Province')?><br/>
												<input type="text" name="shipping_state" value="<?=es($edit_row['shipping_state'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_third">
											<label>
												<?=T_('Shipping Zip/Postal code')?><br/>
												<input type="text" name="shipping_postal" value="<?=es($edit_row['shipping_postal'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_half">
											<label>
												<?=T_('Shipping Country')?><br/>
												<input type="text" name="shipping_country" value="<?=es($edit_row['shipping_country'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_half">
											<label>
												<?=T_('Shipping Phone')?><br/>
												<input type="text" name="shipping_phone" value="<?=es($edit_row['shipping_phone'])?>" maxlength=255>
											</label><br/>
										</div>
										<div class="input_group input_full">
											<?=T_('Shipping Requests')?><br/>
											<textarea name="shipping_requests" rows="5" cols="50"><?=$edit_row['shipping_requests']?></textarea>
										</div>
										<?php
											$allowed = array();
											// only supervisory staff can edit credit card information	
											$sql = "SELECT * FROM admins a "
												." JOIN admin_auths aa on a.admin_id = aa.admin_id "
												." WHERE a.admin_id= " . $admin_user['admin_id'] . " "
												." and (aa.role_id in (16,18) or aa.role_id is null)" ;
											$result = mysql_query($sql);
											while ($row = mysql_fetch_assoc($result)) {
												$allowed[] = $row['admin_id'];
											}
											//$allowed[] = 3; // add shimmy
											$allowed[] = 175069; // add cthAdmin
										
										if( in_array($admin_user['admin_id'], $allowed) ) { ?>
											<h2><?=T_('Payment Settings')?></h2>
											<!--
											<? $set_school_settings = explode(',', $edit_row['school_settings']); ?>
											<? foreach(mysql_enum_values('schools','school_settings') as $setting): ?>
											  <label><input type="checkbox" NAME="school_settings[]" VALUE="<?=$setting?>" <?=in_array($setting, $set_school_settings) ? 'CHECKED' : '' ?>><?=
											$setting == 'home_school' ? T_('Home School.')
											 : $setting ?></label><br/>
											<? endforeach; ?><br/>
											--><br/>
											<pre><?if($debug) print_r($customer_profile);?></pre>
											<?
											if ($action == "edit" && isset($customer_profile) ) {
												if ( count( $customer_profile->paymentProfiles ) > 0 ) {
													$payment_profile = $customer_profile->paymentProfiles[0];
													$cc = $payment_profile["payment"]["creditCard"];
												} else {
													$cc = false;
												}
											}
											if ($admin_user['auth'] == 'super') {
												$type = 'text';
											} else {
												$type = 'password';
											}
											?>
											<? if ($action == "edit") {?>
											<h3>Card on file</h3>
											<p>
												<? if ( isset($customer_profile) && $cc ) { ?>
													<?echo $cc['cardType'];?>: <? echo $cc['cardNumber'];?>.
													Billed to
													<?=isset($payment_profile['billTo']['address']) ? $payment_profile['billTo']['address'] : "N/A";?>,
													<?=isset($payment_profile['billTo']['city']) 	? $payment_profile['billTo']['city'] 	: "N/A";?>,
													<?=isset($payment_profile['billTo']['state']) 	? $payment_profile['billTo']['state'] 	: "N/A";?>
													<?=isset($payment_profile['billTo']["zip"]) 	? $payment_profile['billTo']['zip'] 	: "N/A";?>.
												<? } else { ?>
													No Card on File. Please add one.
												<? } ?>
											</p>
											
											<h3><?=$customer_profile == false ? "Create" : "Edit"?> Card On File</h3>
											<p>(Please note that a test transaction may be applied to validate your card)</p>
											<input type="hidden" name="paymentProfileId" value="<?=$customer_profile ? $payment_profile['customerPaymentProfileId'] : "";?>"/>
											<input type="hidden" name="customerProfileId" value="<?=$customer_profile ? $customer_profile->customerProfileId : "";?>"/>
											
											<? } ?>
											<div class="input_group input_half">
												<label><?=T_('CC Number')?><br/>
													<input TYPE="text" NAME="cc_number" VALUE="<?=$customer_profile ? $cc['cardNumber'] : ""?>" MAXLENGTH="19" placeholder="XXXXXXXXXXXXXXXXX">
												</label>
												<span class="cc_form_error" id="cc_number_errors"></span>
											</div>
											<div class="input_group input_quarter">
												<label><?=T_('Expires MM/YY')?><br/>
													<input TYPE="text" NAME="cc_exp" id="cc_exp" VALUE="<?= $customer_profile ? $cc['expirationDate'] : ""?>" MAXLENGTH="5" placeholder="XX/XX">
												</label>
												<span class="cc_form_error" id="cc_exp_errors"></span>
											</div>
											<div class="input_group input_quarter">
												<label><?=T_('CVV')?><br/>
													<input TYPE="text" NAME="cc_cvv" VALUE="" MAXLENGTH="4" placeholder="XXX">
												</label>
												<span class="cc_form_error" id="cc_cvv_errors"></span>
											</div>
											<center>
												<strong>Autofill: </strong>
												<button id="copy_main_address">Use Primary Address</button>
												<button id="copy_shipping_address">Use Shipping Address</button><br/>
											</center>
                                            <?php $has_billing = isset( $payment_profile['billTo'] ); ?>
											<div class="input_group input_full">
												<label>
													<?=T_('Billing Address')?><br/>
													<input type="text" name="billing_address" value="<?=$customer_profile && $has_billing ? $payment_profile['billTo']['address'] : "";?>" maxlength=255>
												</label><br/>
											</div>
											<div class="input_group input_third">
												<label>
													<?=T_('Billing City')?><br/>
													<input type="text" name="billing_city" maxlength=255
														value="<?=$has_billing && $customer_profile && isset( $payment_profile['billTo']['city']) ? $payment_profile['billTo']['city'] : "";?>"
													/>
												</label><br/>
											</div>
											<div class="input_group input_third">
												<label>
													<?=T_('Billing State/Province')?><br/>
													<input type="text" name="billing_state" value="<?=$has_billing && $customer_profile ? $payment_profile['billTo']['state'] : "";?>" maxlength=255>
												</label><br/>
											</div>
											<div class="input_group input_third">
												<label>
													<?=T_('Billing Zip/Postal code')?><br/>
													<input type="text" name="billing_postal" value="<?=$has_billing && $customer_profile ? $payment_profile['billTo']['zip'] : "";?>" maxlength=10>
												</label><br/>
											</div>
											<br/>
											<h2>Notes</h2>
											<div class="input_group input_full">
												<textarea name='notes' rows=5 cols=50><?=$edit_row['notes']?></textarea><br /><br />
											</div>
										<? } // end section only supervisers can see ?>
									<input class="submit" type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
								</p>
							</form>

					<?	} else if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) {
							if (!isset($_GET['registered']) && !isset($_GET['unregistered'])) { ?>
								<form action="admin_school2.php" method="get">
									<input type="checkbox" name="registered" value="registered"> Registered Schools<br />
									<input type="checkbox" name="unregistered" value="unregistered"> Not registered Schools<br />
									<input type="submit" name="submit" value="go">
								</form>
					<?	} else {
							if (isset($_GET['registered']) && isset($_GET['unregistered'])) {
								$str = "";
							} else if (isset($_GET['registered'])) {
								$str = " and schools.school_era is null ";
							} else if (isset($_GET['unregistered'])) {
								$str = " and schools.school_era is not null";
							}
							
							$sql = 'SELECT schools.school_id, institutions.inst_name, schools.school_name, 
								schools.school_number, school_era, schools.school_country, schools.school_state, 
								schools.school_city, schools.school_store, school_file_id, 
								(SELECT COUNT(*) FROM users WHERE users.school_id = schools.school_id) num_students,  
								(SELECT COUNT(*) FROM users WHERE users.school_id = schools.school_id AND user_registered IS NOT NULL) num_registered, 
								(SELECT IFNULL(sum(item_price), 0) 
								FROM invoice_items 
								WHERE invoice_items.school_id = schools.school_id) balance 
								FROM schools 
								LEFT JOIN institutions USING (inst_id) 
								WHERE ' . ($admin_user['auth'] != 'super' ? ' school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '1=1') . (gri('inst_id') ? ' AND inst_id = ' . gri('inst_id') : '') . 
								$str . ' 
								ORDER BY institutions.inst_name, schools.school_name';
							//echo $sql;
							$result = mysql_query($sql);
						?>
							<a href="admin_school_new.php?action=export_schools"><?=T_('Export Institutions')?></a><br/>
							<a href="admin_school_new.php?action=export_teachers"><?=T_('Export Teachers')?></a><br/>
							<a href="admin_school_new.php?action=export_users"><?=T_('Export All Soldiers')?></a><br/>
							<a href="admin_school_new.php?action=add"><?=T_('Add new institution')?></a>
							<table CLASS="list list_<?=$align_start?>" style="font-size:12px;">
								<thead>
									<tr>
										<th><?=T_('Institution')?></th>
										<th><?=T_('Type')?></th>
										<th><?=T_('Soldiers')?></th>
										<th><?=T_('Registered')?></th>
										<!--<th><?=T_('Points')?></th>-->
										<th><?=T_('Base #')?></th>
										<?if($admin_user['auth'] == 'super'):?>
										  <th><?=T_('Year')?></th>
										  <th><?=T_('Invoice')?></th>
										<?endif;?>
										<th><?=T_('Has file?')?></th>
										<th></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
								<?php
								$students = 0; $reg_students = 0;
								while($row = mysql_fetch_assoc($result)) {
									$students += $row['num_students']; $reg_students += $row['num_registered']; ?>
									<tr>
										<td>
											<?=es($row['school_name'])?>
											<p class="small">
												<?=es($row['school_city'])?>, <?=es($row['school_state'])?><br/><?=es($row['school_country'])?>
											</p>
										</td>
										<td><?=es($row['inst_name'])?></td>
										<td style="text-align: <?=$align_end?>;"><?=$row['num_students']?></td>
										<td style="text-align: <?=$align_end?>;"><?=$row['num_registered']?></td>
										<!--<td style="text-align: <?=$align_end?>;"><?//=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$row['school_id']}")), 0), 2)?></TD>-->
										<td><?=$row['school_number']?></td>
										<?php if($admin_user['auth'] == 'super') { ?>
											<td>
											<?=$row['school_era'] == '1' ? T_('Partially registered new school') : (!is_null($row['school_era']) ? sprintf(T_('School from %d'), $row['school_era']) : '')?>
											</td>
											<td style="text-align: <?=$align_end?>;">
												<?php if($row['balance'] != 0) { ?>
													<a href="admin_invoice_items.php?school_id=<?=$row['school_id']?>"><?=money_format('%n', $row['balance'])?></a>
												<?php } ?>
											</td>
										<?php } ?>
										<td><?=is_null($row['school_file_id']) ? '' : T_('YES')?></td>
										<td class="boxed_links">
											<a href="admin_school.php?action=edit&amp;school_id=<?=$row['school_id']?>"><?=T_('Edit Institution Info')?></a>
											<?if($admin_user['auth'] == 'super'):?><A href="admin_school.php?action=delete&amp;school_id=<?=$row['school_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Institution')?></A><?endif;?>
										</td>
										<td class="boxed_links">
											<a href="admin_class.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Platoons')?></a>
									<!--	<a href="admin_team.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Squads')?></a> -->
											<a href="admin_user.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Soldiers')?></a>
										</td>
									</tr>
									<?php } // end while loop ?>
									<tr>
									  <th><?=T_('Totals')?></th>
									  <td><?=mysql_num_rows($result)?></t>
									  <td style="text-align: <?=$align_end?>;"><?=$students?></t>
									  <td style="text-align: <?=$align_end?>;"><?=$reg_students?></t>
									  <!--<td style="text-align: <?=$align_end?>;"><?//=number_format(mysql_result(mq(totalMarks('JOIN users USING (user_id) JOIN schools USING (school_id)')), 0), 2)?></TD>-->
									  <td></td>
									</tr>
									<? $no_school = mysql_result(mq('SELECT COUNT(*) FROM users LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL AND !(users.school_id <=> -3)'), 0); ?>
									<tr>
									  <td colspan="2"><?=T_('Not in any school')?></td>
									  <td style="text-align: <?=$align_end?>;"><?=$no_school?></TD>
									  <td></td>
									  <td style="text-align: <?=$align_end?>;"><?//=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL")), 0), 2)?></t>
									  <td colspan="3"></t>
									  <td colspan="2"><a href="admin_user_noschool.php"><?=T_('Assign soldiers to a school')?></a></td>
									</tr>
								</tbody>
							</table>
									
							<br style="clear: both;">
					<?	} // end registered/unregistered is set
					} else { ?>
						<script>location.href = "/admin.php"; // redirect if not a superuser and not adding/editing (prevent blank page)</script> 
					<? } // end if editrow exists ?>
					</div>
				</div>
			</div>
		</div>
		<script>
            $('input#toggle_store_reset').change( function( event ){
                var input = $('input#store_reset');
                if ( event.target.checked ) {
                    if ( input.val() ) {
                        event.target.dataset.value = input.val();
                    };
                    input.attr('disabled', true).val('')
                } else {
                    var value = event.target.dataset.value;
                    input.attr('disabled', false).val(
                        value ? value : new Date().toISOString().substr(0, 10)
                    );
                }
            });

			$(".file").change( function( event ) {
				var input = event.target; // get the input
				if (input.files && input.files[0]) { // make sure a file was selected...
                    var reader = new FileReader(); // create a new reader
					
					reader.onload = function(e) { // when the reader is loaded...
						$("img#"+input.name).attr("src", e.target.result); // update the corrosponding image
					};
					reader.readAsDataURL(input.files[0]); // read the first file that was provided...
                }
			});
			
			$( "input[name='school_gender']" ).change( function() {
				if ($("input[name='school_gender']:checked").val() == "B") {
					$(".girls_school_logo").show();
				} else {
					$(".girls_school_logo").hide();
				}
			});
		</script>
		<?php include('admin_footer.php'); ?>
	</body>
</html>
