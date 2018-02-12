<? 
$admin_auth = array(); 

require('header.php'); 
require_once('calendar.php');

$action = gr('action');
$school_type_id = gri('school_type_id', -1);
$subject_id = gri('subject_id', -1);
$level = gri('level', -1);
$track_id = gri('track_id', -1);

if (gr('Save')) {

	echo "*** SAVE ***<br />";
	
	if (gr('goal_start') === '' && gr('goal_end') === '')
		mq("DELETE FROM goals WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id");
	else
		mq("INSERT INTO goals SET school_type_id=$school_type_id, subject_id=$subject_id, level=$level, track_id=$track_id, goal_start=" . ms(gr('goal_start')) . ', goal_end=' . ms(gr('goal_end')) . ' ON DUPLICATE KEY UPDATE goal_start=' . ms(gr('goal_start')) . ', goal_end=' . ms(gr('goal_end')));

	$date_task_ids = gra('date_task_id');
	$date_tasks_mission_ids = gra('date_tasks_mission_id');
	$mission_name = gra('mission_name');
	$mission_number = gra('mission_number');
	$mission_description = gra('mission_description');
	$mission_value = gra('mission_value');
	$start_date = gra('start_date');
	$end_date = gra('end_date');

	$name = gra('name');
	$description = gra('description');
	$points = gra('points');
	$mandatory_qty = gra('mandatory_qty');
	$optional_qty = gra('optional_qty');
	$is_bonus = gra('is_bonus');
	$label_id = gra('label_id');
	$quantity = gra('quantity');
	$nominal_dates = gra('nominal_dates');

	//error checking flags
	$done = false;
	$tasks_done = false;
	$mission_done = false;
	
	$all_date_tasks_mission_ids = array(-1);
	$all_date_task_ids = array(-1);
	
	foreach ($date_tasks_mission_ids as $date_tasks_mission_id) {
		if ($date_tasks_mission_id != 'new')
			$all_date_tasks_mission_ids[] = $date_tasks_mission_id;
	}
	
	reset($date_tasks_mission_ids);
	
	foreach	($date_task_ids as $date_task_id) {
		if ($date_task_id != 'new' && $date_task_id != 'row')
			$all_date_task_ids[] = $date_task_id;
	}
	

	$new_date_tasks_mission_id = 0;

	mq("DELETE FROM date_tasks USING date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id AND date_task_id NOT IN (" . implode(',', $all_date_task_ids) . ')');
	mq("DELETE FROM date_tasks_missions WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id AND date_tasks_mission_id NOT IN (" . implode(',', $all_date_tasks_mission_ids) . ')');
	mq("UPDATE date_tasks_missions SET mission_number = NULL WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id");

	foreach($date_task_ids as $date_task_id) {
	
		if ($date_task_id == 'row') {
		
			if($mission_done) { 
				$done = $mission_done; 
				break; 
			}
			
			mq((current($date_tasks_mission_ids)=='new' ? 'INSERT INTO' : 'UPDATE') . " date_tasks_missions SET school_type_id=$school_type_id, subject_id=$subject_id, level=$level, track_id=$track_id, mission_name=" . ms(current($mission_name)) . ', mission_description=' . ms(current($mission_description)) . ', mission_value=' . floatval(current($mission_value)) . ', start_date=' . intval(current($start_date)) . ', end_date=' . intval(current($end_date)) . (current($date_tasks_mission_ids)=='new' ? '' : ' WHERE date_tasks_mission_id = ' .  intval(current($date_tasks_mission_ids))));
			$new_date_tasks_mission_id = current($date_tasks_mission_ids)=='new' ? mysql_insert_id() : intval(current($date_tasks_mission_ids));
			$ord = 0;
			mq('UPDATE IGNORE date_tasks_missions SET mission_number = ' . nullif(current($mission_number), '') . " WHERE date_tasks_mission_id = $new_date_tasks_mission_id"); //separate statement in case it fails due to unique
			
			if(next($date_tasks_mission_ids)===FALSE) $mission_done = 'date_tasks_mission_id';
			if(next($mission_name)===FALSE) $mission_done = 'mission_name';
			if(next($mission_number)===FALSE) $mission_done = 'mission_number';
			if(next($mission_description)===FALSE) $mission_done = 'mission_description';
			if(next($mission_value)===FALSE) $mission_done = 'mission_value';
			if(next($start_date)===FALSE) $mission_done = 'start_date';
			if(next($end_date)===FALSE) $mission_done = 'end_date';
			continue;
		}

		if ($tasks_done) { 
			$done = $tasks_done; 
			break; 
		}

		mq(($date_task_id=='new' ? 'INSERT INTO' : 'UPDATE') . " date_tasks SET date_tasks_mission_id=$new_date_tasks_mission_id, ord=$ord, name=" . ms(current($name)) . ", description=" . ms(current($description)) . ", mandatory_qty=" . max(0, min(intval(current($mandatory_qty)), 65535)) . ", optional_qty=" . max(0, min(intval(current($optional_qty)), 65535)) . ", is_bonus=" . max(0, min(intval(current($is_bonus)), 255)) . ", label_id=" . nullif(current($label_id), '-1') . ", quantity=" . nullif_max(current($quantity), 65535) . ", points=" . max(0, min(floatval(current($points)), 9999.99)) . ($date_task_id=='new' ? '' : ' WHERE date_task_id = ' .  intval($date_task_id)));

		$new_date_task_id = $date_task_id=='new' ? mysql_insert_id() : intval($date_task_id);
		$nominal_dates_array = array_filter(explode(',', current($nominal_dates)), 'is_numeric');
		
		mq("DELETE FROM date_tasks_dates WHERE date_task_id = $new_date_task_id" . (!empty($nominal_dates_array) ? ' AND nominal_date NOT IN (' . implode(',', $nominal_dates_array) . ')' : ''));
		
		foreach ($nominal_dates_array as $nominal_date) 
			mq("INSERT IGNORE INTO date_tasks_dates (date_task_id, nominal_date) VALUES ($new_date_task_id, $nominal_date)");

		$ord++;
		
		if(next($name)===FALSE) $tasks_done = 'name';
		if(next($description)===FALSE) $tasks_done = 'description';
		if(next($points)===FALSE) $tasks_done = 'points';
		if(next($mandatory_qty)===FALSE) $tasks_done = 'mandatory_qty';
		if(next($optional_qty)===FALSE) $tasks_done = 'optional_qty';
		if(next($is_bonus)===FALSE) $tasks_done = 'is_bonus';
		if(next($label_id)===FALSE) $tasks_done = 'label_id';
		if(next($quantity)===FALSE) $tasks_done = 'quantity';
		if(next($nominal_dates)===FALSE) $tasks_done = 'nominal_dates';
	}

	if ($done) 
		trigger_error("$done beyond end", E_USER_ERROR);

	$message = T_('Date Tasks saved.');

} 
elseif(gr('Clone')) {

	echo "*** CLONE ***<br />";

	if	($date_tasks_mission_id = array_filter(gra('date_tasks_mission_id', array()), 'is_numeric')) {
		$result = mq('SELECT date_tasks_mission_id, mission_name, mission_number, mission_description, mission_value, start_date, end_date FROM date_tasks_missions WHERE school_type_id = ' . gri('orig_school_type_id', -1) . " AND subject_id = $subject_id AND level = " . gri('orig_level', -1) . ' AND track_id = ' . gri('orig_track_id', -1) . ' AND date_tasks_mission_id IN (' . implode(',', $date_tasks_mission_id) . ') ORDER BY start_date, end_date, mission_name, date_tasks_mission_id');

		while ($row = mysql_fetch_assoc($result)) {
			//fixme, does not handle duplicate mission number
			mq("INSERT INTO date_tasks_missions (school_type_id, subject_id, level, track_id, mission_name, mission_number, mission_description, mission_value, start_date, end_date) VALUES ($school_type_id, $subject_id, $level, $track_id, " . ms($row['mission_name']) . ', ' . nullif($row['mission_number'])  . ', '. ms($row['mission_description']) . ", {$row['mission_value']}, {$row['start_date']}, {$row['end_date']})");
			$id = mysql_insert_id();

			$result2 = mq("SELECT $id date_tasks_mission_id, date_task_id, ord, name, description, mandatory_qty, optional_qty, is_bonus, label_id, quantity, points FROM date_tasks WHERE date_tasks_mission_id = {$row['date_tasks_mission_id']}");
		
			while($row2 = mysql_fetch_assoc($result2)) {
				mq('INSERT INTO date_tasks (date_tasks_mission_id, ord, name, description, mandatory_qty, optional_qty, is_bonus, label_id, quantity, points) VALUES (' . $row2['date_tasks_mission_id'] . ', ' . $row2['ord'] . ', ' . ms($row2['name']) . ', ' . ms($row2['description']) . ', ' . $row2['mandatory_qty'] . ', ' . $row2['optional_qty'] . ', ' . $row2['is_bonus'] . ', ' . nullif($row2['label_id']) . ', ' . nullif($row2['quantity']) . ', ' . $row2['points'] . ')');
				$id = mysql_insert_id();

				mq("INSERT INTO date_tasks_dates (date_task_id, nominal_date) SELECT $id date_task_id, nominal_date FROM date_tasks_dates WHERE date_task_id = {$row2['date_task_id']}");
			}
		}
	
		$message = T_('Date Tasks cloned.');
	}

	if(gr('goals')) {
		mq("INSERT INTO goals (school_type_id, subject_id, level, track_id, goal_start, goal_end) SELECT $school_type_id school_type_id, $subject_id subject_id, $level level, $track_id track_id, goal_start, goal_end FROM goals goals_clone WHERE school_type_id = " . gri('orig_school_type_id', -1) . " AND subject_id = $subject_id AND level = " . gri('orig_level', -1) . ' AND track_id = ' . gri('orig_track_id', -1) . ' ON DUPLICATE KEY UPDATE goals.goal_start = goals_clone.goal_start, goals.goal_end = goals_clone.goal_end');

		if (!isset($message))
			$message = '';
		else
			$message .= ' ';

		$message .= T_('Goals cloned.');
	}
}
else {
	echo "***** NOTHING *****<br />";
}

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime; 


$labels_result = mq('SELECT label_id, label_name FROM labels ORDER BY label_name');
$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');

if ($subject_id != -1) 
	$subject = mysql_result(mq("SELECT subject_name FROM subjects WHERE subject_id = $subject_id"), 0);
	
if ($track_id != -1) 
	$track = mysql_result(mq("SELECT track_name FROM tracks WHERE track_id = $track_id"), 0);
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
	<TITLE><?=T_('Date Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
	<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	<? if($school_type_id != -1 && $subject_id != -1 && $level != -1 && $track_id != -1): ?>
	<SCRIPT type="text/javascript">
	var selectedRoom;

	function addRoom(el, half) {
	  var row = el.parentNode;
	  var cell_in = document.createElement('td');
	  cell_in.innerHTML = '<P>&nbsp;<\/P><DIV style="white-space: nowrap;"><A HREF="#" onClick="toggleSelectRoom(this.parentNode.parentNode); return false;">&#9988;  <?=T_('Select')?> &#9988;<\/A><BR><A HREF="#" onClick="copyRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Copy')?><?=$x2197?><\/A> &nbsp; <A HREf="#" onClick="moveRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Move')?><?=$x21d7?><\/A> &nbsp; <A HREF="#" onClick="delRoom(this.parentNode.parentNode); return false;" title="<?=T_('Delete')?>">&times;<\/A><\/DIV><LABEL><?=T_('Task Name')?>:<BR><INPUT type="text" name="name[]" value="" maxlength="255" size="12"><\/LABEL><BR><LABEL><?=T_('Description')?>:<BR><TEXTAREA name="description[]" rows="2" cols="11"><\/TEXTAREA><\/LABEL><BR><LABEL><?=T_('Points')?>:<BR><INPUT type="text" name="points[]" maxlength="7" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseFloat(\'0\'+this.value, 10), 9999.99)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Mandatory Reps')?>:<BR><INPUT type="text" name="mandatory_qty[]" maxlength="5" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Optional Reps')?>:<BR><INPUT type="text" name="optional_qty[]" maxlength="5" size="12" value="0" onChange="this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535)); updateMissionPoints(this.parentNode.parentNode.parentNode);"><\/LABEL><BR><LABEL><?=T_('Is Bonus')?>: <SELECT name="is_bonus[]" style="width: auto"><OPTION value="0"><OPTION value="1"><?=T_('Yes')?><\/SELECT><\/LABEL><BR><LABEL><?=T_('Label')?>:<BR><SELECT name="label_id[]"><OPTION value="-1"><\/OPTION><? @mysql_data_seek($labels_result, 0); ?><? while($label_row = mysql_fetch_assoc($labels_result)): ?><OPTION value="<?=$label_row['label_id']?>"><?=esq(es($label_row['label_name']))?><\/OPTION><? endwhile; ?><\/SELECT><BR><\/LABEL><LABEL><?=T_('Quantity')?>:<BR><INPUT type="text" name="quantity[]" maxlength="5" size="12" value="" onChange="if(this.value != \'\') this.value = Math.max(0, Math.min(parseInt(\'0\'+this.value, 10), 65535));"><\/LABEL><BR><LABEL><?=T_('Action Dates')?>:<BR><INPUT type="text" READONLY value="0 <?=T_('dates set')?>" onClick="getDates(this, getSubElementByName(this.parentNode.parentNode, \'nominal_dates[]\'));"><\/LABEL><INPUT type="hidden" name="nominal_dates[]" value=""><BR><A HREF="#" onClick="copyRoom(this.parentNode, +1); return false;"><?=T_('Copy')?><?=$x2198?><\/A> &nbsp; <A HREf="#" onClick="moveRoom(this.parentNode, +1); return false;"><?=T_('Move')?><?=$x21d8?><\/A><INPUT type="hidden" name="date_task_id[]" value="new">';
	  if(!half) {
		var cell_ar = document.createElement('th');
		cell_ar.innerHTML = '<TH><A HREF="#" title="<?=T_('Swap tasks')?>" onClick="swapRooms(this.parentNode); return false;">&#8644;<\/A><BR><A HREF="#" title="<?=T_('Move Selected')?>" onClick="moveSelected(this.parentNode); return false;">&#9997;<\/A><\/TH>';
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
	  var oldSelected = selectedRoom;
	  unSelectRoom();
	  var new_el;
	  if(el.parentNode.rowIndex == 0 && dir == -1 || el.parentNode.rowIndex >= (el.parentNode.parentNode.rows.length - 3) && dir == +1) {
		addFloor(el.parentNode, el.parentNode.rowIndex+(dir < 0 ? 0 : 1), new_el=el.cloneNode(true));
	  } else {
		new_el = el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].insertBefore(el.cloneNode(true), el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells[el.parentNode.parentNode.rows[el.parentNode.rowIndex+dir*2].cells.length-1]);
		var cell_ar = document.createElement('th');
		cell_ar.innerHTML = '<TH><A HREF="#" title="<?=T_('Swap tasks')?>" onClick="swapRooms(this.parentNode); return false;">&#8644;<\/A><BR><A HREF="#" title="<?=T_('Move Selected')?>" onClick="moveSelected(this.parentNode); return false;">&#9997;<\/A><\/TH>';
		new_el.parentNode.insertBefore(cell_ar, new_el);
	  }
	  updateColspan(el.parentNode.parentNode);
	  getSubElementByName(new_el, 'date_task_id[]').value = 'new';
	  new_el.getElementsByTagName('p')[0].innerHTML = '&nbsp;';
	  new_el.getElementsByTagName('a')[3].style.display='';
	  selectRoom(oldSelected);
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
		  cell_ar.innerHTML = '<TH><A HREF="#" title="<?=T_('Swap tasks')?>" onClick="swapRooms(this.parentNode); return false;">&#8644;<\/A><BR><A HREF="#" title="<?=T_('Move Selected')?>" onClick="moveSelected(this.parentNode); return false;">&#9997;<\/A><\/TH>';
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

	function toggleSelectRoom(el) {
	  if(el == selectedRoom)
		unSelectRoom();
	  else
		selectRoom(el);
	}

	function selectRoom(el) {
	  if(el) {
		if(selectedRoom) selectedRoom.style.backgroundColor = '';
		selectedRoom = el;
		selectedRoom.style.backgroundColor = '#dddddd';
	  }
	}

	function unSelectRoom() {
	  if(selectedRoom) selectedRoom.style.backgroundColor = '';
	  selectedRoom = undefined;
	}

	function moveSelected(el) {
	  if(selectedRoom && selectedRoom.parentNode)
		moveThisRoom(selectedRoom, el);
	  else
		alert('<?=T_('Please select a task first.')?>');
	}

	function moveThisRoom(el, dest) {
	  var last = false;
	  if(el.parentNode.cells.length > 3) {
		dest.parentNode.insertBefore(el.parentNode.cells[el.cellIndex+(el.cellIndex > 1 ? -1 : 1)], dest);
	  } else if(el.parentNode.rowIndex != dest.parentNode.rowIndex) {
		var last = el.parentNode;
		var cell_ar = document.createElement('th');
		cell_ar.innerHTML = '<TH><A HREF="#" title="<?=T_('Swap tasks')?>" onClick="swapRooms(this.parentNode); return false;">&#8644;<\/A><BR><A HREF="#" title="<?=T_('Move Selected')?>" onClick="moveSelected(this.parentNode); return false;">&#9997;<\/A><\/TH>';
		dest.parentNode.insertBefore(cell_ar, dest);
	  }
	  dest.parentNode.insertBefore(el, dest);
	  if(last) delFloor(last);
	  updateColspan(el.parentNode.parentNode);
	}

	function addFloor(el, row, room) {
	  if(!row) row = el.rowIndex;
	  var floor;
	  floor = el.parentNode.insertRow(row);
	  floor.innerHTML = '<TH class="sized"><INPUT type="hidden" name="date_task_id[]" value="row">    <INPUT type="hidden" name="date_tasks_mission_id[]" value="new"><?=T_('Subject')?>:<BR><?=es($subject)?><BR><?=T_('Ladder')?> <?=es($track)?> : <?=T_('Year')?> <?=es($level)?><BR>Mission #<INPUT type="text" name="number" size="3" DISABLED><BR><LABEL><?=T_('Mission name')?><BR><INPUT type="text" name="mission_name[]" maxlength="255" size="12"><\/LABEL><BR><LABEL><?=T_('Mission number')?><BR><INPUT type="text" name="mission_number[]" value="" maxlength="5" size="12" onChange="if(this.value != \'\') this.value = Math.max(0, Math.min(parseFloat(\'0\'+this.value, 10), 999.9)); checkMissionNum(this.form);"><\/LABEL><BR><LABEL><?=T_('Mission description')?><BR><TEXTAREA name="mission_description[]" rows="2" cols="11"><\/TEXTAREA><\/LABEL><BR><LABEL><?=T_('Mission Value')?>:<BR><INPUT type="text" name="mission_value[]" maxlength="6" size="12" value="1" onChange="this.value = Math.max(0, Math.min(parseFloat(\'0\'+this.value, 10), 9999.9)).toFixed(1);"><\/LABEL><BR><LABEL><?=T_('Start Date')?><BR><INPUT type="text" name="start_date_disp" size="12" READONLY value="<?=es(dateToHebrew(unixtojd()))?>" onClick="getDate(this.parentNode.parentNode, \'start_date[]\', \'start_date_disp\', true);"><\/LABEL><INPUT type="hidden" name="start_date[]" value="<?=unixtojd()?>"><BR><LABEL><?=T_('End Date')?><BR><INPUT type="text" name="end_date_disp" size="12" READONLY value="<?=es(dateToHebrew(unixtojd()+7))?>" onClick="getDate(this.parentNode.parentNode, \'end_date[]\', \'end_date_disp\', true);"><\/LABEL><INPUT type="hidden" name="end_date[]" value="<?=unixtojd()+7?>"><BR><LABEL><?=T_('Number of tasks')?><BR><INPUT type="text" name="tasks" size="12" DISABLED><\/LABEL><BR><LABEL><?=T_('Points Mandatory')?><BR><INPUT type="text" name="points_mandatory" size="12" DISABLED><\/LABEL><BR><LABEL><?=T_('Points Optional')?><BR><INPUT type="text" name="points_optional" size="12" DISABLED><\/LABEL><BR><A HREF="#" onClick="delFloor(this.parentNode.parentNode); return false;">&#8622; <?=T_('Delete mission')?> &#8622;<\/A><BR><A HREF="#" onClick="copyFloor(this.parentNode.parentNode); return false;">&#8615; <?=T_('Copy mission')?> &#8615;<\/A><\/TH><TH colspan="0" style="text-align: <?=$align_start?>; white-space: nowrap;"><A HREF="#" onClick="addRoom(this.parentNode); return false;">&laquo; <?=T_('Add task')?><\/A><BR><A HREF="#" onClick="moveSelected(this.parentNode); return false;">&#9997; <?=T_('Move Selected')?><\/A><\/TH>';
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
	  var oldSelected = selectedRoom;
	  unSelectRoom();
	  var floor = el.parentNode.insertRow(el.rowIndex+1);
	  floor.innerHTML = "<TH colspan='0' style='font-size: 225%;'><A HREF='#' title='<?=T_('Swap the missions')?>' onClick='swapFloors(this.parentNode.parentNode); return false;'>&#8693;<\/A><\/TH>";
	  var new_row = el.parentNode.insertBefore(el.cloneNode(true), floor.nextSibling);
	  updateColspan(el.parentNode);

	  getSubElementByName(new_row.cells[0], 'date_tasks_mission_id[]').value = 'new';
	  if(getSubElementByName(new_row.cells[0], 'mission_number[]').value != '') getSubElementByName(new_row.cells[0], 'mission_number[]').value = parseInt('0'+getSubElementByName(new_row.cells[0], 'mission_number[]').value, 10) + 0.1;
	  for(var i=1; i<new_row.cells.length; i+=2) {
		getSubElementByName(new_row.cells[i], 'date_task_id[]').value = 'new';
		new_row.cells[i].getElementsByTagName('p')[0].innerHTML = '&nbsp;';
		new_row.cells[i].getElementsByTagName('a')[3].style.display='';
	  }
	  selectRoom(oldSelected);
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

	function checkMissionNum(form) {
	  var nums = new Array();
	  els = form.getElementsByTagName('input');
	  for(var i=0; i<els.length; i++) {
		if(els[i].name=='mission_number[]' && els[i].value != '') {
		  if(nums[els[i].value]) {
			alert('<?=T_('The same mission number has been used more than once.\nEach mission number must be unique, or blank.')?>');
			return false;
		  }
		  nums[els[i].value] = true;
		}
	  }
	  return true;
	}

	var calendar;

	function getDate(el, value, text, required) {
	  if(calendar) {
	   calendar.parentNode.removeChild(calendar);
	   calendar = null;
	  }
	  calendar = document.createElement('iframe');
	  calendar.className = 'icalendar';
	  text = getSubElementByName(el, text);
	  text.parentNode.style.position = 'relative';
	  value = getSubElementByName(el, value);
	  text.parentNode.insertBefore(calendar, text);
	  calendar.callBack = function (value2, text2) {
		if(value2 != 'close' && (!required || (required && value2 != '' && text2 != ''))) {
		  value.value = value2;
		  text.value = text2;
		}
		calendar.parentNode.removeChild(calendar);
		calendar = null;
	  };
	  calendar.src = 'icalendar.php?date=' + value.value + (required ? '&required' : '');
	}

	function getDates(el, dates) {
	  if(calendar) {
	   calendar.parentNode.removeChild(calendar);
	   calendar = null;
	  }
	  calendar = document.createElement('iframe');
	  calendar.className = 'icalendar';
	  el.parentNode.style.position = 'relative';
	  el.parentNode.insertBefore(calendar, el);
	  calendar.callBack = function (dates2) {
		if(dates2 == '') {
		  dates.value = '';
		  el.value = '0 <?=T_('dates set')?>';
		} else if(dates2 != 'close') {
		  var i;
		  var dates_array = dates.value == '' ? [] : dates.value.split(',');
		  in_array: {
			for(i=0; i<dates_array.length; i++) {
			  if(dates_array[i] == dates2) {
				dates_array.splice(i, 1);
				break in_array;
			  }
			}
			dates_array.push(dates2); //only run if nothing breaks out of in_array
		  } //break will continue here
		  dates.value = dates_array.join(',');
		  el.value = dates_array.length + ' <?=T_('dates set')?>';
		  calendar.src = 'icalendar.php?dates=' +  encodeURIComponent(dates.value) + '&date=' + dates2;
		  return;
		}

		calendar.parentNode.removeChild(calendar);
		calendar = null;
	  };
	  calendar.src = 'icalendar.php?dates=' +  (dates.value);
	}
	</SCRIPT>
	<? endif; ?>
	</HEAD>
	
	<BODY>
	
		<div align='center'>
		
		<br />
		
		<DIV CLASS="body">
		
			<H1><?=T_('Date Tasks')?></H1>

			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>

			<? $school_type_result = mq('SELECT school_type_id, school_type_name FROM school_types ORDER BY school_type_name'); ?>

			<FORM action="admin_date_tasks.php" method="get" accept-charset="UTF-8">
				<P>
					<LABEL>
						<?=T_('Select School Type')?>:
						<SELECT name="school_type_id">
							<? while($row = mysql_fetch_assoc($school_type_result)): ?>
							<OPTION VALUE="<?=$row['school_type_id']?>" <?=$school_type_id == $row['school_type_id'] ? 'SELECTED' : '' ?>><?=es($row['school_type_name'])?></OPTION>
							<? endwhile; ?>
						</SELECT>
					</LABEL>
					<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
				</P>
			</FORM>
			
			<HR>
			
			<? if ($school_type_id == -1) : ?>
				<?=T_('Please select a School Type.')?>
			<? else : ?>
				<? $subject_result = mq("SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects JOIN school_type_subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE school_type_id = $school_type_id ORDER BY institutions.inst_name, subjects.subject_name"); ?>

				<FORM action="admin_date_tasks.php" method="get" accept-charset="UTF-8">
					<P>
						<LABEL>
							<?=T_('Select Subject')?>:
							<SELECT name="subject_id">
								<? while($row = mysql_fetch_assoc($subject_result)): ?>
								<OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
								<? endwhile; ?>
							</SELECT>
						</LABEL>
						<INPUT type="hidden" name="school_type_id" value="<?=$school_type_id?>">
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>	
				</FORM>
				
				<HR>
				
				
	<?if($subject_id == -1):?>
	<?=T_('Please select a Subject.')?>
	<?else:?>

	<TABLE class="grid" id="track_level" style="font-size:11px">
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
		  <TD style="text-align: <?=$align_start?>;" <?= $level==$each_level && $track_id==$track_row['track_id'] ? 'id="selected"' : '' ?>><A HREF="admin_date_tasks.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;level=<?=$each_level?>&amp;track_id=<?=$track_row['track_id']?>"><?=mysql_result(mq("SELECT COUNT(*) FROM date_tasks_missions WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $each_level AND track_id = {$track_row['track_id']}"), 0)?> <?=T_('Missions')?><BR><?=mysql_result(mq("SELECT COUNT(*) FROM date_tasks_missions JOIN date_tasks USING (date_tasks_mission_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $each_level AND track_id = {$track_row['track_id']}"), 0)?> <?=T_('Tasks')?></A></TD>
		<? endforeach; ?>
	  </TR>
	<? endwhile; ?>
	</TABLE>
	<HR>

	<?if($track_id == -1 || $level == -1): ?>

	<?=T_('Please select a ladder and year.')?>

	<?else:?>

	
	<? //$result = mq("SELECT mission_name, mission_number, mission_description, mission_value, date_tasks_mission_id, start_date, end_date, date_task_id, ord, name, description, mandatory_qty, optional_qty, is_bonus, label_id, quantity, points, (SELECT COUNT(*) FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) marked_tasks, (SELECT MIN(user_id) user_id FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) user_id FROM date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $level AND track_id = $track_id ORDER BY school_type_id, subject_id, level, track_id, start_date, mission_number, end_date, mission_name, date_tasks_mission_id, ord, name, date_task_id"); ?>
	<? $result = mq("SELECT mission_name, mission_number, mission_description, mission_value, date_tasks_mission_id, start_date, end_date, date_task_id, ord, name, description, mandatory_qty, optional_qty, is_bonus, label_id, quantity, points, (SELECT COUNT(*) FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) marked_tasks, (SELECT MIN(user_id) user_id FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) user_id FROM date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE start_date > 2455831 and start_date < 2456931 and school_type_id = $school_type_id AND subject_id = $subject_id AND level = $level AND track_id = $track_id ORDER BY school_type_id, subject_id, level, track_id, start_date, mission_number, end_date, mission_name, date_tasks_mission_id, ord, name, date_task_id"); ?>
	<? //$result = mq("SELECT mission_name, mission_number, mission_description, mission_value, date_tasks_mission_id, start_date, end_date, date_task_id, ord, name, description, mandatory_qty, optional_qty, is_bonus, label_id, quantity, points, (SELECT COUNT(*) FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) marked_tasks, (SELECT MIN(user_id) user_id FROM date_tasks_marks WHERE date_tasks_marks.date_task_id = date_tasks.date_task_id) user_id FROM date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE start_date < 2456187 and school_type_id = $school_type_id AND subject_id = $subject_id AND level = $level AND track_id = $track_id ORDER BY school_type_id, subject_id, level, track_id, start_date, mission_number, end_date, mission_name, date_tasks_mission_id, ord, name, date_task_id"); ?>
	
	
	<? $goals_row = mysql_fetch_assoc(mq("SELECT goal_start, goal_end FROM goals WHERE school_type_id=$school_type_id AND subject_id=$subject_id AND level=$level AND track_id=$track_id")); ?>

	

	<?if(gr('action')=='clone'):?>

	<?
	@mysql_data_seek($subject_result, 0);
	@mysql_data_seek($school_type_result, 0);
	@mysql_data_seek($tracks_result, 0);
	?>

	<FORM action="admin_date_tasks.php" method="post" accept-charset="UTF-8">
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
	  <TD><INPUT type="checkbox" id="date_tasks_mission_id_<?=$row['date_tasks_mission_id']?>" name="date_tasks_mission_id[]" value="<?=$row['date_tasks_mission_id']?>"></TD>
	  <TH><LABEL for="date_tasks_mission_id_<?=$row['date_tasks_mission_id']?>"><?=es($row['mission_name'])?></LABEL></TH>
	<?
		$old_row = $row;
		do {
	?>
	  <TD><LABEL for="date_tasks_mission_id_<?=$row['date_tasks_mission_id']?>"><?=es($row['name'])?></LABEL></TD>
	<?
		  $row = mysql_fetch_assoc($result);
		} while($row && $old_row['date_tasks_mission_id'] == $row['date_tasks_mission_id']);
		echo "</TR>\n";
	  } while($row);
	?>

	<? if($goals_row): ?>
	<TR>
	  <TD><INPUT type="checkbox" id="goals" name="goals"></TD>
	  <TH><LABEL for="goals">&lt;<?=T_('Goals')?>&gt;</LABEL></TH>
	  <TD><LABEL for="goals"><?=$goals_row['goal_start']?></LABEL></TD>
	  <TD><LABEL for="goals"><?=$goals_row['goal_end']?></LABEL></TD>
	</TR>
	<? endif; ?>

	</TABLE>

	</FORM>

	<?else:?>

	<P><A HREF="admin_date_tasks.php?action=clone&amp;school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;level=<?=$level?>&amp;track_id=<?=$track_id?>"><?=T_('Clone Missions')?></A></P>

	<FORM action="admin_date_tasks.php" method="post" accept-charset="UTF-8" onSubmit="return checkMissionNum(this);">
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
		<INPUT type="hidden" name="date_task_id[]" value="row">
		<INPUT type="hidden" name="date_tasks_mission_id[]" value="<?=$row['date_tasks_mission_id']?>">
		<?=T_('Subject')?>:<BR>
		<?=es($subject)?><BR>
		<?=T_('Ladder')?> <?=es($track)?> : <?=T_('Year')?> <?=es($level)?><BR>
		<?=T_('Mission Pos#')?><INPUT type="text" name="number" size="3" DISABLED><BR>
		<LABEL><?=T_('Mission name')?><BR>
		<INPUT type="text" name="mission_name[]" value="<?=T_(es($row['mission_name']))?>" maxlength="255" size="12"></LABEL><BR>
		<LABEL><?=T_('Mission number')?><BR>
		<INPUT type="text" name="mission_number[]" value="<?=T_(es($row['mission_number']))?>" maxlength="5" size="12" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999.9)); checkMissionNum(this.form);"></LABEL><BR>
		<LABEL><?=T_('Mission description')?><BR>
		<TEXTAREA name="mission_description[]" rows="2" cols="11"><?=T_(es($row["mission_description"]))?></TEXTAREA></LABEL><BR>
		<LABEL><?=T_('Mission Value')?>:<BR>
		<INPUT type="text" name="mission_value[]" maxlength="6" size="12" value="<?=$row['mission_value']?>" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.9)).toFixed(1);"></LABEL><BR>
		<LABEL><?=T_('Start Date')?><BR>
		<INPUT type="text" name="start_date_disp" size="12" READONLY value="<?=es(dateToHebrew($row['start_date']))?>" onClick="getDate(this.parentNode.parentNode, 'start_date[]', 'start_date_disp', true);"></LABEL><INPUT type="hidden" name="start_date[]" value="<?=$row['start_date']?>"><BR>
		<LABEL><?=T_('End Date')?><BR>
		<INPUT type="text" name="end_date_disp" size="12" READONLY value="<?=es(dateToHebrew($row['end_date']))?>" onClick="getDate(this.parentNode.parentNode, 'end_date[]', 'end_date_disp', true);"></LABEL><INPUT type="hidden" name="end_date[]" value="<?=$row['end_date']?>"><BR>
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
		  if(!$firstRoom) echo '<TH><A HREF="#" title="', T_('Swap tasks'), '" onClick="swapRooms(this.parentNode); return false;">&#8644;</A><BR><A HREF="#" title="', T_('Move Selected'), '" onClick="moveSelected(this.parentNode); return false;">&#9997;</A></TH>';
	?>
	<TD>
	  <P style="white-space: nowrap;">
	  <?if($row['marked_tasks']):?>
		<? $user_task = mysql_fetch_assoc(mq("SELECT school_name, user_serial, first, last FROM users JOIN schools USING (school_id) WHERE user_id = {$row['user_id']}")); ?>
		<SPAN title="<?=es("{$user_task['school_name']} - #{$user_task['user_serial']}, {$user_task['first']} {$user_task['last']}")?>"><?=sprintf('%s marked tasks', $row['marked_tasks'])?></SPAN>
		&nbsp; <A HREF="#" onClick="alert('<?=esq(T_("Can't delete, has marked tasks."))?>'); return false;" title="<?=T_('Delete')?>">&times;</A>
	  <?else:?>
		&nbsp;
	  <?endif;?>
	  </P>
	  <DIV style="white-space: nowrap;">
	  <A HREF="#" onClick="toggleSelectRoom(this.parentNode.parentNode); return false;">&#9988;  <?=T_('Select')?> &#9988;</A><BR>
	  <A HREF="#" onClick="copyRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Copy')?><?=$x2197?></A> &nbsp;
	  <A HREf="#" onClick="moveRoom(this.parentNode.parentNode, -1); return false;"><?=T_('Move')?><?=$x21d7?></A> &nbsp;
	  <A HREF="#" <?if($row['marked_tasks']):?>style="display: none;"<?endif;?> onClick="delRoom(this.parentNode.parentNode); return false;" title="<?=T_('Delete')?>">&times;</A>
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
	  <LABEL><?=T_('Is Bonus')?>: <SELECT name="is_bonus[]" style="width: auto">
	  <OPTION value="0" <?=$row['is_bonus'] == 0 ? 'selected' : ''?>>
	  <OPTION value="1" <?=$row['is_bonus'] == 1 ? 'selected' : ''?>><?=T_('Yes')?>
	  </SELECT></LABEL><BR>
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
	  <LABEL><?=T_('Action Dates')?>:<BR>
	  <? $nominal_dates = mq("SELECT nominal_date FROM date_tasks_dates WHERE date_task_id = {$row['date_task_id']}"); ?>
	  <INPUT type="text" READONLY value="<?=mysql_num_rows($nominal_dates), ' ', T_('dates set')?>" onClick="getDates(this, getSubElementByName(this.parentNode.parentNode, 'nominal_dates[]'));"></LABEL><INPUT type="hidden" name="nominal_dates[]" value="<?=implode(',', mysql_fetch_column($nominal_dates))?>"><BR>
	  <A HREF="#" onClick="copyRoom(this.parentNode, +1); return false;"><?=T_('Copy')?><?=$x2198?></A> &nbsp;
	  <A HREf="#" onClick="moveRoom(this.parentNode, +1); return false;"><?=T_('Move')?><?=$x21d8?></A>
	  <INPUT type="hidden" name="date_task_id[]" value="<?=$row["date_task_id"]?>">
	</TD>
	<?
		  $firstRoom = false;
		  $row = mysql_fetch_assoc($result);
		} while($row && $old_row['date_tasks_mission_id'] == $row['date_tasks_mission_id']);
	?>
	<TH colspan="0" style="text-align: <?=$align_start?>; white-space: nowrap;">
	  <A HREF="#" onClick="addRoom(this.parentNode); return false;">&laquo; <?=T_('Add task')?></A><BR>
	  <A HREF="#" onClick="moveSelected(this.parentNode); return false;">&#9997; <?=T_('Move Selected')?></A>
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
	</div>
	</BODY>
	
</HTML>
