<?// $req_school_type_setting = array('self_managed', 'personal_only'); ?>
<? require('header.php'); ?>
<?
$user['settings']='personal_only';
if($subjects = gra('subject')) {
  foreach($subjects as $subject_id => $data) {
    $subject_id = intval($subject_id);
    $track = nullif(intval($data['track']), -2);
    $level = max(3, min(intval($data['level']), 14));
    if($data['track'] == -1 || ($data['level'] === '' && $user['settings'] == 'personal_only')) {
      mq("DELETE FROM user_tracks WHERE user_id = {$user['user_id']} AND subject_id = $subject_id");
    } else {
      mq("INSERT INTO user_tracks SET user_id = {$user['user_id']}, subject_id = $subject_id, track_id = $track, level = $level ON DUPLICATE KEY UPDATE track_id = $track, level = $level");
    }
  }
  $message = T_("Ladders and Years edited");
}
$user_tracks = mq("SELECT institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level FROM subjects JOIN school_type_subjects ON (subjects.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = {$user['school_type_id']}) LEFT JOIN user_tracks ON (subjects.subject_id = user_tracks.subject_id AND user_id = {$user['user_id']}) LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name");

$tracks_result = mq("SELECT track_id, track_name FROM tracks ORDER BY track_name ");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Ladders and Years'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
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

<FORM action="tasks_tracks.php" method="post" accept-charset="UTF-8" name="user_tracks">
<TABLE class="lines">
<CAPTION><?= T_('Ladders and Years') ?></CAPTION>
<TR>
  <TH><?=T_('Subject')?></TH>
  <?if($user['settings'] != 'personal_only'):?>
    <TH><?=T_('Ladder')?></TH>
  <?endif;?>
  <TH style="text-align: center;">
    <?=T_('Year')?> (1&nbsp;-&nbsp;10)
    <?if($user['settings'] == 'personal_only'):?>
      <BR><SPAN style="font-size: 75%; font-weight: normal;">*<?=T_('Leave blank to disable the subject')?></SPAN>
    <?endif;?>
  </TH>
</TR>
<?while($row = mysql_fetch_assoc($user_tracks)):?>
<TR>
  <TD><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?>
  <?if($user['settings'] == 'personal_only'):?>
  <INPUT type="hidden" name="subject[<?=$row['subject_id']?>][track]" value="<?=is_null($row['track_id']) ? -2 : $row['track_id']?>">
  </TD>
  <?else:?>
  </TD>
  <TD>
    <SELECT name="subject[<?=$row['subject_id']?>][track]">
      <OPTION value="-1">&lt;<?=T_('Subject Disabled')?>&gt;
    <?while($track_row = mysql_fetch_assoc($tracks_result)):?>
      <OPTION value="<?=$track_row['track_id']?>" <?=$track_row['track_id'] == $row['track_id'] ? 'SELECTED' : ''?>><?=es($track_row['track_name'])?></OPTION>
    <?endwhile;?>
    <?mysql_data_seek($tracks_result, 0);?>
    </SELECT>
  </TD>
  <? endif; ?>
  <TD STYLE="text-align: center;"><INPUT type="text" name="subject[<?=$row['subject_id']?>][level]" value="<?=es($row['level'])?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['subject[<?=$row['subject_id']?>][level]']; el.value=Math.max(3, Math.min(parseInt('0'+el.value, 10)+1, 14)); return false;">+</A> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['subject[<?=$row['subject_id']?>][level]']; el.value=Math.max(3, Math.min(parseInt('0'+el.value, 10)-1, 14)); return false;">&minus;</A></TD>
</TR>
<?endwhile;?>
<TR class="bottom">
  <TH colspan=3><INPUT type="submit" value="<?=T_('Save')?>"></TH>
</TR>
</TABLE>
</FORM>
</TD>
<TD CLASS="menu menu_<?=$align_end?>"><? include('menu_tasks.php'); ?></TD>
</TR>
</TABLE>
</DIV>
</BODY>
</HTML>
