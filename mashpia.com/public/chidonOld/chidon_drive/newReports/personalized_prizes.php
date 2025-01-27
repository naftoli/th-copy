<?php
//ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$info = [];
$sql = "SELECT user_id, th_chidon_id, prize, school_name, last, first, he_name, class_grade, class_sub
    from (
        SELECT user_id, th_chidon_id, CONCAT(prize_name, ' - ', color) as prize, s.school_name, u.last, u.first, cup.he_name, class_grade, class_sub
        from chidon_user_prizes cup 
        join users u using (user_id) 
        join th_chidon tc using (user_id) 
        join chidon_prizes cp using (prize_id)
        join schools s on (u.school_id = s.school_id)  
        join classes c using (class_id) 
        join admin_auths aa ON (u.user_id = aa.id AND aa.auth = 'user')
        join admins a using (admin_id)
        where s.school_id in (" . implode(',', array_keys($schools)) . ") 
        and prize_id in (
            select prize_id from chidon_prizes where year = $year and personalization != '')
        and tc.year = $year
    UNION
        SELECT user_id, th_chidon_id, CONCAT('Yarmulka - ', yarmulka), s.school_name, u.last, u.first, null as he_name, class_grade, class_sub
        FROM users u
        join th_chidon tc using (user_id) 
        join schools s on (u.school_id = s.school_id)  
        join classes c using (class_id) 
        join admin_auths aa ON (u.user_id = aa.id AND aa.auth = 'user')
        join admins a using (admin_id)
        where s.school_id in (" . implode(',', array_keys($schools)) . ") 
        and tc.year = $year
        and yarmulka > 0
    ) as pay
    order by school_name, class_grade, class_sub, last, first, th_chidon_id, prize";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_name']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";
// get all cases where personalized prizes were paid for
$rowsPaid = [];
$sqlPaid = "SELECT user_id, date, amount FROM registration_charges WHERE year = $year and type in ('RRYSD', 'RRYDA', 'RRHVN')";
$resPaid = mysql_query($sqlPaid);
while ($rowPaid = mysql_fetch_assoc($resPaid)) {
    $rowsPaid[$rowPaid['user_id']][] = $rowPaid;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Personalized Prizes Report</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            padding: 8px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
<?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
<h1>Personalized Prizes Report</h1>
<?php
$prize_totals = [];
foreach ($info as $school_name => $user_prizes) {
    echo "<h2>" . $school_name . "</h2>";
    ?>
    <table>
        <tr>
            <th>Chidon ID</th>
            <th>Prize</th>
            <th>School</th>
            <th>Grade</th>
            <th>Name</th>
            <th>Personalized name</th>
            <th>Date Paid</th>
            <th>Amount Paid</th>
        </tr>
    <?php
    $j = 0; // index into personalized prizes per child
    $current_user = 0;
    foreach ($user_prizes as $prize) {
        if ($current_user != $prize['user_id']) {
            $current_user = $prize['user_id'];
            $j = 0;
        }
        if (isset($prize_totals[$prize['prize_name']])) $prize_totals[$prize['prize_name']]++;
        else $prize_totals[$prize['prize_name']] = 1;
        $grade = $prize['class_grade'] . (empty($prize['class_sub']) ? '' : '-' . $prize['class_sub']);
        ?>
            <tr>
                <td> <?= $prize['th_chidon_id'] ?> </td>
                <td> <?= $prize['prize'] ?> </td>
                <td> <?= $prize['school_name'] ?> </td>
                <td> <?= $grade ?> </td>
                <td> <?= $prize['first'] ?> <?= $prize['last'] ?> </td>
                <td> <?= $prize['he_name'] ?> </td>
                <td> <?= isset($rowsPaid[$prize['user_id']]) ? $rowsPaid[$prize['user_id']][$j]['date'] : ''?> </td>
                <td> <?= isset($rowsPaid[$prize['user_id']]) ? $rowsPaid[$prize['user_id']][$j++]['amount'] : ''?> </td>
            </tr>
        <?
    }
    echo "</table>";
}

echo "<h2>Prize Totals</h2>";
echo "<table><tr><th>Prize</th><th>Total</th></tr>";
foreach ($prize_totals as $prize => $total) {
    echo "<tr><td>" . $prize . "</td><td>" . $total . "</td></tr>";
}
echo "</table>";
?>
</body>
</html>