<? 
$admin_auth = array(); 
require('header.php'); 

// ***** Determine if the user is a camp director or a super user ***** //
if ($admin_user['auth'] == "super")
	$user_type = "super";
else
	$user_type = "camp";
// ***** Determine if the user is a camp director or a super user ***** //

$action = gr('action');
$subject_id = gri('subject_id', -1);
$tasks_ae_row = false;
$points_result = false;

if (!empty($action)) {
	
	switch($action) {
	
		/*case 'add':
			$result = mq("SELECT -1 task_id, '' name, '' task_type, NULL quantity_name, 'daily' rep_type, " . unixtojd() . " start_date, NULL end_date, 1 every, NULL rep_param1, NULL rep_param2");
			$tasks_ae_row = mysql_fetch_assoc($result);
		break;

		case 'add2':
			$owner_sql = "subject_id = $subject_id, task_type = " . ms(gr('task_type')) . ', quantity_name = ' . nullif_ms(gr('quantity_name'), '');
			include('tasks_ae2.php');
			$task_id = mysql_insert_id();
			$result = mq("SELECT school_type_id, level, track_id FROM task_active WHERE task_id = $task_id");
			$active = array();
			while($row = mysql_fetch_assoc($result)) {
				$active[$row['school_type_id']][$row['level']][$row['track_id']] = true;
			}
			$message = T_('Task added');
		break;

		case 'delete':
			mq('DELETE FROM tasks WHERE task_id = ' . gri('task_id', -1));
			mq('DELETE FROM task_active WHERE task_id = ' . gri('task_id', -1));
			$message = T_('Task deleted');
		break;

		case 'clone':
			$task_id = gri('task_id', -1);
			$tasks_ae_result = mq("SELECT -1 task_id, name, task_type, quantity_name, rep_type, start_date, end_date, every, rep_param1, rep_param2 FROM tasks WHERE task_id = $task_id");
			$tasks_ae_row = mysql_fetch_assoc($tasks_ae_result);
			$action = 'add';
		break;

		case 'clone_all':
			mq('INSERT INTO tasks (subject_id, name, task_type, quantity_name, rep_type, start_date, end_date, every, rep_param1, rep_param2) SELECT subject_id, name, task_type, quantity_name, rep_type, start_date, end_date, every, rep_param1, rep_param2 FROM tasks WHERE task_id = ' . gri('task_id', -1));
			$task_id = mysql_insert_id();
			mq("INSERT INTO task_active (task_id, school_type_id, level, track_id, points, description, quantity) SELECT $task_id task_id, school_type_id, level, track_id, points, description, quantity FROM task_active WHERE task_id = " . gri('task_id', -1));
			$action = 'edit';

		case 'edit':
			if(!isset($task_id)) $task_id = gri('task_id', -1);
			$tasks_ae_result = mq("SELECT task_id, name, task_type, quantity_name, rep_type, start_date, end_date, every, rep_param1, rep_param2 FROM tasks WHERE task_id = $task_id");
			$tasks_ae_row = mysql_fetch_assoc($tasks_ae_result);
		break;

		case 'edit2':
			$task_id = intval(array_shift(array_keys(gra('tasks'))));
			$owner_sql = "subject_id = $subject_id, task_type = " . ms(gr('task_type')) . ', quantity_name = ' . nullif_ms(gr('quantity_name'), '');
			include('tasks_ae2.php');
			$message = T_('Task edited');

		case 'active':
			if(!isset($task_id)) $task_id = gri('task_id', -1);
			$result = mq("SELECT school_type_id, level, track_id FROM task_active WHERE task_id = $task_id");
			$active = array();
			while($row = mysql_fetch_assoc($result)) {
				$active[$row['school_type_id']][$row['level']][$row['track_id']] = true;
			}
		break;

		case 'active2':
			$task_id = gri('task_id', -1);
			$used = array();
			foreach(gra('task_active') as $school_type_id => $levels) {
				$school_type_id = intval($school_type_id);
				if(!is_array($levels)) $levels = array();
				foreach($levels as $level => $tracks) {
					$level = max(3, min(intval($level), 14));
					if(!is_array($tracks)) $tracks = array();
					foreach($tracks as $track_id => $unused) {
						$track_id = intval($track_id);
						mq("INSERT IGNORE INTO task_active (task_id, school_type_id, level, track_id) VALUES ($task_id, $school_type_id, $level, $track_id)");
						$used[] = "($school_type_id, $level, $track_id)";
					}
				}
			}
			mq("DELETE FROM task_active WHERE task_id = $task_id" . ($used ? ' AND (school_type_id, level, track_id) NOT IN (' . implode(',', $used) . ')' : ''));
			$message = T_('Task Active edited');

		case 'points':
			if(!isset($task_id)) $task_id = gri('task_id', -1);
			$result = mq("SELECT school_type_id, level, track_id, points FROM task_active WHERE task_id = $task_id");
			$points = array();
			while($row = mysql_fetch_assoc($result)) {
				$points[$row['school_type_id']][$row['level']][$row['track_id']] = $row['points'];
			}
		break;

		case 'points2':
			$task_id = gri('task_id', -1);
			$used = array();
			foreach(gra('points') as $school_type_id => $levels) {
				$school_type_id = intval($school_type_id);
				if(!is_array($levels)) $levels = array();
				foreach($levels as $level => $tracks) {
					$level = max(3, min(intval($level), 14));
					if(!is_array($tracks)) $tracks = array();
					foreach($tracks as $track_id => $points) {
						$track_id = intval($track_id);
						$points = floatval($points);
						mq("UPDATE task_active SET points = $points WHERE task_id = $task_id AND school_type_id = $school_type_id AND level = $level AND track_id = $track_id");
					}
				}
			}
			unset($points);
			$message = T_('Task Points edited');

		case 'descriptions':
			if(!isset($task_id)) $task_id = gri('task_id', -1);
			$result = mq("SELECT school_type_id, level, track_id, description FROM task_active WHERE task_id = $task_id");
			$descriptions = array();
			while($row = mysql_fetch_assoc($result)) {
				$descriptions[$row['school_type_id']][$row['level']][$row['track_id']] = $row['description'];
			}
		break;

		case 'descriptions2':
			$task_id = gri('task_id', -1);
			$used = array();
			foreach(gra('descriptions') as $school_type_id => $levels) {
				$school_type_id = intval($school_type_id);
				if(!is_array($levels)) $levels = array();
				foreach($levels as $level => $tracks) {
					$level = max(3, min(intval($level), 14));
					if(!is_array($tracks)) $tracks = array();
					foreach($tracks as $track_id => $description) {
						$track_id = intval($track_id);
						mq("UPDATE task_active SET description = " . ms($description) . " WHERE task_id = $task_id AND school_type_id = $school_type_id AND level = $level AND track_id = $track_id");
					}
				}
			}
			$message = T_('Task Descriptions edited');

		case 'quantity':
			if(!isset($task_id)) $task_id = gri('task_id', -1);
			$result = mq("SELECT school_type_id, level, track_id, IFNULL(quantity, '') quantity FROM task_active WHERE task_id = $task_id");
			$quantity = array();
			while($row = mysql_fetch_assoc($result)) {
				$quantity[$row['school_type_id']][$row['level']][$row['track_id']] = $row['quantity'];
			}
		break;

		case 'quantity2':
			$task_id = gri('task_id', -1);
			$used = array();
			
			foreach(gra('quantity') as $school_type_id => $levels) {
				$school_type_id = intval($school_type_id);
				
				if (!is_array($levels)) 
					$levels = array();
					
				foreach($levels as $level => $tracks) {
					$level = max(3, min(intval($level), 14));
					
					if (!is_array($tracks)) 
						$tracks = array();
						
					foreach ($tracks as $track_id => $quantity) {
						$track_id = intval($track_id);
						mq("UPDATE task_active SET quantity = " . nullif($quantity, '') . " WHERE task_id = $task_id AND school_type_id = $school_type_id AND level = $level AND track_id = $track_id");
					}
					
				}
				
			}
			
			unset($quantity);
			$message = T_('Task Quantities edited');
		break;*/

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
			
	}
}


$subject_result = mq('SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript" src="tasks_ae.js"></script>
		
		<? include('tasks_ae.inc'); ?>
		
		<SCRIPT type="text/javascript">
			function change(school_type_id, level, track_id, type, value) {
				if (school_type_id == '') 
					school_type_id = '\\d*';
					
				if (level == '') 
					level = '\\d*';
					
				if (track_id == '') 
					track_id = '\\d*';
					
				var el = document.forms['task'].elements;
				var pattern = new RegExp("(task_active|points|descriptions)\\[" + school_type_id + "\\]\\[" + level + "\\]\\[" + track_id + "\\]");
				
				for (var i = 0; i<el.length; i++) {
					if (pattern.test(el[i].name) && !el[i].disabled) {
						switch(type) {
							case 'active':
								el[i].checked = !el[i].checked;
							break;
							case 'points':
							case 'descriptions':
								el[i].value = value;
							break;
						}
					}
				}
				
			}

			function copy(old_school, new_school, type) {
				var el = document.forms['task'].elements;
				var pattern = new RegExp("((task_active|points|descriptions)\\[)" + old_school + "(\\]\\[\\d*\\]\\[\\d*\\])");
				
				for (var i = 0; i<el.length; i++) {
					if ((matches=pattern.exec(el[i].name)) && !el[i].disabled) {
						switch(type) {
							case 'active':
								el[matches[1] + new_school + matches[3]].checked = el[i].checked;
							break;
							case 'points':
							case 'descriptions':
								el[matches[1] + new_school + matches[3]].value = el[i].value;
							break;
						}
					}
				}
			}
		</SCRIPT>
	</HEAD>
	
	<BODY>
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
			
			<H1><?=T_('Tasks')?></H1>

			<? if (!empty($message)): ?>
				<H2><?=$message?></H2>
			<? endif; ?>
						
			<HR>
			
			<? if ($subject_id == -1) : ?>
				<?=T_('Please select a Subject.')?>
			<? else : ?>

				<? if ($tasks_ae_row) : ?>

					<FORM action="admin_task.php" method="post" accept-charset="UTF-8" name="tasks">
						<DIV CLASS="tasks_ae tasks_ae_<?= $align_start ?>">
							<INPUT type="hidden" name="action" value="<?=$action?>2">
							<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
							<INPUT type="hidden" name="tasks[<?=$tasks_ae_row['task_id']?>][include]" value="YES">
							<?=T_('Step 1 of 5')?>
							<TABLE>
								<? include('tasks_ae.php'); ?>
								<TR>
									<TH>
										<LABEL for="task_type"><?=T_('Type')?>:</LABEL>
									</TH>
									<TD>
										<SELECT name="task_type" id="task_type">
											<? foreach (mysql_enum_values('tasks','task_type') as $type) : ?>
											<OPTION VALUE="<?=$type?>" <?=$tasks_ae_row['task_type'] == $type ? 'SELECTED' : '' ?>><?=$type == 'goal_hist' ? T_('Show goals and history') : $type ?></OPTION>
											<? endforeach; ?>
										</SELECT>
									</TD>
								</TR>
								
								<TR>
									<TH>
										<LABEL for="quantity_name"><?=T_('Quantity name')?>:</LABEL>
									</TH>
									<TD>
										<INPUT type="text" name="quantity_name" id="quantity_name" value="<?=es($tasks_ae_row['quantity_name'])?>"> <?=T_('A quantity is a user defined field, which is simply a number that is totaled. The value of the quantity is set in the ladder/year grid. This defines the word to display when totaling for a soldier.')?>
									</TD>
								</TR>
								
								<TR>
									<TH>
									</TH>									
									<TD>
										<INPUT type="submit" value="<?=$action=='edit' ? T_('Save and edit Active') : T_('Add new and edit Active')?>">
									</TD>
								</TR>							
							</TABLE>
						</DIV>	
					</FORM>
					
					<BR>

					<A HREF="admin_task.php?subject_id=<?=$subject_id?>&action="><?=T_('Cancel')?></A>

				<? elseif (isset($quantity) && is_null(mysql_result(mq("SELECT quantity_name FROM tasks WHERE task_id = $task_id"),0))) : ?>

					<FORM action="admin_task.php" method="post" accept-charset="UTF-8" name="task">
						<P>
							<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
							<INPUT type="hidden" name="task_id" value="<?=$task_id?>">
							<?=T_('Step ') , T_('5') , T_(' of 5') ?>
							<BR>
							<?=T_('This task has no quantity name defined, quantity grid skipped.')?>
							<BR>
							<INPUT type="submit" value="<?=T_('Save')?>">
						</P>	
					</FORM>

				<? elseif (isset($active) || isset($points) || isset($descriptions) || isset($quantity)) : ?>

				<?
					$school_types_result = mq("SELECT school_type_id, school_type_name FROM school_types JOIN school_type_subjects USING (school_type_id) WHERE subject_id = $subject_id ORDER BY school_type_name");
					$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');
				?>

					<FORM action="admin_task.php" method="post" accept-charset="UTF-8" name="task">
						<P>
							<INPUT type="hidden" name="action" value="<?=isset($points) ? 'points' : (isset($descriptions) ? 'descriptions' : (isset($quantity) ? 'quantity' : 'active'))?>2">
							<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
							<INPUT type="hidden" name="task_id" value="<?=$task_id?>">
							<?=T_('Step ') . (isset($points) ? T_('3') : (isset($descriptions) ? T_('4') : (isset($quantity) ? T_('5') : T_('2')))) . T_(' of 5') ?><BR>
							<INPUT type="submit" value="<?=isset($points) ? T_('Save Points and edit Descriptions') : (isset($descriptions) ? T_('Save Descriptions and edit Quantity') : (isset($quantity) ? T_('Save Quantity') : T_('Save Active and edit Points'))) ?>">
						</P>
						
						<TABLE class="grid">
							<CAPTION><?=isset($points) ? T_('Task Points') . ' (0 - 9999.99)' : (isset($descriptions) ? T_('Task Descriptions') : (isset($quantity) ? T_('Task Quantities') : T_('Task Active')))?></CAPTION>
							<? $old_school_type_id = ''; ?>
							<? while($school_type_row = mysql_fetch_assoc($school_types_result)) : ?>
							<TR>
								<TH colspan="14">
								<? if ($old_school_type_id) : ?>
									<INPUT type="button" value="<?=T_('Copy previous Tzivos Hashem Type')?>" onClick="copy(<?=$old_school_type_id?>, <?=$school_type_row['school_type_id']?>, '<?=isset($points) ? 'points' : (isset($descriptions) ? 'descriptions' : 'active')?>');"><BR>
								<? endif; ?>
								<? $old_school_type_id = $school_type_row['school_type_id']; ?>
								<?=es($school_type_row['school_type_name'])?>
								</TH>
							</TR>
							
							<TR>
								<TH>
								</TH>
								<TH>
								</TH>
								<TH colspan="12">
									<?=T_('Year')?>
								</TH>
							</TR>
							
							<TR>
								<TH>
								</TH>
								<TH>
								</TH>
								<? foreach (range(3, 14) as $level) : ?>
								<TH>
									<?=$level?>
								</TH>
								<? endforeach; ?>
								<TH>
								</TH>
							</TR>
							
							<? $first_track = true; ?>
							<? while($track_row = mysql_fetch_assoc($tracks_result)): ?>
							<TR>
								<? if($first_track): ?>
								<?$first_track = false;?>
								<TH rowspan=<?=mysql_num_rows($tracks_result)?>>
									Track
								</TH>
								<? endif;?>
								<TH>
									<?=es($track_row['track_name'])?>
								</TH>
								<? foreach (range(3, 14) as $level) : ?>
									<TD>
									<? if (isset($points)) : ?>
										<INPUT type="text" name="points[<?=$school_type_row['school_type_id']?>][<?=$level?>][<?=$track_row['track_id']?>]" maxlength="7" size="5" value="<?=isset($points[$school_type_row['school_type_id']][$level][$track_row['track_id']]) ? floatval($points[$school_type_row['school_type_id']][$level][$track_row['track_id']]) : '" DISABLED STYLE="background-color: lightgrey;'?>" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99));">
									<? elseif (isset($descriptions)) : ?>
										<TEXTAREA name="descriptions[<?=$school_type_row['school_type_id']?>][<?=$level?>][<?=$track_row['track_id']?>]" rows="2" cols="5" <?=isset($descriptions[$school_type_row['school_type_id']][$level][$track_row['track_id']]) ? '>' . es($descriptions[$school_type_row['school_type_id']][$level][$track_row['track_id']]) : 'DISABLED STYLE="background-color: lightgrey;">' ?></TEXTAREA>
									<? elseif (isset($quantity)) : ?>
										<INPUT type="text" name="quantity[<?=$school_type_row['school_type_id']?>][<?=$level?>][<?=$track_row['track_id']?>]" maxlength="5" size="5" value="<?=isset($quantity[$school_type_row['school_type_id']][$level][$track_row['track_id']]) ? $quantity[$school_type_row['school_type_id']][$level][$track_row['track_id']] : '" DISABLED STYLE="background-color: lightgrey;'?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));">
									<? else : ?>
										<INPUT type="checkbox" name="task_active[<?=$school_type_row['school_type_id']?>][<?=$level?>][<?=$track_row['track_id']?>]" <?=isset($active[$school_type_row['school_type_id']][$level][$track_row['track_id']]) ? 'CHECKED' : ''?>>
									<? endif; ?>
									</TD>
								<? endforeach; ?>
								
								<TH>
									<? if (isset($points)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]" size="5" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'',<?=$track_row['track_id']?>,'points', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]'].value);">
									<? elseif (isset($descriptions)) : ?>
									<TEXTAREA name="all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]" rows="2" cols="5"></TEXTAREA> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'',<?=$track_row['track_id']?>,'descriptions', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]'].value);">
									<? elseif (isset($quantity)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]" size="5" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'',<?=$track_row['track_id']?>,'quantity', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][<?=$track_row['track_id']?>]'].value);">
									<? else : ?>
									<INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'',<?=$track_row['track_id']?>,'active');">
									<?endif;?>								
								</TH>
							</TR>							
							<? endwhile; ?> <!-- while($track_row = mysql_fetch_assoc($tracks_result)):  -->
							
							<? mysql_data_seek($tracks_result, 0); ?>
							<TR>
								<TH></TH>
								
								<TH></TH>
								
								<? foreach (range(3, 14) as $level) : ?>
								<TH>
									<? if (isset($points)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]" size="5" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,<?=$level?>,'','points', this.form.elements['all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]'].value);">
									<? elseif (isset($descriptions)) : ?>
									<TEXTAREA name="all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]" rows="2" cols="5"></TEXTAREA> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,<?=$level?>,'','descriptions', this.form.elements['all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]'].value);">
									<? elseif(isset($quantity)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]" size="5" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,<?=$level?>,'','quantity', this.form.elements['all[<?=$school_type_row['school_type_id']?>][<?=$level?>][]'].value);">
									<? else : ?>
									<INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,<?=$level?>,'','active');">
									<? endif; ?> <!-- if (isset($points)) : -->
								</TH>
								<? endforeach; ?>
								
								<TH>
									<? if(isset($points)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][][]" size="5" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'','','points', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][]'].value);">
									<? elseif (isset($descriptions)) : ?>
									<TEXTAREA name="all[<?=$school_type_row['school_type_id']?>][][]" rows="2" cols="5"></TEXTAREA> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'','','descriptions', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][]'].value);">
									<? elseif(isset($quantity)) : ?>
									<INPUT type="text" name="all[<?=$school_type_row['school_type_id']?>][][]" size="5" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"> <INPUT type="button" value="<?=T_('Change')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'','','quantity', this.form.elements['all[<?=$school_type_row['school_type_id']?>][][]'].value);">
									<? else : ?>
									<INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'','','active');">
									<? endif; ?> <!--  if(isset($points)) : -->
								</TH>
							</TR>
							
							<TR>
								<TH colspan="14">&nbsp;</TH>
							</TR>
							
						<? endwhile; ?> <!-- while($school_type_row = mysql_fetch_assoc($school_types_result)) : -->
						</TABLE>
					
						<P>
							<INPUT type="submit" value="<?=isset($points) ? T_('Save Points and edit Descriptions') : (isset($descriptions) ? T_('Save Descriptions and edit Quantity') : (isset($quantity) ? T_('Save Quantity') : T_('Save Active and edit Points'))) ?>">
						</P>
						
					</FORM>

					<A HREF="admin_task.php?subject_id=<?=$subject_id?>"><?=T_('Cancel')?></A>

				<? else: ?>

					<?$result = mq("SELECT tasks.task_id, subjects.subject_name, institutions.inst_name, tasks.name FROM tasks LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE subject_id = $subject_id ORDER BY institutions.inst_name, subjects.subject_name, tasks.name, tasks.task_id");?>

					<A HREF="admin_task.php?action=add&amp;subject_id=<?=$subject_id?>"><?=T_('Add new Task')?></A>

					<TABLE CLASS="list">
						<TR>
							<TH><?=T_('Subject')?></TH>
							<TH><?=T_('Name')?></TH>
							<TH></TH>
							<TH></TH>
							<TH></TH>
							<TH></TH>
							<TH></TH>
							<TH></TH>
							<TH></TH>
						</TR>
						
						<? while($row = mysql_fetch_assoc($result)) : ?>
						<TR>
							<TD><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></TD>
							<TD><?=es($row['name'])?></TD>
							<TD><A HREF="admin_task.php?action=edit&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=active&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Active for Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=points&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Points for Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=descriptions&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Descriptions for Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=quantity&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit quantities for Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=clone&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Clone Task')?></A></TD>
							<TD><A HREF="admin_task.php?action=clone_all&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Clone Task including grids')?></A></TD>
							<TD><A HREF="admin_task.php?action=delete&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Task')?></A></TD>
						</TR>
						<? endwhile; ?>	
					</TABLE>
				<? endif; ?>
				
			<? endif; ?> <!-- if ($subject_id == -1) : -->
			
		</DIV> <!-- body -->
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
