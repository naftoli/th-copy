<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
/*
 * Make a report that has all kids that are able to register which is all kids in grade 4-8 in 5783.
Should include the following columns:
Serial
Full name
School
Class
Registered in 5779
Registered in 5780
Registered in 5781
Registered in 5782
Registered in 5783
KHK eligible Yes/No
KHK Registered Yes/No
Parents Email
Parents phone Number
At the bottom of each schools section, add totals per class and totals per school registered/non registered. For HQ add the grand total of all schools.
 */

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$startGrade = 4;
$endGrade = 8;

$chidonYr = GlobalSettings::getChidonRegYear();
$year = $_REQUEST['year'];
if ($chidonYr > $year) {
    $startGrade--;
    $endGrade--;
}

// personal info
$users = [];
$userIds = [];
$sql = "SELECT u.first, u.last, u.user_serial, u.school_id, u.user_id, c.class_grade, c.class_sub, a.admin_email, 
            a.admin_phone_mobile, a.admin_phone_work, a.admin_phone_home
        FROM users u 
        JOIN classes c using (class_id) 
        JOIN admin_auths aa on aa.id = u.user_id 
        JOIN admins a using (admin_id) 
        WHERE 
            aa.auth = 'user' AND class_grade >= " . $startGrade . " AND class_grade <= " . $endGrade . " 
            AND u.school_id in (" . implode(',', array_keys($schools)) . ")
        ORDER BY u.school_id, c.class_grade, c.class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
    $userIds[] = $row['user_id'];
}

// chidon info
$chidonInfo = [];
$sql = "SELECT tc.user_id, tc.khk_reg, tc.year, tc.date_paid, tci.highest_track 
        FROM th_chidon tc 
        LEFT JOIN th_chidon_info tci USING (year, user_id) 
        ORDER BY year";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $chidonInfo[$row['user_id']][$row['year']] = $row;
}

$eligibility = KHK::getKHKEligibility($userIds)[0];
//echo "<pre>"; print_r($eligibility); print_r($chidonInfo); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Comprehensive Registration Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 6px;
                font-size: 12px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Comprehensive Registration Report</h1>
    <table>
        <tr>
            <th>Serial Number</th>
            <th>Full Name</th>
            <th>School</th>
            <th>Class</th>
            <?php
            for ($i = 4; $i >= 0; $i--) {
                echo "<th>Registered for " . ($year - $i) . "</th>";
            }
            ?>
            <th>KHK Eligible</th>
            <th>KHK Registered</th>
            <th>Parent Email</th>
            <th>Parent Phone Number</th>
        </tr>
        <?php
        foreach ($users as $user) {
            $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
            echo "<tr><td>" . $user['user_serial'] . "</td><td>" . ($user['first'] . ' ' . $user['last']) . "</td><td>" .
                $schools[$user['school_id']] . "</td><td>" . $grade . "</td>";
            // registration
            for ($i = 4; $i >= 0; $i--) {
                echo "<td>";
                if (isset($chidonInfo[$user['user_id']][$year - $i]) && $chidonInfo[$user['user_id']][$year - $i]['date_paid'] > 0) echo "yes";
                else echo "no";
                echo "</td>";
            }
            // khk
            echo "<td>";
            if ($eligibility[$user['user_id']]) echo "yes";
            else echo "no";
            echo "</td><td>";
            if (isset($chidonInfo[$user['user_id']][$year]) && intval($chidonInfo[$user['user_id']][$year]['khk_reg'])) echo "yes";
            else echo "no";
            echo "</td>";
            // parent info
            echo "<td>" . $user['admin_email'] . "</td>";
            $phone = $user['admin_phone_mobile'] ? $user['admin_phone_mobile'] . "<br />" : '';
            $phone .= $user['admin_phone_home'] ? $user['admin_phone_home'] . "<br />" : '';
            $phone .= $user['admin_phone_work'] ? $user['admin_phone_work'] . "<br />" : '';
            echo "<td>" . $phone . "</td></tr>";
        }
        ?>
        </table>
    </body>
</html>