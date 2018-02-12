<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

/* Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org *
 * This code has been released into the public domain, however please      *
 * give credit to the original author where possible.                      */

function luhn_check($number) {
  // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
  $number=preg_replace('/\D/', '', $number);

  // Set the string length and parity
  $number_length=strlen($number);
  $parity=$number_length % 2;

  // Loop through each digit and do the maths
  $total=0;
  for ($i=0; $i<$number_length; $i++) {
    $digit=$number[$i];
    // Multiply alternate digits by two
    if ($i % 2 == $parity) {
      $digit*=2;
      // If the sum is two digits, add them together (in effect)
      if ($digit > 9) {
        $digit-=9;
      }
    }
    // Total up the digits
    $total+=$digit;
  }

  // If the total mod 10 equals 0, the number is valid
  return ($total % 10 == 0) ? TRUE : FALSE;
}

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
if($school_id == -1) die('Unknown school, please login to your admin page, and click the register link.');

if(gr('save')) {
  $message = '';

  if(strlen(gr('cc_number')) < 12 || !luhn_check(gr('cc_number'))) $message .= T_('The credit card number you entered does not appear to be correct.') . '<BR>';

  if(gr('cc_exp') === '')
    $message .= T_('Please enter the Expiration date.') . '<BR>';

  if(gr('cc_cvv') === '')
    $message .= T_('Please enter the CVV.') . '<BR>';

  if($message === '') {
    unset($message);

    mq('UPDATE schools SET cc_number = ' . ms(gr('cc_number')) . ', cc_exp = ' . ms(gr('cc_exp')) . ', cc_cvv = ' . ms(gr('cc_cvv')) . " WHERE school_id = $school_id");

    mq("UPDATE schools SET school_era = NULL WHERE school_id = $school_id");

    mq("INSERT INTO invoice_items (school_id, item_price, item_ref_type, item_ref_id, item_description) SELECT school_id, fee item_price, 'school_packages' item_ref_type, package_id item_ref_id, package_name item_description FROM schools JOIN school_packages USING (package_id) WHERE school_id = $school_id");
    if(!mysql_affected_rows()) die("Error invoicing package. Please contact TH.");

    header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . (mysql_result(mq("SELECT COUNT(*) FROM classes WHERE class_era != 0 AND school_id = $school_id"), 0) ? 'admin_class_transition.php' : 'admin.php') . '?school_id=' . $school_id);
    exit;
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
    $edit_row = mysql_fetch_assoc(mq("SELECT $school_id school_id, " . ms(gr('cc_number')) . ' cc_number, ' . ms(gr('cc_exp')) . ' cc_exp, ' . ms(gr('cc_cvv')) . " cc_cvv, school_era, package_id, package_name, fee FROM schools LEFT JOIN school_packages USING (package_id) WHERE school_id = $school_id"));
  }
} else {
  $edit_row = mysql_fetch_assoc(mq("SELECT school_id, '' cc_number, '' cc_exp, '' cc_cvv, school_era, package_id, package_name, fee FROM schools LEFT JOIN school_packages USING (package_id) WHERE school_id = $school_id"));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps['billing'], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<SCRIPT type="text/javascript">
function checkFields(form) {
  if(!form.elements['cc_number'].value) {
    alert('<?=esq(T_('Please enter the Credit Card Number.'))?>');
    form.elements['cc_number'].focus();
    return false;
  } else if(!form.elements['cc_exp'].value) {
    alert('<?=esq(T_('Please enter the Expiration Date.'))?>');
    form.elements['cc_exp'].focus();
    return false;
  } else if(!form.elements['cc_cvv'].value) {
    alert('<?=esq(T_('Please enter the CVV.'))?>');
    form.elements['cc_cvv'].focus();
    return false;
  } else if(!form.elements['agree'].checked) {
    alert('<?=T_('You need to agree to the fee before continuing.')?>');
    form.elements['agree'].focus();
    return false;
  }
  return true;
}
</SCRIPT>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_existing('billing')?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<? if(is_null($edit_row['fee'])): ?>
<?=T_('Please choose a package first.')?>
<? elseif(is_null($edit_row['school_era'])): ?>
<?=T_('Your school is already registered.')?>
<? else: ?>
<? if(isset($message) && $message): ?>
<DIV CLASS="message" style="text-align: center;">
<?= $message ?><BR>
</DIV>
<? endif; ?>

<BR><BR>
<FORM action="register_school_billing.php" method="post" accept-charset="UTF-8" onSubmit="return checkFields(this);">
<TABLE>
<TR>
  <TH><?=T_('Billing Summary')?></TH>
</TR>
<TR>
  <TD>
    <BR><?=es($edit_row['package_name'])?> ............. <?=money_format('%n', $edit_row['fee'])?><BR><BR><BR>
  </TD>
</TR>
<TR>
  <TH><?=T_('Enter Payment Information')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Credit Card Number')?><BR>
    <INPUT TYPE="text" NAME="cc_number" VALUE="<?=es($edit_row['cc_number'])?>" MAXLENGTH="19">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Expires (MM/YY)')?><BR>
    <INPUT TYPE="text" NAME="cc_exp" VALUE="<?=es($edit_row['cc_exp'])?>" MAXLENGTH="5">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('CVV (security code from the back of the card)')?><BR>
    <INPUT TYPE="text" NAME="cc_cvv" VALUE="<?=es($edit_row['cc_cvv'])?>" MAXLENGTH="4">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><INPUT TYPE="checkbox" NAME="agree" VALUE="1"> <?=sprintf(T_('I agree for my card to be charged %s.'), money_format('%n', $edit_row['fee']))?>
    </LABEL>
  </TD>
</TR>
</TABLE>

<P style="text-align: <?=$align_end?>;">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT TYPE="submit" name="save" VALUE="<?=T_('Complete Registration and bill my card')?>">
</P>

</FORM>

<? endif; ?>
</DIV>
</DIV>

<?=registration_tail()?>

</BODY>
</HTML>
