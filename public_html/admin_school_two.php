<?
if (!isset($_SERVER['HTTPS'])) {
	$url = "https://mashpia.com" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['argv'][0];
	header("Location: $url");
}

$admin_auth = array('school');
require('header.php'); 

include("admin_schools.php");

/*

$school_store = gri('school_store');

$ui_type = 'school';
require_once('admin_ui.php');
require_once('file_save.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);

if($auth_mode == 'school') {
  if(gr('action') != 'edit' && gr('action') != 'edit2') sgr('action', 'edit');
}

$action = gr('action');
$edit_row = false;

include("camps/includes/classes/child_type.php");
$child_types = array();
$sql = "SELECT * FROM child_types ORDER BY child_type_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$child_type = new child_type($row);
	array_push($child_types, $child_type);
}

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 school_id, '' school_name, '' school_name_he, -1 inst_id, -1 school_makeup_id, '' school_settings, '' school_gender, NULL school_logo_id, NULL school_logo_kiosk_id, 0 school_no_logo, NULL school_file_id, '' school_address1, '' school_address2, '' school_city, '' school_state, '' school_postal, '' school_country, '' school_phone, '' cc_number, '' cc_exp, '' cc_cvv, 1 kiosk_print, '' shipping_method, '' shipping_first, '' shipping_last, '' shipping_phone, '' shipping_address1, '' shipping_address2, '' shipping_city, '' shipping_state, '' shipping_postal, '' shipping_country");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $name = gr('name');

    $result = mq('SELECT 1 FROM schools WHERE school_name = ' . ms($name). ' AND inst_id = ' . gri('inst_id'));
    $school_settings = implode(',', gra('school_settings'));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new institution, this name is already used.');
      $result = mq('SELECT -1 school_id, ' . ms($name) . ' school_name, ' . ms(gr('name_he')) . ' school_name_he, ' . gri('inst_id') . ' inst_id, ' . gri('school_makeup_id') . ' school_makeup_id, ' . ms($school_settings) . ' school_settings, ' . ms(gr('school_gender')) . ' school_gender, NULL school_logo_id, NULL school_logo_kiosk_id, ' . gri('school_no_logo', 0) . ' school_no_logo, NULL school_file_id, ' .  ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, ' .  ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, ' .  ms(gr('postal')) . ' school_postal, ' .  ms(gr('country')) . ' school_country, ' .  ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, ' .  ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, ' .  ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, ' .  ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, ' .  ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, ' .  ms(gr('shipping_state')) . ' shipping_state, ' .  ms(gr('shipping_postal')) . ' shipping_postal, ' .  ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . ' shipping_phone');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      $school_logo_id = 'NULL';
      if(isset($_FILES['logo'])) $school_logo_id = addFile($_FILES['logo'], $school_logo_id);
      $school_file_id = 'NULL';
      $school_logo_kiosk_id = 'NULL';
      if(isset($_FILES['logo_kiosk'])) $school_logo_kiosk_id = addFile($_FILES['logo_kiosk'], $school_logo_kiosk_id);
      $school_file_id = 'NULL';
      if(isset($_FILES['file'])) $school_file_id = addFile($_FILES['file'], $school_file_id);
      mq('INSERT INTO schools SET school_name = ' . ms($name) . ', school_name_he = ' . ms(gr('name_he')) . ', inst_id = ' . gri('inst_id') . ', school_makeup_id = ' . gri('school_makeup_id') . ', school_settings = ' . ms($school_settings) . ', school_gender = ' . ms(gr('school_gender')) . ", school_number = " . mysql_result(mq("(SELECT IFNULL(MAX(school_number), 0)+1 FROM schools schools_max)"), 0) . ", school_logo_id = $school_logo_id, school_logo_kiosk_id = $school_logo_kiosk_id, school_no_logo = " . gri('school_no_logo', 0) . ", school_file_id = $school_file_id, school_address1 = " . ms(gr('address1')) . ', school_address2 = ' . ms(gr('address2')) . ', school_city = ' . ms(gr('city')) . ', school_state = ' . ms(gr('state')) . ', school_postal = ' . ms(gr('postal')) . ', school_country = ' . ms(gr('country')) . ', school_phone = ' . ms(gr('phone')) . ', cc_number = ' . ms(gr('cc_number')) . ', cc_exp = ' . ms(gr('cc_exp')) . ', cc_cvv = ' . ms(gr('cc_cvv')) . ', kiosk_print = ' . gri('kiosk_print', 0) . ', shipping_method = ' . ms(gr('shipping_method')) . ', shipping_first = ' . ms(gr('shipping_first')) . ', shipping_last = ' . ms(gr('shipping_last'))  . ', shipping_address1 = ' . ms(gr('shipping_address1')) . ', shipping_address2 = ' . ms(gr('shipping_address2')) . ', shipping_city = ' . ms(gr('shipping_city')) . ', shipping_state = ' . ms(gr('shipping_state')) . ', shipping_postal = ' . ms(gr('shipping_postal')) . ', shipping_country = ' . ms(gr('shipping_country')) . ', shipping_phone = ' . ms(gr('shipping_phone')));
      $message = T_('Institution added');
    }
    break;

  case 'delete':
    mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_id) WHERE school_id = $school_id");
    mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_kiosk_id) WHERE school_id = $school_id");
    mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");
    mq("DELETE FROM schools WHERE school_id = $school_id");
    mq("DELETE FROM admin_auths WHERE auth = 'school' AND id = $school_id");
    $message = T_('Institution deleted');
    break;

  case 'edit':
    $result = mq("SELECT school_id, school_name, school_name_he, school_makeup_id, inst_id, school_settings, school_gender, school_logo_id, school_logo_kiosk_id, school_no_logo, school_file_id, school_address1, school_address2, school_city, school_state, school_postal, school_country, school_phone, cc_number, cc_exp, cc_cvv, kiosk_print, shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, school_store FROM schools WHERE school_id = $school_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    if($school_id == -1) break;
    $name = gr('name');
    $inst_id = $admin_user['auth'] == 'super' ? gri('inst_id') : 'inst_id';
    $school_settings = implode(',', gra('school_settings'));

    $result = mq('SELECT 1 FROM schools WHERE school_name = ' . ms($name) . " AND inst_id = $inst_id AND school_id != $school_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit institution, this name is already used.');
      $result = mq('SELECT school_id, ' . ms($name) . ' school_name, ' . ms(gr('name_he')) . ' school_name_he, ' . gri('school_makeup_id', -1) . ' school_makeup_id, ' . $inst_id . ' inst_id, ' . ms($school_settings) . ' school_settings, ' . ms(gr('school_gender')) . ' school_gender, school_logo_id, school_logo_kiosk_id, ' . gri('school_no_logo', 0) . ' school_no_logo, school_file_id, ' .  ms(gr('address1')) . ' school_address1, ' .  ms(gr('address2')) . ' school_address2, ' .  ms(gr('city')) . ' school_city, ' .  ms(gr('state')) . ' school_state, ' .  ms(gr('postal')) . ' school_postal, ' .  ms(gr('country')) . ' school_country, ' .  ms(gr('phone')) . ' school_phone, ' .  ms(gr('cc_number')) . ' cc_number, ' .  ms(gr('cc_exp')) . ' cc_exp, ' .  ms(gr('cc_cvv')) . ' cc_cvv, ' . gri('kiosk_print', 0) . ' kiosk_print, ' .   ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, ' .  ms(gr('shipping_last')) . ' shipping_last, ' .  ms(gr('shipping_address1')) . ' shipping_address1, ' .  ms(gr('shipping_address2')) . ' shipping_address2, ' .  ms(gr('shipping_city')) . ' shipping_city, ' .  ms(gr('shipping_state')) . ' shipping_state, ' .  ms(gr('shipping_postal')) . ' shipping_postal, ' .  ms(gr('shipping_country')) . ' shipping_country, ' .  ms(gr('shipping_phone')) . " shipping_phone, school_store FROM schools WHERE school_id = $school_id");
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      $school_logo_id = gri('logo_delete', 0) ? 'NULL' : 'school_logo_id';
      if(isset($_FILES['logo'])) $school_logo_id = addFile($_FILES['logo'], $school_logo_id);

      if($school_logo_id !== 'school_logo_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_id) WHERE school_id = $school_id");

      $school_logo_kiosk_id = gri('logo_kiosk_delete', 0) ? 'NULL' : 'school_logo_kiosk_id';
      if(isset($_FILES['logo_kiosk'])) $school_logo_kiosk_id = addFile($_FILES['logo_kiosk'], $school_logo_kiosk_id);

      if($school_logo_kiosk_id !== 'school_logo_kiosk_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_logo_kiosk_id) WHERE school_id = $school_id");

      $school_file_id = gri('file_delete', 0) ? 'NULL' : 'school_file_id';
      if(isset($_FILES['file'])) $school_file_id = addFile($_FILES['file'], $school_file_id);

      if($school_file_id !== 'school_file_id') mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");

      		
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
			school_store=" . gri('school_store', 0) . 
			" WHERE school_id = $school_id");

			
		if(strlen(ms(gr('cc_number')))>2)
		{
			mq('UPDATE schools SET  
			cc_number = ' . ms(gr('cc_number'))  . ', 
			cc_exp = ' . ms(gr('cc_exp'))  . ', 
			cc_cvv = ' . ms(gr('cc_cvv')) .
			" WHERE school_id = $school_id");
		}

      $message = T_('Institution edited');
    }
    break;

  case 'export_schools':
    require_once('export.php');
    export('SELECT school_id, school_name, school_name_he, inst_name institution_type, school_number, school_gender, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, school_store FROM schools LEFT JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, inst_id, school_name, school_id', 'schools');
    exit;
    break;

  case 'export_teachers':
    require_once('export.php');
    export('SELECT school_id, school_name, inst_name institution_type, school_number, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, class_id, class_grade, class_sub, class_teacher, school_store FROM schools JOIN classes USING (school_id) LEFT JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, inst_id, school_name, school_id,  class_grade, class_sub, class_id', 'teachers');
    exit;
    break;

  case 'export_users':
    require_once('export.php');
    export('SELECT school_id, school_name, inst_name institution_type, school_number, class_id, class_grade, class_sub, class_teacher, user_id, username, email, first, last, first_he, last_he, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, gender, user_start_date, user_registered, dob FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (class_id, school_id) LEFT JOIN institutions USING (inst_id) WHERE school_id IS NOT NULL' . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name, school_id, class_grade, class_sub, class_id, last, first, username, user_id', 'soldiers');
    exit;
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE>Base Profile - Tzivos Hashem Management System</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				</DIV>
				
				<H1>Base Profile</H1>

				<script>
					var school_id = <?=$admin->school_id;?>;
			
					$(document).ready(function() {					
						var url = "ajax_get_school.php?school_id=" + school_id;
						$.ajax({
							url: url,
							success: function(data) 
							{
								$("#report_div").html(data);											
							}
						});						
					});
			
					$(function()
					{
						$('.marking_list div select').each(function() {
							if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
							if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
						});
						
						$('.marking_list div a.next').click(function(){
							$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
						});
						
						$('.marking_list div a.prev').click(function(){
							$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
						});
					
						$("#school_id").sSelect().change(function () 
						{
							var url = "ajax_get_school.php?school_id=" + $(this).val();
							$.ajax({
								url: url,
								success: function(data) 
								{
									$("#report_div").html(data);											
								}
							});						
						});
						
					});
				</script>
				
				
				<? include("admin_school_select.php"); ?>
				
			</DIV>
			
			<DIV id="report_div">
			</DIV>
			
			<? include('admin_footer.php'); ?>
			
		</BODY>
	
</HTML>
