<?php
require 'db.php';
$info = array();
$sql = "select * from users where user_serial in (773789)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

foreach ($info as $row) {
    $sql = "insert into th_chidon
            set user_id = " . $row['user_id'] . ",
            year = 5777,
            school_id = " . $row['school_id'] . ",
            reg_date = now()";
    mysql_query($sql);
}