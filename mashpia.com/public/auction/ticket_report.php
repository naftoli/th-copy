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
            COUNT(*) AS tickets,
            pa.prize_name,
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
            aup.auction_id = 83
                AND aup.prize_id IN (SELECT 
                    prize_id
                FROM
                    auction_prizes
                WHERE
                    auction_id = $auction_id)
        GROUP BY aup.user_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[$row['prize_name'] . ' - Prize ID:' . $row['prize_id']][] = $row;
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
    <?php
    foreach ( $info as $prize => $more ) {
      echo "<h1>" . $prize . "</h1>";
      ?>
      <table>
        <tr>
          <th>School</th><th>Grade</th><th>Sub</th><th>First Name</th><th>Last Name</th><th># of Tickets</th><th>User ID</th>
        </tr>
      <?
      foreach ( $more as $row ) {
        echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['class_grade'] . "</td><td>" . $row['class_sub'] . "</td><td>" . 
          $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['tickets'] . "</td><td>" . $row['user_id'] . "</td></tr>";
      }
      echo "</table>";
    }
    ?>
  </body> 
</html>