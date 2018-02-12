<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$school_type_id = gri('school_type_id', -1);
$subject_id = gri('subject_id', -1);
$level = gri('level', -1);
$track_id = gri('track_id', -1);

if(gr('Save')) {

  if(gr('goal_start') === '' && gr('goal_end') === '')
    mq("DELETE FROM goals WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id");
  else
    mq("INSERT INTO goals SET school_type_id=$school_type_id, subject_id=$subject_id, level=$level, track_id=$track_id, goal_start=" . ms(gr('goal_start')) . ', goal_end=' . ms(gr('goal_end')) . ' ON DUPLICATE KEY UPDATE goal_start=' . ms(gr('goal_start')) . ', goal_end=' . ms(gr('goal_end')));

  $chain_item_ids = gra('chain_item_id');
  $mission_name = gra('mission_name');
  $mission_description = gra('mission_description');
  $name = gra('name');
  $description = gra('description');
  $mandatory_qty = gra('mandatory_qty');
  $optional_qty = gra('optional_qty');
  $label_id = gra('label_id');
  $quantity = gra('quantity');
  $points = gra('points');

  $floor = -1;
  //error checking flags
  $done = false;
  $tasks_done = false;
  $mission_done = false;

  mq("UPDATE chain_items SET floor=-floor-1, room=-room-1 WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id");

  foreach($chain_item_ids as $chain_item_id) {

    if($chain_item_id == 'row') {
      if($mission_done) { $done = $mission_done; break; }
      $floor++;
      $room=0;
      mq("INSERT INTO chain_missions SET school_type_id=$school_type_id, subject_id=$subject_id, level=$level, track_id=$track_id, floor=$floor, mission_name=" . ms(current($mission_name)) . ', mission_description=' . ms(current($mission_description)) . ' ON DUPLICATE KEY UPDATE mission_name=' . ms(current($mission_name)) . ', mission_description=' . ms(current($mission_description)));
      if(next($mission_name)===FALSE) $mission_done = 'mission_name';
      if(next($mission_description)===FALSE) $mission_done = 'mission_description';
      continue;
    }

    if($tasks_done) { $done = $tasks_done; break; }

    mq(($chain_item_id=='new' ? 'INSERT INTO' : 'UPDATE') . " chain_items SET school_type_id=$school_type_id, subject_id=$subject_id, level=$level, track_id=$track_id, floor=$floor, room=$room, name=" . ms(current($name)) . ", description=" . ms(current($description)) . ", mandatory_qty=" . max(0, min(intval(current($mandatory_qty)), 65535)) . ", optional_qty=" . max(0, min(intval(current($optional_qty)), 65535)) . ", label_id=" . nullif(current($label_id), '-1') . ", quantity=" . nullif_max(current($quantity), 65535) . ", points=" . max(0, min(floatval(current($points)), 9999.99)) . ($chain_item_id=='new' ? '' : ' WHERE chain_item_id = ' .  intval($chain_item_id)));

    $room++;

    if(next($name)===FALSE) $tasks_done = 'name';
    if(next($description)===FALSE) $tasks_done = 'description';
    if(next($mandatory_qty)===FALSE) $tasks_done = 'mandatory_qty';
    if(next($optional_qty)===FALSE) $tasks_done = 'optional_qty';
    if(next($label_id)===FALSE) $tasks_done = 'label_id';
    if(next($quantity)===FALSE) $tasks_done = 'quantity';
    if(next($points)===FALSE) $tasks_done = 'points';
  }

  mq("DELETE FROM chain_items WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id AND (room < 0 OR floor < 0)");
  mq("DELETE FROM chain_missions WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id AND floor > $floor");

  if($done) trigger_error("$done beyond end", E_USER_ERROR);

  $message = T_('Chain items saved.');

} elseif(gr('Clone')) {

  if($floors = array_filter(gra('floor', array()), 'is_numeric')) {

    $result = mq('SELECT floor, mission_name, mission_description FROM chain_missions WHERE school_type_id = ' . gri('orig_school_type_id', -1) . " AND subject_id = $subject_id AND level = " . gri('orig_level', -1) . ' AND track_id = ' . gri('orig_track_id', -1) . ' AND floor IN (' . implode(',', $floors) . ') ORDER BY floor');

    while($row = mysql_fetch_assoc($result)) {

      $floor = mysql_result(mq("SELECT IFNULL(MAX(floor), 0) floor FROM (
          SELECT MAX(floor)+1 floor FROM chain_missions WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id
            UNION
          SELECT MAX(floor)+1 floor FROM chain_items WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id
        ) floors"), 0);

      mq("INSERT INTO chain_missions (school_type_id, subject_id, level, track_id, floor, mission_name, mission_description) VALUES ($school_type_id, $subject_id, $level, $track_id, $floor, " . ms($row['mission_name']) . ', ' . ms($row['mission_description']) . ')');

      mq("INSERT INTO chain_items (school_type_id, subject_id, level, track_id, floor, room, name, description, mandatory_qty, optional_qty, label_id, quantity, points) SELECT $school_type_id school_type_id, $subject_id subject_id, $level level, $track_id track_id, $floor, room, name, description, mandatory_qty, optional_qty, label_id, quantity, points FROM chain_items WHERE school_type_id = " . gri('orig_school_type_id', -1) . " AND subject_id = $subject_id AND level = " . gri('orig_level', -1) . ' AND track_id = ' . gri('orig_track_id', -1) . " AND floor = {$row['floor']}");
    }
    $message = T_('Chain items cloned.');
  }

  if(gr('goals')) {

    mq("INSERT INTO goals (school_type_id, subject_id, level, track_id, goal_start, goal_end) SELECT $school_type_id school_type_id, $subject_id subject_id, $level level, $track_id track_id, goal_start, goal_end FROM goals goals_clone WHERE school_type_id = " . gri('orig_school_type_id', -1) . " AND subject_id = $subject_id AND level = " . gri('orig_level', -1) . ' AND track_id = ' . gri('orig_track_id', -1) . ' ON DUPLICATE KEY UPDATE goals.goal_start = goals_clone.goal_start, goals.goal_end = goals_clone.goal_end');

    if(!isset($message))
      $message = '';
    else
      $message .= ' ';

    $message .= T_('Goals cloned.');
  }
}

$labels_result = mq('SELECT label_id, label_name FROM labels ORDER BY label_name');
$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');

if($school_type_id != -1) $school_type = mysql_result(mq("SELECT school_type_name FROM school_types WHERE school_type_id = $school_type_id"), 0);
if($subject_id != -1) $subject = mysql_result(mq("SELECT subject_name FROM subjects WHERE subject_id = $subject_id"), 0);
if($track_id != -1) $track = mysql_result(mq("SELECT track_name FROM tracks WHERE track_id = $track_id"), 0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Chains'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<? if($school_type_id != -1 && $subject_id != -1 && $level != -1 && $track_id != -1): ?>
<SCRIPT type="text/javascript">
function addRoom(el, half) {
  var row = el.parentNode;
  var cell_in = document.createElement('td');
  cell_in.innerHTML = '<DIV style="white-space: nowrap;"><A HREF="#" onClick="copyRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Copy')?><?=$x2197?><\/A> &nbsp; <A HREf="#" onClick="moveRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Move')?><?=$x21d7?><\/A> &nbsp; <A HREF="#" onClick="delRoom(this.parentNode.parentNode); return false;" title="<?=T_('Delete')?>">&times;<\/A><\/DIV><LABEL><?=T_('Task Name')?>:<BR><INPUT type="text" name="name[]" value="" maxlength="255" size="12"><\/LABEL><BR><LABEL><?=T_('Description')?>:<BR><TEXTAREA name="description[]" rows="2" cols="11"><\/TEXTAREA><\/LABEL><BR><LABEL><?=T_('Points')?>:<BR><INPUT type="text" name="points[]" maxlength="7" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseFloat(\'0\'+this.value, 10), 9999.99)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Mandatory Reps')?>:<BR><INPUT type="text" name="mandatory_qty[]" maxlength="5" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Optional Reps')?>:<BR><INPUT type="text" name="optional_qty[]" maxlength="5" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Label')?>:<BR><SELECT name="label_id[]"><OPTION value="-1"><\/OPTION><? @mysql_data_seek($labels_result, 0); ?><? while($label_row = mysql_fetch_assoc($labels_result)): ?><OPTION value="<?=$label_row['label_id']?>"><?=esq(es($label_row['label_name']))?><\/OPTION><? endwhile; ?><\/SELECT><BR><\/LABEL><LABEL><?=T_('Quantity')?>:<BR><INPUT type="text" name="quantity[]" maxlength="5" size="12" value="" onChange="if(this.value != \'\') this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535));"><\/LABEL><BR><A HREF="#" onClick="copyRoom(this.parentNode, +1); return false;"><?=T_('Copy')?><?=$x2198?><\/A> &nbsp; <A HREf="#" onClick="moveRoom(this.parentNode, +1); return false;"><?=T_('Move')?><?=$x21d8?><\/A><INPUT type="hidden" name="chain_item_id[]" value="new">';
  if(!half) {
    var cell_ar = document.createElement('th');
    cell_ar.innerHTML = "<TH><A HREF='#' title='<?=T_('Swap tasks')?>' onClick='swapRooms(this.parentNode); return false;'>&#8644;<\/A><\/TH>";
    row.insertBefore(cell_ar, el);
  }
  row.insertBefore(cell_in, el);
  updateColspan(el.parentNode.parentNode);
}

function delRoom(el) {
  var table=el.parentNode.parentNode;
  if(el.parentNode.cells.length == 3) {
    delFloor(el.parentNode);
  } else {
    el.parentNode.deleteCell(el.cellIndex+(el.cellIndex > 1 ? -1 : 1));
    el.parentNode.deleteCell(el.cellIndex);
  }
  updateColspan(table);
}

function copyRoom(el, dir) {
  var new_el;
  if(el.parentNode.rowIndex == 0 && dir == -1 || el.parentNode.rowIndex >= (el.parentNode.parentNode.rows.length - 3) && dir == +1) {
    addFloor(el.parentNode, el.parentNode.rowIndex+(dir < 0 ? 0 : 1), new_el=el.cloneNode(true));
  } else {
    new_el = el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].insertBefore(el.cloneNode(true), el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells[el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells.length-1]);
    var cell_ar = document.createElement('th');
    cell_ar.innerHTML = "<TH><A HREF='#' title='<?=T_('Swap tasks')?>' onClick='swapRooms(this.parentNode); return false;'>&#8644;<\/A><\/TH>";
    new_el.parentNode.insertBefore(cell_ar, new_el);
  }
  updateColspan(el.parentNode.parentNode);
  getSubElementByName(new_el, 'chain_item_id[]').value = 'new';
}

function moveRoom(el, dir) {
  if(el.parentNode.rowIndex == 0 && dir == -1 || el.parentNode.rowIndex >= (el.parentNode.parentNode.rows.length - 3) && dir == +1) {
    if(el.parentNode.cells.length <= 3) return;
    el.parentNode.deleteCell(el.cellIndex+(el.cellIndex > 1 ? -1 : 1));
    addFloor(el.parentNode, el.parentNode.rowIndex+(dir < 0 ? 0 : 1), el);
  } else {
    var last;
    if(el.parentNode.cells.length > 3) {
      var el_arr = el.parentNode.cells[el.cellIndex+(el.cellIndex > 1 ? -1 : 1)];
      el_arr.parentNode.parentNode.rows[el_arr.parentNode.rowIndex+dir*2].insertBefore(el_arr, el_arr.parentNode.parentNode.rows[el_arr.parentNode.rowIndex+dir*2].cells[el_arr.parentNode.parentNode.rows[el_arr.parentNode.rowIndex+dir*2].cells.length-1]);
      last = false;
    } else {
      last = el.parentNode;
    }
    el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].insertBefore(el, el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells[el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells.length-1]);

    if(last) {
      var cell_ar = document.createElement('th');
      cell_ar.innerHTML = "<TH><A HREF='#' title='<?=T_('Swap tasks')?>' onClick='swapRooms(this.parentNode); return false;'>&#8644;<\/A><\/TH>";
      el.parentNode.insertBefore(cell_ar, el);
      delFloor(last);
    }
  }
  updateColspan(el.parentNode.parentNode);
}

function swapRooms(el) {
  var left = el.parentNode.cells[el.cellIndex - 1];
  var right = el.parentNode.cells[el.cellIndex + 1];

  var next = right.nextSibling;
  left.parentNode.insertBefore(right, left);
  next.parentNode.insertBefore(left, next);
}

function addFloor(el, row, room) {
  if(!row) row = el.rowIndex;
  var floor;
  floor = el.parentNode.insertRow(row);
  floor.innerHTML = '<TH class="sized"><INPUT type="hidden" name="chain_item_id[]" value="row"><?=T_('Subject')?>:<BR><?=es($subject)?><BR><?=T_('Ladder')?> <?=es($track)?> : <?=T_('Year')?> <?=es($level)?><BR>Mission #<INPUT type="text" name="number" size="3" DISABLED><BR><LABEL><?=T_('Mission name')?><BR><INPUT type="text" name="mission_name[]" maxlength="255" size="12"><\/LABEL><BR><LABEL><?=T_('Mission description')?><BR><TEXTAREA name="mission_description[]" rows="2" cols="11"><\/TEXTAREA><\/LABEL><BR><LABEL><?=T_('Number of tasks')?><BR><INPUT type="text" name="tasks" size="12" DISABLED><\/LABEL><BR><LABEL><?=T_('Points Mandatory')?><BR><INPUT type="text" name="points_mandatory" size="12" DISABLED><\/LABEL><BR><LABEL><?=T_('Points Optional')?><BR><INPUT type="text" name="points_optional" size="12" DISABLED><\/LABEL><BR><A HREF="#" onClick="delFloor(this.parentNode.parentNode); return false;">&#8622; <?=T_('Delete mission')?> &#8622;<\/A><BR><A HREF="#" onClick="copyFloor(this.parentNode.parentNode); return false;">&#8615; <?=T_('Copy mission')?> &#8615;<\/A><\/TH><TH colspan="0" style="text-align: <?=$align_start?>; white-space: nowrap;"><A HREF="#" onClick="addRoom(this.parentNode); return false;">&laquo; <?=T_('Add task')?><\/A><\/TH>';
  if(!room) {
    addRoom(floor.cells[floor.cells.length-1], true);
  } else {
    floor.insertBefore(room, floor.cells[1]);
  }

  if(el.parentNode.rows.length>3) {
    floor = el.parentNode.insertRow(row + (row ? 0 : 1));
    floor.innerHTML = "<TH colspan='0' style='font-size: 225%;'><A HREF='#' title='<?=T_('Swap the missions')?>' onClick='swapFloors(this.parentNode.parentNode); return false;'>&#8693;<\/A><\/TH>";
  }

  updateColspan(el.parentNode);
}

function delFloor(el) {
  var table=el.parentNode;
  if(el.parentNode.rows.length > 3) {
    el.parentNode.deleteRow(el.rowIndex + (el.rowIndex ? -1 : 1));
  }
  el.parentNode.deleteRow(el.rowIndex);
  updateColspan(table);
}

function copyFloor(el) {
  var floor = el.parentNode.insertRow(el.rowIndex+1);
  floor.innerHTML = "<TH colspan='0' style='font-size: 225%;'><A HREF='#' title='<?=T_('Swap the missions')?>' onClick='swapFloors(this.parentNode.parentNode); return false;'>&#8693;<\/A><\/TH>";
  var new_row = el.parentNode.insertBefore(el.cloneNode(true), floor.nextSibling);
  updateColspan(el.parentNode);

  for(var i=1; i<new_row.cells.length; i+=2) {
    getSubElementByName(new_row.cells[i], 'chain_item_id[]').value = 'new';
  }
}

function swapFloors(el) {
  var above = el.parentNode.rows[el.rowIndex - 1];
  var below = el.parentNode.rows[el.rowIndex + 1];

  var next = below.nextSibling;
  above.parentNode.insertBefore(below, above);
  next.parentNode.insertBefore(above, next);
  updateMissionNumber(above);
  updateMissionNumber(below);
}

function updateColspan(table) {
  var max=1;
  for(var i=0; i<table.rows.length; i++) {
    max = Math.max(table.rows[i].cells.length, max);
  }
  for(var i=0; i<table.rows.length; i++) {
    table.rows[i].cells[table.rows[i].cells.length-1].colSpan = max-(table.rows[i].cells.length-1);
    if(table.rows[i].cells.length>1) {
      updateMissionTasks(table.rows[i]);
      updateMissionNumber(table.rows[i]);
    }
  }
}

function updateMissionNumber(row) {
  getSubElementByName(row.cells[0], 'number').value = (row.rowIndex/2)+1;
}

function updateMissionTasks(row) {
  getSubElementByName(row.cells[0], 'tasks').value = (row.cells.length-1)/2;
  updateMissionPoints(row);
}

function updateMissionPoints(row) {
  var man = 0;
  var opt = 0;
  for(var i=1; i<row.cells.length; i+=2) {
    man += getSubElementByName(row.cells[i], 'points[]').value * getSubElementByName(row.cells[i], 'mandatory_qty[]').value;
    opt += getSubElementByName(row.cells[i], 'points[]').value * getSubElementByName(row.cells[i], 'optional_qty[]').value;
  }
  getSubElementByName(row.cells[0], 'points_mandatory').value = man.toFixed(2);
  getSubElementByName(row.cells[0], 'points_optional').value = opt.toFixed(2);
}

function getSubElementByName(parent, name) {
  var els = parent.getElementsByTagName('input');
  for(var i=0; i<els.length; i++) {
    if(els[i].name==name) return els[i];
  }
  return null;
}
</SCRIPT>
<? endif; ?>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Chains')?></H1>

<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<? $school_type_result = mq('SELECT school_type_id, school_type_name FROM school_types ORDER BY school_type_name'); ?>

<FORM action="admin_chains.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select School Type')?>:
<SELECT name="school_type_id">
  <? while($row = mysql_fetch_assoc($school_type_result)): ?>
    <OPTION VALUE="<?=$row['school_type_id']?>" <?=$school_type_id == $row['school_type_id'] ? 'SELECTED' : '' ?>><?=es($row['school_type_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($school_type_id == -1):?>
<?=T_('Please select a School Type.')?>
<?else:?>
<? $subject_result = mq("SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects JOIN school_type_subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE school_type_id = $school_type_id ORDER BY institutions.inst_name, subjects.subject_name"); ?>

<FORM action="admin_chains.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id">
  <? while($row = mysql_fetch_assoc($subject_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>
<INPUT type="hidden" name="school_type_id" value="<?=$school_type_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($subject_id == -1):?>
<?=T_('Please select a Subject.')?>
<?else:?>

<TABLE class="grid" id="track_level">
<TR>
  <TH rowspan="<?=mysql_num_rows($tracks_result)+2?>"><?=T_('Ladder')?></TH>
  <TH colspan="13"><?=T_('Year')?></TH>
</TR>
<TR>
    <TH></TH>
    <? foreach(range(3, 14) as $each_level): ?>
      <TH><?=$each_level?></TH>
    <? endforeach; ?>
</TR>
<? while($track_row = mysql_fetch_assoc($tracks_result)): ?>
  <TR>
    <TH><?=es($track_row['track_name'])?></TH>
    <? foreach(range(3, 14) as $each_level): ?>
      <? $row = mysql_fetch_assoc(mq("SELECT COUNT(DISTINCT floor) missions, COUNT(*) tasks FROM chain_items WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $each_level AND track_id = {$track_row['track_id']}")); ?>
      <TD style="text-align: <?=$align_start?>;" <?= $level==$each_level && $track_id==$track_row['track_id'] ? 'id="selected"' : '' ?>><A HREF="admin_chains.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;level=<?=$each_level?>&amp;track_id=<?=$track_row['track_id']?>"><?=$row['missions']?> <?=T_('Missions')?><BR><?=$row['tasks']?> <?=T_('Tasks')?></A></TD>
    <? endforeach; ?>
  </TR>
<? endwhile; ?>
</TABLE>
<HR>

<?if($track_id == -1 || $level == -1): ?>

<?=T_('Please select a ladder and year.')?>

<?else:?>

<? $result = mq("SELECT mission_name, mission_description, chain_item_id, floor, room, name, description, mandatory_qty, optional_qty, label_id, quantity, points FROM chain_items LEFT JOIN chain_missions USING (school_type_id, subject_id, level, track_id, floor) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $level AND track_id = $track_id ORDER BY school_type_id, subject_id, level, track_id, floor, room"); ?>
<? $goals_row = mysql_fetch_assoc(mq("SELECT goal_start, goal_end FROM goals WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id")); ?>

<?if(gr('action')=='clone'):?>

<?
@mysql_data_seek($subject_result, 0);
@mysql_data_seek($school_type_result, 0);
@mysql_data_seek($tracks_result, 0);
?>

<FORM action="admin_chains.php" method="post" accept-charset="UTF-8">
<P>

<?=T_('Clone missions to')?>:

<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="orig_school_type_id" value="<?=$school_type_id?>">
<INPUT type="hidden" name="orig_level" value="<?=$level?>">
<INPUT type="hidden" name="orig_track_id" value="<?=$track_id?>">

<LABEL><?=T_('Ladder')?>:
<SELECT name="track_id">
  <? while($row = mysql_fetch_assoc($tracks_result)): ?>
    <OPTION VALUE="<?=$row['track_id']?>" <?=$track_id == $row['track_id'] ? 'SELECTED' : '' ?>><?=es($row['track_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>

<LABEL><?=T_('Year')?>:
<SELECT name="level">
  <? foreach(range(3, 14) as $each_level): ?>
    <OPTION VALUE="<?=$each_level?>" <?=$level == $each_level ? 'SELECTED' : '' ?>><?=$each_level?></OPTION>
  <? endforeach; ?>
</SELECT></LABEL>

<LABEL><?=T_('School Type')?>:
<SELECT name="school_type_id">
  <? while($row = mysql_fetch_assoc($school_type_result)): ?>
    <OPTION VALUE="<?=$row['school_type_id']?>" <?=$school_type_id == $row['school_type_id'] ? 'SELECTED' : '' ?>><?=es($row['school_type_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>

<INPUT type="submit" name="Clone" value="Clone">
</P>
<TABLE cellpadding="6">
<CAPTION><?=T_('Select the missions you want to copy.')?></CAPTION>
<?
  $old_row = $row = mysql_fetch_assoc($result);
  if($row) do {
?>
<TR>
  <TD><INPUT type="checkbox" id="floor_<?=$row['floor']?>" name="floor[]" value="<?=$row['floor']?>" checked></TD>
  <TH><LABEL for="floor_<?=$row['floor']?>"><?=es($row['mission_name'])?></LABEL></TH>
<?
    $old_row = $row;
    do {
?>
  <TD><LABEL for="floor_<?=$row['floor']?>"><?=es($row['name'])?></LABEL></TD>
<?
      $row = mysql_fetch_assoc($result);
    } while($row && $old_row['floor'] == $row['floor']);
    echo "</TR>\n";
  } while($row);
?>

<? if($goals_row): ?>
<TR>
  <TD><INPUT type="checkbox" id="goals" name="goals" checked></TD>
  <TH><LABEL for="goals">&lt;<?=T_('Goals')?>&gt;</LABEL></TH>
  <TD><LABEL for="goals"><?=$goals_row['goal_start']?></LABEL></TD>
  <TD><LABEL for="goals"><?=$goals_row['goal_end']?></LABEL></TD>
</TR>
<? endif; ?>

</TABLE>

</FORM>

<?else:?>

<P><A HREF="admin_chains.php?action=clone&amp;school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;level=<?=$level?>&amp;track_id=<?=$track_id?>"><?=T_('Clone Missions')?></A></P>

<FORM action="admin_chains.php" method="post" accept-charset="UTF-8">
<TABLE id="tasks" class="task_grid grid" style="empty-cells: show; border-collapse: separate;">
<?
  $old_row = $row = mysql_fetch_assoc($result);
  $firstFloor = true;
  if($row) do {
    if(!$firstFloor) echo "<TR><TH colspan='0' style='font-size: 225%;'><A HREF='#' title='", T_('Swap the missions'),  "' onClick='swapFloors(this.parentNode.parentNode); return false;'>&#8693;</A></TH></TR>\n";
    $firstFloor = false;
?>
<TR>
  <TH class='sized'>
    <INPUT type="hidden" name="chain_item_id[]" value="row">
    <?=T_('Subject')?>:<BR>
    <?=es($subject)?><BR>
    <?=T_('Ladder')?> <?=es($track)?> : <?=T_('Year')?> <?=es($level)?><BR>
    Mission #<INPUT type="text" name="number" size="3" DISABLED><BR>
    <LABEL><?=T_('Mission name')?><BR>
    <INPUT type="text" name="mission_name[]" value="<?=T_(es($row['mission_name']))?>" maxlength="255" size="12"></LABEL><BR>
    <LABEL><?=T_('Mission description')?><BR>
    <TEXTAREA name="mission_description[]" rows="2" cols="11"><?=T_(es($row["mission_description"]))?></TEXTAREA></LABEL><BR>
    <LABEL><?=T_('Number of tasks')?><BR>
    <INPUT type="text" name="tasks" size="12" DISABLED></LABEL><BR>
    <LABEL><?=T_('Points Mandatory')?><BR>
    <INPUT type="text" name="points_mandatory" size="12" DISABLED></LABEL><BR>
    <LABEL><?=T_('Points Optional')?><BR>
    <INPUT type="text" name="points_optional" size="12" DISABLED></LABEL><BR>
    <A HREF="#" onClick="delFloor(this.parentNode.parentNode); return false;">&#8622; <?=T_('Delete mission')?> &#8622;</A><BR>
    <A HREF="#" onClick="copyFloor(this.parentNode.parentNode); return false;">&#8615; <?=T_('Copy mission')?> &#8615;</A>
  </TH>
<?
    $old_row = $row;
    $firstRoom = true;
    do {
      if(!$firstRoom) echo "<TH><A HREF='#' title='", T_('Swap tasks'), "' onClick='swapRooms(this.parentNode); return false;'>&#8644;</A></TH>";
?>
<TD>
  <DIV style="white-space: nowrap;">
  <A HREF="#" onClick="copyRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Copy')?><?=$x2197?></A> &nbsp;
  <A HREf="#" onClick="moveRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Move')?><?=$x21d7?></A> &nbsp;
  <A HREF="#" onClick="delRoom(this.parentNode.parentNode); return false;" title="<?=T_('Delete')?>">&times;</A>
  </DIV>
  <LABEL><?=T_('Task Name')?>:<BR>
  <INPUT type="text" name="name[]" value="<?=T_(es($row["name"]))?>" maxlength="255" size="12"></LABEL><BR>
  <LABEL><?=T_('Description')?>:<BR>
  <TEXTAREA name="description[]" rows="2" cols="11"><?=T_(es($row["description"]))?></TEXTAREA></LABEL><BR>
  <LABEL><?=T_('Points')?>:<BR>
  <INPUT type="text" name="points[]" maxlength="7" size="12" value="<?=floatval($row['points'])?>" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99)); updateMissionPoints(this.parentNode.parentNode.parentNode);"></LABEL><BR>
  <LABEL><?=T_('Mandatory Reps')?>:<BR>
  <INPUT type="text" name="mandatory_qty[]" maxlength="5" size="12" value="<?=$row['mandatory_qty']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"></LABEL><BR>
  <LABEL><?=T_('Optional Reps')?>:<BR>
  <INPUT type="text" name="optional_qty[]" maxlength="5" size="12" value="<?=$row['optional_qty']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"></LABEL><BR>
  <LABEL><?=T_('Label')?>:<BR>
  <SELECT name="label_id[]">
  <OPTION value="-1"></OPTION>
  <? @mysql_data_seek($labels_result, 0); ?>
  <? while($label_row = mysql_fetch_assoc($labels_result)): ?>
    <OPTION value="<?=$label_row['label_id']?>" <?=$label_row['label_id'] == $row['label_id'] ? 'SELECTED' : ''?>><?=es($label_row['label_name'])?></OPTION>
  <? endwhile; ?>
  </SELECT><BR></LABEL>
  <LABEL><?=T_('Quantity')?>:<BR>
  <INPUT type="text" name="quantity[]" maxlength="5" size="12" value="<?=$row['quantity']?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"></LABEL><BR>
  <A HREF="#" onClick="copyRoom(this.parentNode, +1); return false;"><?=T_('Copy')?><?=$x2198?></A> &nbsp;
  <A HREf="#" onClick="moveRoom(this.parentNode, +1); return false;"><?=T_('Move')?><?=$x21d8?></A>
  <INPUT type="hidden" name="chain_item_id[]" value="<?=$row["chain_item_id"]?>">
</TD>
<?
      $firstRoom = false;
      $row = mysql_fetch_assoc($result);
    } while($row && $old_row['floor'] == $row['floor']);
?>
<TH colspan="0" style="text-align: <?=$align_start?>; white-space: nowrap;">
  <A HREF="#" onClick="addRoom(this.parentNode); return false;">&laquo; <?=T_('Add task')?></A><BR>
</TH>
</TR>
<?
  } while($row);
?>
  <TR>
    <TH colspan="0"><BR><A HREF="#" onClick="addFloor(this.parentNode.parentNode); return false;">&uArr;<?=T_('Add mission')?>&uArr;</A><BR><BR></TH>
  </TR>
  <TR>
    <TH colspan="0">
      <INPUT type="hidden" name="school_type_id" value="<?=$school_type_id?>">
      <INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
      <INPUT type="hidden" name="level" value="<?=$level?>">
      <INPUT type="hidden" name="track_id" value="<?=$track_id?>">
      <LABEL><?=T_('Goal at Start of year')?>:<BR><INPUT type="text" name="goal_start" maxlength="255" value="<?=$goals_row['goal_start']?>"></LABEL><BR>
      <LABEL><?=T_('Goal at End of year')?>:<BR><INPUT type="text" name="goal_end" maxlength="255" value="<?=$goals_row['goal_end']?>"></LABEL><BR>
      <INPUT type="submit" name="Save" value="Save">
    </TH>
  </TR>
</TABLE>
</FORM>
<SCRIPT type="text/javascript">updateColspan(document.getElementById('tasks'));</SCRIPT>
<?endif;?>
<?endif;?>
<?endif;?>
<?endif;?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
