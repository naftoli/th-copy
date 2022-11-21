<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonRegYear();
$req_yr = isset($_REQUEST['year']) ? $_REQUEST['year'] : $year;

$ct = new ChidonTests();

$info = [];
$sql = "
    SELECT 
        u.user_id,
        user_serial,
        gender,
        u.first, 
        u.last, 
        s.school_id,
        s.school_name, 
        c.class_id, 
        c.class_grade, 
        c.class_sub, 
        a.admin_id 
    FROM
        users u
            JOIN
        th_chidon tc USING (user_id)
            JOIN
        schools s ON u.school_id = s.school_id 
            JOIN 
        classes c on c.class_id = u.class_id 
            LEFT JOIN
        admin_auths aa ON aa.id = tc.user_id
            LEFT JOIN
        admins a USING (admin_id)
    WHERE
        tc.year = $req_yr AND u.school_id in (" . implode(',', array_keys($schools)) . ") 
    ORDER BY school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$prizes = [];
$sql = "select u.user_id, p.prize_name, p.size, p.color, p.price, u.he_name 
        from chidon_prizes p 
        join chidon_user_prizes u using (prize_id) 
        where u.year = $req_yr  
        and u.he_name != '' 
        order by u.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['user_id']][] = $row;
}

$types = [
    'maven' => 'Yesod',
    'pro'   => 'Yediah',
    'expert'=> 'Havonah',
    'genius'=> 'Iyun'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Bracelet Report</title>
    <style>
      tr, th, td {
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        font-size: 12px;
        padding: 5px;
        border-bottom: 1px solid grey;
      }
    </style>
</head>
<body>
<h1>Chidon Bracelet Report <?= $req_yr ?></h1>
<div>
    Choose Year:
    <select name="year" id="year">
        <?php
        $cur_yr = $year;
        for ($i = 0; $i < 5; $i++) {
            echo "<option value='" . $cur_yr . "'";
            if ($req_yr && $req_yr == $cur_yr) echo " selected ";
            echo ">" . $cur_yr . "</option>";
            $cur_yr--;
        }
        ?>
    </select>
</div>
<br />
<table>
    <tr>
        <th>Serial Number</th>
        <th>Highest Track Passed</th>
        <th>Highest Track Passed Mark</th>
        <th>School</th>
        <th>Class</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Prizes</th>
        <th>Personalized Prize Name</th>
        <th>Gender</th>
        <th>Admin ID</th>
    </tr>
    <?php
    foreach ($info as $row) {
        $serial = $row['user_serial'];
        $trackInfo = $ct->getHighestTrackPassed($row);
        $school = $row['school_name'];
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);

        echo "<tr><td>" . $serial . "</td><td>" . $trackInfo['highest_track'] . "</td><td>" . $trackInfo['highest_track_avg'] .
            "</td><td>" . $school . "</td><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td>";
        if (isset($prizes[$row['user_id']])) {
            echo "<td>";
            foreach ($prizes[$row['user_id']] as $i => $prize) {
                echo $prize['prize_name'];
                if ($prize['size']) echo " Size: " . $prize['size'];
                if ($prize['color']) echo " Color: " . $prize['color'];
                if ($i < count($prizes[$row['user_id']]) - 1) echo "<hr />";
            }
            echo "</td><td>";
            foreach ($prizes[$row['user_id']] as $i => $prize) {
                echo $prize['he_name'];
                if ($i < count($prizes[$row['user_id']]) - 1) echo "<hr />";
            }
            echo "</td>";
        } else {
          echo "<td colspan=2></td>";
        }
        echo "<td>";
        if (in_array($row['school_id'], [61, 269])) echo $row['admin_id'];
        echo "</td></tr>";
    }
    ?>
</table>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<script>
  $("#year").change( function () {
    let yr = $(this).val()
    location.href = "reg_report.php?year=" + yr
  })
</script>
</html>
