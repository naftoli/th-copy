<?
require('db.php');
require('lang.php');

$camp_id = $_GET['camp_id'];
$term_id = $_GET['term_id'];

$sql = "SELECT * FROM campaigns WHERE camp_id="	. $camp_id . " AND term_id=" . $term_id;
	
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);
?>
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
			
		<? while ($campaign = mysql_fetch_assoc($query)) : ?>
			<tr>
				<td><?=$campaign['campaign_name'];?></td>
				<td><?=$campaign['points'];?></td>
				<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('campaign_id').value='<?=$campaign['campaign_id'];?>'; document.forms['campaigns_form'].submit();"><?=T_('Edit Campaign');?></a></td>		
				<td><a href="#" onclick="var dlt = confirm ('<?=T_('Are you sure that you want to delete this Campaign?');?>); if (dlt == true) { document.getElementById('action').value='delete'; document.forms['campaigns_form'].submit(); } "><?=T_('Delete Term');?></a></td>
			</tr>
		<? endwhile; ?>			
	</table>
<? else : ?> 
		<br />
		<table class="pretty_grid">
			<th>No campaigns found</th>
		</table>
<? endif; ?> 
