<?php
require '../../db.php';

$info = array();
$sql = "select * from th_chidon tc
        join users u using (user_id)
        join schools s on tc.school_id = s.school_id
        join classes c on c.class_id = u.class_id
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-family: sans-serif;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>User ID</th>
                <th>Change Gender To</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                echo "<tr id=" . $row['user_id'] . "><td>" . $row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" .
                $row['last'] . "</td><td>" . $row['gender'] . "</td><td>";
                if (empty($row['gender'])) echo "<span style='color:red'>" . $row['user_id'] . "</span>";
                else echo $row['user_id'];
                echo "</td><td>";
                $genders = array('F','M');
                echo "<select name='gender' class='gender'>";
                echo "<option value='0'>Change gender to</option>";
                foreach ($genders as $gender) {
                    if ($row['gender'] == $gender) continue;
                    echo "<option value='" . $gender . "'>" . $gender . "</option>";
                }
                echo "</select></td></tr>";
            }
            ?>
        </table>
    </body>
    <script src="/scripts/jquery-1.8.3.js"></script>
    <script>
        $(function() {
            $(".gender").change( function() {
                var id = $(this).parent().parent().attr('id');
                var val = $(this).val();
                if (val) {
                    $.post('/ajax/updateGender.php', { user : id, val : val }, function( error ) {
                        if (parseInt(error)) {
                            alert(error);
                        } else {
                            alert('updated.');
                        }
                    });
                }
            });
        });
    </script>
</html>