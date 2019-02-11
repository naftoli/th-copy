<?php
ini_set('display_errors',1);
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// get all users signed up to chidon 
$year = GlobalSettings::getChidonYear();
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.admin_id, a.last, s.school_name, c.class_grade, c.class_sub
    FROM
        th_chidon tc
            JOIN
        admins a ON a.admin_id = tc.parent_id
            JOIN
        schools s ON s.school_id = tc.school_id
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year = :year
            AND (((tc.test1a + tc.test2a + tc.test3a) / 3) >= 70)
    ORDER BY s.school_name , c.class_grade , c.class_sub , u.last , u.first
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      #main {
        width: 8.5in;
      }
      .flyer img {
        width: 8.5in;
        margin-top: 20px;
      }
      .name {
        position: relative;
        top: 6.5in;
        left: 5in;
        color: #fff;
        width: 250px;
        text-align: center;
      }
      .link {
        position: relative;
        top: 7.9in;
        left: 5in;
        color: #fff;
        font-size: 20px;
        width: 250px;
        text-align: center;
      }
      .text {
        margin-top: 3in;
        text-align: center;
        font-size: 72px;
      }
    </style>
  </head>
  <body>
    <div id="main">
      <?php
      $previousSchool = '';
      $previousGrade = '';
      foreach ( $rows as $row ) {
        $school = $row['school_name'];
        $grade = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
        if ( $school != $previousSchool ) {
          echo "<div class='text'>" . $school . "<br />Grade: " . $grade . "</div>";
          echo "<div style='page-break-after: always'></div>";
          $previousSchool = $school;
          $previousGrade = $grade;
        } else if ( $grade != $previousGrade ) {
          echo "<div class='text'>" . $school . "<br />Grade: " . $grade . "</div>";
          echo "<div style='page-break-after: always'></div>";
          $previousGrade = $grade;
        }

        echo "<div class='flyer'>";
        echo "<div class='name'>Help Team " . $row['last'] . "</div>";
        echo "<div class='link'>/" . strtoupper( $row['last'] ) . "/" . $row['admin_id'] . "</div>";
        echo "<img src='images/ChidonDrive-flyer-D3-no-link-2.jpg' />";
        echo "</div><div style='page-break-after: always'></div>";
      }
      ?>
    </div>
  </body>
</html>