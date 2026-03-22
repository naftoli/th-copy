<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// Business Logic Functions
function getRegisteredStudents() {
    global $MASHPIA_DB, $year, $schools;
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            u.first,
            u.last,
            u.user_id,
            u.user_serial,
            u.first_he,
            u.last_he,
            u.hachayol AS hachayol_status,
            IF(htg.user_id IS NOT NULL, 1, 0) AS hachayol,
            c.*,
            aa.admin_id
        FROM
            users u
                JOIN
            admin_auths aa ON aa.id = u.user_id
                JOIN
            classes c USING (class_id)
                JOIN
            user_registration ur ON ur.user_id = u.user_id
                LEFT JOIN
            hachayols_to_give htg ON htg.user_id = u.user_id
        WHERE
            u.school_id = :id 
                AND u.user_registered > 0
                AND ur.year = :year 
        ORDER BY class_grade , class_sub , hachayol DESC , last , first
    ");

    $users = [];
    foreach ($schools as $id => $name) {
        $stmt->execute(['id' => $id, 'year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $users[$id][$row['class_grade']][$row['class_sub']][] = $row;
        }
    }
    return $users;
}

function getHachayolInfo($user) {
    global $MASHPIA_DB, $year;
    $stmt = $MASHPIA_DB->prepare("
        SELECT u.*, s.school_name 
        FROM users u 
        JOIN schools s USING (school_id) 
        JOIN admin_auths aa ON aa.id = u.user_id 
        JOIN user_registration ur ON ur.user_id = u.user_id 
        JOIN hachayols_to_give htg ON htg.user_id = u.user_id AND htg.year = ur.year 
        WHERE u.user_registered > 0 
        AND ur.year = :year
        AND aa.admin_id = :admin_id 
    ");

    $hachayols = [];
    $stmt->execute([
        'admin_id' => $user['admin_id'],
        'year' => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $hachayols[$row['user_id']] = $row['first'] . ' ' . $row['last'] . ' (' . $row['school_name'] . ')';
    }
    return $hachayols;
}

function getGradeData($students) {
    $total = 0;
    $rows = [];
    foreach ($students as $user) {
        if (intval($user['hachayol'])) {
            $total++;
        }
        $rows[] = [
            'grade' => $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : ''),
            'hebrew_name' => $user['first_he'] . ' ' . $user['last_he'],
            'name' => $user['first'] . ' ' . $user['last'],
            'family_id' => $user['admin_id'],
            'hachayol' => intval($user['hachayol']) ? 'yes' : 'no',
            'children' => getHachayolInfo($user)
        ];
    }
    return ['total' => $total, 'rows' => $rows];
}

// Get data
$year = GlobalSettings::getCurrentYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$users = getRegisteredStudents();

// Prepare data for display
$report_data = [];
foreach ($users as $school_id => $grades) {
    foreach ($grades as $grade => $subs) {
        foreach ($subs as $sub => $students) {
            $report_data[] = [
                'school_name' => $schools[$school_id],
                'grade' => $grade,
                'sub' => $sub,
                'data' => getGradeData($students)
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hachayol Report</title>
    <style>
        #main {
            margin-left: 2%;
            margin-right: 2%;
        }

        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            padding: 5px;
            border-bottom: 1px solid lightgrey;
        }
    </style>
</head>
<body>
    <div id='main'>
        <?php foreach ($report_data as $section): ?>
            <h3><?php echo $section['school_name']; ?> (<?php echo $section['grade'] . ($section['sub'] ? '-' . $section['sub'] : ''); ?>)</h3>
            <hr />
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Hebrew Name</th>
                    <th>Student</th>
                    <th>Family ID</th>
                    <th>Receives Hachayol</th>
                    <th>Who gets Hachayol in Family</th>
                </tr>
                <?php foreach ($section['data']['rows'] as $row): ?>
                    <tr>
                        <td><?php echo $row['grade']; ?></td>
                        <td><?php echo $row['hebrew_name']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['family_id']; ?></td>
                        <td><?php echo $row['hachayol']; ?></td>
                        <td><?php echo implode("<br />", $row['children']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th>Total:</th>
                    <th><?php echo $section['data']['total']; ?></th>
                    <th colspan='4'></th>
                </tr>
            </table>
            <div style='page-break-after: always;'></div>
        <?php endforeach; ?>
    </div>
</body>
</html>