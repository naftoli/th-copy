<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$superAdmin = $admin_user['auth'] == 'super';

$sql = "SELECT 
            *
        FROM
            th_chidon tc
                JOIN
            users u USING (user_id)
                JOIN
            schools s ON s.school_id = u.school_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            tc.year = :year AND tc.ultimate_trip = 0 
                AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
        ORDER BY u.school_id , class_grade , class_sub , last , first";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);
$info = $stmt->fetchAll();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$user_ids = array_map(function($user) {
    return $user['user_id'];
}, $info);
// get khk eligibility
$eligible = KHK::getKHKEligibility($user_ids)[0];
echo "<pre>"; print_r($eligible); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta charset="UTF-8">
    <title>Ultimate Trip Eligible</title>
    <style>
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
            font-family: Arial, sans-serif;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .data-table thead tr {
            background-color: #009879;
            color: #ffffff;
            text-align: left;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dddddd;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .data-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .data-table tbody tr:last-of-type {
            border-bottom: 2px solid #009879;
        }

        .data-table tbody tr:hover {
            background-color: #f5f5f5;
            cursor: default;
        }
    </style>
</head>
<body>
    <h1>Eligible for Ultimate Trip but not going</h1>
    <table class="data-table">
        <tr>
            <th>School</th>
            <th>Grade</th>
            <th>User ID</th>
            <th>User Serial</th>
            <th>First Name</th>
            <th>Last Name</th>
        </tr>
        <?php
        foreach ($info as $row) {
            if (! isset($eligible[$row['user_id']])) continue;
            echo '<tr>';
            echo '<td>' . $schools[$row['school_id']] . '</td>';
            echo '<td>' . $row['class_grade'] . '</td>';
            echo '<td>' . $row['user_id'] . '</td>';
            echo '<td>' . $row['user_serial'] . '</td>';
            echo '<td>' . $row['first'] . '</td>';
            echo '<td>' . $row['last'] . '</td>';
            echo '</tr>';
        }
        ?>
    </table>
</body>
</html>