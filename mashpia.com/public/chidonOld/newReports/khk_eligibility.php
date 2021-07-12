<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$super = $admin_user['auth'] == 'super';

$children = [];
$stmt = $MASHPIA_DB->query("
    SELECT 
        tc.user_id, COUNT(*) AS total, u.*, c.*
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        date_paid > 0 AND c.class_grade IN ('7','8')
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ")
    GROUP BY u.user_id
    ORDER BY u.school_id , c.class_grade , c.class_sub , u.last , u.first
");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $children[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>KHK Eligibility</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 14px;
            }
            th, td {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <?php include('../../admin_header.php'); ?>
        <h1>KHK Eligibility</h1>
        <table>
            <tr>
                <th>Serial Number</th>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Number of times Registered for Shabbaton</th>
                <th>Eligible</th>
                <th>Eligibility Copy</th>
            </tr>
            <?php
            foreach ($schools as $id => $school) {
                if (isset($children[$id])) {
                    foreach ($children[$id] as $child) {
                        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                        echo "<tr id='" . $child['user_id'] . "'><td>" . $child['user_serial'] . "</td><td>" . $school .
                            "</td><td>" . $grade . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td><td>" .
                            $child['total'] . "</td><td><input type='checkbox' class='eligibility' name='eligibility[" . $child['user_id'] . "]' ";
                        if ($child['khk_eligible']) echo "checked ";
                        if (! $super) echo " disabled ";
                        echo "/></td><td>";
                        if ($child['khk_eligible']) echo "&#10003;";
                        else echo "&#10007;";
                        echo "</td></tr>";
                    }
                }
            }
            ?>
        </table>
    </body>
    <script>
        <?php if ($super) : ?>
            $(".eligibility").click( function () {
                let id = $(this).parent().parent().attr('id')
                let checked = $(this).is(":checked") ? 1 : 0
                $.post('updateKHK.php', { user_id: id, checked: checked }, function( result ) {
                    let res = JSON.parse(result)
                    if (res.success) alert('Updated.')
                    else alert(res.error)
                })
            })
        <?php endif; ?>
    </script>
</html>
