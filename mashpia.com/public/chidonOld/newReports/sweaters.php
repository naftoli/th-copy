<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonRegYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
$school_ids = array_keys($schools);
$school_ids_str = implode(',', $school_ids);

$sweaters =[];
$sql = "select tc.size, tc.sweater_shipped, tc.sweater_replaced, u.user_id, u.user_serial, u.school_id, u.first, u.last, 
            c.class_grade, c.class_sub, s.sweaters_confirmed_5782 as confirmed 
        from users u 
        join th_chidon tc using (user_id) 
        join classes c on c.class_id = u.class_id 
        join schools s on s.school_id = u.school_id 
        where tc.date_paid > 0 
        and tc.year = " . $year;
if ($admin_user['auth'] != 'super') $sql .= " and u.school_id in ($school_ids_str)";
$sql .= " order by u.school_id, c.class_grade, c.class_sub, u.last, u.first";
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
<div class="infobox">
    We have hired extra people to pack the sweaters correctly to make sure that schools are not missing anything, mistakes do happen.
    <br /><br />
    Please take inventory of all of your school's sweaters BEFORE you give them out to the kids to make sure that nothing is missing.
    If anything is missing, please uncheck it below and a new one will be shipped to your school. Once the school gives the sweaters
    out to the kids, Chidon HQ takes NO responsibility if anything is missing.
</div>
<br />
<?php if ($admin_user['auth'] != 'super') : ?>
<div class="infobox2" style="min-height: fit-content; padding: 20px;">
    <input type="checkbox" id="<?= $school_ids_str ?>" class="reviewed" name="reviewed" /> I have reviewed all of my sweaters and unchecked everything that is missing.
</div>
<?php endif; ?>
<table>
    <tr>
        <?php if ($admin_user['auth'] == 'super') : ?>
        <th>School</th>
        <?php endif; ?>
        <th>Serial Number</th>
        <th>Grade</th>
        <th>Name</th>
        <th>Sweater Size</th>
        <th>Shipped/Missing</th>
        <th></th>
    </tr>
    <?php
    foreach ($sweaters as $school_id => $details) {
        foreach ($details as $sweater) {
            if ($sweater['confirmed']) updateCheckbox(); // check checkbox if school is confirmed
            $user_id = $sweater['user_id'];
            $grade = $sweater['class_grade'] . (empty($sweater['class_sub']) ? '' : '-' . $sweater['class_sub']);
            $name = $sweater['first'] . ' ' . $sweater['last'];
            $checked = intval($sweater['sweater_shipped']) ? 'checked' : '';
            echo "<tr id='$user_id'>";
            if ($admin_user['auth'] == 'super') echo "<td>" . $schools[$school_id] . "</td>";
            echo "<td>" . $sweater['user_serial'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" .
                $sweater['size'] . "</td><td><input type='checkbox' class='sent' $checked /></td><td>";
            if (intval($sweater['sweater_replaced'])) echo "Replacement Sent";
            echo "</td></tr>";
        }
    }
    ?>
</table>
</body>
<script>
    $(".sent").click( function() {
        const checked = $(this).is(':checked') ? 1 : 0;
        const user = $(this).parent().parent().attr('id')
        const field = 'sweater_shipped'
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

    $(".reviewed").click( function () {
        const ids = $(this).attr('id')
        const checked = $(this).is(":checked") ? 1 : 0
        const input = this
        $.post('../ajax/updateSweaters.php', { ids, checked }, function (result) {
            const res = JSON.parse(result)
            if (res.success) {
                alert('Saved.')
            } else {
                console.log(res.error)
                alert('Error saving.')
                $(input).attr('checked', !checked)
            }
        })
    })

    function updateCheckbox() {
        $(".reviewed").attr('checked', true)
    }
</script>
</html>