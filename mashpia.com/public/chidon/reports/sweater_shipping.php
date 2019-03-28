<?php
//echo "Needs update for this report.";
//exit;

require '../../db.php';
require 'vars.php';

$info = [];
$sql = "SELECT s.school_id, s.school_name, u.last, u.first, u.gender, tc.grade, tc.size, tc.school_rep FROM th_chidon tc"
    ." JOIN users u USING (user_id) "
    ." JOIN schools s ON tc.school_id = s.school_id "
    ." WHERE tc.year = '$year' AND s.test_school='0' "
	." AND date_paid IS NOT NULL "
	." AND u.gender = 'F' "
    ." ORDER BY school_name, gender, grade, last, first, size";

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_name']][$row['gender']][] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
			caption {
				font-size: 20px;
				font-weight: bold;
			}
            table {
                page-break-after: always;
            }
		</style>
	</head>
	
	<body>
        <?php
        foreach($info as $school_name => $gender_split) {
            foreach( $gender_split as $gender => $students) { ?>
            <table>
                <caption><?= $school_name ?> - <?= $gender ?></caption>
                <tr>
                    <th>First</th><th>Last</th><th>Grade</th><th>Sweater Size</th><th></th>
                </tr>
                <?php foreach($students as $student) {?>
                <tr>
                    <td><?=$student['first']?></td>
                    <td><?=$student['last']?></td>
                    <td><?=$student['grade']?></td>
                    <td><?=$student['size']?></td>
                    <td><?=$student['school_rep'] ? "Representative" : "Contestant"?></td>
                </tr>
                <?php } ?>
            </table>
        <?php  
            } // end foreach gender
        } // end foreach school ?>
        
	</body>
</html>