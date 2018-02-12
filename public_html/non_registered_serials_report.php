<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Serials Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Serials Report</h1>
        <? 
        $sql = "select user_serial, last, first from users where user_registered is null or user_registered = 0 order by last, first";
		$result = mysql_query( $sql );
	    echo "<h2>Non Registered Students</h2>";
        echo "<table>";
        echo "<tr><th>Student</th><th>Serial</th></tr>";
		while ( $user = mysql_fetch_assoc( $result ) ) { 
            echo "<tr><td>" . $user['first'] . " " . $user['last'] . "</td><td>" . $user['user_serial'] . "</td></tr>"; 
        }
		echo "</table><br />";
        ?>
    </body>
</html>