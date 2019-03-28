<?
require('db.php');
require('lang.php');

$delete_message = T_('Are you certain that you want to delete this campaign?');

$camp_id = $_GET['camp_id'];
$group_type_id = $_GET['group_type_id'];
$group_id = $_GET['group_id'];
$campaign_id = $_GET['campaign_id'];
$divs = $_GET['divs'];

$pos1 = strpos($divs, "4");
$pos2 = strpos($divs, "4");
$pos3 = strpos($divs, "4");
$pos4 = strpos($divs, "4");

$sql = "SELECT * from camp_group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$row_num = 0;
?>
<? if ($pos1 !== false) : ?>
	<?=T_('Select a Group Type')?>:
	<label>
		<select name="group_type_id" id="group_type_id" onchange="get_campaign_tasks('234');">
			<? while($row = mysql_fetch_assoc($query)) : ?>
			<? if ($row_num == 0 && $group_type_id == -1) $group_type_id = $row['group_type_id']; ?>
				<? if ($group_type_id == $row['group_type_id']) : ?>
				<option value="<?=$row['group_type_id'];?>" selected><?=$row['group_type_name'];?></option>
				<? else : ?>
				<option value="<?=$row['group_type_id'];?>"><?=$row['group_type_name'];?></option>
				<? endif; ?>
			<? $row_num++; ?>
			<? endwhile; ?>
		</select>
	</label>
	[SPLIT]
<? endif; ?> <!-- if ($pos1 !== false) : -->

<? if ($pos2 !== false) : ?>
	<?
	$sql = "SELECT * FROM camp_groups WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
	?>
	<? if ($num_rows == 0) : ?>
		<table class="pretty_grid">
			<th><?=T_('No Groups Found');?></th>
		</table>
	<? else : ?>
		<?=T_('Select a Group')?>:
		<label>
			<select name="group_id" id="group_id" onchange="get_campaign_tasks('34');">
				<? while($row = mysql_fetch_assoc($query)) : ?>			
					<? if ($row_num == 0 && $group_id == -1) $group_id = $row['group_id']; ?>
				
					<? if ($group_id == $row['group_id']) : ?>
					<option value="<?=$row['group_id'];?>" selected><?=$row['group_name'];?></option>
					<? else :?>
					<option value="<?=$row['group_id'];?>"><?=$row['group_name'];?></option>
					<? endif; ?>
					
					<? $row_num++; ?>
				<? endwhile; ?>
			</select>
		</label>
	<? endif; ?>
	[SPLIT]
<? endif; ?> <!-- if ($pos2 !== false) : -->

<? if ($pos3 !== false) : ?>
	<?
	$sql = "SELECT * FROM campaigns WHERE group_id=" . $group_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
	?>
	<? if ($num_rows == 0) : ?>
		<table class="pretty_grid">
			<th><?=T_('No Campaigns Found');?></th>
		</table>
	<? else : ?>
		<?=T_('Select a Campaign')?>:
		<label>
			<select name="campaign_id" id="campaign_id" onchange="get_campaign_tasks('34');">
				<? while($row = mysql_fetch_assoc($query)) : ?>
					<? if ($row_num == 0 && $campaign_id == -1) $campaign_id = $row['campaign_id']; ?>
						<? if ($campaign_id == $row['campaign_id']) : ?>
						<option value="<?=$row['campaign_id'];?>" selected><?=$row['campaign_name'];?></option>
						<? else :?>
						<option value="<?=$row['campaign_id'];?>"><?=$row['campaign_name'];?></option>
						<? endif; ?>
					<? $row_num++; ?>
				<? endwhile; ?>
			</select>
		</label>
	<? endif; ?> <!-- if ($num_rows == 0) : -->
	[SPLIT]
<? endif; ?> <!-- if ($pos3 !== false) : -->

<? if ($pos4 !== false) : ?>
	<?
	$sql = "SELECT * FROM campaign_tasks WHERE campaign_id=" . $campaign_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	?>
	<? if ($num_rows == 0) : ?>
		<table class="pretty_grid">
			<th><?=T_('No Campaign Tasks Found');?></th>
		</table>	
	<? else : ?>
		<table class="pretty_grid">
			<th><?=T_('Name');?></th>
			<th><?=T_('Points');?></th>
			<th><?=T_('Max Times');?></th>
						
			<? while($row = mysql_fetch_assoc($query)) : ?>
			<tr>
				<td><?=$row['task_name'];?></td>
				<td><?=$row['points'];?></td>
				<td><?=$row['max_times'];?></td>
			</tr>
			<? endwhile; ?>
		</table>		
	<? endif; ?>
<? endif; ?> <!-- if ($pos4 !== false) : -->

