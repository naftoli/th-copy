<?
require('db.php');
require('lang.php');

$camp_id = $_GET['camp_id'];
$group_type_id = $_GET['group_type_id'];
$group_id = $_GET['group_id'];
$campaign_id = $_GET['campaign_id'];

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