<?php
ini_set('display_errors',1);
require 'db.php';

$auction_id = 79;

// get prize info
$prizes = array();
$sql = "select pa.prize_id, pa.prize_name from auction_prizes ap 
        join prizes_auction pa using (prize_id) 
        where ap.auction_id = " . $auction_id;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $prizes[$row['prize_id']] = $row['prize_name'];
}

// get user info
$users = array();
$sql = "select u.user_id, u.user_serial, u.first, u.last, c.class_grade, c.class_sub, s.school_name from users u  
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join auction_user_prizes aup using (user_id) 
        where aup.auction_id = " . $auction_id . " 
        group by aup.user_id";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $users[$row['user_id']] = $row;
}

// get ticket info
$tickets = array();
foreach ($prizes as $id => $prize) {
    $sql = "select user_id, sum(quantity) as total from auction_user_prizes aup 
            join users u using (user_id) 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where aup.auction_id = " . $auction_id . " 
            and aup.prize_id = " . $id . " 
            group by aup.user_id 
            order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $tickets[$id][$row['user_id']] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: 'Arial';
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <h1>End of Year Auction 5778</h1>
        <table>
            <tr>
                <th>Prize</th>
                <th>Name</th>
                <th>Grade</th>
                <th>School</th>
                <th>Serial Number</th>
                <th>Number of tickets entered</th>
            </tr>
            <?php
            foreach ($tickets as $prize_id => $more) {
                foreach ($more as $user_id => $total) {
                    if (isset($users[$user_id])) {
                        $name = $users[$user_id]['first'] . ' ' . $users[$user_id]['last'];
                        $grade = $users[$user_id]['class_grade'] . (empty($users[$user_id]['class_sub']) ? '' : '-' . $users[$user_id]['class_sub']);
                        $school = $users[$user_id]['school_name'];
                        $serial = $users[$user_id]['user_serial'];
                        echo "<tr><td>" . $prizes[$prize_id] . "</td><td>" . $name . "</td><td>" . $grade . "</td><td>" . $school . "</td><td>" . 
                            $serial . "</td><td>" . $total . "</td></tr>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>