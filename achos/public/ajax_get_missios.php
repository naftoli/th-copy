<?php
require('db.php');

$subject_id = $_GET['subject_id'];

$sql = 'SELECT DISTINCT subject_id, subject_name, inst_name, mission_name, mission_number FROM subjects JOIN institutions USING (inst_id) JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE subject_type != \'school_points\' AND mission_number IS NOT NULL ORDER BY inst_name, subject_name, mission_number, mission_name';
$query = mysql_query($sql);
?>

<label>
	<select name="subject_mission">
		<option value="-1">Select Mission</option>
		<? while($row = mysql_fetch_assoc($query)) : ?>
		<option VALUE="<?=$row['mission_number']?>"><?=$row['mission_number'];?></option>
		<? endwhile; ?>
	</select>
</label>