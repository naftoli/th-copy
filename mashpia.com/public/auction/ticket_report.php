<?php
require_once '../db.php';

$info = [];
$sql = "SELECT 
        aup.*,
        pa.prize_name,
        s.school_name,
        c.class_grade,
        c.class_sub,
        u.first,
        u.last
        FROM
        auction_user_prizes aup
            JOIN
        auction_prizes ap USING (prize_id)
            JOIN
        prizes_auction pa USING (prize_id)
            JOIN
        users u USING (user_id)
            JOIN
        schools s ON s.school_id = u.school_id
            JOIN
        classes c ON c.class_id = u.class_id
        WHERE
        aup.auction_id = 80
        GROUP BY prize_id , user_id
        ORDER BY prize_id , school_name , class_grade , class_sub , last , first";
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
          $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['quantity'] . "</td><td>" . $row['user_id'] . "</td></tr>";
      }
      echo "</table>";
    }
    ?>
  </body> 
</html>