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

// data base connected on functional interface at this point

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
    $result = mq("SELECT -1 school_id, '' school_name, '' school_name_he, -1 inst_id, -1 school_makeup_id, '' school_settings, '' school_gender, NULL school_logo_id, NULL school_logo_kiosk_id, 0 school_no_logo, NULL school_file_id, '' school_address1, '' school_address2, '' school_city, '' school_state, '' school_postal, '' school_country, '' school_phone, 1 kiosk_print, '' shipping_method, '' shipping_first, '' shipping_last, '' shipping_phone, '' shipping_address1, '' shipping_address2, '' shipping_city, '' shipping_state, '' shipping_postal, '' shipping_country");
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
				   .  ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, '
				   .  ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, '
				   .  ms(gr('postal')) . ' school_postal, ' .  ms(gr('country')) . ' school_country, '
				   .  ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, '
				   .  ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, '
				   .  ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, '
				   .  ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, '
				   .  ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, '
				   .  ms(gr('shipping_state')) . ' shipping_state, ' .  ms(gr('shipping_postal')) . ' shipping_postal, '
				   .  ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . ' shipping_phone');
      $edit_row = mysql_fetch_assoc($result); // set the result to the current row on the editing table
      $action = 'add'; // and revert to add
    } else { // if this is a genuine new organization
      $school_logo_id = 'NULL'; // there is no logo by default
      if(isset($_FILES['logo'])) $school_logo_id = addFile($_FILES['logo'], $school_logo_id); // if one was provided, then save it and get the ID
	  // School kosik logo form removed
      //$school_logo_kiosk_id = 'NULL'; // same with the koisk logo
      //if(isset($_FILES['logo_kiosk'])) $school_logo_kiosk_id = addFile($_FILES['logo_kiosk'], $school_logo_kiosk_id);
	  // removed from form below. See backup for implamentation.
      //$school_file_id = 'NULL'; // resetting it to null?
      //if(isset($_FILES['file'])) $school_file_id = addFile($_FILES['file'], $school_file_id);

	  // removed school_logo_kiosk_id and $school_file_id from insert query
      mq('INSERT INTO schools SET school_name = ' . ms($name)
		 . ', school_name_he = ' . ms(gr('name_he')) . ', inst_id = ' . gri('inst_id')
		 . ', school_makeup_id = ' . gri('school_makeup_id', 0) . ', school_settings = ' . ms($school_settings)
		 . ', school_gender = ' . ms(gr('school_gender')) . ", school_number = " . mysql_result(mq("(SELECT IFNULL(MAX(school_number), 0)+1 FROM schools schools_max)"), 0)
		 . ", school_logo_id = $school_logo_id, school_no_logo = " . gri('school_no_logo', 0) . ", school_address1 = " . ms(gr('address1'))
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
    $result = mq("SELECT school_id, school_name, school_name_he, school_makeup_id, inst_id, school_settings, school_gender, school_logo_id, school_logo_kiosk_id, school_no_logo, school_file_id, school_address1, school_address2, school_city, school_state, school_postal, school_country, school_phone, cc_number, cc_exp, cc_cvv, authorize_customer_profile_id, authorize_payment_profile_id, kiosk_print, shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, school_store, notes, shipping_requests FROM schools WHERE school_id = $school_id");
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
	
    if(mysql_num_rows($result)) { // if said institution exists
      $message = T_('Unable to edit institution, this name is already used.'); // tell the user that names must be unique
	  // added the authorize data to what should be retrived from the database
      $result = mq('SELECT school_id, ' . ms($name) . ' school_name, ' . ms(gr('name_he')) . ' school_name_he, ' . gri('school_makeup_id', -1) . ' school_makeup_id, ' . $inst_id . ' inst_id, ' . ms($school_settings) . ' school_settings, ' . ms(gr('school_gender')) . ' school_gender, school_logo_id, school_logo_kiosk_id, ' . gri('school_no_logo', 0) . ' school_no_logo, school_file_id, ' .  ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, ' .  ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, ' .  ms(gr('postal')) . ' school_postal, ' .  ms(gr('country')) . ' school_country, ' .  ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, ' .  ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, ' .   ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, ' .  ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, ' .  ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, ' .  ms(gr('shipping_state')) . ' shipping_state, ' .  ms(gr('shipping_postal')) . ' shipping_postal, ' .  ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . " shipping_phone, authorize_customer_profile_id, authorize_payment_profile_id, school_store FROM schools WHERE school_id = $school_id");
      $edit_row = mysql_fetch_assoc($result); // get the generated edit_row
      $action = 'edit'; // set the action to edit
    } else { // the user is not using another institutions name
	  // upload new school logo if it is being replaced
      $school_logo_id = gri('logo_delete', 0) ? 'NULL' : 'school_logo_id';
      if(isset($_FILES['logo'])) $school_logo_id = addFile($_FILES['logo'], $school_logo_id);
	  // delete the old one
      if($school_logo_id !== 'school_logo_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_id) WHERE school_id = $school_id");
	  // same for kiosk logos
      $school_logo_kiosk_id = gri('logo_kiosk_delete', 0) ? 'NULL' : 'school_logo_kiosk_id';
      if(isset($_FILES['logo_kiosk'])) $school_logo_kiosk_id = addFile($_FILES['logo_kiosk'], $school_logo_kiosk_id);

      if($school_logo_kiosk_id !== 'school_logo_kiosk_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_kiosk_id) WHERE school_id = $school_id");
	  // and the schools file
      $school_file_id = gri('file_delete', 0) ? 'NULL' : 'school_file_id';
      if(isset($_FILES['file'])) $school_file_id = addFile($_FILES['file'], $school_file_id);

      if($school_file_id !== 'school_file_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");

		// update the school
      mq('UPDATE schools SET school_name = ' . ms($name) . ', school_name_he = ' . ms(gr('name_he')) . ', 
			school_makeup_id = ' . gri('school_makeup_id', -1) . ', 
			inst_id = ' . $inst_id . ', 
			school_settings = ' . ms($school_settings) . ', 
			school_gender = ' . ms(gr('school_gender')) . ", 
			school_logo_id = $school_logo_id, 
			school_logo_kiosk_id = $school_logo_kiosk_id, 
			school_no_logo = " . gri('school_no_logo', 0) . ", 
			school_file_id = $school_file_id, 
			school_address1 = " . ms(gr('address1')) . ', 
			school_address2 = ' . ms(gr('address2')) . ', 
			school_city = ' . ms(gr('city')) . ', 
			school_state = ' . ms(gr('state')) . ', 
			school_postal = ' . ms(gr('postal')) . ', 
			school_country = ' . ms(gr('country')) . ', 
			school_phone = ' . ms(gr('phone')) . ', 
			kiosk_print = ' . gri('kiosk_print', 0) . ', 
			shipping_method = ' . ms(gr('shipping_method')) . ', 
			shipping_first = ' . ms(gr('shipping_first')) . ', 
			shipping_last = ' . ms(gr('shipping_last'))  . ', 
			shipping_address1 = ' . ms(gr('shipping_address1')) . ', 
			shipping_address2 = ' . ms(gr('shipping_address2')) . ', 
			shipping_city = ' . ms(gr('shipping_city')) . ', 
			shipping_state = ' . ms(gr('shipping_state')) . ', 
			shipping_postal = ' . ms(gr('shipping_postal')) . ', 
			shipping_country = ' . ms(gr('shipping_country')) . ', 
			shipping_phone = ' . ms(gr('shipping_phone')) . ", 
			school_store=" . gri('school_store', 0) . ",
			shipping_requests=" . ms(gr('shipping_requests', null)) . ", 
			notes = " . ms(gr('notes', null)) . 
			" WHERE school_id = $school_id");


		// if there is a CC update that too.
		if(strlen(ms(gr('cc_number')))>2)
		{
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
					
				} else {
					//mq('UPDATE schools SET  
					//	cc_number = ' . ms(gr('cc_number'))  . ', 
					//	cc_exp = ' . ms(gr('cc_exp'))  . ', 
					//	cc_cvv = ' . ms(gr('cc_cvv')) .
					//	" WHERE school_id = $school_id");
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
			
			// update in database for now	
			
		}
		// update child types where required - Form removed from rendered result
//        if ( isset( $_POST['child_type_id'] ) ) {
//		  $sql = 'update school_child_types set child_type_id = ' . $_POST['child_type_id'] . ' where school_id = ' . $school_id;
//		  mq($sql);
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

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=($action == 'edit' ? T_('Base Profile') : T_('Bases')), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="/admin_styles.css" rel="stylesheet" type="text/css">
		<link href="/styles/admin/forms.css"  rel="stylesheet" type="text/css"/>
		<script src="camps/scripts/jquery.tools.min.js"></script>
		<script src="/js/utils/cc_validate.js"></script>
		<script src="/js/admin/admin_school.php.js"></script>
	</HEAD>
	
	<BODY>
		
		<?include('admin_header.php');?>
		
		<div>
			<div>
				<div class="sub_menu">
					<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				</div>
				
				<h1><?=($action == 'edit' ? T_('Base Profile') : T_('Bases'))?></h1>

				<div class="ui_body">
					<DIV class="ui_menu">
						<?ui_menu();?>
					</DIV>
					
					<div>
						<P><?=mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'base_mission'"), 0);?></P>
							<?if(!!$edit_row) { // is there a row to edit?
								if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) {?>
									<A HREF="admin_school2.php"><?=T_('Cancel')?></A><br/>
								<? } // show a cancel button if the user is a super or has many schools
								if (gr('message')){ ?>
									<h2>Error Messages</h2>
									<p><center><? echo htmlspecialchars(gr('message'))?></center></p>
								<? } // end displaying message from get request ?>
							
								<FORM action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
									<P class="rows">
										<h2><?=T_('Base Information')?></h2>
										<INPUT type="hidden" name="action" value="<?=$action?>2">
										<INPUT type="hidden" name="school_id" value="<?=$edit_row['school_id']?>">
										<div class="input_group input_half">
											<LABEL><?=T_('Name')?><BR><INPUT type="text" name="name" value="<?=es($edit_row['school_name'])?>"></LABEL>
										</div><div class="input_group input_half">
											<LABEL><?=T_('Hebrew Name')?><BR><INPUT type="text" name="name_he" value="<?=es($edit_row['school_name_he'])?>"> (<?=T_('This is how it will appear on school banner')?>)</LABEL>
										</div>
										<?if($admin_user['auth'] == 'super'):?>
										<? $institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name'); ?>
										<div class="input_group input_half">
											<LABEL><?=T_('Institution type')?><BR>
												<SELECT name="inst_id">
												<? while($row = mysql_fetch_assoc($institution_result)): ?>
													<OPTION VALUE="<?=$row['inst_id']?>" <?=$row['inst_id'] == $edit_row['inst_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?></OPTION>
												<? endwhile; ?>
												</SELECT>
											</LABEL>
										</div>
										<?endif;?>
										<? $school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name'); ?>
										<? // removed mission Type (child_type_id). Please see backup copy for implamentation ?>
										<div class="input_group input_half">
											<label><?=T_('Gender')?></label><BR>
											<!-- School Gender Options -->
											<LABEL>
												<INPUT type="radio" name="school_gender" value="M" <?=$edit_row['school_gender'] == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?>
											</LABEL>
											<LABEL>
												<INPUT type="radio" name="school_gender" value="F" <?=$edit_row['school_gender'] == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?>
											</LABEL>
											<LABEL>
												<INPUT type="radio" name="school_gender" value="B" <?=$edit_row['school_gender'] == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?>
											</LABEL>
										</div><div class="input_group input_full">
											<!-- Address information -->
											<LABEL><?=T_('Address 1')?><BR>
												<INPUT type="text" name="address1" value="<?=es($edit_row['school_address1'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_full">
											<LABEL><?=T_('Address 2')?><BR>
												<INPUT type="text" name="address2" value="<?=es($edit_row['school_address2'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_third">
											<LABEL><?=T_('City')?><BR>
												<INPUT type="text" name="city" value="<?=es($edit_row['school_city'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_third">
										<LABEL>
											<?=T_('State/Province')?><BR>
											<INPUT type="text" name="state" value="<?=es($edit_row['school_state'])?>" maxlength=255>
										</LABEL>
										</div><div class="input_group input_third">
											<LABEL><?=T_('Zip/Postal code')?><BR>
												<INPUT type="text" name="postal" value="<?=es($edit_row['school_postal'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_half">
											<LABEL><?=T_('Country')?><BR>
												<INPUT type="text" name="country" value="<?=es($edit_row['school_country'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_half">
											<LABEL><?=T_('Phone')?><BR>
												<INPUT type="text" name="phone" value="<?=es($edit_row['school_phone'])?>" maxlength=255>
											</LABEL>
										</div>
										<h2>New Logos</h2>
										<div id="school_logos">
											
										</div>
										<h2>Old Logo</h2>
										<?if(!is_null($edit_row['school_logo_id'])){?>
										<div id="logo_img_div">
											<?=linkImgFile($edit_row['school_logo_id'], NULL, '100')?><BR>
											<LABEL>
												<?=T_('Delete current logo')?> <INPUT type="checkbox" name="logo_delete" class="checkbox" value="1"><BR>
											</LABEL>
										</div>
										<div>
										<?} else { // delete old logo? ?>
										<div>
										<?} ?>
											<LABEL>
												<?=T_('Our school does not have a school logo')?> <INPUT type="checkbox" name="school_no_logo" class="checkbox" value="1" <?=$edit_row['school_no_logo'] ? 'checked' : ''?>></LABEL><BR>
											<LABEL>
												<?=T_('Logo')?> - <?=T_('PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.')?><BR>
												<INPUT type="file" name="logo" class="file">
											</LABEL>
											<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
											<?=T_('Uploading a new logo will replace the old.')?><BR>
										</div>
	
										<br />
										<h2><?=T_('Shipping Info')?></h2>
										<?=T_('Shipping Method')?><BR>
										<LABEL>
											<INPUT type="radio" name="shipping_method" value="pickup" <?=$edit_row['shipping_method'] == 'pickup' ? 'CHECKED' : ''?>><?=T_('Pickup')?>
										</LABEL>
										<LABEL>
											<INPUT type="radio" name="shipping_method" value="deliver" <?=$edit_row['shipping_method'] == 'deliver' ? 'CHECKED' : ''?>><?=T_('Deliver')?>
										</LABEL>
										<br/>
										<div class="input_group input_half">
											<LABEL><?=T_('Shipping First')?><BR>
												<INPUT type="text" name="shipping_first" value="<?=es($edit_row['shipping_first'])?>" maxlength=128>
											</LABEL>
										</div><div class="input_group input_half">
											<LABEL><?=T_('Shipping Last')?><BR>
												<INPUT type="text" name="shipping_last" value="<?=es($edit_row['shipping_last'])?>" maxlength=128>
											</LABEL>
										</div><div class="input_group input_full">
											<LABEL>
												<?=T_('Shipping Address 1')?><BR>
												<INPUT type="text" name="shipping_address1" value="<?=es($edit_row['shipping_address1'])?>" maxlength=255>
											</LABEL>
										</div><div class="input_group input_full">
											<LABEL>
												<?=T_('Shipping Address 2')?><BR>
												<INPUT type="text" name="shipping_address2" value="<?=es($edit_row['shipping_address2'])?>" maxlength=255>
											</LABEL><BR>
										</div><div class="input_group input_third">
											<LABEL>
												<?=T_('Shipping City')?><BR>
												<INPUT type="text" name="shipping_city" value="<?=es($edit_row['shipping_city'])?>" maxlength=255>
											</LABEL><BR>
										</div><div class="input_group input_third">
											<LABEL>
												<?=T_('Shipping State/Province')?><BR>
												<INPUT type="text" name="shipping_state" value="<?=es($edit_row['shipping_state'])?>" maxlength=255>
											</LABEL><BR>
										</div><div class="input_group input_third">
											<LABEL>
												<?=T_('Shipping Zip/Postal code')?><BR>
												<INPUT type="text" name="shipping_postal" value="<?=es($edit_row['shipping_postal'])?>" maxlength=255>
											</LABEL><BR>
										</div><div class="input_group input_half">
										<LABEL>
											<?=T_('Shipping Country')?><BR>
											<INPUT type="text" name="shipping_country" value="<?=es($edit_row['shipping_country'])?>" maxlength=255>
										</LABEL><BR>
										</div><div class="input_group input_half">
										<LABEL>
											<?=T_('Shipping Phone')?><BR>
											<INPUT type="text" name="shipping_phone" value="<?=es($edit_row['shipping_phone'])?>" maxlength=255>
										</LABEL><BR>
										</div><div class="input_group input_full">
											<?=T_('Shipping Requests')?><BR>
											<textarea name="shipping_requests" rows="5" cols="50"><?=$edit_row['shipping_requests']?></textarea>
										</div>
										<?php
											$allowed = array();
											// only supervisory staff can edit credit card information	
											$sql = "SELECT * FROM admins a
													JOIN admin_auths aa on a.admin_id = aa.admin_id 
													WHERE a.admin_id= " . $admin_user['admin_id'] .
													" and (aa.role_id in (16,18) or aa.role_id is null)" ;
											$result = mysql_query($sql);
											while ($row = mysql_fetch_assoc($result)) {
												$allowed[] = $row['admin_id'];
											}
											//$allowed[] = 3; // add shimmy
                      $allowed[] = 175069 // add cthAdmin
										?>
										
										<?if(in_array($admin_user['admin_id'], $allowed)) {?>
											<h2><?=T_('Payment Settings')?></h2>
											<!--
											<? $set_school_settings = explode(',', $edit_row['school_settings']); ?>
											<? foreach(mysql_enum_values('schools','school_settings') as $setting): ?>
											  <LABEL><INPUT type="checkbox" NAME="school_settings[]" VALUE="<?=$setting?>" <?=in_array($setting, $set_school_settings) ? 'CHECKED' : '' ?>><?=
											$setting == 'home_school' ? T_('Home School.')
											 : $setting ?></LABEL><BR>
											<? endforeach; ?><BR>
											--></BR>
											<pre><?if($debug) print_r($customer_profile);?></pre>
											<?
											if ($action == "edit" && $customer_profile) {
												$payment_profile = $customer_profile->paymentProfiles[0];
												$cc = $payment_profile["payment"]["creditCard"];
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
												<? if ($customer_profile) { ?>
													<?echo $cc['cardType'];?>: <? echo $cc['cardNumber'];?>.
													Billed to <?=$payment_profile['billTo']['address'];?>, <?=$payment_profile['billTo']['city'];?>, <?=$payment_profile['billTo']['state'];?> <?=$payment_profile['billTo']["zip"];?>. 
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
												<LABEL><?=T_('CC Number')?><BR>
													<INPUT TYPE="text" NAME="cc_number" VALUE="<?=$customer_profile ? $cc['cardNumber'] : ""?>" MAXLENGTH="19" placeholder="XXXXXXXXXXXXXXXXX">
												</LABEL>
												<span class="cc_form_error" id="cc_number_errors"></span>
											</div><div class="input_group input_quarter">
												<LABEL><?=T_('Expires MM/YY')?><BR>
													<INPUT TYPE="text" NAME="cc_exp" id="cc_exp" VALUE="<?= $customer_profile ? $cc['expirationDate'] : ""?>" MAXLENGTH="5" placeholder="XX/XX">
												</LABEL>
												<span class="cc_form_error" id="cc_exp_errors"></span>
											</div><div class="input_group input_quarter">
												<LABEL><?=T_('CVV')?><BR>
													<INPUT TYPE="text" NAME="cc_cvv" VALUE="" MAXLENGTH="4" placeholder="XXX">
												</LABEL>
												<span class="cc_form_error" id="cc_cvv_errors"></span>
											</div>
											<center>
												<strong>Autofill: </strong>
												<button id="copy_main_address">Use Primary Address</button>
												<button id="copy_shipping_address">Use Shipping Address</button><br/>
											</center>
											<div class="input_group input_full">
												<LABEL>
													<?=T_('Billing Address')?><BR>
													<INPUT type="text" name="billing_address" value="<?=$customer_profile ? $payment_profile['billTo']['address'] : "";?>" maxlength=255>
												</LABEL><BR>
											</div><div class="input_group input_third">
												<LABEL>
													<?=T_('Billing City')?><BR>
													<INPUT type="text" name="billing_city" value="<?=$customer_profile ? $payment_profile['billTo']['city'] : "";?>" maxlength=255>
												</LABEL><BR>
											</div><div class="input_group input_third">
												<LABEL>
													<?=T_('Billing State/Province')?><BR>
													<INPUT type="text" name="billing_state" value="<?=$customer_profile ? $payment_profile['billTo']['state'] : "";?>" maxlength=255>
												</LABEL><BR>
											</div><div class="input_group input_third">
												<LABEL>
													<?=T_('Billing Zip/Postal code')?><BR>
													<INPUT type="text" name="billing_postal" value="<?=$customer_profile ? $payment_profile['billTo']['zip'] : "";?>" maxlength=10>
												</LABEL><BR>
											</div>
											<br>
											<h2>Notes</h2>
											<div class="input_group input_full">
											<textarea name='notes' rows=5 cols=50><?=$edit_row['notes']?></textarea><br /><br />
											</div>
											
										<? } // end section only supervisers can see ?>
										<INPUT class="submit" type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
									</P>
								</FORM>

							<? } else if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) {
								if (!isset($_GET['registered']) && !isset($_GET['unregistered'])) { ?>
								<form action="admin_school2.php" method="get">
									<input type="checkbox" name="registered" value="registered"> Registered Schools<br />
									<input type="checkbox" name="unregistered" value="unregistered"> Not registered Schools<br />
									<input type="submit" name="submit" value="go">
								</form>
								
								<?
								} else {
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
									//exit;
									/*
									$result = mq('
										SELECT schools.school_id, institutions.inst_name, schools.school_name, 
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
										ORDER BY institutions.inst_name, schools.school_name');?>
									*/
								?>
									<A HREF="admin_school_new.php?action=export_schools"><?=T_('Export Institutions')?></A><BR>
									<A HREF="admin_school_new.php?action=export_teachers"><?=T_('Export Teachers')?></A><BR>
									<A HREF="admin_school_new.php?action=export_users"><?=T_('Export All Soldiers')?></A><BR>
									<A HREF="admin_school_new.php?action=add"><?=T_('Add new institution')?></A>
									<TABLE CLASS="list list_<?=$align_start?>" style="font-size:12px;">
									<THEAD>
									<TR>
									  <TH><?=T_('Institution')?></TH>
									  <TH><?=T_('Type')?></TH>
									  <TH><?=T_('Soldiers')?></TH>
									  <TH><?=T_('Registered')?></TH>
									  <!--<TH><?=T_('Points')?></TH>-->
									  <TH><?=T_('Base #')?></TH>
									  <?if($admin_user['auth'] == 'super'):?>
										<TH><?=T_('Year')?></TH>
										<TH><?=T_('Invoice')?></TH>
									  <?endif;?>
									  <TH><?=T_('Has file?')?></TH>
									  <TH></TH>
									  <TH></TH>
									</TR>
									</THEAD>
									<? $students = 0; $reg_students = 0; ?>
									<? while($row = mysql_fetch_assoc($result)): ?>
									<? $students += $row['num_students']; $reg_students += $row['num_registered']; ?>
									<TR>
										<TD><?=es($row['school_name'])?><p class="small"><?=es($row['school_city'])?>, <?=es($row['school_state'])?><BR><?=es($row['school_country'])?></p></TD>
										<TD><?=es($row['inst_name'])?></TD>
										<TD style="text-align: <?=$align_end?>;"><?=$row['num_students']?></TD>
										<TD style="text-align: <?=$align_end?>;"><?=$row['num_registered']?></TD>
										<!--<TD style="text-align: <?=$align_end?>;"><?//=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$row['school_id']}")), 0), 2)?></TD>-->
										<TD><?=$row['school_number']?></TD>
										<?if($admin_user['auth'] == 'super'):?>
										  <TD><?=$row['school_era'] == '1' ? T_('Partially registered new school') : (!is_null($row['school_era']) ? sprintf(T_('School from %d'), $row['school_era']) : '')?></TD>
										  <TD style="text-align: <?=$align_end?>;"><?if($row['balance'] != 0):?><A HREF="admin_invoice_items.php?school_id=<?=$row['school_id']?>"><?=money_format('%n', $row['balance'])?></A><?endif;?></TD>
										<?endif;?>
										<TD><?=is_null($row['school_file_id']) ? '' : T_('YES')?></TD>
										<TD class="boxed_links">
										  <A HREF="admin_school.php?action=edit&amp;school_id=<?=$row['school_id']?>"><?=T_('Edit Institution Info')?></A>
										  <?if($admin_user['auth'] == 'super'):?><A HREF="admin_school.php?action=delete&amp;school_id=<?=$row['school_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Institution')?></A><?endif;?>
										</TD>
										<TD class="boxed_links">
										  <A HREF="admin_class.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Platoons')?></A>
									<!--       <A HREF="admin_team.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Squads')?></A> -->
										  <A HREF="admin_user.php?school_id=<?=$row['school_id']?>"><?=T_('Manage Soldiers')?></A>
										</TD>
									</TR>
									<? endwhile; ?>
									<TR>
									  <TH><?=T_('Totals')?></TH>
									  <TD><?=mysql_num_rows($result)?></TD>
									  <TD style="text-align: <?=$align_end?>;"><?=$students?></TD>
									  <TD style="text-align: <?=$align_end?>;"><?=$reg_students?></TD>
									  <!--<TD style="text-align: <?=$align_end?>;"><?//=number_format(mysql_result(mq(totalMarks('JOIN users USING (user_id) JOIN schools USING (school_id)')), 0), 2)?></TD>-->
									  <TD colspan=7></TD>
									</TR>
									<? $no_school = mysql_result(mq('SELECT COUNT(*) FROM users LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL AND !(users.school_id <=> -3)'), 0); ?>
									<TR>
									  <TD colspan="2"><?=T_('Not in any school')?></TD>
									  <TD style="text-align: <?=$align_end?>;"><?=$no_school?></TD>
									  <TD></TD>
									  <TD style="text-align: <?=$align_end?>;"><?//=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL")), 0), 2)?></TD>
									  <TD colspan="3"></TD>
									  <TD colspan="2"><A HREF="admin_user_noschool.php"><?=T_('Assign soldiers to a school')?></A></TD>
									</TR>
									</TABLE>
									
									<BR style="clear: both;">
									<? } // end registered/unregistered is set
								} else { ?>
									<script>location.href = "/admin.php"; // redirect if not a superuser and not adding/editing (prevent blank page)</script> 
								<? } // end if editrow exists ?>
						</div>
					</div>
				</div>
		</div>
			
			<? include('admin_footer.php'); ?>
			
		</BODY>
	
</HTML>
