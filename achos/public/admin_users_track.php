<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'programs';
require_once('admin_ui.php');
require_once('calendar.php');
$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$subject_id = gri('subject_id', -1);

if(!empty($action)) switch($action) {
  case 'save':
    $result = mq("SELECT users.user_id FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : ''));
    $tracks = gra('tracks');
    $start_dates = gra('user_start_date');
    while($row = mysql_fetch_assoc($result)) {
      if(isset($tracks[$row['user_id']])) {
        foreach($tracks[$row['user_id']] as $subject_id => $data) {
          $subject_id = intval($subject_id);
          $track = intval($data['track']);
          $level = max(3, min(intval($data['level']), 14));
          if($data['track'] == -1) {
            mq("DELETE FROM user_tracks WHERE user_id = {$row['user_id']} AND subject_id = $subject_id");
          } else {
            mq("INSERT INTO user_tracks SET user_id = {$row['user_id']}, subject_id = $subject_id, track_id = $track, level = $level ON DUPLICATE KEY UPDATE track_id = $track, level = $level");
          }
//        echo("DELETE FROM user_tracks USING user_tracks LEFT JOIN school_type_subjects ON (user_tracks.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = {$user_row['school_type_id']}) WHERE user_id = $user_id AND school_type_subjects.subject_id IS NULL");
        }
      }
    }
    $message = T_("Soldier's ladders edited");
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$tracks_result = mq("SELECT track_id, track_name FROM tracks ORDER BY track_name ");
$subjects_result = mq("SELECT subject_name, subject_id, inst_name FROM subjects LEFT JOIN institutions USING (inst_id) WHERE subject_type NOT IN ('school_points', 'home_points', 'Tanya') ORDER BY subject_name, subject_id");

$edit_result = mq("SELECT users.user_id, users.first, users.last, users.username, user_start_date, class_grade, class_sub, institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level, user_tracks.enrolled FROM users JOIN subjects ON (subject_type NOT IN ('school_points', 'home_points', 'Tanya')) JOIN school_type_subjects USING (subject_id, school_type_id) LEFT JOIN classes USING (school_id, class_id) LEFT JOIN user_tracks USING (subject_id, user_id) LEFT JOIN institutions USING (inst_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($subject_id != -1 ? " AND subject_id = $subject_id" : '') . ($admin_user['auth'] != 'super' ? ' AND institutions.inst_id IN (' . implode(',', $admin_user['inst_ids']) . ')' : '') . ' ORDER BY users.last, users.first, users.username, institutions.inst_name, subjects.subject_name');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Soldier's Ladders/Years"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
// Popup window code
function newPopup(url) {
	popupWindow = window.open(
		url,'popUpWindow','height=400,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
}
</script>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Campaigns')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_users_track.php" method="get" accept-charset="UTF-8">
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
<DIV class="infobox">
<P>
<?=T_('You need to set the Ladder and Year for each soldier for them to see reports.')?>
</P>
<P>
<?=T_('You should review the ladder charts with the soldier to decide the ladder.')?>
</P>
<P>
<?=T_('Year is the age the class will be turning at the end of the year.')?>
</P>
<P>
	Click <a href="JavaScript:newPopup('http://www.mashpia.com/chart.html');">here</a> to use a chart to help you 
	decide what year to put your child on to.
</P>
</DIV>
<DIV class="infobox2">
<H2><?=T_("Soldier's Ladders/Years")?></H2>
<FORM action="admin_users_track.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL><?=T_('Show only Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id" id="subject_id">
  <OPTION value="-1">&lt;All&gt;
  <? while($row = mysql_fetch_assoc($subjects_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : '', es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
</DIV>

<?if($edit_result): ?>

<FORM action="admin_users_track.php" method="post" accept-charset="UTF-8" name="user_tracks">
<DIV>
<INPUT type="hidden" name="action" value="save">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<TABLE class="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Soldier')?></TH>
  <?if($subject_id==-1):?><TH><?=T_('Subject')?></TH><?endif;?>
  <TH><?=T_('Ladder')?></TH>
  <TH><?=T_('Year')?> (6 - 14)</TH>
  <TH><?=T_('Enrolled?')?></TH>
</TR>
</THEAD>
<TR>
  <TH<?if($subject_id==-1):?> colspan="2"<?endif;?>><?=T_('Change all')?>:</TH>
  <TH>
    <SELECT name="track_all">
      <OPTION value="-1">&lt;<?=T_('Subject Disabled')?>&gt;
    <?while($track_row = mysql_fetch_assoc($tracks_result)):?>
      <OPTION value="<?=$track_row['track_id']?>"><?=es($track_row['track_name'])?></OPTION>
    <?endwhile;?>
    <?mysql_data_seek($tracks_result, 0);?>
    </SELECT><BR><INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tracks')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[track]')==this.form.elements[i].name.length-7) this.form.elements[i].selectedIndex = this.form.elements['track_all'].selectedIndex;}">
  </TH>
  <TH>
    <INPUT type="text" name="level_all" maxlength="2" size="2" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"><BR><INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tracks')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[level]')==this.form.elements[i].name.length-7) { this.form.elements[i].value = this.form.elements['level_all'].value; this.form.elements[i].onchange();}}">
  </TH>
  <TH></TH>
</TR>
<? $old_user_id = -1; ?>
<?while($row = mysql_fetch_assoc($edit_result)):?>
<? if ($row['subject_id'] == 15) continue; ?>
<TR>
  <? if($old_user_id != $row['user_id']): ?>
    <TD><?=$row['class_grade'], '-', $row['class_sub'], ': ', $row['first'], ' ', $row['last']?></TD>
  <? else: ?>
    <TD style="border-bottom: none; border-top: none;"></TD>
  <? endif;?>
  <?if($subject_id==-1):?><TD><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></TD><?endif;?>
  <TD>
    <SELECT name="tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][track]">
      <OPTION value="-1">&lt;<?=T_('Subject Disabled')?>&gt;
    <?while($track_row = mysql_fetch_assoc($tracks_result)):?>
      <OPTION value="<?=$track_row['track_id']?>" <?=$track_row['track_id'] == $row['track_id'] ? 'SELECTED' : ''?>><?=es($track_row['track_name'])?></OPTION>
    <?endwhile;?>
    <?mysql_data_seek($tracks_result, 0);?>
    </SELECT>
  </TD>
  <TD STYLE="text-align: center;"><INPUT type="text" name="tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][level]" value="<?=es($row['level'])?>" maxlength="2" size="2" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][level]']; el.value=Math.max(3, Math.min(parseInt('0'+el.value, 10)+1, 14)); return false;">+</A> <A HREF="#" onClick="el=document.forms['user_tracks'].elements['tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][level]']; el.value=Math.max(3, Math.min(parseInt('0'+el.value, 10)-1, 14)); return false;">&minus;</A></TD>
  <TD><?if($row['enrolled']):?>&#10004;<?endif;?></TD>
</TR>
<?endwhile;?>
</TABLE>
<P>
<INPUT type="submit" value="<?=T_('Save')?>">
<INPUT type="reset" value="<?=T_('Undo Changes')?>">
</P>
</DIV>
</FORM>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
