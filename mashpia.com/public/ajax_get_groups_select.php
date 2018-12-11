<?
require('db.php');
require('lang.php');

$group_type_id = $_GET['group_type_id'];

$sql = "SELECT * FROM camp_groups WHERE group_type_id=" . $group_type_id;	
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
$row_num = 0;
$group_id = 0;
?>

	<br />
	
	<? if ($num_rows > 0) : ?>	
		<?=T_('Select a Group')?>:
		<label>
			<select name="group_id" id="group_id" onchange="get_campaigns();">
				<? while($row = mysql_fetch_assoc($query)) : ?>
				<? if ($row_num == 0) $group_id = $row['group_id']; ?>
				<option value="<?=$row['group_id'];?>"><?=$row['group_name'];?></option>
				<? $row_num++; ?>
				<? endwhile; ?>
			</select>
		</label>
	<? else : ?>
		<table class="pretty_grid">
			<th><?=T_('No Groups Found');?></th>
		</table>
	<? endif; ?>
	
	<br />
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
			<td><a><?=T_('Edit Campaign');?></a></td>
			<td><a><?=T_('Delete Campaign');?></a></td>
		</tr>
		<? endwhile; ?>
	</table>
<? endif; ?>	