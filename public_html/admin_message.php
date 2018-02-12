<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'edit':
    $result = mq('SELECT message_type, message_text FROM messages WHERE message_type = ' . ms(gr('message_type')));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    mq('UPDATE messages SET message_text = ' . ms(gr('message_text')) . ' WHERE message_type = ' . ms(gr('message_type')));
    $message = T_('Message edited');
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Messages'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Messages')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_message.php" method="post" accept-charset="UTF-8">
<H2><?=T_('Note: Messages are output as is, including any HTML.')?></H2>
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="message_type" value="<?=$edit_row['message_type']?>">
<LABEL><?=T_('Text')?><BR><TEXTAREA ROWS="10" COLS="70" name="message_text"><?=es($edit_row['message_text'])?></TEXTAREA></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_message.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT message_type, message_text FROM messages ORDER BY message_type');?>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Type')?></TH>
  <TH></TH>
  <TH><?=T_('Text')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR STYLE="vertical-align: top;">
    <TD><?=es($row['message_type'])?></TD>
    <TD><A HREF="admin_message.php?action=edit&amp;message_type=<?=$row['message_type']?>"><?=T_('Edit Message')?></A></TD>
    <TD><DIV style="border: 1px solid black; margin: 0px; padding: 2px;" onClick="window.location='admin_message.php?action=edit&amp;message_type=<?=$row['message_type']?>'"><?=nl2br(es($row['message_text']))?></DIV></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
