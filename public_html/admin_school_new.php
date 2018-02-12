<?
session_start();

if (!isset($_SERVER['HTTPS'])) {
	$url = "https://mashpia.com" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['argv'][0];
	header("Location: $url");
}
?>

<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?

$school_store = gri('school_store');
echo "<input type='hidden' name='school_store' value='" . $school_store . "'>\n";

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

      if($school_file_id !== 'school_file_id') 
		mq("DELETE FROM files USING files JOIN schools ON (files.file_id = schools.school_file_id) WHERE school_id = $school_id");
		
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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=($action == 'edit' ? T_('Base Profile') : T_('Bases')), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=($action == 'edit' ? T_('Base Profile') : T_('Bases'))?></H1>

<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<P><?=mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'base_mission'"), 0);?></P>

<?if($edit_row):?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?><A HREF="admin_school_new.php"><?=T_('Cancel')?></A><?endif;?>
<FORM action="admin_school_new.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P class="rows">
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="school_id" value="<?=$edit_row['school_id']?>">
<LABEL><?=T_('Name')?><BR><INPUT type="text" name="name" value="<?=es($edit_row['school_name'])?>"></LABEL><BR>
<LABEL><?=T_('Hebrew Name')?><BR><INPUT type="text" name="name_he" value="<?=es($edit_row['school_name_he'])?>"><br/> (<?=T_('This is how it will appear on school banner')?>)</LABEL><BR><br/>
<?if($admin_user['auth'] == 'super'):?>
  <? $institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name'); ?>
  <LABEL><?=T_('Institution type')?><BR><SELECT name="inst_id">
  <? while($row = mysql_fetch_assoc($institution_result)): ?>
    <OPTION VALUE="<?=$row['inst_id']?>" <?=$row['inst_id'] == $edit_row['inst_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?></OPTION>
  <? endwhile; ?>
  </SELECT></LABEL><BR>
<?endif;?>
<? $school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name'); ?>
<LABEL><?=T_('School type')?><BR><SELECT name="school_makeup_id">
<? while($row = mysql_fetch_assoc($school_makeup_result)): ?>
  <OPTION VALUE="<?=$row['school_makeup_id']?>" <?=$row['school_makeup_id'] == $edit_row['school_makeup_id'] ? 'SELECTED' : '' ?>><?=es($row['school_makeup_name'])?></OPTION>
<? endwhile; ?>
</SELECT></LABEL><BR>
<?=T_('Gender')?><BR>
<LABEL><INPUT type="radio" name="school_gender" value="M" <?=$edit_row['school_gender'] == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?></LABEL><BR>
<LABEL><INPUT type="radio" name="school_gender" value="F" <?=$edit_row['school_gender'] == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?></LABEL><BR>
<LABEL><INPUT type="radio" name="school_gender" value="B" <?=$edit_row['school_gender'] == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?></LABEL><BR>
<LABEL><?=T_('Address 1')?><BR><INPUT type="text" name="address1" value="<?=es($edit_row['school_address1'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Address 2')?><BR><INPUT type="text" name="address2" value="<?=es($edit_row['school_address2'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('City')?><BR><INPUT type="text" name="city" value="<?=es($edit_row['school_city'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('State/Province')?><BR><INPUT type="text" name="state" value="<?=es($edit_row['school_state'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Zip/Postal code')?><BR><INPUT type="text" name="postal" value="<?=es($edit_row['school_postal'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Country')?><BR><INPUT type="text" name="country" value="<?=es($edit_row['school_country'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Phone')?><BR><INPUT type="text" name="phone" value="<?=es($edit_row['school_phone'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Our school does not have a school logo')?> <INPUT type="checkbox" name="school_no_logo" class="checkbox" value="1" <?=$edit_row['school_no_logo'] ? 'checked' : ''?>></LABEL><BR>
<LABEL><?=T_('Logo')?> - <?=T_('PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.')?><BR><INPUT type="file" name="logo" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['school_logo_id'])):?>
<?=T_('Uploading a new logo will replace the old.')?><BR>
<LABEL><?=T_('Delete current logo')?> <INPUT type="checkbox" name="logo_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['school_logo_id'], NULL, '100')?><BR>
</LABEL>
<?endif?>
<LABEL><?=T_('Kiosk Logo')?> - <?=T_('PNG, GIF, or JPEG, but a transparent PNG is strongly recommended.')?><BR><INPUT type="file" name="logo_kiosk" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['school_logo_kiosk_id'])):?>
<?=T_('Uploading a new kiosk logo will replace the old.')?><BR>
<LABEL><?=T_('Delete current kiosk logo')?> <INPUT type="checkbox" name="logo_kiosk_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['school_logo_kiosk_id'], NULL, '100')?><BR>
</LABEL>
<?endif?>
<BR><LABEL><?=T_('File')?> - <?=T_('Use this to upload a database of your students for us to import, or to send us other files.')?><BR>
<A HREF="students.xls" style="background-color: lightblue;">Download</A> a spreadsheet template to use when sending us students to import.<BR>
<A HREF="Uploading_your_School_Database.doc" style="background-color: lightblue;">Instructions</A> for what to send us for the student import.
<BR><INPUT type="file" name="file" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['school_file_id'])):?>
<?=T_('Uploading a new file will replace the old.')?><BR>
<LABEL><?=T_('Delete current file')?> <INPUT type="checkbox" name="file_delete" class="checkbox" value="1"></LABEL><BR>
<A HREF="file_view.php?id=<?=$edit_row['school_file_id']?>&amp;m=d"><?=T_('Download current file')?></A><BR>
<?endif?>
<LABEL><?=T_('Our kiosks have printers')?> <INPUT type="checkbox" name="kiosk_print" class="checkbox" value="1" <?=$edit_row['kiosk_print'] ? 'checked' : ''?>></LABEL><BR>
<LABEL><?=T_('Our school has a store')?> <INPUT type="checkbox" name="school_store" id="school_store" class="checkbox" value="1" <?=$edit_row['school_store'] ? 'checked' : ''?>></LABEL><BR>
<BR>

<?=T_('Shipping Method')?><BR>
<LABEL><INPUT type="radio" name="shipping_method" value="pickup" <?=$edit_row['shipping_method'] == 'pickup' ? 'CHECKED' : ''?>><?=T_('Pickup')?></LABEL><BR>
<LABEL><INPUT type="radio" name="shipping_method" value="deliver" <?=$edit_row['shipping_method'] == 'deliver' ? 'CHECKED' : ''?>><?=T_('Deliver')?></LABEL><BR>
<LABEL><?=T_('Shipping First')?><BR><INPUT type="text" name="shipping_first" value="<?=es($edit_row['shipping_first'])?>" maxlength=128></LABEL><BR>
<LABEL><?=T_('Shipping Last')?><BR><INPUT type="text" name="shipping_last" value="<?=es($edit_row['shipping_last'])?>" maxlength=128></LABEL><BR>
<LABEL><?=T_('Shipping Phone')?><BR><INPUT type="text" name="shipping_phone" value="<?=es($edit_row['shipping_phone'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping Address 1')?><BR><INPUT type="text" name="shipping_address1" value="<?=es($edit_row['shipping_address1'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping Address 2')?><BR><INPUT type="text" name="shipping_address2" value="<?=es($edit_row['shipping_address2'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping City')?><BR><INPUT type="text" name="shipping_city" value="<?=es($edit_row['shipping_city'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping State/Province')?><BR><INPUT type="text" name="shipping_state" value="<?=es($edit_row['shipping_state'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping Zip/Postal code')?><BR><INPUT type="text" name="shipping_postal" value="<?=es($edit_row['shipping_postal'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Shipping Country')?><BR><INPUT type="text" name="shipping_country" value="<?=es($edit_row['shipping_country'])?>" maxlength=255></LABEL><BR>

<?php
	// only supervisory staff can see credit card information	
	$sql = "SELECT * FROM admins a
			JOIN admin_auths aa on a.admin_id = aa.admin_id 
			WHERE a.admin_id= " . $admin_user['admin_id'] .
			" and aa.role_id  in (16,18,19,20,34,35)" ;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$admin_user['auth'] = 'super';
	}
?>

<?if($admin_user['auth'] == 'super'):?>

<?=T_('Settings')?><BR>
<? $set_school_settings = explode(',', $edit_row['school_settings']); ?>
<? foreach(mysql_enum_values('schools','school_settings') as $setting): ?>
  <LABEL><INPUT type="checkbox" NAME="school_settings[]" VALUE="<?=$setting?>" <?=in_array($setting, $set_school_settings) ? 'CHECKED' : '' ?>><?=
$setting == 'home_school' ? T_('Home School.')
 : $setting ?></LABEL><BR>
<? endforeach; ?><BR>
<LABEL><?=T_('CC Number')?><BR><INPUT TYPE="text" NAME="cc_number" VALUE="<?=es($edit_row['cc_number'])?>" MAXLENGTH="19"></LABEL><BR>
<LABEL><?=T_('Expires MM/YY')?><BR><INPUT TYPE="text" NAME="cc_exp" VALUE="<?=es($edit_row['cc_exp'])?>" MAXLENGTH="5"></LABEL><BR>
<LABEL><?=T_('CVV')?><BR><INPUT TYPE="text" NAME="cc_cvv" VALUE="<?=es($edit_row['cc_cvv'])?>" MAXLENGTH="4"></LABEL><BR>
<?endif;?>
<INPUT class="submit" type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<?elseif($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>

<?$result = mq('SELECT schools.school_id, institutions.inst_name, schools.school_name, schools.school_number, school_era, schools.school_country, schools.school_state, schools.school_city, schools.school_store, school_file_id, (SELECT COUNT(*) FROM users WHERE users.school_id = schools.school_id) num_students,  (SELECT COUNT(*) FROM users WHERE users.school_id = schools.school_id AND user_registered IS NOT NULL) num_registered, (SELECT IFNULL(sum(item_price), 0) FROM invoice_items WHERE invoice_items.school_id = schools.school_id) balance FROM schools LEFT JOIN institutions USING (inst_id) WHERE ' . ($admin_user['auth'] != 'super' ? ' school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '1=1') . (gri('inst_id') ? ' AND inst_id = ' . gri('inst_id') : '') . ' ORDER BY institutions.inst_name, schools.school_name');?>

<A HREF="admin_school_new.php?action=export_schools"><?=T_('Export Institutions')?></A><BR>
<A HREF="admin_school_new.php?action=export_teachers"><?=T_('Export Teachers')?></A><BR>
<A HREF="admin_school_new.php?action=export_users"><?=T_('Export All Soldiers')?></A><BR>
<A HREF="admin_school_new.php?action=add"><?=T_('Add new institution')?></A>

<TABLE CLASS="list list_<?=$align_start?>" style="font-size:10px">
<THEAD>
<TR>
  <TH><?=T_('Institution')?></TH>
  <TH><?=T_('Institution type')?></TH>
  <TH><?=T_('# Soldiers')?></TH>
  <TH><?=T_('# Registered')?></TH>
  <TH><?=T_('Points')?></TH>
  <TH><?=T_('Base #')?></TH>
  <TH><?=T_('City')?>, <?=T_('State')?><BR><?=T_('Country')?></TH>
  <?if($admin_user['auth'] == 'super'):?>
    <TH><?=T_('Year')?></TH>
    <TH><?=T_('$ Invoice')?></TH>
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
    <TD><?=es($row['school_name'])?></TD>
    <TD><?=es($row['inst_name'])?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=$row['num_students']?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=$row['num_registered']?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$row['school_id']}")), 0), 2)?></TD>
    <TD><?=$row['school_number']?></TD>
    <TD><?=es($row['school_city'])?>, <?=es($row['school_state'])?><BR><?=es($row['school_country'])?></TD>
    <?if($admin_user['auth'] == 'super'):?>
      <TD><?=$row['school_era'] == '1' ? T_('Partially registered new school') : (!is_null($row['school_era']) ? sprintf(T_('School from %d'), $row['school_era']) : '')?></TD>
      <TD style="text-align: <?=$align_end?>;"><?if($row['balance'] != 0):?><A HREF="admin_invoice_items.php?school_id=<?=$row['school_id']?>"><?=money_format('%n', $row['balance'])?></A><?endif;?></TD>
    <?endif;?>
    <TD><?=is_null($row['school_file_id']) ? '' : T_('YES')?></TD>
    <TD class="boxed_links">
      <A HREF="admin_school_new.php?action=edit&amp;school_id=<?=$row['school_id']?>"><?=T_('Edit Institution Info')?></A>
      <?if($admin_user['auth'] == 'super'):?><A HREF="admin_school_new.php?action=delete&amp;school_id=<?=$row['school_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Institution')?></A><?endif;?>
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
  <TD style="text-align: <?=$align_end?>;"><?=number_format(mysql_result(mq(totalMarks('JOIN users USING (user_id) JOIN schools USING (school_id)')), 0), 2)?></TD>
  <TD colspan=7></TD>
</TR>
<? $no_school = mysql_result(mq('SELECT COUNT(*) FROM users LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL AND !(users.school_id <=> -3)'), 0); ?>
<TR>
  <TD colspan="2"><?=T_('Not in any school')?></TD>
  <TD style="text-align: <?=$align_end?>;"><?=$no_school?></TD>
  <TD></TD>
  <TD style="text-align: <?=$align_end?>;"><?=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL")), 0), 2)?></TD>
  <TD colspan="5"></TD>
  <TD colspan="2"><A HREF="admin_user_noschool.php"><?=T_('Assign soldiers to a school')?></A></TD>
</TR>
</TABLE>
<BR style="clear: both;">
<? endif; ?>
</DIV>
</DIV>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
