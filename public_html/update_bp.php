<?php
require 'db.php';

$sql = "select ll.* from lines_learned ll 
        join users u using (user_id) 
        where ll.campaign_id in (9,10) 
        and u.user_registered is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sql = "insert into bp_user_summary ( campaign_id, user_id, num_lines )
            values ( " . $row['campaign_id'] . ", " . $row['user_id'] . ", " . $row['lines_learned'] . " )
            on duplicate key update num_lines = " . $row['lines_learned'];
    mysql_query($sql) or die( mysql_error() );
}
