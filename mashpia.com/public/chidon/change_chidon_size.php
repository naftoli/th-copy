<?php
require '../db.php';

$ids = array();
$sql = "select th_chidon_id from th_chidon tc
        join users u using (user_id) 
        where tc.year = 5777
        and u.gender = 'F'
        and tc.size = 'children l' 
        limit 18";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ids[] = $row['th_chidon_id'];
}

foreach ($ids as $id) {
    $sql = "update th_chidon set size = 'children xl' where th_chidon_id = " . $id;
    mysql_query($sql);
}