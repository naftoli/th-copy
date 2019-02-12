<?php
ini_set('display_errors',1);
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// get all users signed up to chidon 
$year = GlobalSettings::getChidonYear();
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.admin_id, a.first as Afirst, a.last, u.first, s.school_name, c.class_grade, c.class_sub
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
    <link rel="stylesheet" type="text/css" href="pr.css" />
  </head>
  <body>
    <div id="main">
      <?php
      $previousSchool = '';
      $previousGrade = '';
      foreach ( $rows as $index => $row ) {
        $school = $row['school_name'];
        $grade = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
        if ( $school != $previousSchool || $grade != $previousGrade ) {
          echo "<div class='text'>" . $school . "<br />Grade: " . $grade . "</div>";
          echo "<div style='page-break-after: always'></div>";
          if ( $school != $previousSchool ) $previousSchool = $school;
          $previousGrade = $grade;
        }

        echo "<div class='flyer'>";
        echo "<img src='images/ChidonDrive-flyer-D3-no-link-1.jpg' />";
        echo "<div class='name'>Help Team " . $row['last'] . "</div>";
        echo "<div class='link'>/" . ucfirst( trim( $row['last'] ) ) . "/" . $row['admin_id'] . "</div>";
        echo "<img src='images/ChidonDrive-flyer-D3-no-link-2.jpg' /></div>";
        ?>
        <div class="letter">
          <img src='images/Chidon-Header-5779-01.jpg' />

          <div class="content">
          <h2>Thank you for being such a great partner!</h2>
            Dear <?=$row['Afirst'] . ' ' . $row['last']?>,
            <br /><br />
            I would like to thank you. Your child <?=$row['first']?> has gained so much from learning with you and we truly recognize the time, effort, and encouragement you invested. They could not have done this without you, and for this, you deserve a chidon medal.<br /><br />
            When I helped restart Chidon six years ago, who would have believed the revolution in chinuch it would inspire? That it would so capture the hearts and minds of thousands of children worldwide? Thousands of children today voluntarily sacrifice their time and comfort to toil in Torah to master the 613 mitzvos, and spread that passion to those around them. They know details of the mitzvos that even their parents and teachers don’t know!<br /><br />
            Indeed, after six years of growing Chidon, the results are in: The participants’ voluntary learning fosters  study skills and a foundation of Torah knowledge they will build on for the rest of their lives, including (but certainly not limited to) the study of Rambam—cultivating greater Hiskashrus to the Rebbe and the development of their Yiras Shomayim. These are the children on the front lines to greet Moshiach and are truly prepared for the day when we will merit the fulfillment of all 613 Mitzvos, bekarov mamosh.
            <br /><br />
            Whether they made it to the Shabbaton or not, they already achieved the ultimate success. To me, to you, to all of their mechanchim, they are all truly winners.<br /><br />
            Wishing you yiddishe and chassidishe nachas, always,<br /><br />
            Rabbi Shimmy Weinbaum<br />
            Tzivos Hashem
          </div>
        </div>
        <div style='page-break-after: always'></div>
      <?php break;} ?>
    </div>
  </body>
</html>