<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Pushka Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="mobile/reg/js/keyboard.js" charset="UTF-8"></script>
        <link rel="stylesheet" type="text/css" href="mobile/reg/css/keyboard.css">
        <style type='text/css'>
            table {
                font-size: 14px;
            }
            th, td {
                padding: 5px 10px;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <h1>Pushka Report</h1>
        
        <? 
        require_once 'class.adminSchools.php';		
		$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools(); 
		
		$confirmed = array();
		foreach ( $schools as $school => $name ) {
			$sql = "select school_name, conf_pushka_users from schools where school_id = " . $school;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			if ($row['conf_pushka_users']) {
				$confirmed[] = $row['school_name'];
			}
		}
		
		if ( empty( $confirmed ) ) {
			echo "There are no schools that confirmed their pushka list.";
		} else {
			echo "<p>Here is the list of schools that confirmed their pushka list:</p>";
			foreach ( $confirmed as $school ) {
				echo $school . "<br />";
			}
		}
		?>
	</body>
</html>