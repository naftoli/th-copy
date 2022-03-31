<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$sweaters =[];
$sql = "select tc.size, tc.sweater_shipped, tc.sweater_replaced, u.user_id, u.user_serial, u.school_id, u.first, u.last, 
            c.class_grade, c.class_sub 
        from users u 
        join th_chidon tc using (user_id) 
        join classes c on c.class_id = u.class_id 
        where tc.date_paid > 0 
        and tc.year = " . $year . " 
        and u.school_id in (" . implode(',', array_keys($schools)) . ") 
        and tc.sweater_shipped = 0 
        order by u.school_id, tc.size";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sweaters[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sweater Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Sweater Report</h1>
<table>
    <tr>
        <th>School</th>
        <th>Serial Number</th>
        <th>Grade</th>
        <th>Name</th>
        <th>Sweater Size</th>
        <th>Replacement Sent</th>
    </tr>
    <?php
    foreach ($sweaters as $school_id => $details) {
        foreach ($details as $sweater) {
            $user_id = $sweater['user_id'];
            $grade = $sweater['class_grade'] . (empty($sweater['class_sub']) ? '' : '-' . $sweater['class_sub']);
            $name = $sweater['first'] . ' ' . $sweater['last'];
            $checked = intval($sweater['sweater_replaced']) ? 'checked' : '';
            echo "<tr id='$user_id'><td>" . $schools[$school_id] . "</td><td>" . $sweater['user_serial'] . "</td><td>" .
                $grade . "</td><td>" . $name . "</td><td>" . $sweater['size'] . "</td><td>";
            echo "<input type='checkbox' class='sent' $checked /></td></tr>";
        }
    }
    ?>
</table>
</body>
<script>
    $(".sent").click( function() {
        const checked = $(this).is(':checked') ? 1 : 0;
        const user = $(this).parent().parent().attr('id')
        const field = 'sweater_replaced'
        const input = this
        $.post('../ajax/updateShipped.php', { user, checked, field }, function(success) {
            if (! parseInt(success)) {
                alert('Error saving info.')
                $(input).attr('checked', !checked)
            } else {
                alert('Saved.')
            }
        })
    })
</script>
</html>