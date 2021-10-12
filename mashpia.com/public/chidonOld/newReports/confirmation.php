<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$confirmed = [];
$not_confirmed = [];
$sql = "select *, a.first as pFirst, a.last as pLast   
        from admins a 
        join admin_auths aa using (admin_id) 
        join users u on aa.id = u.user_id 
        join th_chidon tc using (user_id) 
        join classes c on c.class_id = u.class_id 
        join schools s on s.school_id = u.school_id 
        where u.school_id in (
        " . implode(',', array_keys($schools)) . ") 
        and tc.year = $year
        group by u.user_id 
        order by u.school_id, class_grade, class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['chidon_confirmed_5782']) $confirmed[$row['school_id']][] = $row;
    else $not_confirmed[$row['school_id']][] = $row;
}

function convertBool($val) {
    return intval($val) == 1 ? 'yes' : 'no';
}

function generateTable($school_id, $name, $info) {
    global $year;
    if (isset($info[$school_id])) {
        echo "<h2>" . $name . "</h2>";
        foreach ($info[$school_id] as $row) {
            if ($row['chidon_confirmed_5782']) $caption = "Confirmed Students";
            else $caption = "Not Confirmed Students";
            break;
        }
        ?>
        <table>
            <caption><?= $caption ?></caption>
            <tr>
                <th>Serial Number</th>
                <th>Grade</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Chose Prizes</th>
                <th>Confirmed</th>
                <th>Parent Email</th>
                <?php if ($row['chidon_confirmed_5782']) : ?>
                    <th></th>
                <?php endif; ?>
            </tr>
            <?php
            foreach ($info[$school_id] as $row) {
                $sql = "select * from chidon_user_prizes 
                        where user_id = " . $row['user_id'] . " 
                        and year = " . $year;
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) $prizes = 'yes';
                else $prizes = 'no';
                $grade = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');

                echo "<tr id=" . $row['user_id'] . "><td>" . $row['user_serial'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" .
                    $row['last'] . "</td><td>" . $prizes . "</td><td class='confirm'>";
                if ($row['chidon_confirmed_5782']) echo 'yes';
                else echo 'no';
                echo "</td><td><a href='mailto:" . $row['admin_email'] . "'>" . $row['admin_email'] . "</a></td>";
                if ($row['chidon_confirmed_5782']) echo "<td><button class='unconfirm'>Unconfirm Child</button></td>";
//                echo "<td><button class='unconfirm'>Unconfirm Child</button></td>";
                echo "</tr>";
            }
            ?>
        </table>
        <?php
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <link href="../../../admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 5px;
            border: 1px solid darkcyan;
        }
    </style>
</head>
<body>
<?php
include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
echo "<h1>Confirmation Status</h1>";
foreach ($schools as $school_id => $school_name) {
    generateTable($school_id, $school_name, $confirmed);
    generateTable($school_id, $school_name, $not_confirmed);
}
?>
</body>
<script>
    $(function () {
        $(".unconfirm").click( function () {
            let user = $(this).parent().parent().attr('id')
            let that = this
            $.post('unconfirm.php', { user }, function(success) {
                if (!success) {
                    alert("error unconfirming.")
                } else {
                    $(that).parent().parent().find('.confirm').text('no')
                }
            })
        })
    })
</script>
</html>
