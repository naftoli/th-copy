<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
if($school_id == -1) die('Unknown school, please login to your admin page, and click the register link.');

if(gr('save')) {
  $message = '';

  $package_id = gri('package_id', -1);
  if($package_id == -1)
    $message .= T_('Please pick a package.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM school_packages WHERE package_id = $package_id"), 0)) $message .= T_('Invalid package.') . '<BR>';

  if($message === '') {
    unset($message);

    mq("UPDATE schools SET package_id = $package_id WHERE school_id = $school_id");

    header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_school_billing.php?school_id=' . $school_id);
    exit;
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
  }
}

$edit_row = mysql_fetch_assoc(mq("SELECT school_id, package_id FROM schools WHERE school_id = $school_id"));

$packages = mq('SELECT package_id, package_name, package_description, fee FROM school_packages ORDER BY package_ord');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps['package'], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<SCRIPT type="text/javascript">
function checkFields(form) {
  if(!form.elements['confirm'].checked) {
    alert('<?=T_('You need to agree to the fee before continuing.')?>');
    form.elements['confirm'].focus();
    return false;
  }
  return true;
}
</SCRIPT>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_existing('package')?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?><BR>
</DIV>
<? endif; ?>
<BR><BR>
<P>
<?=T_('Tzivos Hashem recognizes the individual method and focus of each base. We have invested the resources to bring you a full-scale and first-rate program that is customizable and that you can join for a fraction of the cost per child.')?>
</P>
<FORM action="register_school_package.php" method="post" accept-charset="UTF-8" onSubmit="return checkFields(this);">
<TABLE class="package">
<TR>
  <TH colspan="2"><?=T_('Please select one of three program packages.')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($packages)): ?>
<TR>
  <TD colspan="2">
    <BR><BR><BR>
    <LABEL><INPUT type="radio" name="package_id" value="<?=$row['package_id']?>"> <?=es($row['package_name'])?></LABEL>
    <BR>
    <TABLE>
    <TR>
      <TD>
        <H4><?=es($row['package_name'])?></H4>
        <?=$row['package_description']?>
        <DIV style="background-color: #f8fbf7; width: 20em; display: block; border: 1px solid #63e49e; padding: 0px 1em;">
        <P><?=T_('Annual Base Membership')?>: <?=money_format('%n', $row['fee'])?></P>
        <P>
        <?=T_('Annual Chayol membership')?>:<BR>
        <? $fees = mq("SELECT fee_id, fee_each, fee_name FROM school_package_fees WHERE package_id = {$row['package_id']} ORDER BY fee_each"); ?>
        <? while($fee = mysql_fetch_assoc($fees)): ?>
          &bull; <?=money_format('%n', $fee['fee_each'])?> <?=es($fee['fee_name'])?><BR>
        <? endwhile; ?>
        </P>
        </DIV>
      </TD>
    </TR>
    </TABLE>
  </TD>
</TR>
<? endwhile; ?>
</TABLE>

<P>
<LABEL><INPUT type="checkbox" name="confirm"> <?=T_('I agree for my card to be charged the registration fee for every student that is registered into the TH program from my school.')?></LABEL>
</P>
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
