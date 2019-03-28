<? require('header.php'); ?>
<?
if(isset($_GETPOST['lang'])) {
  if(!array_key_exists(gr('lang'), $langs)) user_error('lang not in list', E_USER_ERROR);

  mq('UPDATE users SET first=' . ms(gr('first')) . ', last=' . ms(gr('last')) . ', lang=' . ms(gr('lang')) . ((gr('password')) ? ', password=' . ms(gr('password')) : '') . " WHERE user_id={$user['user_id']}");
  require('auth.php');
  $message = T_('Preferences saved.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Preferences'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<? include('banner.php'); ?>
<DIV CLASS="body">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?>
</DIV>
<? endif; ?>

<TABLE CLASS="split" CELLSPACING=0 CELLPADDING=0>
<TR>
<TH></TH>
<TD CLASS="special"><? include('specials.php'); ?></TD>
<TH></TH>
</TR>
<TR>
<TD CLASS="tasks"><? include('todo.php'); ?></TD>
<TD CLASS="middle form form_<?= $align_start ?>">

<FORM action="preferences.php" method="post" accept-charset="UTF-8" onSubmit="if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); this.elements['password'].focus(); return false; } else {return true;}">
<TABLE>
<CAPTION><?= T_('Preferences') ?></CAPTION>
<TR>
  <TH><LABEL for="first"><?=T_('First Name')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="first" ID="first" VALUE="<?=$user['first']?>" MAXLENGTH="128"></TD>
</TR>
<TR>
  <TH><LABEL for="last"><?=T_('Last Name')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="last" ID="last" VALUE="<?=$user['last']?>" MAXLENGTH="128"></TD>
</TR>
<TR>
  <TH><LABEL for="lang"><?=T_('Language')?>:</LABEL></TH>
  <TD>
    <SELECT NAME="lang" ID="lang">
      <?
        foreach($langs as $lang_id => $lang_name) {
          echo "<OPTION value='$lang_id'" . ($lang_id == $user['lang'] ? ' SELECTED' : '') . ">$lang_name";
        }
      ?>
    </SELECT>
  </TD>
</TR>
<TR>
  <TH COLSPAN="2" CLASS="pass_change"><?=T_('Enter a new password to change it')?></TH>
</TR>
<TR>
  <TH><LABEL for="password"><?=T_('Password')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="password" ID="password" VALUE="" MAXLENGTH="64"></TD>
</TR>
<TR>
  <TH><LABEL for="password2"><?=T_('Password-again')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="password2" ID="password2" VALUE="" MAXLENGTH="64"></TD>
</TR>
<TR>
  <TH><INPUT TYPE="submit" VALUE="<?=T_('Save')?>"></TH>
</TR>
</TABLE>
</FORM>

</TD>
<TD CLASS="menu menu_<?=$align_end?>"><? include('menu.php'); ?></TD>
</TR>
</TABLE>
</DIV>
</BODY>
</HTML>
