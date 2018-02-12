<?
require('db.php');

$camp_id = $_GET['camp_id'];
$user_type = $_GET['user_type'];

if ($camp_id > -1)
	$sql = "SELECT * FROM terms WHERE camp_id=" . $camp_id;
else
	$sql = "SELECT * FROM terms WHERE camp_id IS NULL";
	
$term_id = 0;
	
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
?>
<? if ($num_rows > 0) : ?>
<label>
	Select A Term
	<select name="term_id" id="term_id">
		<? $row_num = 0; ?>
		<? while($row = mysql_fetch_assoc($query)) : ?>
		<? if ($row_num == 0) $term_id = $row['term_id']; ?>
		<? $row_num++; ?>
		<option VALUE="<?=$row['term_id']?>"><?=$row['term_name'];?></option>
		<? endwhile; ?>
	</select>
</label>
<? endif; ?>
:
<? if ($term_id > 0) : ?>
	<? if ($camp_id == -1) : ?>
		<? $campaigns_query = mq("SELECT cmpgs.*, c.camp_name FROM campaigns AS cmpgs LEFT JOIN camps AS c USING (camp_id) WHERE camp_id IS NULL"); ?>
	<? else : ?>
		<? $campaigns_query = mq("SELECT cmpgs.*, c.camp_name FROM campaigns AS cmpgs JOIN camps AS c USING (camp_id) WHERE camp_id=" . $camp_id); ?>
	<? endif; ?>
	<? $num_rows = mysql_num_rows($campaigns_query); ?>
	<? if ($num_rows > 0) : ?>
	<br />
	<table class="pretty_grid">
		<? if ($user_type == "super") : ?>
		<th>Camp</th>
		<? endif; ?>
		<th>Name</th>
		<th>Points</th>
		<th></th>
		<th></th>
			
		<? while ($campaign = mysql_fetch_assoc($campaigns_query)) : ?>
			<tr>
				<? if ($user_type == "super") : ?>
					<? if ($campaign['camp_name'] == "") : ?>
					<td>All Camps</td>
					<? else : ?>
					<td><?=$campaign['camp_name'];?></td>
					<? endif; ?>
				<? endif; ?>
				<td><?=$campaign['campaign_name'];?></td>
				<td><?=$campaign['points'];?></td>
				<td><a href="admin_campaigns.php?action=edit&camp_id=<?=$camp_id;?>&term_id=<?=$term_id;?>&campaign_id=<?=$campaign['campaign_id'];?>">Edit Campaign</a></td>		
				<td><a href="admin_campaigns.php?action=delete&campaign_id=<?=$campaign['campaign_id'];?>" onclick="return confirm ('Are you sure that you want to delete this Campaign?');">Delete Term</a></td>
			</tr>
		<? endwhile; ?>			
	</table>
	<? else : ?> 
		<br />
		<table class="pretty_grid">
			<th>No campaigns found</th>
		</table>
	<? endif; ?> 
<? endif; ?>