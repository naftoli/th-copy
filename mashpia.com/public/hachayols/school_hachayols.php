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
        SELECT u.*, c.*, aa.admin_id 
        FROM users u 
        JOIN admin_auths aa ON aa.id = u.user_id 
        JOIN classes c USING (class_id) 
        JOIN user_registration ur ON ur.user_id = u.user_id 
        WHERE u.school_id = :id 
        AND u.user_registered > 0 
        AND ur.year = :year 
        ORDER BY class_grade, class_sub, hachayol DESC, last, first
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
        WHERE u.hachayol = 1 
        AND u.user_registered > 0 
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
$year = GlobalSettings::getRegistrationYear();
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
    <!--      <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>-->
    <!--      <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>-->
    <!--      <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>-->
    <!--      <link-->
    <!--        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"-->
    <!--        rel="stylesheet"-->
    <!--        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"-->
    <!--        crossorigin="anonymous" />-->
    <!--      <script-->
    <!--        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"-->
    <!--        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"-->
    <!--        crossorigin="anonymous"></script>-->
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
<!--    <script type="text/babel">-->
<!--      const { useState, useEffect } = React-->
<!--      const { createRoot } = ReactDOM-->
<!---->
<!--      const Table = () => {-->
<!--        const schools = --><?php //= json_encode($schools) ?><!--//;-->
<!--        const users = --><?php ////= json_encode($users) ?><!--//;-->
<!--        const hachayols = --><?php ////= json_encode($hachayols) ?><!--//;-->
<!---->
<!--        return (-->
<!--          {Object.keys(users).map(school_id => (-->
<!--            Object.keys(users[school_id]).map(grade => (-->
<!--              Object.keys(users[school_id][grade]).map(sub => (-->
<!--                <div style={{ pageBreakAfter: 'always' }}>-->
<!--                  <h3 className="mt-4">{schools[school_id]}</h3><hr />-->
<!--                  <table className="table table-striped">-->
<!--                    <thead>-->
<!--                      <tr>-->
<!--                        <th>Grade</th>-->
<!--                        <th>Hebrew Name</th>-->
<!--                        <th>Student</th>-->
<!--                        <th>Family ID</th>-->
<!--                        <th>Receives Hachayol</th>-->
<!--                        <th>Who gets Hachayol in Family</th>-->
<!--                      </tr>-->
<!--                    </thead>-->
<!--                    <tbody>-->
<!--                    {users[school_id][grade][sub].map(user) (-->
<!--                      <tr>-->
<!--                        <td>-->
<!--                          {grade}{sub ? '-' + sub : ''}-->
<!--                        </td>-->
<!--                        <td>-->
<!--                          {user.first_he} {user.last_he}-->
<!--                        </td>-->
<!--                        <td>-->
<!--                          {user.first} {user.last}-->
<!--                        </td>-->
<!--                        <td>-->
<!--                          {user.admin_id}-->
<!--                        </td>-->
<!--                        <td>-->
<!--                          {user.hachayol ? 'yes' : 'no'}-->
<!--                        </td>-->
<!--                        <td>-->
<!--                          {user.hachayol ? '' : hachayols[user.user_id].join('<br />')}-->
<!--                        </td>-->
<!--                      </tr>-->
<!--                    )}-->
<!--                    </tbody>-->
<!--                  </table>-->
<!--                </div>-->
<!--              ))-->
<!--            ))-->
<!--          ))}-->
<!--        )-->
<!--     }-->
<!---->
<!--      const container = document.getElementById('main')-->
<!--      const root = createRoot(container)-->
<!--      root.render(<Table/>) // render the app-->
<!--    </script>-->
</html>
