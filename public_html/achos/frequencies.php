<? 
include("db.php");

include("classes/frequency.php");
$frequencies = array();
$sql = "SELECT * FROM frequencies";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$frequency = new frequency($row);
	$frequency->get_frequency_period();
	array_push($frequencies, $frequency);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	</HEAD>
	
	<BODY>
	
		<FORM method="post" action="frequencies.php">
			<input type="hidden" name="action" value="delete">
			<input type="hidden" name="frequency_id" id="frequency_id" value="">
	
			<TABLE>
				<TR>
					<TH>Frequency</TH>
					<TH></TH>
				</TR>
				
				<? for ($fno = 0; $fno < count($frequencies); $fno++) : ?>
					<? $frequency = $frequencies[$fno]; ?>
					<TR>
						<TD>
							<?=$frequency->frequency_name;?> <?=$frequency->frequency_period->frequency_period_name;?>
						</TD>
						<TD>
							<input type="submit" value="DELETE" onclick="document.getElementById('frequency_id').value=<?=$frequency->frequency_id;?>">
						</TD>
					</TR>
				<? endfor; ?>
			</TABLE>
						
		</FORM>
	
		<br />
		<br />
		
		<FORM>
			<input type="hidden" name="action" value="add">			
			<input type="text" name="frequency_name">

			<SELECT name="frequency_period_id">
			
				<?php
				
				$frequency_periods = array();
				$sql = "SELECT * FROM frequency_periods";
				$query = mysql_query($sql);
				while ($row = mysql_fetch_assoc($query)) {
					echo "<OPTION value='" . $row['frequency_period_id'] . "'>" . $row['frequency_period_name'] . "</OPTION>";
				}
				?>

			</SELECT>
			
			<input type="submit" value="ADD">

		</FORM>
		
	</BODY>
	
</HTML>
