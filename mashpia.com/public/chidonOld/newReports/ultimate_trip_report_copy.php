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
                AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
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
        position: sticky;
        top: 0;
    }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dddddd;
        white-space: nowrap;
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

    /* Container for table with horizontal scroll */
    .table-container {
        max-width: 100%;
        overflow-x: auto;
        margin: 20px 0;
        padding: 0 10px;
    }

    /* Additional styles for better readability */
    body {
        margin: 0;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    h1 {
        color: #009879;
        margin-bottom: 20px;
    }
    </style>
</head>
<body>
    <h1>Ultimate Trip Info</h1>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>School</th>
                    <th>Grade/Class</th>
                    <th>Student</th>
                    <th>Serial Number</th>
                    <th>Gender</th>
                    <th>Sandwich</th>
                    <th>Height</th>
                    <th>Weight</th>
                    <th>Ski/Snowboard</th>
                    <th>Skill Level</th>
                    <th>Outerwear</th>
                    <th>Shoe Size</th>
                    <th>Allergies</th>
                    <th>Trip Option</th>
                    <th>In Walking Zone</th>
                    <th>Host</th>
                    <th>Host Phone Number</th>
                    <th>Street Number</th>
                    <th>Street Number Suffix</th>
                    <th>Street Name</th>
                    <th>Apt. #</th>
                    <th>Host Cross Street 1</th>
                    <th>Host Cross Street 2</th>
                    <th>Thursday Walking</th>
                    <th>Motzei Shabbos Walking</th>
                    <th>Zone ID</th>
                    <th>Comments</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($info as $row) {
                    $chidon_id = $row['th_chidon_id'];
                    $school = $row['school_name'];
                    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                    $student = $row['first'] . ' ' . $row['last'];
                    $serial = $row['user_serial'];
                    $gender = strtolower($row['gender']) == 'm' ? 'boys' : 'girls';
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
                    $zone = $row['walking_zone'];
                    $cross1 = $row['between_streets1'];
                    $cross2 = $row['between_streets2'];
                    $poll = $row['poll'];
                    $thurs_walking = $row['thurs_walking'];
                    $ms_walking = $row['ms_walking'];
                    $height = $row['height'];
                    $weight = $row['weight'];
                    $ski = $row['ski'];
                    $skill = $row['skill'];
                    $outerwear = $row['outerwear'];
                    $trip_option = $row['trip_option'];

                    switch ($thurs_walking) {
                        case 0:
                            $thurs = 'child walking alone';
                            break;
                        case 1:
                            $thurs = 'parent picking up';
                            break;
                        case 2:
                            $thurs = 'NEEDS TO BE DROPPED OFF';
                            break;
                    }

                    switch ($ms_walking) {
                        case 0:
                            $ms = 'child walking alone';
                            break;
                        case 1:
                            $ms = 'parent picking up';
                            break;
                        case 2:
                            $ms = 'NEEDS TO BE DROPPED OFF';
                            break;
                    }

                    echo "<tr class='' id='" . $chidon_id . "'><td>" . $school . "</td><td>" . $grade . "</td><td>" . $student . "
                        </td><td>" . $serial . "</td><td>" . $gender . "</td><td>" . $sandwich . "</td><td>" . $height . 
                            "</td><td>" . $weight . "</td><td>" . $ski . "</td><td>" . $skill . "</td><td>" . $outerwear . "</td><td>" . $shoe . 
                            "</td><td>" . $allergies . "</td><td>" . ($trip_option > 0 ? 'Option ' . $trip_option : '') . "</td><td>" . $in_zone . 
                            "</td><td>" . $host . "</td><td>" . $host_phone . "</td><td>" . $street_num . "</td><td>" . $suffix . 
                            "</td><td>" . $street . "</td><td>" . $apt . "</td><td>" . $cross1 . "</td><td>" . $cross2 . "</td><td>" . $thurs . 
                            "</td><td>" . $ms . "</td><td>" . $zone . "</td><td>" . $poll . "</td><td></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>