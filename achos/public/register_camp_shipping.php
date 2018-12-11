<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
if($school_id == -1) die('Unknown school, please login to your admin page, and click the register link.');

if(gr('save')) {
  $message = '';

  if(gr('shipping_method') === '')
    $message .= T_('Please choose a delivery method.') . '<BR>';

  if(gr('shipping_first') === '')
    $message .= T_('Please enter the First Name.') . '<BR>';

  if(gr('shipping_last') === '')
    $message .= T_('Please enter the Last Name.') . '<BR>';

  if(gr('shipping_phone') === '')
    $message .= T_('Please enter the Shipping Phone.') . '<BR>';

  if(gr('shipping_address1') === '')
    $message .= T_('Please enter the Address 1.') . '<BR>';

  if(gr('shipping_city') === '')
    $message .= T_('Please enter the City.') . '<BR>';

  if(gr('shipping_state') === '')
    $message .= T_('Please enter the State.') . '<BR>';

  if(gr('shipping_country') === '')
    $message .= T_('Please enter the Country.') . '<BR>';

  if($message === '') {
    unset($message);

    mq('UPDATE schools SET shipping_method = ' . ms(gr('shipping_method')) . ', shipping_first = ' . ms(gr('shipping_first')) . ', shipping_last = ' . ms(gr('shipping_last')) . ', shipping_phone = ' . ms(gr('shipping_phone')) . ', shipping_address1 = ' . ms(gr('shipping_address1')) . ', shipping_address2 = ' . ms(gr('shipping_address2')) . ', shipping_city = ' . ms(gr('shipping_city'))  . ', shipping_state = ' . ms(gr('shipping_state'))  . ', shipping_postal = ' . ms(gr('shipping_postal'))  . ', shipping_country = ' . ms(gr('shipping_country')) . " WHERE school_id = $school_id");

    header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_school_package.php?school_id=' . $school_id);
    exit;
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
    $edit_row = mysql_fetch_assoc(mq('SELECT ' . ms(gr('shipping_method')) . ' shipping_method, ' . ms(gr('shipping_first')) . ' shipping_first, ' . ms(gr('shipping_last')) . ' shipping_last, ' . ms(gr('shipping_phone')) . ' shipping_phone, ' . ms(gr('shipping_address1')) . ' shipping_address1, ' . ms(gr('shipping_address2')) . ' shipping_address2, ' . ms(gr('shipping_city'))  . ' shipping_city, ' . ms(gr('shipping_state'))  . ' shipping_state, ' . ms(gr('shipping_postal'))  . ' shipping_postal, ' . ms(gr('shipping_country')) . ' shipping_country'));
  }
} else {
  $edit_row = mysql_fetch_assoc(mq("SELECT school_id, shipping_method, shipping_first, shipping_last, shipping_phone, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country FROM schools WHERE school_id = $school_id"));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps['shipping'], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<SCRIPT type="text/javascript">
function checkFields(form) {
  if(!form.elements['shipping_method'][0].checked && !form.elements['shipping_method'][1].checked) {
    alert('<?=esq(T_('Please choose a delivery method.'))?>');
    form.elements['shipping_method'][0].focus();
    return false;
  } else if(!form.elements['shipping_first'].value) {
    alert('<?=esq(T_('Please enter the First Name.'))?>');
    form.elements['shipping_first'].focus();
    return false;
  } else if(!form.elements['shipping_last'].value) {
    alert('<?=esq(T_('Please enter the Last Name.'))?>');
    form.elements['shipping_last'].focus();
    return false;
  } else if(!form.elements['shipping_phone'].value) {
    alert('<?=esq(T_('Please enter the Shipping Phone.'))?>');
    form.elements['shipping_phone'].focus();
    return false;
  } else if(!form.elements['shipping_address1'].value) {
    alert('<?=esq(T_('Please enter the Address 1.'))?>');
    form.elements['shipping_address1'].focus();
    return false;
  } else if(!form.elements['shipping_city'].value) {
    alert('<?=esq(T_('Please enter the City.'))?>');
    form.elements['shipping_city'].focus();
    return false;
  } else if(!form.elements['shipping_state'].value) {
    alert('<?=esq(T_('Please enter the State.'))?>');
    form.elements['shipping_state'].focus();
    return false;
  } else if(!form.elements['shipping_country'].value) {
    alert('<?=esq(T_('Please enter the Country.'))?>');
    form.elements['shipping_country'].focus();
    return false;
  }
  return true;
}
</SCRIPT>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_existing('shipping')?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?><BR>
</DIV>
<? endif; ?>

<BR><BR>
<P>
<?=T_('For your convenience, we can hold your monthly Tzivos Hashem materials in our New York headquarters to be picked up with no shipping charge, or we can ship your materials at standard shipping rates.')?>
</P>
<FORM action="register_school_shipping.php" method="post" accept-charset="UTF-8" onSubmit="return checkFields(this);">
<TABLE>
<TR>
  <TH colspan="2"><?=T_('How would you like to receive your program material?')?></TH>
</TR>
<TR>
  <TD colspan="2">
    <LABEL><INPUT type="radio" name="shipping_method" value="pickup" <?=$edit_row['shipping_method'] == 'pickup' ? 'checked' : ''?>><?=T_('Pickup at TH Headquarters, 792 Eastern Parkway, Brooklyn, NY.')?></LABEL>
    <BR><BR>
    <LABEL><INPUT type="radio" name="shipping_method" value="deliver" <?=$edit_row['shipping_method'] == 'deliver' ? 'checked' : ''?>><?=T_('Deliver to me at the address below.')?></LABEL>
    <BR><BR>
    <?=T_('The person responsible for picking up materials is: / Name on Shipping Label:')?>
    <BR><BR>
  </TD>
<TR>
  <TD>
    <LABEL><?=T_('First Name')?><BR>
    <INPUT TYPE="text" NAME="shipping_first" VALUE="<?=es($edit_row['shipping_first'])?>" MAXLENGTH="128">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Last Name')?><BR>
    <INPUT TYPE="text" NAME="shipping_last" VALUE="<?=es($edit_row['shipping_last'])?>" MAXLENGTH="128">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Shipping Phone')?><BR>
    <INPUT TYPE="text" NAME="shipping_phone" VALUE="<?=es($edit_row['shipping_phone'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD colspan="2">
    <BR><BR>
    <?=T_('PLEASE NOTE: We will notify you as soon as your material is ready for pickup. Material not collected 7 days after notification will be shipped to the address below and is subject to standard shipping rates.')?>
    <BR><BR>
    <?=T_('Delivery Address (required):')?>
    <BR><BR>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Address 1')?><BR>
    <INPUT TYPE="text" NAME="shipping_address1" VALUE="<?=es($edit_row['shipping_address1'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Address 2')?><BR>
    <INPUT TYPE="text" NAME="shipping_address2" VALUE="<?=es($edit_row['shipping_address2'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('City')?><BR>
    <INPUT TYPE="text" NAME="shipping_city" VALUE="<?=es($edit_row['shipping_city'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('State')?><BR>
    <INPUT TYPE="text" NAME="shipping_state" VALUE="<?=es($edit_row['shipping_state'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Postal code')?><BR>
    <INPUT TYPE="text" NAME="shipping_postal" VALUE="<?=es($edit_row['shipping_postal'])?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Country')?><BR>
    <INPUT TYPE="text" NAME="shipping_country" VALUE="<?=es($edit_row['shipping_country'])?>" MAXLENGTH="255">
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
</DIV>

<?=registration_tail()?>

</BODY>
</HTML>
