<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);


$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$users_sql = "SELECT chidon.*, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last,
        c_5777.date_paid as enrolled_5777,
        c_5778.date_paid as enrolled_5778,
        c_5779.date_paid as enrolled_5779,
        c_5780.date_paid as enrolled_5780,
        chidon.date_paid as enrolled_5781, chidon.th_chidon_id as chidon_id
    from users u
    left join th_chidon c_5777 on (c_5777.user_id = u.user_id and c_5777.year = 5777)
    left join th_chidon c_5778 on (c_5778.user_id = u.user_id and c_5778.year = 5778)
    left join th_chidon c_5779 on (c_5779.user_id = u.user_id and c_5779.year = 5779)
    left join th_chidon c_5780 on (c_5780.user_id = u.user_id and c_5780.year = 5780)
    left join th_chidon chidon on (chidon.user_id = u.user_id and chidon.year = 5781)
    join classes c on c.class_id = u.class_id 
    join schools s on s.school_id = u.school_id
    where chidon.th_chidon_id is not null
    and (chidon.shabbaton_maven = 1 or chidon.shabbaton_pro = 1 or chidon.shabbaton_expert = 1 or chidon.shabbaton_trophy = 1)
    ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first";
$users_query = mysql_query($users_sql);
$users = [];


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Registration history</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Chidon Registration history</h1>
    <table>
        <tr>
            <th>Chidon ID</th>
            <th>School</th>
            <th>Class</th>
            <th>Name</th>
            <th>Total Eligible&nbsp;/ Enrolled</th>
            <th>Eligible</th>
            <th>Enrolled</th>
            <th>Enrolled 5780</th>
            <th>Enrolled 5779</th>
            <th>Enrolled 5778</th>
            <th>Enrolled 5777</th>
        </tr>
        <? while($user = mysql_fetch_assoc($users_query)) {
            $enrolled = $user['shabbaton_maven'] || $user['shabbaton_pro'] || $user['shabbaton_expert'] || $user['shabbaton_trophy'];
            $total = count(array_filter([$enrolled || $user['enrolled_5781'], $user['enrolled_5780'], $user['enrolled_5779'], $user['enrolled_5778'], $user['enrolled_5777']]));
        ?>
            <tr>
                <td> <?= $user['chidon_id'] ?> </td>
                <td> <?= $user['school_name'] ?> </td>
                <td> <?= $user['class_grade'] . ($user['class_sub'] ? ' - '.$user['class_sub'] : '') ?> </td>
                <td> <?= $user['first'] ?> - <?= $user['last'] ?> </td>
                <td> <?= $total ?? "0" ?> </td>
                <td> <?= $enrolled ? "1" : "0" ?> </td>
                <td> <?= $user['enrolled_5781'] ? "1" : "0" ?> </td>
                <td> <?= $user['enrolled_5780'] ? "1" : "0" ?> </td>
                <td> <?= $user['enrolled_5779'] ? "1" : "0" ?> </td>
                <td> <?= $user['enrolled_5778'] ? "1" : "0" ?> </td>
                <td> <?= $user['enrolled_5777'] ? "1" : "0" ?> </td>
            </tr>
        <? } ?>
    </table>

</body>
</html>
