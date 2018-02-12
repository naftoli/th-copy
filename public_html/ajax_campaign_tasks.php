<?
require('db.php');

$camp_id = $_GET['camp_id'];

if ($camp_id == 1) {
	$camps_query = mq("SELECT * FROM camps");
}
else {
	$camps_query = mq("SELECT * FROM camps WHERE camp_id=" . $camp_id);
}
?>
<? if ($camp_id == 1) : ?>
	<select name="camp_id" id="camp_id">
	<? while ($camp = mysql_fetch_assoc($camps_query)) : ?>
		<? if ($camp_id == $camp['camp_id']) : ?>
		<option value="<?=$camp['camp_id']?>" selected><?=$camp['camp_name'];?></option>
		<? else : ?>
		<option value="<?=$camp['camp_id']?>"><?=$camp['camp_name'];?></option>
		<? endif; ?>
	<? endwhile; ?>
	</select>
<? endif; ?>
