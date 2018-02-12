<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Registered Report</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
            caption {
            	font-size: 20px;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>Registered Report</h1>
		<?
		$schools = array();
		$sql = "select * from schools where school_era is null";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['school_id']] = $row['school_name'];
		}
		
		foreach ($schools as $id => $name) {
			echo "<table>";
			echo "<caption>" . $name . "</caption>";
			echo "<tr><th>Grade</th><th>Student</th><th>User ID</th><th>Start Date</th><th>Registered this year</th></tr>";
            
            $users = array();
            $sql = "select * from users u 
            		join classes c using (class_id) 
            		where u.school_id = $id 
            		and user_registered > 0";
			$result = mysql_query($sql) or die( mysql_error() );
			while ($user = mysql_fetch_assoc($result)) {
                $grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . 
                    "</td><td>" . $user['user_id'] . "</td><td>" . jdtogregorian( $user['user_start_date'] ) . 
                    "</td><td>" . $user['user_registered'] . "</td></tr>"; 
            }
            echo "</table><br /><div class='page-break'></div>";
		}
		?>
	</body>
</html>