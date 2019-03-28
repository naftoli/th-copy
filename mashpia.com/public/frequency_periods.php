<? 
include("db.php");

include("classes/frequency_period.php");
$frequency_periods = array();
$sql = "SELECT * FROM frequency_periods";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$frequency_period = new frequency_period($row);
	array_push($frequency_periods, $frequency_period);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	</HEAD>
	
	<BODY>
	
		<FORM method="post" action="frequency_periods.php">
			<input type="hidden" name="action" value="delete">
			<input type="hidden" name="frequency_period_id" id="frequency_period_id" value="">
	
			<TABLE>
				<TR>
					<TH>Period</TH>
					<TH></TH>
				</TR>
				
				<? for ($fpno = 0; $fpno < count($frequency_periods); $fpno++) : ?>
					<? $frequency_period = $frequency_periods[$fpno]; ?>
					<TR>
						<TD>
							<?=$frequency_period->frequency_period_name;?>
						</TD>
						<TD>
							<input type="submit" value="DELETE" onclick="document.getElementById('frequency_period_id').value=<?=$frequency_period->frequency_period_id;?>">
						</TD>
					</TR>
				<? endfor; ?>
			</TABLE>
			
		</FORM>

		<br />
		<br />
		
		<FORM method="post" action="frequency_periods.php">
			<input type="hidden" name="action" value="add">
			Period
			<input type="text" name="frequency_period_name">
		</FORM>
		
	</BODY>
</HTML>
