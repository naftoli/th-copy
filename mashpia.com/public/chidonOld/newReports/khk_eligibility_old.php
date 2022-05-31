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
        tc.user_id, 
        IFNULL(COUNT(tc.date_paid > 0), 0) AS total,
        u.*,
        c.*
    FROM
        users u
            JOIN
        classes c ON c.class_id = u.class_id
            LEFT JOIN
        th_chidon tc USING (user_id)
    WHERE
        c.class_grade IN ('7' , '8')
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
<!--                <th>Number of times Registered for Shabbaton</th>-->
                <th>5777</th>
                <th>5778</th>
                <th>5779</th>
                <th>5780</th>
                <th>5781</th>
                <th>Eligible</th>
                <?php if ($super) : ?>
                    <th>Eligibility Copy</th>
                <?php endif; ?>
                <th>Registered for KHK</th>
            </tr>
            <?php
            $khkTotal = 0;
            foreach ($schools as $id => $school) {
                if (isset($children[$id])) {
                    foreach ($children[$id] as $child) {
                        $years = [];
                        $sqlYears = "select year from th_chidon where date_paid > 0 and user_id = " . $child['user_id'];
                        $resYears = mysql_query($sqlYears);
                        while ($rowYears = mysql_fetch_assoc($resYears)) {
                            $years[] = $rowYears['year'];
                        }
                        if ($child['total'] > 3) $khkTotal++;
                        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                        echo "<tr id='" . $child['user_id'] . "'><td>" . $child['user_serial'] . "</td><td>" . $school .
                            "</td><td>" . $grade . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td>";
                        for ($y = 5777; $y <= 5781; $y++) {
                            echo "<td>" . (in_array($y, $years) ? "&#10003;" : "&#10007;") . "</td>";
                        }
                        echo "<td><input type='checkbox' class='eligibility' name='eligibility[" . $child['user_id'] . "]' ";
                        if ($child['khk_eligible']) echo "checked ";
                        if (! $super) echo " disabled ";
                        echo "/></td>";
                        if ($super) {
                            echo "<td>";
                            if ($child['khk_eligible']) echo "&#10003;";
                            else echo "&#10007;";
                            echo "</td>";
                        }
                        echo "<td>";
                        $sqlKhk = "select khk_reg from th_chidon where year = 5782 and user_id = " . $child['user_id'];
                        $resKhk = mysql_query($sqlKhk);
                        $rowKhk = mysql_fetch_assoc($resKhk);
                        if ($rowKhk['khk_reg']) echo "&#10003;";
                        else echo "&#10007;";
                        echo "</td></tr>";
                    }
                }
            }
            ?>
        </table>
        <?= $khkTotal ?>
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