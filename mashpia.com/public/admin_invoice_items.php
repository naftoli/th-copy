<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);

if($admin_user['auth'] == 'super' && gr('save')) {
  $item_price = abs(grf('item_price'));
  switch($item_ref_type = gr('item_ref_type')) {
    case 'payment':
    case 'credit':
      $item_price = -$item_price;
      break;

    case 'charge':
      break;

    case 'note':
      $item_price = 0;
      break;

    default:
      user_error('unknown type ', E_USER_ERROR);
      break;
  }

  mq("INSERT INTO invoice_items (school_id, item_price, item_ref_type, item_description) VALUES ($school_id, $item_price, " . ms($item_ref_type) . ', ' . ms(gr('item_description')) . ')');
  $message = T_('Invoice item added.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Transaction History'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Base Management')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_invoice_items.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<H2><?=T_('Transaction History')?></H2>
<DIV class="infobox">
<? $balance = mysql_result(mq("SELECT IFNULL(sum(item_price), 0) FROM invoice_items WHERE school_id = $school_id"), 0); ?>
<?=sprintf(T_('Current Balance: %s'), money_format('%n', $balance));?>
</DIV>
<? if($admin_user['auth'] == 'super'): ?>
<DIV class="infobox2">
<H3><?=T_('Add invoice entry')?></H3>
<FORM action="admin_invoice_items.php" method="post" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Type')?>:
<SELECT name="item_ref_type">
<OPTION value="payment"><?=T_('Payment - TH has received money in this amount. (Automatically negative.)')?>
<OPTION value="charge"><?=T_('Charge - Fee that TH is charging this school.')?>
<OPTION value="credit"><?=T_('Credit - Reverse a charge, correct a billing error, or give a discount. (Automatically negative.)')?>
<OPTION value="note"><?=T_('Note - eg. tracking number, billing related messages, etc. Dollar amount ignored.')?>
</SELECT></LABEL><BR>
<?=T_('Amount')?>: $<INPUT type="text" name="item_price" maxlength="9" size="9" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99)).toFixed(2);"><BR>
<?=T_('Description')?>: <INPUT type="text" name="item_description" maxlength="512" size="60"><BR>
<INPUT type="submit" name="save" value="<?=T_('Save')?>">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
</P>
<P>
<?=T_('Note: Invoice items can not be deleted or changed, please double check before saving.')?>
</P>
</DIV>
<? endif; ?>
<TABLE CLASS="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TD>#</TD>
  <TH><?=T_('Date')?></TH>
  <TH><?=T_('Type')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Amount')?></TH>
  <TH><?=T_('Balance')?></TH>
</TR>
</THEAD>
<? $toggle = 0; ?>
<? $result = mq("SELECT item_id, item_price, item_date, item_description, item_ref_type, item_ref_id FROM invoice_items WHERE school_id = $school_id ORDER BY item_date DESC, item_id DESC"); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR class="<?=($toggle ^= 1) ? 'odd' : 'even'?>">
  <TD><?=$school_id, '-', $row['item_id']?></TD>
  <TD><?=$row['item_date']?></TD>
  <TD><?=$row['item_ref_type'] == 'school_packages' ? T_('Annual Base Membership') : ($row['item_ref_type'] == 'school_package_fees' ? T_('Annual Chayol membership') : ($row['item_ref_type'] == 'payment' ? T_('Payment') : ($row['item_ref_type'] == 'charge' ? T_('Charge') : ($row['item_ref_type'] == 'credit' ? T_('Credit') : ($row['item_ref_type'] == 'note' ? T_('Note') : $row['item_ref_type'])))))?></TD>
  <TD><?=es($row['item_description'])?></TD>
  <TD style="text-align: <?=$align_end?>;"><?=money_format('%n', $row['item_price'])?></TD>
  <TD style="text-align: <?=$align_end?>;"><?=money_format('%n', $balance)?></TD>
</TR>
<? $balance -= $row['item_price']; ?>
<? endwhile; ?>
</TABLE>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
