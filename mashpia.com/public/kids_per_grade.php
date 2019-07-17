<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once 'header.php';
require_once 'class.adminSchools.php'; 
require_once 'class.schoolClasses.php';

$kids = [];
$totalsPerGrade = [];
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
foreach ( $schools as $id => $school ) {
  $sc = new SchoolClasses( $id );
  $grades = $sc->getClasses();
  foreach ( $grades as $grade ) {
    $class = $grade['class_grade'] . ($grade['class_sub'] ? '-' . $grade['class_sub'] : '');
    $sql = "select first, last from users where user_registered > 0 and class_id = " . $grade['class_id'];
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
      $kids[$school][$class][] = $row['first'] . ' ' . $row['last'];
      if ( isset( $totalsPerGrade[$grade['class_grade']] ) ) $totalsPerGrade[$grade['class_grade']]++;
      else $totalsPerGrade[$grade['class_grade']] = 1;
    }
  }
}

// $kids = [];
// $sql = "select * from users u 
//         join schools s using (school_id) 
//         join classes c on c.class_id = u.class_id 
//         where user_registered > 0";
// $result = mysql_query( $sql );
// while ( $row = mysql_fetch_assoc( $result ) ) {
//   $name = $row['first'] . ' ' . $row['last']; 
//   $grade = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
//   $kids[$row['school_name']][$grade][] = $name;
// }
// // sort
// foreach ( $kids as $school => $more ) {
//   ksort($kids[$school]);
// }
// ksort($kids);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      tr, th, td {
        font-family: Arial;
        font-size: 14px;
        padding: 5px;
      }
    </style>
  </head>
  <body>
    <?php foreach ( $kids as $school => $more ) : ?>
      <h2><?= $school ?></h2>
      <table>
        <tr>
          <th>Grade</th>
          <th>Student</th>
        </tr>
        <?php
        foreach ( $more as $grade => $students ) {
          $totals[$school][$grade] = 0;
          $total = 0;
          foreach ( $students as $student ) {
            echo "<tr><td>" . $grade . "</td><td>" . $student . "</td></tr>";
            $total++;
          }
          $totals[$school][$grade] += $total;
        }
        ?>
      </table>
    <?php endforeach; ?>
    <h2>Totals</h2>
    <?php 
    $grandTotal = 0;
    foreach ( $totals as $school => $more ) {
      echo "<h3>" . $school . "</h3>";
      ?>
      <table>
        <tr>
          <th>Grade</th>
          <th>Total</th>
        </tr>
        <?php
        $schoolTotal = 0;
        foreach ( $more as $grade => $total ) {
          echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
          $schoolTotal += $total;
        }
        echo "<tr><th>School Total:</th><th>" . $schoolTotal . "</th></tr>";
        $grandTotal += $schoolTotal;
      echo "</table>";
    }
    ?>
    <table>
      <tr>
        <th>Army Wide Grade</th><th>Total</th>
      </tr>
      <?php
      foreach ( $totalsPerGrade as $grade => $total ) {
        echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
      }
    echo "</table>";
    echo "<h2>Grand Total: " . $grandTotal . "</h2>";
    ?>
  </body>
</html>