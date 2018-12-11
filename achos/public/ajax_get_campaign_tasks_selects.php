<?
require('db.php');
require('lang.php');

$delete_message = T_('Are you certain that you want to delete this campaign?');

$camp_id = $_GET['camp_id'];
$group_type_id = $_GET['group_type_id'];
$group_id = $_GET['group_id'];
$campaign_id = $_GET['campaign_id'];
if (isset($_GET['task_id']))
	$task_id = $_GET['task_id'];
else
	$task_id = -1;
$divs = $_GET['divs'];

$pos1 = strpos($divs, "1");
$pos2 = strpos($divs, "2");
$pos3 = strpos($divs, "3");
$pos4 = strpos($divs, "4");
$pos5 = strpos($divs, "5");

?>
<? if ($pos1 !== false) : ?>
<?
$sql = "SELECT * from camp_group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$row_num = 0;
?>
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
	$row_num = 0;
	?>
	
	<? if ($num_rows == 0) : ?>
		<table class="pretty_grid">
			<th><?=T_('No Campaign Tasks Found');?></th>
		</table>	
	<? else : ?>
		<?=T_('Select a Campaign Task')?>:
			<select name="task_id" id="task_id" onchange="get_campaign_tasks('5');">				
				<? while($row = mysql_fetch_assoc($query)) : ?>
					<? if ($task_id == -1 && $row_num == 0) $task_id = $row['task_id']; ?>
					<? if ($task_id == $row['task_id']) : ?>
					<option value="<?=$row['task_id'];?>" selected><?=$row['task_name'];?></option>
					<? else : ?>
					<option value="<?=$row['task_id'];?>"><?=$row['task_name'];?></option>
					<? endif; ?>
					<? $row_num++; ?>
				<? endwhile; ?>
			</select>
	<? endif; ?>
	[SPLIT]
<? endif; ?> <!-- if ($pos4 !== false) : -->

<? if ($pos5 !== false) : ?>
	<?
	$sql = "SELECT * FROM campaign_tasks WHERE task_id=" . $task_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);	
	?>
	<? if ($num_rows == 0) : ?>
		<table class="pretty_grid">
			<th><?=T_('No Campaign Tasks Found');?></th>
		</table>	
	<? else : ?>
		<? $row = mysql_fetch_assoc($query); ?>
		<input type="hidden" name="task_id" id="task_id" value="<?=$row['task_id'];?>">
		<p>
			<label>			
				<?=T_('Name');?>:<input type="text" value="<?=$row['task_name'];?>" readonly="readonly" size="100">
			</label>
			
			<br />
			
			<label>
				<?=T_('Miles');?>:<input type="text" name="miles" id="miles" value="<?=$row['points'];?>">
			</label>
			
			<br />
			
			<label>
				<?=T_('Left Circle');?>:<input name="left_circle" id="left_circle" value="<?=$row['points'];?>" maxlength="3" type="text">
			</label>

			<br />
			
			<label>
				<?=T_('Right Circle');?>:<input name="right_circle" id="right_circle" value="<?=$row['points'];?>" maxlength="3" type="text">
			</label>

			<br />
			
			<label>
				<?=T_('Number of Cards');?>:<input name="number_of_cards" id="number_of_cards" value="3" maxlength="1" type="text">
			</label>		

		</p>
	<? endif; ?>
	<br />
<? endif; ?> <!-- if ($pos5 !== false) : -->




