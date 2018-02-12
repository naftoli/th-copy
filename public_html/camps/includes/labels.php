<?php
include ("db.php");

$sql = "SELECT COUNT(*) AS total_tasks, s.subject_name, dtm.mission_name, dt.name, dt.description, l.frequency_id, l.label_id, l.label_name, l.label_description ";
$sql = $sql . "FROM date_tasks AS dt ";
$sql = $sql . "JOIN labels AS l USING (label_id) ";
$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
$sql = $sql . "JOIN subjects AS s USING (subject_id) ";
$sql = $sql . "GROUP BY l.label_id";
$query = mysql_query($sql);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<HTML xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<script src="../scripts/jquery.tools.min.js"></script>
		
		<SCRIPT>						
			function update_label_period(label_id) {
				var frequency_id = document.getElementById("frequency_id_" + label_id).options[document.getElementById("frequency_id_" + label_id).selectedIndex].value;
				
				var function_name = "update_label_period";
				var parameters = [label_id, frequency_id];				
				var url = "http://www.mashpia.com/camps/includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;

				$.getJSON(url, function(success) {
					if (success == 0)
						alert("Could not update label. Please try again.");
				});				
			}
		</SCRIPT>
	</head>

	<BODY>
		<TABLE style="width:1600px;">
			<TR>			
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">SUBJECT</span></TH>							
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">MISSION</span></TH>				
				<TH align="left"><span style="color:darkblue; text-decoration:underline;">Task Name</span></TH>
				
				<TH align="left"><span style="color:darkred; text-decoration:underline;">LABEL</span></TH>
				<TH align="left"><span style="color:darkred; text-decoration:underline;">FREQUENCY</span></TH>				
				<TH align="left"></TH>
			</TR>
			<?	
			//$i = 1; 
				while ($row = mysql_fetch_assoc($query)) : 
				$task_name = substr($row["name"], 0, 50);
			?>
			<TR>
				<!--<TD style="width:50px;"><span style="color:red;"><?//= $i++ ?></span></TD>-->
				
				<TD style="width:100px;"><span style="color:blue;"><?=$row["subject_name"]?></span></TD>	
				<TD style="width:150px;"><span style="color:blue;"><?=$row["mission_name"];?></span></TD>					
				<TD style="width:300px;"><span style="color:yellow;"><?=$total_tasks;?> <?=$task_name;?></span></TD>
				
				<TD style="width:150px;"><span style="color:green;"><?=$row["label_name"];?></span></TD>
				
<?php
$frequencies = "<SELECT name='frequency_id_" . $row["label_id"] . "' id='frequency_id_" . $row["label_id"] . "'>";
$frequencies_sql = "SELECT * FROM frequencies AS f JOIN frequency_periods AS fp USING (frequency_period_id) ORDER BY frequency_id";
$frequencies_query = mysql_query($frequencies_sql);
while ($frequencies_row = mysql_fetch_assoc($frequencies_query)) {
	if ($row["frequency_id"] == $frequencies_row["frequency_id"]) 
		$frequencies = $frequencies . "<OPTION selected value='" . $frequencies_row["frequency_id"] . "'>" . $frequencies_row["frequency_name"] . " - " . $frequencies_row["frequency_period_name"] . "</OPTION>";
	else
		$frequencies = $frequencies . "<OPTION value='" . $frequencies_row["frequency_id"] . "'>" . $frequencies_row["frequency_name"] . " - " . $frequencies_row["frequency_period_name"] . "</OPTION>";	
}
$frequencies = $frequencies . "</SELECT>";
?>

				
				<TD style="width:100px;"><?=$frequencies;?></TD>
				
				
				<TD><INPUT type="button" onclick="update_label_period(<?=$row['label_id'];?>);" value="UPDATE"></TD>
			</TR>
			<? endwhile; ?>
		<TABLE>
	</BODY>
<HTML>