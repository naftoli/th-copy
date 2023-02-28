<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

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
            tc.year = :year AND tc.ultimate_trip = 1 
        ORDER BY u.school_id , class_grade , class_sub , last , first";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);
$info = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta charset="UTF-8">
    <title>Ultimate Trip Info</title>
    <style>
        tr, th, td {
          font-size: 14px;
          padding: 10px;
          border-bottom: grey;
          font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    <h1>Ultimate Trip Info</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Grade/Class</th>
            <th>Student</th>
            <th>Serial Number</th>
            <th>Shoe Size</th>
            <th>Sandwich</th>
            <th>Allergies</th>
            <th>In Walking Zone</th>
            <th>Host</th>
            <th>Host Phone Number</th>
            <th>Host Address</th>
            <th>Host Cross Street 1</th>
            <th>Host Cross Street 2</th>
            <th>Permission to walk alone/th>
            <th>Zone ID</th>
            <th>Poll</th>
        </tr>
        <?php
        foreach ($info as $row) {
            $school = $row['school_name'];
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $student = $row['first'] . ' ' . $row['last'];
            $serial = $row['user_serial'];
            $shoe = $row['shoe_size'];
            $sandwich = $row['sandwich'];
            $allergies = $row['allergies'];
            $in_zone = $row['in_zone'];
            $host = $row['host'];
            $host_phone = $row['host_number'];
            $street_num = $row['host_street_num'];
            $suffix = $row['host_street_num_suffix'];
            $street = $row['host_street'];
            $apt = $row['host_street_apt'];
            $address = $street_num . ' ' . $suffix . ' ' . $street . ' ' . $apt;
            $zone = $row['walking_zone'];
            $cross1 = $row['between_streets1'];
            $cross2 = $row['between_streets2'];
            $permission = $row['walking'];
            $poll = $row['poll'];

            echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $student . "</td><td>" . $serial . "</td><td>";
            echo $shoe . "</td><td>" . $sandwich . "</td><td>" . $allergies . "</td><td>" . ($in_zone ? 'yes' : 'no') .
                "</td><td>" . $host . "</td><td>" . $host_phone . "</td><td>" . $address . "</td><td>" . $cross1 . "</td><td>";
            echo $cross2 . "</td><td>" . ($permission ? 'yes' : 'no') . "</td><td>" . $zone . "</td><td>" . $poll . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>