<?php
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
require 'db.php';

$classes = array();
$sql = "select * from classes
        where class_era = 5776";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $classes[$row['school_id']][] = $row;
}

mysql_query("set autocommit = 0");
mysql_query("begin");

$errors = array();
$success = true;
foreach ($classes as $school => $info) {
    foreach ($info as $row) {
        $newID = 0;
        $sql = "select * from classes
                where school_id = " . $school . "
                and class_grade = '" . $row['class_grade'] . "'
                and class_sub = '" . $row['class_sub'] . "'
                and class_era = 0";
        $result = mysql_query($sql);
        if (!$result) {
            $errors[] = $sql;
            $success = false;
            break;
        }
        if (mysql_num_rows($result) > 0) {
            // new class was already created so get id and update teacher login
            $r2 = mysql_fetch_assoc($result);
            $newID = $r2['class_id'];
        } else {
            $sql = "insert into classes
                    set school_id = " . $school . ",
                    class_grade = '" . $row['class_grade'] . "',
                    class_sub = '" . $row['class_sub'] . "',
                    class_teacher = \"" . mysql_real_escape_string($row['class_teacher']) . "\",
                    email = '" . $row['email'] . "',
                    cell = '" . $row['cell'] . "',
                    default_level = " . $row['default_level'] . ",
                    gender_view = '" . $row['gender_view'] . "',
                    teacher_gender = '" . $row['teacher_gender'] . "',
                    teacher_hname = \"" . mysql_real_escape_string($row['teacher_hname']) . "\",
                    class_era = 0";
            if (mysql_query($sql)) {
                $newID = mysql_insert_id();
            } else {
                $errors[] = $sql;
                $success = false;
                break;
            }
        }
        if ($newID) {
            $sql = "select * from admin_auths
                    where id = " . $row['class_id'] . "
                    and auth = 'class'
                    and role_id = 13";
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $sql = "update admin_auths
                        set id = " . $newID . "
                        where id = " . $row['class_id'] . "
                        and auth = 'class'
                        and role_id = 13";
                if (!mysql_query($sql)) {
                    $errors[] = $sql;
                    $success = false;
                    break;
                }
            }
        }
    }
}

if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit = 1");
    echo "Done.";
} else {
    mysql_query("rollback");
    mysql_query("set autocommit = 1");
    foreach ($errors as $error) {
        echo $error . "<br />";
    }
}