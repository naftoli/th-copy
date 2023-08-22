<?php
require_once '../db.php';

$sql = 'select auction_id from auctions order by auction_id desc limit 1';
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$auction_id = $row['auction_id'];

$info = [];
$sql = "SELECT 
            aup.user_id,
            aup.prize_id,
            aup.quantity,
            pa.prize_name,
            s.school_id, 
            s.school_name, 
            c.class_grade,
            c.class_sub,
            u.first,
            u.last
        FROM
            auction_user_prizes aup
                JOIN
            prizes_auction pa USING (prize_id)
                JOIN
            users u USING (user_id)
                JOIN
            schools s ON s.school_id = u.school_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            aup.auction_id = $auction_id 
                AND aup.prize_id IN (SELECT 
                    prize_id
                FROM
                    auction_prizes
                WHERE
                    auction_id = $auction_id)
        GROUP BY aup.user_id, aup.prize_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <style>
      tr, th, td {
        font-size: 12px;
        padding: 5px;
        font-family: Arial;
      }
    </style>
</head>
<body>
<table>
    <tr>
        <th>Prize ID</th><th>Prize</th><th>School ID</th><th>School</th><th>Grade</th><th>Sub</th><th>First Name</th><th>Last Name</th><th># of Tickets</th><th>User ID</th>
    </tr>
    <?
    foreach ( $info as $row ) {
        echo "<tr><td>" . $row['prize_id'] . "</td><td>" . $row['prize_name'] . "</td><td>" . $row['school_id'] . "</td><td>" .
            $row['school_name'] . "</td><td>" . $row['class_grade'] . "</td><td>" . $row['class_sub'] . "</td><td>" .
            $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['quantity'] . "</td><td>" . $row['user_id'] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>