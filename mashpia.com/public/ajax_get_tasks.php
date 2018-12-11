<?
require('db.php');
require('lang.php');

$camp_id = $_GET['camp_id'];
$term_id = $_GET['term_id'];
$campaign_id = $_GET['campaign_id'];

$delete_message = T_('Are you sure that you want to delete this campaign task?');

$sql = "SELECT * FROM campaign_tasks WHERE camp_id=" . $camp_id . " AND term_id=" . $term_id . " AND campaign_id=" . $campaign_id;
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
?>
<? if ($num_rows > 0) : ?>		
		<table class="pretty_grid">
			<th><?=T_('Name');?></th>
			<th><?=T_('Points');?></th>
			<th><?=T_('Max Times');?></th>
			<th></th>
			<th></th>

			<? while($task = mysql_fetch_assoc($query)) : ?>
				<tr>
					<td><?=$task['task_name'];?></td>
					<td><?=$task['points'];?></td>
					<td><?=$task['max_times'];?></td>					
					<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('task_id').value='<?=$task['task_id'];?>'; document.forms['tasks_form'].submit();"><?=T_('Edit Campaign Task');?></a></td>		
					<td><a href="#" onclick="document.getElementById('action').value='delete'; document.getElementById('task_id').value='<?=$task['task_id'];?>'; var dlt = confirm ('<?=$delete_message;?>'); if (dlt == true) document.forms['tasks_form'].submit();"><?=T_('Delete Campaign Task');?></a></td>
				</tr>
			<? endwhile; ?>
		</table>
<? else : ?>
		<br />
		<table class="pretty_grid">
			<th><?=T_('No tasks found');?></th>
		</table>
<? endif; ?>