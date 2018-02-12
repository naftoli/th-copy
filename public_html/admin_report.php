<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');

function showTasks($tasks, $start_date, $end_date, $style = false) {
  if(!mysql_num_rows($tasks)) {
    echo '<P>', T_('No tasks.'), "</P>\n";
  } else {
    $old_mission = -1;
    $mission_sent = -1;
    $seen_tasks = array();

    while($row = mysql_fetch_assoc($tasks)) {
      $active = false;
      ob_start();
      if($mission_sent == -1) echo "<DL>\n";

      if($old_mission != $row['mission_id']) {
        $old_mission = $row['mission_id'];
        echo '<DT>' . es($row['subject_name']), ' - ', es($row['mission_name']), "\n";
      }

      echo '<DD>';
      if(isset($seen_tasks[$row['task_id']])) echo '(', T_('Dup'), ') ';
      echo es($row['name']);
      if($style == 'history')
        echo ' ', $row['num'],
          'x',
          ' (', floatval($row['total_points']), ')';
      elseif($style == 'quantity')
        echo ' ', $row['quantity_name'], ': ', $row['quantity'];
      else
        echo ' (', floatval($row['points']), ')',
        ' - ',
        es($row['description']),
        "\n";

      if(!$style) foreach(range($start_date, $end_date) as $date)
        if($row['start_date'] <= $date && ($row['end_date'] >= $date || $row['end_date'] == '') && isRepeatToday($row['rep_type'], $date, $row['start_date'], $row['every'], $row['rep_param1'], $row['rep_param2']))
          $active = true;

      if($style || $active) {
        ob_end_flush();
        $mission_sent = $old_mission;
        $seen_tasks[$row['task_id']] = true;
      } else {
        ob_end_clean();
        if($mission_sent != $old_mission) $old_mission = $mission_sent;
      }
    }
    if($mission_sent != -1)
      echo "</DL><BR style='clear: both;'>\n";
    else
      echo '<P>'. T_('No tasks.') . "</P>\n";
  }
}

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);
// $auction_id = gri('auction_id', -1);
// $task_dates = gri('task_dates', 1);

$start_date = gri('start_date', unixtojd()+1);
$end_date = gri('end_date', unixtojd()+8);

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");
// $auction_result = mq('SELECT auction_id, auction_date FROM auctions ORDER BY auction_date DESC');
// if($auction_id != -1) $prizes_auction = mq("
// SELECT   prizes_auction.prize_id, prizes_auction.prize_name, prizes_auction.prize_points
// FROM     prizes_auction JOIN auction_prizes USING (prize_id)
// WHERE    auction_prizes.auction_id = $auction_id
// ORDER BY prizes_auction.prize_points, prizes_auction.prize_name
// ");
$request = mq('SELECT COUNT(DISTINCT users.user_id) all_count, IFNULL(SUM(marks.mark_points), 0) all_points FROM users LEFT JOIN marks USING (user_id)');
list($all_count, $all_points) = mysql_fetch_array($request);
$message1 = mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'report1'"), 0);
$message2 = mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'report2'"), 0);

$max_cols = 6;
$width = floor(100/$max_cols);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Soldier Report'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Soldier Report')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_report.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="start_date" value="<?=$start_date?>">
<INPUT type="hidden" name="end_date" value="<?=$end_date?>">
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>"><BR>
<!-- <?=T_("Note: Only tasks with an institution type matching this institution's institution type will be shown or calculated.")?> -->
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?><P><A HREF="admin_school.php"><?=T_('Back to Institution list')?></A></P><?endif;?>
<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></A></P>
<P><A HREF="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Back to Soldier list')?></A></P>

<FORM action="admin_report.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">

<?=T_('Show Platoon')?> <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>
<?=T_('Show Soldier')?> <SELECT name="user_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>
</SELECT><BR>

<?if($admin_user['auth'] == 'super'):?>
<LABEL><?=T_('From')?>: <INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew($start_date))?>" onClick="getDate(this.form, 'start_date');"></LABEL><INPUT type="hidden" name="start_date" value="<?=$start_date?>"><BR>
<LABEL><?=T_('To')?>:<INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew($end_date))?>" onClick="getDate(this.form, 'end_date');"></LABEL><INPUT type="hidden" name="end_date" value="<?=$end_date?>"><BR>
<?else:?>
<INPUT type="hidden" name="start_date" value="<?=$start_date?>">
<INPUT type="hidden" name="end_date" value="<?=$end_date?>">
<?endif;?>
<? /*
<?=T_('Auction')?>: <SELECT name="auction_id">
<OPTION value="-1">&lt;<?=T_("Don't include")?>&gt;</OPTION>
<? while($auction_row = mysql_fetch_assoc($auction_result)): ?>
<OPTION value="<?=$auction_row['auction_id']?>" <?=$auction_row['auction_id'] == $auction_id ? 'SELECTED' : ''?>><?=es(dateToHebrew($auction_row['auction_date']))?></OPTION>
<?endwhile;?>
</SELECT><BR>

<?=T_('Show task dates')?>: <INPUT type="hidden" name="task_dates" value="0"><INPUT type="checkbox" name="task_dates" value="1" <?=$task_dates ? 'CHECKED' : ''?>><BR>
*/ ?>

<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<DIV class="report report_<?=$align_start?>">
<?
$users = mq("
SELECT user_id, first, last, first_he, last_he, username, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, inst_logo_id,
       IFNULL((SELECT  SUM(mark_points) points_total
          FROM  marks
          WHERE marks.user_id = users.user_id), 0) user_points,
       IFNULL((SELECT  SUM(mark_points) points_total
          FROM  users users_team
           JOIN teams USING (school_id, team_id)
           JOIN marks USING (user_id)
          WHERE school_id = users.school_id AND team_id = users.team_id), 0) team_points,
       (SELECT  COUNT(users_team.user_id) num
          FROM  users users_team
           JOIN teams USING (school_id, team_id)
          WHERE school_id = users.school_id AND team_id = users.team_id) team_count,
       IFNULL((SELECT  SUM(mark_points) points_total
          FROM  users users_school
           JOIN marks USING (user_id)
          WHERE school_id = users.school_id), 0) school_points,
       (SELECT  COUNT(users_school.user_id) num
          FROM  users users_school
          WHERE school_id = users.school_id) school_count
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
WHERE school_id = $school_id" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
' ORDER BY class_grade, class_sub, last, first'
);

$inst_id = mysql_result(mq("SELECT inst_id FROM schools WHERE school_id = $school_id"), 0);

/*
$army_statistics = mq("
SELECT subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name, SUM(marks.mark_quantity) quantity
FROM     tasks
         JOIN marks USING (task_id)
         JOIN users USING (user_id)
         LEFT JOIN (missions JOIN mission_tasks USING (mission_id)) USING (task_id)
         LEFT JOIN subjects ON (subjects.subject_id = tasks.subject_id)
WHERE    tasks.quantity_name IS NOT NULL
         AND marks.mark_quantity IS NOT NULL
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
GROUP BY subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");

$school_statistics = mq("
SELECT subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name, SUM(marks.mark_quantity) quantity
FROM     tasks
         JOIN marks USING (task_id)
         JOIN users USING (user_id)
         LEFT JOIN (missions JOIN mission_tasks USING (mission_id)) USING (task_id)
         LEFT JOIN subjects ON (subjects.subject_id = tasks.subject_id)
WHERE    tasks.quantity_name IS NOT NULL
         AND marks.mark_quantity IS NOT NULL
         AND users.school_id = $school_id
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
GROUP BY subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
*/
?>
<? while($user_row = mysql_fetch_assoc($users)): ?>
<DIV class="onepage">
<HR class="noprint">
<TABLE style="width: 100%;" class="logos">
<TR>
<TD style="text-align: <?=$align_start?>;"><?=!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id']) : ''?></TD>
<TD style="text-align: <?=$align_end?>;"><?=!is_null($user_row['inst_logo_id']) ? linkImgFile($user_row['inst_logo_id']) : ''?></TD>
</TR>
</TABLE>
<H1><?=T_('My Profile')?></H1>
<TABLE class="grid">
<TR>
<TD>
<H2><?=T_('Post Location #')?>: <?=es($user_row['school_number'])?></H2>
<H2><?=T_('Institution Name')?>: <?=es($user_row['school_name'])?></H2>
<? if($user_row['class_grade'] != ''): ?><H2><?=T_('Grade')?>: <?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?></H2><?endif;?>
<H2><?=T_('Teacher')?>: <?=es($user_row['class_teacher'])?></H2>
</TD>
<TD>
<H2><?=T_('Name of Soldier')?>: <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?></H2>
<H2><?=T_('Serial #')?>: <?=es($user_row['user_serial'])?></H2>
<H2><?=T_('Rank')?>:</H2>
<H2><?=T_('Points')?>: <?=floatval($user_row['user_points'])?></H2>
</TD>
<TD>
<H2><?=T_('Soldier Address')?>:</H2>
<ADDRESS>
<?=es($user_row['user_address1'])?><?=$user_row['user_address1'] ? '<BR>' : ''?>
<?=es($user_row['user_address2'])?><?=$user_row['user_address2'] ? '<BR>' : ''?>
<?=es($user_row['user_city'])?><?=$user_row['user_city'] && $user_row['user_state'] ? ', ' : ''?><?=es($user_row['user_state'])?> <?=es($user_row['user_postal'])?><?=$user_row['user_city'] || $user_row['user_state'] || $user_row['user_postal'] ? '<BR>' : ''?>
<?=es($user_row['user_country'])?><?=$user_row['user_country'] ? '<BR>' : ''?>
<?=es($user_row['user_phone'])?><?=$user_row['user_phone'] ? '<BR>' : ''?>
</ADDRESS>
</TD>
</TR>
</TABLE>

<H1><?=T_('Statistics')?></H1>
<TABLE class="grid">
<TR>
<TH><H2><?=T_('Army Participation')?></H2></TH>
<TH><H2><?=T_('Post Participation')?></H2></TH>
<TH><H2><?=T_('Squad Participation')?></H2></TH>
</TR>
<TR>
<TD>
<H2><?=T_('Soldiers')?>: <?=$all_count?></H2>
<H2><?=T_('Points')?>: <?=floatval($all_points)?></H2>
<H2><?=T_('Average points')?>: <?=number_format($all_points/$all_count, 2)?></H2>
</TD>
<TD>
<H2><?=T_('Soldiers')?>: <?=$user_row['school_count']?></H2>
<H2><?=T_('Points')?>: <?=floatval($user_row['school_points'])?></H2>
<H2><?=T_('Average points')?>: <?=number_format($user_row['school_points']/$user_row['school_count'], 2)?></H2>
</TD>
<TD>
<?if($user_row['team_id']):?>
  <H2><?=T_('Soldiers')?>: <?=$user_row['team_count']?></H2>
  <H2><?=T_('Points')?>: <?=floatval($user_row['team_points'])?></H2>
  <H2><?=T_('Average points')?>: <?=number_format($user_row['team_points']/$user_row['team_count'], 2)?></H2>
  <H2><?=T_('Squad')?>: <?=$user_row['team_name']?></H2>
<? else: ?>
  <H2><?=T_('Not in a platoon')?></H2>
<? endif; ?>
</TD>
</TR>
<? /*
<TR>
<TD>
<? showTasks($army_statistics, NULL, NULL, 'quantity'); ?>
</TD>
<TD>
<? showTasks($school_statistics, NULL, NULL, 'quantity'); ?>
</TD>
<TD>
<?
if($user_row['team_id']) {
if(!isset($team_statistics[$user_row['team_id']])) //cache the results
  $team_statistics[$user_row['team_id']] = mq("
SELECT subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name, SUM(marks.mark_quantity) quantity
FROM     tasks
         JOIN marks USING (task_id)
         JOIN users USING (user_id)
         LEFT JOIN (missions JOIN mission_tasks USING (mission_id)) USING (task_id)
         LEFT JOIN subjects ON (subjects.subject_id = tasks.subject_id)
WHERE    tasks.quantity_name IS NOT NULL
         AND marks.mark_quantity IS NOT NULL
         AND users.school_id = $school_id
         AND users.team_id = {$user_row['team_id']}
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
GROUP BY subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($team_statistics[$user_row['team_id']], NULL, NULL, 'quantity');
}
?>
</TD>
</TR>
*/ ?>
</TABLE>

<? /*
<H1><?=T_('Mission statistics')?></H1>
<?
$quantity = mq("
SELECT subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name, SUM(marks.mark_quantity) quantity
FROM     tasks
         JOIN marks USING (task_id)
         JOIN users USING (user_id)
         LEFT JOIN (missions JOIN mission_tasks USING (mission_id)) USING (task_id)
         LEFT JOIN subjects ON (subjects.subject_id = tasks.subject_id)
WHERE    tasks.quantity_name IS NOT NULL
         AND marks.mark_quantity IS NOT NULL
         AND users.school_id = $school_id
         AND user_id = {$user_row['user_id']}
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
GROUP BY subjects.subject_id, subjects.subject_name, missions.mission_id, missions.mission_name, task_id, tasks.name, tasks.quantity_name
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($quantity, NULL, NULL, 'quantity');
?>
*/ ?>

<? /*
<H1><?=T_('Missions')?></H1>
<?
$tasks = mq("
SELECT   tasks.*, task_active.description, task_active.points,  subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
FROM     (users
         JOIN user_tracks USING (user_id)
         JOIN tasks USING (subject_id)
         JOIN task_active USING (task_id, track_id, level, school_type_id))
          JOIN mission_tasks USING (task_id)
          JOIN missions USING (mission_id)
          JOIN mission_active USING (mission_id, school_type_id, level, track_id)
         LEFT JOIN (subjects JOIN school_type_subjects USING (subject_id))
           ON (tasks.subject_id = subjects.subject_id AND users.school_type_id = school_type_subjects.school_type_id)
WHERE   (tasks.start_date <= $end_date
          AND (tasks.end_date >= $start_date
               OR tasks.end_date IS NULL))
         AND users.user_id = {$user_row['user_id']}
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
?>
<? if(!mysql_num_rows($tasks)): ?>
<P><?=T_('No tasks.')?></P>
<? else: ?>
<? if(!$task_dates) echo "<!--"; //I need the calulations from this section for the next section, so it needs to run even if !task_dates ?>
<? ob_start(); ?>
<TABLE class="pretty_grid tasks">
<THEAD>
<TR>
  <TH><?=T_('Mission')?></TH>
  <TH><?=T_('Task')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Points')?></TH>
  <? foreach(range($start_date, $end_date) as $date): ?>
    <TH style="white-space: nowrap;"><?=es(dateToHebrewNoYear($date))?></TH>
  <? endforeach; ?>
</TR>
</THEAD>
<TBODY>
<?
$old_mission = -1;
$mission_sent = -1;
$seen_tasks = array();
?>
<? while($row = mysql_fetch_assoc($tasks)): ?>
<?
$active = false;
ob_start();
?>
<TR>
  <TH>
  <?
  if($old_mission != $row['mission_id']) {
    $old_mission = $row['mission_id'];
    echo es($row['subject_name']), '<BR>', es($row['mission_name']);
  }
  ?>
  </TH>
  <TH><?=es($row['name'])?><?=isset($seen_tasks[$row['task_id']]) ? '<BR><SPAN STYLE="font-size: 90%; font-weight: normal;">' . T_('Notice: this task is a duplicate') . '</SPAN>' : ''?></TH>
  <TD class="description"><?=es($row['description'])?></TD>
  <TD><?=floatval($row['points'])?></TD>
  <? foreach(range($start_date, $end_date) as $date): ?>
    <TD>
    <? if($row['start_date'] <= $date && ($row['end_date'] >= $date || $row['end_date'] == '') && isRepeatToday($row['rep_type'], $date, $row['start_date'], $row['every'], $row['rep_param1'], $row['rep_param2'])): ?>
      <? $active = true; ?>
      <DIV class="printbox">&nbsp;</DIV>
    <? else: ?>
      &mdash;
    <? endif; ?>
    </TD>
  <? endforeach; ?>
  <? if($active) $seen_tasks[$row['task_id']] = 0; ?>
</TR>
<?
if($active) {
  ob_end_flush();
  $mission_sent = $old_mission;
} else {
  ob_end_clean();
  if($mission_sent != $old_mission) $old_mission = $mission_sent;
}
?>
<? endwhile; ?>
</TBODY>
</TABLE>
<?
if($old_mission == -1):
  ob_end_clean();
  echo '<P>', T_('No Tasks.'), '</P>';
else:
  ob_end_flush();
?>
<H1><?=T_('Summary')?></H1>
<P><?=T_('Please count how many times you did each task, and fill in the boxes.')?></P>
<? if(!$task_dates) echo "-->"; ?>
<TABLE class="pretty_grid summary">
<?
mysql_data_seek($tasks, 0);
$old_row = $row = mysql_fetch_assoc($tasks);
do {
  if(isset($seen_tasks[$row['task_id']])) {
  $cols = 0;
?>
  <TR>
    <TH style="white-space: nowrap;"><?=es($row['subject_name']) . '<BR>' . es($row['mission_name'])?></TH>
<?
    }
    do { //loop tasks within a mission
      if(isset($seen_tasks[$row['task_id']])) {
        $old_row = $row;
        if(++$cols > $max_cols) {
          echo "</TR>\n<TR><TH></TH>\n";
          $cols = 1;
        }
  ?>
        <TD style="width: <?=$width?>%;">
          <DIV class="name"><?=es($old_row['name'])?> (<?=floatval($old_row['points'])?>)</DIV>
          <DIV class="description"><?=es($old_row['description'])?>&nbsp;</DIV>
          <? if($seen_tasks[$old_row['task_id']]): ?>
            <DIV class="duplicate"><?=T_('Duplicate of task already displayed.')?></DIV>
          <? else: ?>
            <DIV class="printbox">&nbsp;</DIV>
            <? $seen_tasks[$old_row['task_id']] = 1; ?>
          <? endif; ?>
        </TD>
<?
      }
      $row = mysql_fetch_assoc($tasks);
    } while($row && $old_row['subject_id'] == $row['subject_id'] && $old_row['mission_id'] == $row['mission_id']);
  if(isset($seen_tasks[$old_row['task_id']]))
    while($cols++ < $max_cols)
      echo "<TD style='width: $width%;'><BR><BR></TD>\n";

  if(isset($seen_tasks[$row['task_id']]))
    echo "</TR>\n";

} while($row);
?>
</TABLE>
<? endif; //ob_flush ?>
<? endif; //num_tasks>0 ?>
*/ ?>
<? /*
<H1><?=T_('Skill Building Tasks')?></H1>
<? $tasks = mq("
SELECT   floors.user_id, subject_id, subject_name, floor, mission_name, mission_description, chain_items.chain_item_id, room, name, description, points, mandatory_qty, optional_qty, IFNULL(done_total, 0) done_total, IFNULL(skipped_total, 0) skipped_total
FROM     (SELECT   users.user_id, school_type_id, subject_id, level, track_id, MIN(floor) floor
          FROM     users
                   JOIN user_tracks
                     USING (user_id)
                   JOIN chain_items
                     USING(school_type_id, subject_id, level, track_id)
                   LEFT JOIN (SELECT   user_id, chain_item_id, SUM(done_qty) + SUM(override_qty) done_total, SUM(skipped_qty) skipped_total
                              FROM     chain_marks
                              WHERE    user_id = {$user_row['user_id']}
                              GROUP BY user_id, chain_item_id) marks
                     ON (users.user_id = marks.user_id
                         AND chain_items.chain_item_id = marks.chain_item_id
                         AND done_total >= mandatory_qty
                         AND done_total + skipped_total >= mandatory_qty + optional_qty)
          WHERE    users.user_id = {$user_row['user_id']}
                   AND marks.chain_item_id IS NULL
          GROUP BY user_id, school_type_id, subject_id, level, track_id) floors
         JOIN chain_items
           USING (school_type_id, subject_id, level, track_id, floor)
         JOIN chain_missions
           USING (school_type_id, subject_id, level, track_id, floor)
         LEFT JOIN subjects
           USING (subject_id)
         LEFT JOIN (SELECT   user_id, chain_item_id, SUM(done_qty) + SUM(override_qty) done_total, SUM(skipped_qty) skipped_total
                    FROM     chain_marks
                    WHERE    user_id = {$user_row['user_id']}
                    GROUP BY user_id, chain_item_id) marks
           ON (floors.user_id = marks.user_id
               AND chain_items.chain_item_id = marks.chain_item_id)
WHERE    marks.chain_item_id IS NULL
          OR done_total < mandatory_qty
          OR done_total + skipped_total < mandatory_qty + optional_qty
ORDER BY user_id, subject_name, subject_id, mission_name, floor, room, chain_items.chain_item_id
"); ?>
<? if(!mysql_num_rows($tasks)): ?>
<P><?=T_('No tasks.')?></P>
<? else: ?>
<P><?=T_('Please mark the box for each time you did the task, or if available, you may choose to skip doing (some of) the task. Do not mark both boxes on the same line.')?></P>

<P><?=T_('Tasks that are skippable are optional, but you may do them for extra points. To complete the task for the mission, all of the lines must be marked, either as Done or as Skipped. If you don\'t finish the mission this week, your marks will be remembered and you can continue next week.')?></P>

<P><?=T_('Before turning in your paper, please count how many times you marked the task done (don\'t include tasks that were done previously), and how many times you skipped it, and enter it at the bottom of the task.')?></P>
<TABLE class="pretty_grid chain_tasks">
<?
$old_floor = -1;
$old_subject_id = -1;
while($row = mysql_fetch_assoc($tasks)):
  if($old_floor != $row['floor'] || $old_subject_id != $row['subject_id']) {
    if($old_floor != -1) {
      while($cols++ < $max_cols) echo "<TD style='width: $width%;'></TD>\n";;
      echo "</TR>\n";
    }
    $cols = 0;
    $old_floor = $row['floor'];
    $old_subject_id = $row['subject_id'];
    echo '<TR><TH colspan="', $max_cols, '">', es($row['subject_name']), ' - ', es($row['mission_name']), '<P class="mission_description">', es($row['mission_description']), "</P></TH></TR>\n<TR>";
  }
  $cols++;
  if($cols > $max_cols) {
    echo "</TR>\n<TR>\n";
    $cols = 1;
  }
?>
  <TD style="width: <?=$width?>%;">
    <DIV class="name"><?=es($row['name'])?> (<?=floatval($row['points'])?>)</DIV>
    <DIV class="description"><?=es($row['description'])?></DIV>
    <OL style="line-height: 2; white-space: nowrap; list-style-type: hebrew; padding: 0px; margin: 0px; text-align: <?=$align_end?>;">
      <? for($loop = 0; $loop < $row['mandatory_qty'] + $row['optional_qty']; $loop++): ?>
      <LI>
      <SPAN class="inline_printbox <?= $loop < $row['done_total'] ? 'filled_box' : ''?>"></SPAN> &nbsp; <?=T_('Done')?>
      <SPAN <?= $loop >= $row['mandatory_qty'] ? '' : 'style="visibility: hidden;"' ?>>&nbsp; &nbsp; <SPAN class="inline_printbox <?= $loop >= $row['mandatory_qty'] && $loop >= $row['done_total']  && ($loop < $row['done_total'] + $row['skipped_total'] || $loop < $row['mandatory_qty'] + $row['skipped_total']) ? 'filled_box' : ''?>"></SPAN> &nbsp; <?=T_('Skipped')?></SPAN>
      <? endfor; ?>
    </OL>
    <P class="parent_countbox">
      <SPAN class="inline_countbox"></SPAN> &nbsp; #<?=T_('Done')?><BR>
      <SPAN class="inline_countbox"></SPAN> &nbsp; #<?=T_('Skipped')?>
    </P>
  </TD>
  <? endwhile; ?>
  <? while($cols++ < $max_cols) echo "<TD style='width: $width%;'></TD>\n"; ?>
  </TR>
</TABLE>
<? endif;?>
*/ ?>

<H1><?=T_('Date Limited Tasks')?></H1>
<? $tasks = mq("
SELECT   user_id, subject_name, subject_id, start_date, end_date, mission_name, mission_description, date_tasks_mission_id,  name, date_task_id, description, points, mandatory_qty, optional_qty, IFNULL(done_total, 0) done_total
FROM     users
         JOIN user_tracks
           USING (user_id)
         JOIN date_tasks_missions
           USING (school_type_id, subject_id, level, track_id)
         JOIN date_tasks
           USING (date_tasks_mission_id)
         LEFT JOIN subjects
           USING (subject_id)
         LEFT JOIN (SELECT   user_id, date_task_id, SUM(done_qty) done_total
                    FROM     date_tasks_marks
                    WHERE    user_id = {$user_row['user_id']}
                    GROUP BY user_id, date_task_id) marks
           USING (user_id, date_task_id)
WHERE    users.user_id = {$user_row['user_id']}
         AND start_date <= $end_date
         AND end_date >= $start_date
ORDER BY user_id, subject_name, subject_id, start_date, end_date, mission_name, date_tasks_mission_id, ord, name, date_task_id
"); ?>
<? /* $tasks = mq("
SELECT   user_id, subject_name, subject_id, start_date, end_date, mission_name, date_tasks_mission_id,  name, date_task_id, description, points, mandatory_qty, optional_qty, IFNULL(SUM(done_qty), 0) done_total
FROM     users
         JOIN user_tracks
           USING (user_id)
         JOIN date_tasks_missions
           USING (school_type_id, subject_id, level, track_id)
         JOIN date_tasks
           USING (date_tasks_mission_id)
         LEFT JOIN subjects
           USING (subject_id)
         LEFT JOIN date_tasks_marks
           USING (user_id, date_task_id)
WHERE    users.user_id = {$user_row['user_id']}
         AND start_date <= $end_date
         AND end_date >= $start_date
GROUP BY user_id, subject_name, subject_id, start_date, end_date, mission_name, date_tasks_mission_id,  name, date_task_id, description, points, mandatory_qty, optional_qty
ORDER BY user_id, subject_name, subject_id, start_date, end_date, mission_name, date_tasks_mission_id, name, date_task_id;
"); */ ?>
<? if(!mysql_num_rows($tasks)): ?>
<P><?=T_('No tasks.')?></P>
<? else: ?>
<P><?=T_('Please mark a box each time you complete the task. Boxes under \'Other\' can be done on any date. Before turning in your paper please count how many times you marked the boxes (don\'t include tasks that were done previously) and enter the number in the Total column.')?></P>
<P><?=T_('You must complete the tasks at least the Mandatory number of times to complete the Mission, but you may do more (if available) for extra points.')?></P>
<P><?=T_('Each mission has a date range that it must be done within. If you didn\'t complete the mission this week, and the date range allows it, your marks will be remembered for next week. Even if you didn\'t complete the mission you will still get points for whatever you do.')?></P>
<TABLE class="pretty_grid tasks">
<THEAD>
<TR>
  <TH><?=T_('Mission')?></TH>
  <TH><?=T_('Task')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Points')?></TH>
  <TH><?=T_('Other')?></TH>
  <? foreach(range($start_date, $end_date) as $date): ?>
    <TH style="white-space: nowrap;"><?=es(dateToHebrewNoYear($date))?></TH>
  <? endforeach; ?>
  <TH><?=T_('Total')?></TH>
</TR>
</THEAD>
<TBODY>
<? $old_mission = -1; ?>
<? while($row = mysql_fetch_assoc($tasks)): ?>
<TR>
  <TH>
  <?
  if($old_mission != $row['date_tasks_mission_id']) {
    $old_mission = $row['date_tasks_mission_id'];
    echo es($row['subject_name']), '<BR>', es($row['mission_name']), '<BR><SPAN style="white-space: nowrap;">', es(dateToHebrewNoYear($row['start_date'])), '</SPAN> - <SPAN style="white-space: nowrap;">', es(dateToHebrewNoYear($row['end_date'])), '</SPAN><P class="mission_description">' . es($row['mission_description']) . '</P>';
  }
  ?>
  </TH>
  <TH>
    <?=es($row['name'])?><BR>
    <DIV style="font-weight: normal;">
    <?=T_('Mandatory')?>:&nbsp;<?=$row['mandatory_qty']?><BR>
    <?=T_('Optional')?>:&nbsp;<?=$row['optional_qty']?><BR>
    <?=T_('Done')?>:&nbsp;<?=$row['done_total']?><BR>
    </DIV>
  </TH>
  <TD class="description"><?=es($row['description'])?></TD>
  <TD><?=floatval($row['points'])?></TD>
  <? $dates = mq("SELECT nominal_date FROM date_tasks_dates WHERE date_task_id = {$row['date_task_id']} AND nominal_date >= $start_date AND nominal_date <= $end_date ORDER BY nominal_date LIMIT " . ($row['mandatory_qty'] + $row['optional_qty'])); ?>
  <TD>
    <? for($i = 0; $i < $row['mandatory_qty'] + $row['optional_qty'] - mysql_num_rows($dates); $i++): ?>
      <DIV class="printbox <?= $i < $row['done_total'] ? 'filled_box' : '' ?>">&nbsp;</DIV>
    <? endfor; ?>
    <? if(!$i) echo '-';?>
  </TD>
  <? list($nominal_date) = mysql_fetch_array($dates); ?>
  <? foreach(range($start_date, $end_date) as $date): ?>
    <TD>
    <? if($nominal_date == $date): ?>
      <? list($nominal_date) = mysql_fetch_array($dates); ?>
      <DIV class="printbox <?= $i++ < $row['done_total'] ? 'filled_box' : '' ?>">&nbsp;</DIV>
    <? else: ?>
      &mdash;
    <? endif; ?>
    </TD>
  <? endforeach; ?>
  <TD class="summary"><DIV class="printbox">&nbsp;</DIV></TD>
</TR>
<? endwhile; ?>
</TBODY>
</TABLE>
<? endif;?>

<? /*
<? if($auction_id != -1): ?>
<H1><?=T_('Auction Prizes')?></H1>
<TABLE CLASS="pretty_grid prizes">
<THEAD>
<TR>
<TH style="text-align: <?=$align_end?>;"><?=T_('Points')?></TH>
<?
mysql_data_seek($prizes_auction, 0);
$old_points_count = 0;
$row = $old_row = mysql_fetch_assoc($prizes_auction);
?>
<?
  do {
    if($old_row['prize_points'] == $row['prize_points']) {
      $old_points_count++;
    } else {
      echo "<TH colspan='{$old_points_count}'>" . es($old_row['prize_points']) . "</TH>\n";
      $old_points_count = 1;
      $old_row = $row;
    }
    $row = mysql_fetch_assoc($prizes_auction);
  } while($old_row);
?>
</TR>
<TR>
<TH style="text-align: <?=$align_end?>;"><?=T_('Name')?></TH>
<? mysql_data_seek($prizes_auction, 0); ?>
<? while($prize = mysql_fetch_assoc($prizes_auction)): ?>
  <TH><?=es($prize['prize_name'])?></TH>
<? endwhile; ?>
</TR>
</THEAD>
<TBODY>
<TR class="printbox">
  <TH><?=T_('# of entries')?></TH>
  <?=str_repeat('<TD>&nbsp;</TD>', mysql_num_rows($prizes_auction))?>
</TR>
</TBODY>
</TABLE>
<? endif; ?>
*/ ?>

<? /*
<H1><?=T_('History')?></H1>
<H3><?=T_('These are some of the tasks you did in the previous month.')?></H3>
<?
$tasks = mq("
SELECT   COUNT(task_id) num, SUM(marks.mark_points) total_points, tasks.task_id, tasks.name, subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
FROM     marks
         JOIN tasks USING (task_id)
         JOIN mission_tasks USING (task_id)
         JOIN missions USING (mission_id)
         LEFT JOIN subjects ON (subjects.subject_id = tasks.subject_id)
WHERE    (tasks.start_date <= $start_date
          AND (tasks.end_date >= $start_date-30
               OR tasks.end_date IS NULL))
         AND marks.user_id = {$user_row['user_id']}
         AND (subject_type = 'goal_hist' OR task_type = 'goal_hist')
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
GROUP BY task_id, tasks.name, subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($tasks, NULL, NULL, 'history');
?>
<H1><?=T_('Future Goals')?></H1>
<H3><?=T_('Short Term: Some of your upcoming tasks for next month.')?></H3>
<?
$tasks = mq("
SELECT   tasks.*, task_active.description, task_active.points,  subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
FROM     (users
         JOIN user_tracks USING (user_id)
         JOIN tasks USING (subject_id)
         JOIN task_active USING (task_id, track_id, level, school_type_id))
          JOIN mission_tasks USING (task_id)
          JOIN missions USING (mission_id)
          JOIN mission_active USING (mission_id, school_type_id, level, track_id)
         LEFT JOIN (subjects JOIN school_type_subjects USING (subject_id))
           ON (tasks.subject_id = subjects.subject_id AND users.school_type_id = school_type_subjects.school_type_id)
WHERE    (tasks.start_date <= $end_date+30
          AND (tasks.end_date >= $end_date
               OR tasks.end_date IS NULL))
         AND users.user_id = {$user_row['user_id']}
         AND (subject_type = 'goal_hist' OR task_type = 'goal_hist')
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($tasks, $end_date, $end_date+30);
?>
<H3><?=T_('Mid Term: If you went up a year, these would be some of your tasks.')?></H3>
<?
$tasks = mq("
SELECT   tasks.*, task_active.description, task_active.points,  subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
FROM     (users
         JOIN user_tracks USING (user_id)
         JOIN tasks USING (subject_id)
         JOIN task_active USING (task_id, track_id, school_type_id))
          JOIN mission_tasks USING (task_id)
          JOIN missions USING (mission_id)
          JOIN mission_active USING (mission_id, school_type_id, track_id)
         LEFT JOIN (subjects JOIN school_type_subjects USING (subject_id))
           ON (tasks.subject_id = subjects.subject_id AND users.school_type_id = school_type_subjects.school_type_id)
WHERE    (tasks.start_date <= $end_date
          AND (tasks.end_date >= $start_date
               OR tasks.end_date IS NULL))
         AND users.user_id = {$user_row['user_id']}
         AND mission_active.level = user_tracks.level+1
         AND task_active.level = user_tracks.level+1
         AND (subject_type = 'goal_hist' OR task_type = 'goal_hist')
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($tasks, $start_date, $end_date);
?>
<H3><?=T_('Long Term: At year 14, these would be some of your tasks.')?></H3>
<?
$tasks = mq("
SELECT   tasks.*, task_active.description, task_active.points,  subjects.subject_name, subjects.subject_id, missions.mission_id, missions.mission_name
FROM     (users
         JOIN user_tracks USING (user_id)
         JOIN tasks USING (subject_id)
         JOIN task_active USING (task_id, track_id, school_type_id))
          JOIN mission_tasks USING (task_id)
          JOIN missions USING (mission_id)
          JOIN mission_active USING (mission_id, school_type_id, track_id)
         LEFT JOIN (subjects JOIN school_type_subjects USING (subject_id))
           ON (tasks.subject_id = subjects.subject_id AND users.school_type_id = school_type_subjects.school_type_id)
WHERE    (tasks.start_date <= $end_date
          AND (tasks.end_date >= $start_date
               OR tasks.end_date IS NULL))
         AND users.user_id = {$user_row['user_id']}
         AND mission_active.level = 14
         AND task_active.level = 14
         AND (subject_type = 'goal_hist' OR task_type = 'goal_hist')
         AND (subjects.inst_id IS NULL OR subjects.inst_id = $inst_id)
ORDER BY subjects.subject_name, missions.mission_name, tasks.name, tasks.task_id
");
showTasks($tasks, $start_date, $end_date);
?>
*/ ?>

<DIV><?=$message1?></DIV>
<DIV><?=$message2?></DIV>
</DIV>
<? endwhile; ?>
</DIV>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
