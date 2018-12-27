<?php
require 'db.php';
$users = [];
$marks = [];

$sql = "
  SELECT 
      u.user_id, user_serial, first, last, school_name, class_grade, class_sub
  FROM
      users u
          JOIN
      schools s USING (school_id)
          JOIN
      classes c ON c.class_id = u.class_id
  WHERE
      u.user_registered > 0
  ORDER BY 
      school_name, class_grade, class_sub, last, first
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $users[$row['user_id']] = $row;
}

$sql = "
  SELECT 
      dtm.user_id, count(distinct mark_date) as total
  FROM
      date_tasks_marks dtm
          JOIN
      date_tasks dt USING (date_task_id)
          JOIN
      date_tasks_missions dtmm USING (date_tasks_mission_id)
  WHERE
      dtmm.start_date >= 2458284
          AND dtmm.end_date <= 2458358
  GROUP BY user_id
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $marks[$row['user_id']] = $row['total'];
}
// echo "<pre>"; 
// print_r( $users );
// print_r( $marks ); 
// echo "</pre>";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      tr, th, td {
        padding: 5px;
        font-size: 12px;
        font-family: Verdana;
      }
    </style>
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>School</th>
          <th>Grade</th>
          <th>Student</th>
          <th>User ID</th>
          <th>Total Days</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ( $users as $user_id => $info ) {
          if ( isset( $marks[$user_id] ) ) {
            $mark = $marks[$user_id];
            if ( $mark >= 60 ) {
              $school = $info['school_name'];
              $grade = $info['class_grade'] . (empty( $info['class_sub'] ) ? '' : '-' . $info['class_sub']);
              $user = $info['first'] . ' ' . $info['last'];
              echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $user . "</td><td>" . $user_id . "</td><td>" . $mark . "</td></tr>";
            }
          }
        }
        ?>
      </tbody>
    </table>
  </body>
</html>