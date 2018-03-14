<?php
//echo "Needs update for this report.";
//exit;

$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require 'vars.php';

$info = [];
$sql = "SELECT s.school_name, u.gender, tc.size, COUNT(*) AS total FROM th_chidon tc"
    ." JOIN users u USING (user_id) "
    ." JOIN schools s ON tc.school_id = s.school_id "
    ." WHERE tc.year = '$year' AND s.test_school='0' "
	." AND date_paid IS NOT NULL "
	." AND u.gender = 'M' "
	." GROUP BY school_name, gender, size "
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
				width: 300px;
				margin: 0px auto 40px;
				font-size: 1.5em;
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
                    <th>Size</th><th>Total</th>
                </tr>
                <?php foreach($students as $student) {?>
                <tr>
                    <td><?=$student['size']?></td>
                    <td><?=$student['total']?></td>
                </tr>
                <?php } ?>
            </table>
        <?php  
            } // end foreach gender
        } // end foreach school ?>
        
	</body>
</html>