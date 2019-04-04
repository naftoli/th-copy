<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../class.adminSchools.php';
require_once __DIR__ . '/../../class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();
$school_list = array_keys( $schools );
$multiple_schools = false;
if ( count( $schools ) > 1 ) $multiple_schools = true;

$times = [];
$marks = [];
$sql = "
  SELECT 
    t.*, s.school_name, u.first, u.last, c.class_grade, c.class_sub, m.marked
  FROM
    th_chidon tc
        JOIN
    schools s on s.school_id = tc.school_id 
        JOIN
    users u USING (user_id)
        JOIN
    classes c ON c.class_id = u.class_id
        JOIN
    th_chidon_attendance_times t ON tc.bunk_number = t.att_type_number
        LEFT JOIN
    th_chidon_attendance_marks m USING (att_time_id, th_chidon_id)
  WHERE
    tc.year = 5779 AND u.gender = 'M'
        AND tc.date_paid > 0
        AND t.year = 5779
        AND t.chidon_type = 'boys'
        AND tc.school_id IN (" . implode(',', $school_list) . ") 
  ORDER BY t.att_time, t.att_type_number, u.last, u.first
";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $times[$row['att_time']] = $row;
  $marks[$row['att_time']][$row['att_type_number']][$row['last']][$row['first']] = $row;
}
//echo "<pre>"; print_r( $times ); print_r( $marks ); echo "</pre>";  
// $bunks = [];
// $sql = "select bunk_number from th_chidon where date_paid > 0 and year = " . $year . " group by bunk_number";
// $result = mysql_query( $sql );
// while ( $row = mysql_fetch_assoc( $result ) ) {
//   if ( !empty( $row['bunk_number'] ) ) $bunks[] = $row['bunk_number'];
// } 
// sort( $bunks );

// $walking_groups = [];
// $sql = "select walking_group from th_chidon where date_paid > 0 and year = " . $year . " group by walking_group";
// $result = mysql_query( $sql );
// while ( $row = mysql_fetch_assoc( $result ) ) {
//   if ( !empty( $row['walking_group'] ) ) $walking_groups[] = $row['walking_group'];
// } 
// natsort( $walking_groups );
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <title>Attendance Report</title>
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 5px;
        font-family: Arial;
      }
    </style>
  </head>
  <body>
    <?php
    //echo "<pre>"; print_r( $marks ); echo "</pre>";
    foreach ( $times as $time_id => $time ) {
      echo "<h3>" . $time['day_of_week'] . " " . $time['att_time'] . " " . $time['short_name'] . " (" . $time['att_type'] . ")</h3><hr />";
      ?>
      <table>
        <tr>
          <th>Bunk / Walking Group</th>
          <th>Student</th>
          <th>Marked</th>
          <?php if ( $multiple_schools ) : ?>
          <th>School</th>
          <?php endif; ?>
          <th>Grade</th>
        </tr>
        <?php
        ksort( $marks[$time_id] );
        foreach ( $marks[$time_id] as $group => $more ) {
          foreach ( $more as $last => $other ) {
            foreach ( $other as $first => $mark ) {
              echo "<tr><td>" . $mark['att_type_number'] . "</td><td>" . $mark['first'] . ' ' . $mark['last'] . "</td><td>" . ($mark['marked'] == '1' ? 'yes' : 'no') . "</td>";
              if ( $multiple_schools ) echo "<td>" . $mark['school_name'] . "</td>";
              echo "<td>" . $mark['class_grade'] . (empty( $mark['class_sub'] ) ? '' : '-' . $mark['class_sub']) . "</td></tr>";
            }
          }
        }
      echo "</table>";
    } 
    ?>
    
      
    <!-- <form action="report.php" method="post">
      Choose Bunk: 
      <select name="bunk">
        <option>Choose One</option>
        <?php
        foreach ( $bunks as $bunk ) {
          echo "<option>" . $bunk . "</option>";
        }
        ?>
      </select>
      <br />
      OR
      <br />
      Choose Walking Group: 
      <select name="walking_group">
        <option>Choose One</option>
        <?php
        foreach ( $walking_groups as $group ) {
          echo "<option>" . $group . "</option>";
        }
        ?>
      </select>
      <br /><br />
      <input type="submit" name="submit" value="submit" />
    </form> -->
  </body>
</html>