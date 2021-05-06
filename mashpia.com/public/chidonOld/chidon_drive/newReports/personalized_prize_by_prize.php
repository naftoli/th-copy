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
$sql = "SELECT th_chidon_id, prize_name, color, s.school_name, u.first, u.last, cup.he_name, confirmed_chidon_5781, confirmed_prizes
        from chidon_user_prizes cup 
        join users u using (user_id) 
        join th_chidon tc using (user_id) 
        join chidon_prizes cp using (prize_id)
        join schools s on (u.school_id = s.school_id)  
        join classes c using (class_id) 
        join admin_auths aa ON (u.user_id = aa.id AND aa.auth = 'user')
        join admins a using (admin_id)
        where s.school_id in (" . implode(',', array_keys($schools)) . ") 
        and prize_id in (44, 45, 48, 50, 53, 54, 59, 60)
        and tc.year = $year
        order by prize_name, color, s.school_name, cup.he_name, user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['prize_name'] . " - " . $row['color']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";
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
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
<h1>Personalized Prizes Report</h1>
<?php
$prize_totals = [];
foreach ($info as $prize_name => $user_prizes) {
    echo "<h2>" . $prize_name . "</h2>";
    ?>
    <table>
        <tr>
            <th>Chidon ID</th>
            <th>Prize Name - color</th>
            <th>School</th>
            <th>Name</th>
            <th>Personalized name</th>
            <th>Confirmed before Pesach</th>
            <th>Confirmed Now</th>
        </tr>
    <?php
    foreach ($user_prizes as $prize) {
        if (isset($prize_totals[$prize['prize_name']])) $prize_totals[$prize['prize_name']]++;
        else $prize_totals[$prize['prize_name']] = 1;
        ?>
            <tr>
                <td> <?= $prize['th_chidon_id'] ?> </td>
                <td> <?= $prize['prize_name'] ?> - <?= $prize['color'] ?> </td>
                <td> <?= $prize['school_name'] ?> </td>
                <td> <?= $prize['first'] . ' ' . $prize['last'] ?> </td>
                <td> <?= $prize['he_name'] ?> </td>
                <td> <?= $prize['confirmed_chidon_5781'] ? "✅" : "❌" ?> </td>
                <td> <?= $prize['confirmed_prizes'] ? "✅" : "❌" ?> </td>
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