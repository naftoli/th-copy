<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$grades = [];
$info = [];
foreach ($schools as $id => $school) {
    $sql = "SELECT 
                u.user_id, first, last, school_name, c.class_id, class_grade, class_sub, ut.*
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
        $grades[$row['class_grade']][$row['class_id']] = $grade;
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
    <h1>Tanya in 5 Ladder Assignment</h1>
    <p>
        Not sure which ladder to choose?<br />
        Review the Tanya in 5 Ladder Overview and Growth Plan <a href="https://www.dropbox.com/s/dceat087m523my2/Tanya%20in%205%20Overview.pdf?dl=0">here</a> (pages 5-6)!
        <br /><br />
        Remember to visit the <a href="https://www.dropbox.com/sh/qm3jbn09qky9xny/AAAd4rc1vXOK2uaxZIPAimRZa?dl=0">Resource Library</a> for comprehensive resources including Tanya Cards, Yearly Schedules, and Audio tracks personalized for every grade and every ladder!
        <br /><br />
        Use the following form to choose the tbp ladder you would like the children to be on.
    </p>
    <p>
        Change all children in Grade
        <select id="fromGrade">
            <?php
            foreach ($grades as $grade => $classes) {
                echo "<option value='grade_" . $grade . "'>" . $grade . "</option>";
                foreach ($classes as $class_id => $class_name) {
                    echo "<option value='class_" . $class_id . ">" . $class_name . "</option>";
                }
            }
            ?>
        </select>
        to ladder
        <select id="toLadder">
            <?php
            for ($i = 1; $i <= 5; $i++) {
                echo "<option value=" . $i . ">" . $i . "</option>";
            }
            ?>
        </select>
        <button id="changeLadders">Apply</button>
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
    const grades = <?=$grades?>;
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

    $("#changeLadders").click( function() {
        const grade = $("#fromGrade").val();
        const ladder = $("#toLadder").val();
        // figure out what grade refers to
        let class_grade = 0
        let class_id = 0
        if (grade.includes('grade')) {
            // it's a grade not a class
            const pos = grade.indexOf('grade_')
            class_grade = grade.substring(pos + 6)
        } else if (grade.includes('class')) {
            // get class
            const pos = grade.indexOf('class_')
            class_id = grade.substring(pos + 6)
        }
        $.post('changeLadder.php', { grade: class_grade, id: class_id, ladder: ladder }, function(success) {
            if (!success) {
                alert('Error updating ladders.')
            } else {
                alert('Ladders changed.')
                location.reload()
            }
        })
    })
</script>
</html>
