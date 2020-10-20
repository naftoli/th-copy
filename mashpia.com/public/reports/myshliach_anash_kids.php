<?php
ini_set('max_execution_time', 600);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$qry = "
    SELECT 
        a.*, a.first as parent_first, a.last as parent_last, u.first, u.last, u.user_serial, u.user_id, u.dob, u.school_id,  
           s.school_name, c.class_grade, c.class_sub
    FROM
        users u
            LEFT JOIN
        schools s USING (school_id)
            LEFT JOIN
        classes c USING (class_id)
            LEFT JOIN
        admin_auths aa ON aa.id = u.user_id
            LEFT JOIN
        admins a USING (admin_id)
    WHERE
        u.school_id IN (61 , 269)
    ORDER BY u.last , u.first
";
if (isset($_GET['all'])) {
    $qry = "
        SELECT 
            a.*, a.first as parent_first, a.last as parent_last, u.first, u.last, u.user_serial, u.user_id, u.dob, u.school_id,  
               s.school_name, c.class_grade, c.class_sub
        FROM
            users u
                LEFT JOIN
            schools s USING (school_id)
                LEFT JOIN
            classes c USING (class_id)
                LEFT JOIN
            admin_auths aa ON aa.id = u.user_id
                LEFT JOIN
            admins a USING (admin_id)
        ORDER BY u.last , u.first
    ";
}
$stmt = $MASHPIA_DB->query($qry);
$users = $stmt->fetchAll();

$stmtMissions = $MASHPIA_DB->prepare("
    select count(*) as total from date_tasks_mission_marks where user_id = :user
");
$stmtMedals = $MASHPIA_DB->prepare("
    select count(*) as total from medal_marks where user_id = :user
");
$stmtRank = $MASHPIA_DB->prepare("
    select rank_name from ranks 
    where rank_ord = (
        select max(rank_ord) from rank_marks 
        where user_id = :user
    )
");

$stmtCTH = $MASHPIA_DB->prepare("
    select year from registration_charges where type = 'chayolei' and user_id = :user
");
$stmtChidon = $MASHPIA_DB->prepare("
    select year from registration_charges where type = 'chidon' and user_id = :user
");

$fields = [
    'Base',
    'Parent ID',
    'Serial Number',
    'Grade',
    'First Name',
    'Last Name',
    'DOB',
    'Parent Name',
    'Address',
    'Father\'s Name',
    'Mother\'s Name',
    'Father\'s Cell',
    'Mother\'s Cell',
    'Email Address',
    'Miles',
    'Missions',
    'Medals',
    'Rank',
    'CTH Years Registered',
    'Chidon Years Registered',
    'Delete Account'
];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=""utf8" />
        <title>MyShliach / Anash Kinder Students</title>
        <style>
            table {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
            }
            tr, th, td {
                padding: 5px;
                border: 1px solid #848383;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <?php foreach ($fields as $field) echo "<th>" . $field . "</th>"; ?>
            </tr>
            <?php
            foreach ($users as $user) {
                $user_id = $user['user_id'];

                // get miles
                $p = new Points($user_id);
                $miles = $p->getTotalPoints();
                // get missions
                $stmtMissions->execute([':user' => $user_id]);
                $missions = $stmtMissions->fetch()['total'];
                // get medals
                $stmtMedals->execute([':user' => $user_id]);
                $medals = $stmtMedals->fetch()['total'];
                // get rank
                $stmtRank->execute([':user' => $user_id]);
                $rank = $stmtRank->fetch()['rank_name'];

                // cth reg years
                $cthYears = [];
                $stmtCTH->execute([':user' => $user_id]);
                $rows = $stmtCTH->fetchAll();
                foreach ($rows as $row) {
                    $cthYears[] = $row['year'];
                }

                // chidon reg years
                $chidonYears = [];
                $stmtChidon->execute([':user' => $user_id]);
                $rows = $stmtChidon->fetchAll();
                foreach ($rows as $row) {
                    $chidonYears[] = $row['year'];
                }

                if ($user['school_id'] == 61) $base = "MyShliach";
                else if ($user['school_id'] == 269) $base = "Anash Kinder";
                $grade = $user['class_grade'] ? $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '') : '';
                $address = $user['admin_address1'] . "<br />" . $user['admin_city'] . ", " . $user['admin_state'] . " " .
                    $user['admin_postal'] . "<br />" . $user['admin_country'];
                echo "<tr><td>" . $base . "</td><td>" . $user['admin_id'] . "</td><td>" . $user['user_serial'] . "</td><td>" .
                    $grade . "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . $user['dob'] . "</td><td>" .
                    $user['parent_first'] . ' ' . $user['parent_last'] . "</td><td>" . $address . "</td><td>" .
                    $user['father'] . "</td><td>" . $user['mother'] . "</td><td>" . $user['admin_phone_mobile'] . "</td><td>" .
                    $user['admin_phone_mobile2'] . "</td><td>" . $user['admin_email'] . "</td><td>" . $miles . "</td><td>" .
                    $missions . "</td><td>" . $medals . "</td><td>" . $rank . "</td><td>" . implode(',', $cthYears) . "</td><td>" .
                    implode(',', $chidonYears) . "</td><td></td></tr>";
            }
            ?>
        </table>
    </body>
</html>
