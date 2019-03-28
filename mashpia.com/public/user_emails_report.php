<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>User Email Report</title>
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
        <h1>User Email Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        
		$users = array();
		require_once 'class.schoolsUsers.php';
        foreach ($schools as $id => $school) {
            $s = new SchoolsUsers($id);
            $users[$id] = $s->getUserInfo();
        } 
		
        foreach ($users as $school => $info) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Email</th></tr>";
            foreach ($info as $user) {
                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
                    "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . $user['email'] . "</td></tr>"; 
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>