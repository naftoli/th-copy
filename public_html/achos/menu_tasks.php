<UL>
<LI><A HREF="index.php"><?= T_('Home') ?></A>

<?if(in_array($user['settings'], array('self_managed'))):?>
  <LI><A HREF="tasks_tracks.php"><?= T_('Manage Ladders and Years') ?></A>
<?endif;?>

<?if(in_array($user['settings'], array('self_managed', 'personal_only'))):?>
  <LI><A HREF="tasks_school_type.php"><?= T_('Change Tzivos Hashem Type') ?></A>
<?endif;?>

<?if(in_array($user['settings'], array('managed_personal', 'self_managed', 'personal_only'))):?>
  <LI><A HREF="tasks_personal.php"><?= T_('Manage personal tasks') ?></A>
<?endif;?>

<?if($user['settings'] == 'personal_only'):?>
  <LI><A HREF="tasks_tracks.php"><?= T_('Manage task years') ?></A>
<?endif;?>
</UL>
