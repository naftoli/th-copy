<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = [];
foreach ($schools as $id => $school) {
    $sql = "SELECT 
                u.user_id, first, last, school_name, class_grade, class_sub, ut.*
            FROM
                users u
                    JOIN
                user_tracks ut USING (user_id)
                    JOIN
                schools s USING (school_id)
                    JOIN
                classes c ON c.class_id = u.class_id
            WHERE
                u.user_registered > 0
                    AND ut.subject_id = 27
                    AND u.school_id = $id
            ORDER BY school_name , class_grade , class_sub , last , first
    ";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . ' ' . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
        $info[$id][$grade][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Choose TBP Ladder</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <?php require '../admin_header.php'; ?>
    <h1>Choose TBP Ladder</h1>
    <p>
        Use the following form to choose the tbp ladder you would like the children to be on.
    </p>
    <table>
        <tr>
            <th>School</th>
            <th>Grade</th>
            <th>Student</th>
            <th>Year/Age</th>
            <th>Ladder</th>
        </tr>
        <?php
        foreach ($info as $school_id => $more) {
            foreach ($more as $grade => $ladders) {
                foreach ($ladders as $row) {
                    echo "<tr><td>" . $schools[$school_id] . "</td><td>" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] .
                        "</td><td>" . $row['level'] . "</td><td>";
                    echo "<select name='ladder' class='ladder' id=" . $row['user_id'] . ">";
                    for ($i = 1; $i <= 5; $i++) {
                        echo "<option value=" . $i;
                        if ($i == $row['track_id']) echo " selected ";
                        echo ">" . $i . "</option>";
                    }
                    echo "</select>";
                    echo "</td></tr>";
                }
            }
        }
        ?>
    </table>
</body>
<script>
    $(".ladder").change( function() {
        let id = $(this).attr('id')
        let val = $(this).val()
        $.post('changeLadder.php', { user: id, ladder: val }, function(success) {
            if (!success) {
                alert('Error updating ladder.')
            } else {
                alert('Ladder changed.')
            }
        })
    })
</script>
</html>
