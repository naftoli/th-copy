<? require_once('calendar.php'); ?>
<?
$date = gri('date', unixtojd());
?>
<DIV CLASS="banner">
<DIV CLASS="rebbe <?=$align_start?>">
<IMG SRC="images/rebbe.jpg" ALT="Picture of Rebbe">
</DIV>
<DIV CLASS="<?=$align_end?>">
<? include('cal.php'); ?>
</DIV>
<DIV CLASS="middle">
<H1><IMG SRC="images/logo.png" ALT="<?= T_('Tzivos Hashem Management System') ?>"></H1>
<H2><?= isset($ruser) ? sprintf(T_("Welcome %s acting as %s"), $ruser['display'], $user['display']) . ' <A HREF="index.php?auth_become=-1" style="white-space: nowrap;">' . T_('Be Yourself') . '</A>' : sprintf(T_("Welcome %s"), $user['display']) ?> <A HREF="logout.php" style="white-space: nowrap;"><?=T_('Sign out')?></A></H2>
<H3>
<A TITLE="<?= es(T_('Previous')) ?>" HREF="tasks.php?date=<?= $date - 1?>"> &nbsp; &nbsp; &nbsp; &nbsp;<IMG SRC="images/arrow_14_<?= $align_start ?>.gif" ALT="<?=$prev_Arr?>"></A>
<SPAN DIR='rtl'><?= dateToHebrew($date) ?></SPAN>
<A TITLE="<?= es(T_('Next')) ?>" HREF="tasks.php?date=<?= $date + 1?>"><IMG SRC="images/arrow_14_<?= $align_end ?>.gif" ALT="<?=$next_Arr?>"> &nbsp; &nbsp; &nbsp; &nbsp;</A>
</H3>
</DIV>
<DIV CLASS="saying" STYLE="clear: <?=$align_start?>;">
Saying of the day
</DIV>
</DIV>
