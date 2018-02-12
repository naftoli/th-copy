<?
require('db.php');
require('lang.php');

$delete_message = T_('Are you certain that you want to delete this campaign?');

$camp_id = $_GET['camp_id'];
$group_type_id = $_GET['group_type_id'];
$group_id = $_GET['group_id'];

$sql = "SELECT * from camp_group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$row_num = 0;
?>
<?=T_('Select a Group Type')?>:
<label>
	<select name="group_type_id" id="group_type_id" onchange="get_groups_select();">
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
		<select name="group_id" id="group_id" onchange="get_campaigns();">
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
<?
$sql = "SELECT * FROM campaigns WHERE group_id=" . $group_id;
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
?>
<? if ($num_rows == 0) : ?>
	<table class="pretty_grid">
		<th><?=T_('No Campaigns Found');?></th>
	</table>
<? else : ?>
	<table class="pretty_grid">
		<tr>
			<th><?=T_('Name');?></th>
			<th><?=T_('Points');?></th>
			<th></th>
			<th></th>
		</tr>
		
		<? while($row = mysql_fetch_assoc($query)) : ?>
		<tr>
			<td><?=$row['campaign_name'];?></td>
			<td><?=$row['points'];?></td>
			<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; document.forms['campaigns_form'].submit();"><?=T_('Edit Campaign');?></a></td>
			<td><a href="#" onclick="document.getElementById('action').value='delete'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; var dlt = confirm ('<?=$delete_message;?>'); if (dlt == true) document.forms['campaigns_form'].submit();"><?=T_('Delete Campaign');?></a></td>
		</tr>
		<? endwhile; ?>
	</table>
<? endif; ?>