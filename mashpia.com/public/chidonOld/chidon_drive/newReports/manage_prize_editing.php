<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$info = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        join classes c on u.class_id = c.class_id 
        where tc.year = 5781 
        and (tc.shabbaton_maven = 1 or tc.shabbaton_pro = 1 or tc.shabbaton_expert or tc.shabbaton_trophy = 1)
        and u.school_id in (" . implode(',', array_keys($schools)) . ") 
        order by u.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_id']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Prize Editing</title>
        <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Chidon Prize Editing</h1>
    <?php
    foreach ($schools as $school_id => $school_name) {
        if (isset($info[$school_id])) {
            echo "<h2>" . $school_name . "</h2>";
            ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Allow Editing of Prizes</th>
                </tr>
                <?php
                foreach ($info[$school_id] as $row) {
                    $grade = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
                    echo "<tr><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] .
                        "</td><td><input type='checkbox' name='edit[]' class='edit' data-id='" . $row['th_chidon_id'] . "' ";
                    if (intval($row['edit_prizes']) == 1) echo "checked ";
                    echo "/></td></tr>";
                }
            echo "</table>";
        }
    }
    ?>
    </body>
    <script>
        $( function () {
            $(".edit").click( function () {
                const id = $(this).data('id')
                const checked = $(this).is(":checked") ? 1 : 0
                if (id) {
                    $.post('../ajax/updateEditing.php', { chidon_id: id, checked: checked }, function(result) {
                        if (parseInt(result) == 1) {
                            alert('Updated.')
                        } else {
                            alert('Error updating.')
                        }
                    })
                }
            })
        })
    </script>
</html>