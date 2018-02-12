<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Not Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Not Registered Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
			$getParentInfo = true;
            $schoolsUsers[$id] = $s->getNotRegisteredUsers($getParentInfo);
        }
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>User ID</th><th>Start Date</th><th>Contact Info</th></tr>";
            foreach ( $users as $user ) {
                $grade = $user['user']['class_grade'] . ( empty( $user['user']['class_sub']) ? '' : "-" . $user['user']['class_sub'] );
                echo "<tr><td>" . $grade . "</td><td>" . $user['user']['first'] . " " . $user['user']['last'] . 
                    "</td><td>" . $user['user']['user_id'] . "</td><td>" . jdtogregorian( $user['user']['user_start_date'] ) . 
                    "</td><td>";
				if (isset($user['parent']) && (!empty($user['parent']['admin_email']))) {
					echo $user['parent']['first'] . ' ' . $user['parent']['last'] . "<br />";
					echo "<a href='mailto:" . $user['parent']['admin_email'] . "'>" . $user['parent']['admin_email'] . "</a><br />";
					if (!empty($user['parent']['admin_phone_home'])) echo "Home phone: " . $user['parent']['admin_phone_home'] . "<br />";
					if (!empty($user['parent']['admin_phone_work'])) echo "Work phone: " . $user['parent']['admin_phone_work'] . "<br />";
					if (!empty($user['parent']['admin_phone_mobile'])) echo "Cell phone: " . $user['parent']['admin_phone_mobile'] . "<br />";
				} else {
					echo (!empty($user['user']['email']) ? $user['user']['email'] : '');
				} 
				echo "</td></tr>";
            }
            echo "</table><br /><div class='page-break'></div>";
        }
        ?>
    </body>
</html>