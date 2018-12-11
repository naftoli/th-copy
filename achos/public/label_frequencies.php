<? 
include("db.php");

$message = "";
if (isset($_POST["action"])) {
	$sql = "UPDATE labels SET frequency_id=" . $_POST['frequency_id'] . " WHERE label_id=" . $_POST['label_id'];
	echo $sql . "<br />";
	$query = mysql_query($sql);
	if (!$query) 
		$message = "<H1><span style='color:red;'>Update Failed.</span></H1>";
}

include("classes/label.php");
$labels = array();
$sql = "SELECT * FROM labels";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	array_push($labels, new label($row));
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<script>
			function set_label_id(label_id) {
				document.getElementById("label_id").value = label_id;
								
				var frequency_select = document.getElementById("frequency_id_" + label_id);
				var selected_index = frequency_select.selectedIndex;
				var frequency_id = frequency_select.options[selected_index].value;
				if (frequency_id == 0) {
					alert("Choose a frequency");
				}
				else {
					document.getElementById("frequency_id").value = frequency_id;
					document.forms["labels_form"].submit();				
				}
			}
		</script>
	</HEAD>
	
	<BODY>
	
		<FORM method="post" name="labels_form" action="label_frequencies.php">
			<input type="hidden" name="action" id="action" value="update">
			<input type="hidden" name="label_id" id="label_id" value="">
			<input type="hidden" name="frequency_id" id="frequency_id" value="">
			<input type="hidden" name="sequence_number" id="sequence_number" value="">
			
			<TABLE>
				<TR style="text-align:left; color:darkblue;">
					<TH>LABEL</TH>
					<TH>FREQUENCY</TH>
					<TH>SEQUENCE #</TH>
				</TR>
				
				<? for ($lno = 0; $lno < count($labels); $lno++) : ?>
				<TR>
					<TD>
						<?=$labels[$lno]->label_name;?>
					</TD>
					
					<TD>
						<? $sql = "SELECT f.frequency_id, f.frequency_name, fp.frequency_period_name FROM frequencies AS f JOIN frequency_periods AS fp USING (frequency_period_id) ORDER BY f.frequency_id"; ?>
						<? $query = mysql_query($sql); ?>
						
						<SELECT name="frequency_id_<?=$labels[$lno]->label_id;?>" id="frequency_id_<?=$labels[$lno]->label_id;?>">
							<OPTION value="0">SELECT A FREQUENCY</OPTION>
							<? while ($row = mysql_fetch_assoc($query)) : ?>
							
								<? if ($row['frequency_id'] == $labels[$lno]->frequency_id) : ?>
								<OPTION selected value="<?=$row['frequency_id'];?>"><?=$row['frequency_name'];?> - <?=$row['frequency_period_name'];?></OPTION>
								<? else : ?>
								<OPTION value="<?=$row['frequency_id'];?>"><?=$row['frequency_name'];?> - <?=$row['frequency_period_name'];?></OPTION>
								<? endif; ?>
								
							<? endwhile; ?>
						</SELECT>
					</TD>
					
					<TD>
						<input type="button" value="UPDATE" onclick="set_label_id(<?=$labels[$lno]->label_id;?>);">
					</TD>
				</TR>
				<? endfor; ?>
			</TABLE>
		</FORM>
		
	</BODY>
</HTML>
