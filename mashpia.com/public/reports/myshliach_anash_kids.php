<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt = $MASHPIA_DB->query("
    SELECT 
        u.first, u.last, u.user_serial, u.user_id, u.dob, a.*, a.first as parent_first, a.last as parent_last, s.school_name, c.class_grade, c.class_sub
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
");
$users = $stmt->fetchAll();
$fields = [
    'Base',
    'Parent ID',
    'Serial Number',
    'Grade',
    'First Name',
    'Last Name',
    'DOB',
    'Admin Name',
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
    'CTH Registered 5779',
    'CTH Registered 5780',
    'CTH Registered 5781',
    'Chidon Registered 5779',
    'Chidon Registered 5780',
    'Chidon Registered 5781',
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
                border: 1px solid black;
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
                if ($user['school_id'] == 61) $base = "MyShliach";
                else if ($user['school_id'] == 269) $base = "Anash Kinder";
                $grade = $user['class_grade'] ? $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '') : '';
                $address = $user['admin_address1'] . "<br />" . $user['admin_city'] . ", " . $user['admin_state'] . " " .
                    $user['admin_postal'] . "<br />" . $user['admin_country'];
                echo "<tr><td>" . $base . "</td><td>" . $user['admin_id'] . "</td><td>" . $user['user_serial'] . "</td><td>" .
                    $grade . "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . $user['dob'] . "</td><td>" .
                    $user['parent_first'] . ' ' . $user['parent_last'] . "</td><td>" . $address . "</td><td>" .
                    $user['father'] . "</td><td>" . $user['mother'] . "</td><td>" . $user['admin_phone_mobile'] . "</td><td>" .
                    $user['admin_phone_mobile2'] . "</td><td>" . $user['admin_email'] . "</td><td colspan='11'></td></tr>";
            }
            ?>
        </table>
    </body>
</html>
