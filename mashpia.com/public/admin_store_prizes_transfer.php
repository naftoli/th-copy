<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('file_save.php');

$prize_ids = gr('prize_ids', '');


$transfer_school_id = gri('transfer_school_id', -1);

if ($prize_ids != "" && $transfer_school_id > -1) {
	$prize_id_numbers = explode(";", $prize_ids);
	for ($cntr = 0; $cntr < count($prize_id_numbers); $cntr++) {		
		$sql = "SELECT * FROM prizes_auction WHERE prize_id=" . $prize_id_numbers[$cntr];
		$query = mysql_query($sql);
		$prize = mysql_fetch_assoc($query);
		
		$insert_sql = "INSERT INTO prizes_store (school_id, prize_name, prize_description, prize_points, prize_available, prize_image_id) VALUES (" . $transfer_school_id . ", " . ms($prize['prize_name']) . ", " . ms($prize['prize_description']) . ", " . $prize['prize_points'] . ", 0, " . $prize['prize_image_id'] . ")";
		mq($insert_sql);
	}
}

$school_id = gri('school_id', -1);

if ($school_id > -1)
	$sql = "SELECT * FROM prizes_auction WHERE school_id=" . $school_id . " GROUP BY prize_name";
else
	$sql = "SELECT pa.* FROM prizes_auction AS pa WHERE pa.prize_name NOT IN (SELECT ps.prize_name FROM prizes_store AS ps) GROUP BY pa.prize_name";
//echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
$query = mysql_query($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Store Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
			var checked = false;
			
			function check_all(chckbx) {
				if (chckbx.checked)
					checked = true;
				else
					checked = false;
					
				var elements = document.getElementById("transfer_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") 
						elements[cntr].checked = checked;
				} 
			}
			
			function submit_form() {
				if (document.getElementById("transfer_form").elements["transfer_school_id"].value == -1) {
					alert("Please select a school");
				}
				else {
					var post_string = "";
					
					var elements = document.getElementById("transfer_form").elements;
					
					for (cntr = 0; cntr < elements.length; cntr++) {
						if (elements[cntr].type == "checkbox") {
							
							if (elements[cntr].checked == true) {
							
								if (elements[cntr].name != "check_global") {
									var info = elements[cntr].name.split("_");
									post_string = post_string + info[1] + ";";
								}
								
							}
							
						}
					} 
				
					post_string = post_string.substr(0, post_string.length - 1);
					
					if (post_string != "") {
						document.getElementById("transfer_form").elements["prize_ids"].value = post_string;
						document.getElementById("transfer_form").submit();
					}
				}
			}
		</script>		
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<form name="transfer_form" id="transfer_form" action="admin_store_prizes_transfer.php" method="post" accept-charset="UTF-8">
			
				<input type="hidden" name="prize_ids" id="prize_ids" value="">
				
				<H1>
					<?=T_('Store Prizes')?>
					<input type="button" name="transfer" id="transfer" onclick="submit_form();" value="TRANSFER">
				</H1>			
			
				<? if ($school_id == -1 && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)) : ?>
			
					<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

					<P>
						<?=T_('Select Institution')?>: 
						
						<select name="transfer_school_id" id="transfer_school_id">							
							<option value="-1">Select a school</option>
							
							<? while($school_row = mysql_fetch_assoc($school_result)): ?>
								<option value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>>
									<?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?>
								</option>							
							<?endwhile;?>
						</select> 
						
					</P>
			
				<?endif;?>

			
				<table class="pretty_grid">
					<tr>
						<th><input type="checkbox" name="check_global" id="check_global" onclick="check_all(this);"><?=T_('All')?></th>
						<th><?=T_('Name')?></th>
						<th><?=T_('Points')?></th>
					</tr>
					
					<? while ($prize = mysql_fetch_assoc($query)) { ?>
						<tr>
							<td>
								<input type="checkbox" id="checkbox_<?=$prize['prize_id'];?>" name="checkbox_<?=$prize['prize_id'];?>">
							</td>
							
							<td>
								<?=es($prize['prize_name'])?>
							</td>
							
							<td>
								<?=es($prize['prize_points'])?>
							</td>							
						</tr>
					<? } ?>				
				</table>
			
			</form>
			
	</BODY>
	
</HTML>
