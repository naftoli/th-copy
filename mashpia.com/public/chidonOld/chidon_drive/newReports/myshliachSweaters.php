<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

// find out which parents are getting extra celeb boxes
$extra = [];
$sql = "select parent_id, count(user_id) as total 
        from th_chidon 
        where year = 5781 
        and (shabbaton_expert = 1 or shabbaton_trophy = 1) 
        and parent_id not in (
            select admin_id from th_chidon_parent_purchases where celeb_box >= 1) 
        group by parent_id 
        order by parent_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $extra[$row['parent_id']] = $row['total'];
}

$purchases = [];
$sql = "select * from th_chidon_parent_purchases order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // add extra celeb boxes where needed
    if (isset($extra[$row['admin_id']])) {
        $row['celeb_box'] = $extra[$row['admin_id']];
    }
    $purchases[$row['admin_id']][] = $row;
}

$info = [];
$fields = ['celeb_box', 'celeb_box_add', 'celeb_box_add_ship', 'celeb_box_add_addr', 'sweater_mother', 'sweater_mother_ship',
    'sweater_mother_ship_addr', 'sweater_father', 'sweater_father_ship', 'sweater_father_ship_addr', 'sweater_bubby',
    'sweater_bubby_ship', 'sweater_bubby_ship_addr', 'sweater_zaidy', 'sweater_zaidy_ship', 'sweater_zaidy_ship_addr'];
foreach ($purchases as $admin => $details) {
    foreach ($details as $purchase) {
        foreach ($fields as $field) {
            if (!isset($info[$admin][$field])) $info[$admin][$field] = $purchase[$field];
            else {
                switch ($field) {
                    case 'celeb_box':
                    case 'celeb_box_add':
                    case 'celeb_box_add_ship':
                    case 'sweater_mother_ship':
                    case 'sweater_father_ship':
                    case 'sweater_bubby_ship':
                    case 'sweater_zaidy_ship':
                        if ($purchase[$field] > $info[$admin][$field]) $info[$admin][$field] = $purchase[$field];
                        break;
                    case 'sweater_mother':
                    case 'sweater_father':
                    case 'sweater_bubby':
                    case 'sweater_zaidy':
                    case 'celeb_box_add_addr':
                    case 'sweater_mother_ship_addr':
                    case 'sweater_father_ship_addr':
                    case 'sweater_bubby_ship_addr':
                    case 'sweater_zaidy_ship_addr':
                        if (!empty($purchase[$field])) $info[$admin][$field] = $purchase[$field];
                }
            }
        }
    }
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>MyShliach Sweater Report</title>
    </head>
    <body>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>Number of Celebration Boxes</th>
                <th>Extra Celebration Box</th>
                <th>Ship to</th>
                <th>Mother Sweater</th>
                <th>Ship to</th>
                <th>Father Sweater</th>
                <th>Ship to</th>
                <th>Bubby Sweater</th>
                <th>Ship to</th>
                <th>Zaidy Sweater</th>
                <th>Ship to</th>
            </tr>
            <?php
            foreach ($info as $admin_id => $row) {
                echo "<tr><td>" . $admin_id . "</td><td>" . $row['celeb_box'] . "</td><td>" . $row['celeb_box_add'] .
                    "</td><td>" . $row['celeb_box_add_addr'] . "</td><td>" . $row['sweater_mother'] . "</td><td>" .
                    $row['sweater_mother_addr'] . "</td><td>" . $row['sweater_father'] . "</td><td>" .
                    $row['sweater_father_addr'] . "</td><td>" . $row['sweater_bubby'] . "</td><td>" .
                    $row['sweater_bubby_addr'] . "</td><td>" . $row['sweater_zaidy'] . "</td><td>" .
                    $row['sweater_zaidy_addr'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>
