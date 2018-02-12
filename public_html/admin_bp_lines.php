<?php
require 'db.php';

$total = 0;
$info = array();
$learned = array();
$sql = "select * from bp_user_summary bp
        join users u using (user_id)
        join schools s using (school_id)
        join classes c on u.class_id = c.class_id
        where bp.campaign_id in (9,10)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['user_id']] = $row;
    $learned[$row['user_id']][$row['campaign_id']] = $row['num_lines'];
    $total += $row['num_lines'];
}
//echo "Total: " . $total;
//echo "<pre>"; print_r($learned); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>BP Report</title>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
		<style>
			body, table {
				font-family: sans-serif;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
        <h2>BP Report</h2>
        <table id="table" class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Tanya Lines</th>
                    <th>Mishna Lines</th>
					<th>User ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($info as $user) {
                    echo "<tr><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . $user['school_name'] . "</td><td>" .
                        $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "</td><td>" .
                        $learned[$user['user_id']][9] . "</td><td>" . $learned[$user['user_id']][10] . "</td><td>" .
						$user['user_id'] . "</td></tr>";
                }
                ?>
            </tbody>
		</table>
    </body>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
	<script>
		$('#table').DataTable({
			paging : false
		});
	</script>