<?php
require '../db.php';

$users = array();
$sql = "select * from auction_user_prizes aup 
        join users u using (user_id) 
        where u.school_id = 61     
        and auction_id = 77 
        and prize_id = 430       
        and user_id not in (
            select user_id from auction_winners
            where auction_id = 77 
        )
        group by user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$max = count($users);
$winner = rand(0, --$max);
echo $users[$winner];