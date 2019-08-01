<?php
$admin_auth = ['school'];
require_once 'header.php';

if ( $admin_user['auth'] != 'super' ) {
  echo "No permission to view this page.";
  exit;
}

$answers = [];
$notes = [];
$sql = "select * from hachayol_poll hp 
        join hachayol_poll_details d using (hachayol_poll_id) 
        join users u using (user_id) 
        order by last, first";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $name = $row['first'] . ' ' . $row['last'];
  $answers[$row['user_id']][$name][$row['question']] = $row['answer'];
  $notes[$row['user_id']] = $row['notes'];
}

$questions = ['Parshifier', 'Veholachto Bidrochov', 'Roots', 'Comics', 'Shine Back Page & Game', 'Dubbie\'s Diary', 'Yom Tov Poems'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf8' />
    <style>
      tr, th, td {
        padding: 5px;
        font-size: 14px;
        font-family: Arial;
      }
    </style>
  </head>
  <body>
    <table>
      <tr>
        <th>Child</th>
        <?php foreach ( $questions as $question ) : ?>
        <th><?= $question ?></th>
        <?php endforeach; ?>
        <th>Notes</th>
      </tr>
      <?php
      foreach ( $answers as $user => $more ) {
        foreach ( $more as $name => $rows ) {
          echo "<tr><td>" . $name . "</td>";
          foreach ( $questions as $question ) {
            if ( isset( $rows[$question] ) ) {
              echo "<td>" . $rows[$question] . "</td>";
            } else {
              echo "<td></td>";
            }
          }
          if ( isset( $notes[$user] ) && !empty( $notes[$user] ) ) {
            echo "<td>" . $notes[$user] . "</td>";
          } else {
            echo "<td></td>";
          }
          echo "</tr>";
        }
      }
      ?>
    </table>
  </body>
</html>