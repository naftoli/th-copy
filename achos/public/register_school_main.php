<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
if($school_id == -1) die('Unknown school, please login to your admin page, and click the register link.');

if(gr('save')) {
  $message = '';

  $school_makeup_id = gri('school_makeup_id', -1);

  if($school_makeup_id == -1)
    $message .= T_('Please pick a school type.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM school_makeups WHERE school_makeup_id = $school_makeup_id"), 0)) $message .= T_('Invalid school type.') . '<BR>';

  $inst_id = gri('inst_id', -1);

  if($inst_id == -1)
    $message .= T_('Please pick an institution type.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM institutions WHERE inst_id = $inst_id"), 0)) $message .= T_('Invalid institution type.') . '<BR>';

  if(gr('school_name') === '')
    $message .= T_('The school name can not be blank.') . '<BR>';
  else
    if(mysql_result(mq('SELECT COUNT(*) FROM schools WHERE school_name = ' . ms(gr('school_name')) . " AND inst_id = $inst_id AND school_id != $school_id"), 0)) $message .= T_('This school name has been taken for this institution type. Please choose a different name (or institution type).') . '<BR>';

  if(gr('school_gender') === '')
    $message .= T_('Please select a gender.') . '<BR>';

  if(gr('school_address1') === '')
    $message .= T_('The address can not be blank.') . '<BR>';

  if(gr('school_city') === '')
    $message .= T_('The city can not be blank.') . '<BR>';

  if(gr('school_state') === '')
    $message .= T_('The state can not be blank.') . '<BR>';

  if(gr('school_country') === '')
    $message .= T_('The country can not be blank.') . '<BR>';

  if(gr('school_phone') === '')
    $message .= T_('The phone can not be blank.') . '<BR>';

  if($message === '') {
    unset($message);

    mq('UPDATE schools SET school_name = ' . ms(gr('school_name')) . ', school_name_he = ' . ms(gr('school_name_he')) . ', school_gender = ' . ms(gr('school_gender')) . ', school_address1 = ' . ms(gr('school_address1')) . ', school_address2 = ' . ms(gr('school_address2')) . ', school_city = ' . ms(gr('school_city')) . ', school_state = ' . ms(gr('school_state'))  . ', school_postal = ' . ms(gr('school_postal'))  . ', school_country = ' . ms(gr('school_country'))  . ', school_phone = ' . ms(gr('school_phone')) . ", school_makeup_id = $school_makeup_id, inst_id = $inst_id WHERE school_id = $school_id");

    header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_school_admin.php?school_id=' . $school_id);
    exit;
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
    $edit_row = mysql_fetch_assoc(mq('SELECT ' . ms(gr('school_name')) . ' school_name, ' . ms(gr('school_name_he')) . ' school_name_he, ' . ms(gr('school_gender')) . ' school_gender, ' . ms(gr('school_address1')) . ' school_address1, ' . ms(gr('school_address2')) . ' school_address2, ' . ms(gr('school_city')) . ' school_city, ' . ms(gr('school_state'))  . ' school_state, ' . ms(gr('school_postal'))  . ' school_postal, ' . ms(gr('school_country'))  . ' school_country, ' . ms(gr('school_phone')) . " school_phone, $school_makeup_id school_makeup_id, $inst_id inst_id"));
  }
} else {
  $edit_row = mysql_fetch_assoc(mq("SELECT school_id, school_makeup_id, school_name, school_name_he, school_gender, inst_id, school_address1, school_address2, school_city, school_state, school_postal, school_country, school_phone FROM schools WHERE school_id = $school_id"));
}

$institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name');
$school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps['main'], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<SCRIPT type="text/javascript">
function checkFields(form) {
  if(!form.elements['school_name'].value) {
    alert('<?=esq(T_('Please enter the School Name.'))?>');
    form.elements['school_name'].focus();
    return false;
  } else if(!form.elements['school_address1'].value) {
    alert('<?=esq(T_('Please enter the address.'))?>');
    form.elements['school_address1'].focus();
    return false;
  } else if(!form.elements['school_city'].value) {
    alert('<?=esq(T_('Please enter the city.'))?>');
    form.elements['school_city'].focus();
    return false;
  } else if(!form.elements['school_state'].value) {
    alert('<?=esq(T_('Please enter the state.'))?>');
    form.elements['school_state'].focus();
    return false;
  } else if(!form.elements['school_country'].value) {
    alert('<?=esq(T_('Please enter the country.'))?>');
    form.elements['school_country'].focus();
    return false;
  } else if(!form.elements['school_phone'].value) {
    alert('<?=esq(T_('Please enter the phone.'))?>');
    form.elements['school_phone'].focus();
    return false;
  }
  return true;
}
</SCRIPT>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_existing('main')?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?><BR>
</DIV>
<? endif; ?>
<BR><BR>
<H1><?=T_('Just Think')?></H1>
<P>
<?=sprintf(T_('As an official Tzivos Hashem base, your school will access over $1,200,000%s of programming, school curricula and technology during the coming year.'), '<A href="#thanks">*</A>')?>
</P>
<P>
<?=T_('To help you take full advantage of this program, we ask you to confirm that:')?>
</P>
<UL>
<LI><?=T_('You are the school principal responsible for supervising Tzivos Hashem.')?>
<LI><?=T_("You are familiar with the basic format of this program, and how it works seamlessly with your school's curricula. (Full support is available at all times.)")?>
<LI><?=T_('You have designated or will designate a program director who is fully committed to the ongoing growth of Tzivos Hashem on your base.')?>
</UL>
<FORM action="register_school_main.php" method="post" accept-charset="UTF-8" onSubmit="return checkFields(this);">
<TABLE>
<CAPTION><?=T_('<U>Please note</U> that a valid credit card is necessary to complete the registration.')?></CAPTION>
<TR>
  <TH colspan="2"><?=T_('Tell us about your School')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('School Name')?><BR>
    <INPUT TYPE="text" NAME="school_name" VALUE="<?=es($edit_row['school_name'])?>" MAXLENGTH="255"></LABEL>
  </TD>
  <TD rowspan="2">
    <?=T_('Gender')?><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="M" <?=$edit_row['school_gender'] == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?></LABEL><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="F" <?=$edit_row['school_gender'] == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?></LABEL><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="B" <?=$edit_row['school_gender'] == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?></LABEL><BR>
    <BR>

    <?=T_('School Type')?><BR>
    <? while($row = mysql_fetch_assoc($school_makeup_result)): ?>
      <LABEL><INPUT type="radio" name="school_makeup_id" value="<?=$row['school_makeup_id']?>" <?=$row['school_makeup_id'] == $edit_row['school_makeup_id'] ? 'CHECKED' : '' ?>><?=es($row['school_makeup_name'])?></LABEL><BR>
    <? endwhile; ?>
    <BR>

    <DIV style="display: none;">
    <LABEL><?=T_('Institution Type')?><BR>
    <SELECT name="inst_id">
    <? while($row = mysql_fetch_assoc($institution_result)): ?>
      <OPTION value="<?=$row['inst_id']?>" <?=$row['inst_id'] == $edit_row['inst_id'] ? 'SELECTED' : '' ?>> <?=es($row['inst_name'])?>
    <? endwhile; ?>
    </SELECT></LABEL>
    </DIV>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('School Name in Hebrew Letters')?><BR>
    <SPAN style="font-size: 65%;">(<?=T_('This is how it will appear on school banner')?>)</SPAN><BR>
    <INPUT TYPE="text" NAME="school_name_he" VALUE="<?=es($edit_row['school_name_he'])?>" MAXLENGTH="255"></LABEL><BR>
    <?=T_("Don't have Hebrew?")?><BR>
    <A HREF="http://www.mikledet.com/" target="_blank">www.mikledet.com</A>
  </TD>
</TR>
<TR>
  <TH colspan="2"><?=T_('School Address')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Address 1')?><BR>
    <INPUT TYPE="text" NAME="school_address1" VALUE="<?=es($edit_row['school_address1'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Address 2')?><BR>
    <INPUT TYPE="text" NAME="school_address2" VALUE="<?=es($edit_row['school_address2'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('City')?><BR>
    <INPUT TYPE="text" NAME="school_city" VALUE="<?=es($edit_row['school_city'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('State')?><BR>
    <INPUT TYPE="text" NAME="school_state" VALUE="<?=es($edit_row['school_state'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Postal code')?><BR>
    <INPUT TYPE="text" NAME="school_postal" VALUE="<?=es($edit_row['school_postal'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Country')?><BR>
    <INPUT TYPE="text" NAME="school_country" VALUE="<?=es($edit_row['school_country'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Phone')?><BR>
    <INPUT TYPE="text" NAME="school_phone" VALUE="<?=es($edit_row['school_phone'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>


</TABLE>

<P style="text-align: <?=$align_end?>;">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT TYPE="submit" name="save" VALUE="<?=T_('Save &amp; Continue')?>">
</P>

</FORM>

</DIV>
<P>
<A name="thanks">*</A><?=T_("Special thanks to Tzivos Hashem, Merkos L'inyonei Chinuch, Anash.com and all the businesses and individuals who have given so much of their time and resources to help bring this program to all Chabad schools.")?>
</P>

</DIV>
<?=registration_tail()?>

</BODY>
</HTML>
