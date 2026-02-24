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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg: #f8fafc;
        --card: #ffffff;
        --border: #e2e8f0;
        --text: #1e293b;
        --text-muted: #64748b;
        --accent: #2563eb;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 24px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg);
        color: var(--text);
        line-height: 1.5;
    }

    .page-header {
        margin-bottom: 24px;
    }

    h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text);
    }

    .count-badge {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 400;
        margin-top: 4px;
    }

    .table-card {
        background: var(--card);
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .data-table thead {
        background: #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .data-table th {
        padding: 12px 14px;
        text-align: left;
        font-weight: 500;
        color: var(--text-muted);
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }

    .data-table td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Ultimate Trip Info</h1>
        <span class="count-badge"><?= count($info) ?> participants</span>
    </div>
    <div class="table-card">
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
    </div>
</body>
</html>