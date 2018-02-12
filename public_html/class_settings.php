<?php
require 'db.php';

if (isset($_POST['submit'])) {
    //echo "<pre>"; print_r($_POST); echo "</pre>";
    $qrys = array();
    foreach ($_POST as $k => $v) {
        if (is_numeric($k)) {
            $qry = "update classes set class_gender = '" . mysql_real_escape_string($v) . "' where class_id = " . intval(mysql_real_escape_string($k));
            $qrys[] = $qry;
        }
    }
    //echo "<pre>"; print_r($qrys); echo "</pre>";
    foreach ($qrys as $qry) {
        mysql_query($qry);
    }
}

$classes = array();
$sql = "select * from classes c
        join schools s using (school_id) 
        where class_era = 0
        and school_era is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $classes[$row['school_name']][] = $row;
}
ksort($classes);
?>
<html>
    <head>
        <style>
            tr, th, td {
                font-size: 12px;
                padding: 5px;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <form action="class_settings.php" method="post">
            <input type="submit" name="submit" value="save" />
            <br /><br />
            <?php foreach ($classes as $school => $info) : ?>
                <table>
                    <caption><?=$school?></caption>
                    <tr>
                        <th>Grade</th>
                        <th>Teacher</th>
                        <th>Type</th>
                    </tr>
                    <?php foreach ($info as $row) : ?>
                        <tr>
                            <td><?=$row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub'])?></td>
                            <td><?=$row['class_teacher']?></td>
                            <td>
                                <input type="radio" name="<?=$row['class_id']?>" class="gender" value="m"
                                <?php if ($row['class_gender'] == 'm') echo "checked "?>
                                /> Boys<br />
                                <input type="radio" name="<?=$row['class_id']?>" class="gender" value="f"
                                <?php if ($row['class_gender'] == 'f') echo "checked "?>
                                /> Girls
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <br /><br />
            <?php endforeach; ?>
            <input type="submit" name="submit" value="save" />
        </form>
    </body>
</html>