<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
if($tanya_goals = gra('tanya_goals')) {
  foreach($tanya_goals as $track => $lines_goal) {
    $track = intval($track);
    if($lines_goal !== '') {
      $lines_goal = intval($lines_goal);
      mq("INSERT INTO tanya_goals SET track = $track, lines_goal = $lines_goal ON DUPLICATE KEY UPDATE lines_goal = $lines_goal");
    } else {
      mq("DELETE FROM tanya_goals WHERE track = $track");
    }
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Tanya Goals'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Tanya Goals')?></H1>
<FORM action="admin_tanya_goals.php" method="post" accept-charset="UTF-8">
<P>
<? $goals = mysql_fetch_column(mq("SELECT track, lines_goal FROM tanya_goals")); ?>
<? for($i = 1; $i <= 20; $i++): ?>
<LABEL><?=str_replace(' ', '&#8199;', str_pad($i, 2, ' ', STR_PAD_LEFT))?> <INPUT type="text" name="tanya_goals[<?=$i?>]" value="<?=isset($goals[$i]) ? $goals[$i] : ''?>" maxlength="5" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"></LABEL><BR>
<? endfor; ?>
<INPUT type="submit" value="Save">
</P>
</FORM>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
