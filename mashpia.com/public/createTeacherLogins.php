<?php
require 'db.php';

$classes = array();
$sql = "select * from classes
        join schools using (school_id)
        where class_era = 0
        and school_initials is not null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $classes[] = $row;
}

$create = array();
foreach ($classes as $row) {
    $username = $row['school_initials'] . $row['class_id'];
    $pass = 1234;
    $create[$row['class_id']] = "insert into admins
                set username = '" . $username . "',
                password = '" . $pass . "',
                last = '" . mysql_real_escape_string($row['class_teacher']) . "'";
}

mysql_query("set autocommit = 0");
mysql_query("begin");

$success = true;
foreach ($create as $class_id => $sql) {
    if (mysql_query($sql)) {
        $id = mysql_insert_id();
        $qry = "insert into admin_auths 
                set admin_id = " . $id . ", 
                auth = 'class', 
                id = $class_id, 
                role_id = 13";
        if (!mysql_query($qry)) {
            $success = false;
            break;
        }
    } else {
        $success = false;
        break;
    }
}

if ($success) {
    mysql_query("commit");
} else {
    echo $sql . mysql_error();
    mysql_query("rollback");
}
mysql_query("set autocommit = 1");

if ($success) echo "Done";