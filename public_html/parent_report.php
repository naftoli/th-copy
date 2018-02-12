<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Parents Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
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
        <h1>Parents Report</h1>
        <? 
        require_once 'class.adminSchools.php';
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolIDs = array();
        foreach ($schools as $id => $school) {
            $schoolIDs[] = $id;
        }
        
        $sql = "select * from users u
                left join classes c on (u.class_id = c.class_id)
                where u.school_id in (" . implode(',', $schoolIDs) . ")
                order by u.last, u.first";
        //echo $sql;
        /*
        $sql = "SELECT DISTINCT a.admin_id, a.username, a.password, a.first, a.last, a.admin_address1, a.admin_city, a.admin_country, a.admin_email 
                FROM admins AS a 
                join admin_auths aa using (admin_id)  
                WHERE aa.auth =  'user' 
                and aa.id in (
                    select user_id from users where school_id in ( " . implode(',', $schoolIDs) . " )
                )
                order by a.last";
        */
        $result = mysql_query($sql) or die(mysql_error());
        ?>
        <table>
            <tr>
                <th>Student First Name</th>
                <th>Student Last Name</th>
                <th>Grade / Platoon</th>
                <th>Registered</th>
                <th>Parent Name</th>
                <th>Parent Email</th>
                <th>Parent Home Phone</th>
                <th>Parent Cell Phone</th>
            </tr>
            <?php
            while ($row = mysql_fetch_assoc($result)) {
                $user_id = $row['user_id'];
                $sql2 = "select * from admins a
                        join admin_auths aa using (admin_id)
                        where aa.id = " . $user_id . " 
                        and aa.role_id = 1
                        and aa.auth = 'user'";
                $result2 = mysql_query($sql2) or die(mysql_error());
                $row2 = mysql_fetch_assoc($result2);
                /*
                echo "<tr><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['admin_email'] . "</td><td>" .
                    $row['admin_phone_home'] . "</td><td>" . $row['admin_phone_mobile'] . "</td><td>";
                $sql2 = "select u.first, u.last
                        from users u
                        join admin_auths aa on aa.id = u.user_id 
                        where aa.admin_id = " . $row['admin_id'];
                $result2 = mysql_query($sql2) or die(mysql_error());
                while ($row2 = mysql_fetch_assoc($result2)) {
                    echo $row2['first'] . ' ' . $row2['last'] . "<br />";
                }
                echo "</td></tr>";
                */
                $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                echo "<tr><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $grade . "</td><td>" . ($row['user_registered'] ? $row['user_registered'] : 'no') . 
                    "</td><td>" . $row2['first'] . ' ' . $row2['last'] . "</td><td>" . $row2['admin_email'] . "</td><td>" . $row2['admin_phone_home'] . "</td><td>" .
                    $row2['admin_phone_mobile'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>