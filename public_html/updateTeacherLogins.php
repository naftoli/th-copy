<?php
require 'db.php';

$classes = array();
$sql = "select * from classes c 
        join schools s using (school_id)
        where c.class_era = 5776
        and s.school_initials is not null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $classes[] = $row;
}

$create = array();
foreach ($classes as $row) {
    $username = $row['school_initials'] . $row['class_id'];
    $pass = 1234;
    // check if account exists
    // find admin
    $sql = "select admin_id from admins where username = '" . $username . "' and password = '1234'";
    $result = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($result) > 0) {
        // check if login auth created
        $r2 = mysql_fetch_assoc($result);
        $sql = "select * from admin_auths where admin_id = " . $r2['admin_id'] . " and auth = 'class' and role_id = 13";
        $result = mysql_query($sql);
        $aa = mysql_fetch_assoc($result);
        if (mysql_num_rows($aa) == 0) {
            $create[] = "insert into admin_auths
                        set id = " . $row['class_id'] . ",
                        admin_id = " . $r2['admin_id'] . ",
                        auth = 'class', 
                        role_id = 13";
        } else {
            // check if we need to update
            if ($aa['id'] != $row['class_id']) {
                $create[] = "update admin_auths
                            set id = " . $row['class_id'] "
                            where id = " . $aa['id'] . "
                            and admin_id = " . $aa['admin_id'] . "
                            and auth = 'class'
                            and role_id = 13";
            }
        }
    } else echo "No admin created for: " . $row['class_id'] . "<br />";
}

mysql_query("set autocommit = 0");
mysql_query("begin");

$success = true;
foreach ($create as $sql) {
    echo $sql . "<br />";
    if (!mysql_query($sql)) {
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