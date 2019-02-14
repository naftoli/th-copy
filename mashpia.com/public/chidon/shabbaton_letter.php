<?php
$admin_auth = ['school'];
require_once '../header.php';

require_once '../class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$users = [];
$sql = "
  SELECT 
      u.first, aa.admin_id, a.last, s.school_name, c.class_grade, c.class_sub 
  FROM
      users u
          JOIN
      th_chidon tc USING (user_id)
          JOIN
      admin_auths aa ON aa.id = tc.user_id
          JOIN
      admins a USING (admin_id)
          JOIN
      schools s on s.school_id = tc.school_id 
          JOIN 
      classes c on c.class_id = u.class_id 
  WHERE
      tc.year = 5779
          AND (tc.contestant = 1 OR tc.school_rep = 1)
          AND tc.school_id in (" . implode(',', array_keys( $schools )) . ")
          ORDER BY s.school_name, c.class_grade, c.class_sub          
";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $grade = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
  $users[$row['school_name']][$grade][] = $row;
}
//echo "<pre>"; print_r( $users ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <style>
      body {
        width: 8in;
      }
      .letter {
        margin-top: 50px;
        font-size: 16px;
        line-height: 1.4;
        padding: 20px;
        width: 8in;
      }
      .schoolInfo {
        font-size: 72px;
        text-align: center;
        margin-top: 40%;
      }
    </style>
  </head>
  <body>
    <?php 
    $i = 0;
    $prevSchool = '';
    $prevGrade = '';
    foreach ( $users as $school => $other ) {
      foreach ( $other as $grade => $children ) {
        foreach ( $children as $row ) {
          if ( $school != $prevSchool ) {
            echo "<div class='schoolInfo'>" . $school . "<br />";
            echo "Grade: " . $grade . "</div><div style='page-break-after: always;'></div>";
            $prevSchool = $school;
          } else if ( $grade != $prevGrade ) {
            echo "<div class='schoolInfo'>" . $school . "<br />";
            echo $grade . "</div><div style='page-break-after: always;'></div>";
            $prevGrade = $grade;
          }
          $child = $row['first'];
          $admin = $row['admin_id'];
          $last = $row['last'];
          $link = "www.ChidonDrive.com/" . $last . "/" . $admin;
          ?>
          <div class="letter">
            Dear Parents of <strong><?=$child?></strong>,<br /><br />
            I’m so proud to inform you that your child[ren] passed all three qualifying tests and [are/is] eligible to join the International Chidon Shabbaton. You have seen firsthand the impact of Chidon, and are surely receiving much nachas from seeing all the time invested and the knowledge they’ve gained.<br /><br />
            Over the past six years, the Chidon Shabbaton has Boruch Hashem exploded from 45 to 2,500 children. Our dream is that the Chidon will continue to grow in leaps and bounds until every Jewish child is passionately studying Hashem’s 613 mitzvos in their spare time, just like yours.<br /><br />
            This <strong>Monday, Yud Gimmel Adar 1 (February 18), enrollment opens</strong> for the incredible four-day Chidon experience that drives our children to accomplish the unfathomable.<br /><br />
            While the actual cost for the Shabbaton is $350 per child, to make it as affordable as possible, Tzivos Hashem charges only $150 for enrollment, and is responsible for covering the remainder. We also understand that even this amount is a lot for most of our parents. Especially those with multiple children attending Chidon, and for those who must also pay travel costs.<br /><br /> 
            Last year there were over 150 children who were eligible to join the Shabbaton, but could not come due to the cost. Since Chidon promotes <strong>every child every mitzvah</strong>, no child should be held back from the Shabbaton for financial reasons. So we have created the <strong>ChidonDrive</strong>.<br /><br />
            The drive includes a personal page for each family. Share your link with family and friends. Tell them how the Chidon has impacted your home and ask that they support the Torah learning of your amazing children. <strong>ChidonDrive</strong> is also designed to enable Chidon partners and community members to help cover the cost of the overhead for children in their local schools.<br /><br />
            Here is your link: [<a href="https://<?=$link?>"><?=$link?></a>].<br /><br />
            As of today, Headquarters has secured a generous grant from Mr. George Rohr of $100 for 2,000 Chidon finalists.  You can enable this grant on your personal page. All funds donated there by your friends and family will be applied, until you are “filled up” and cover the entire cost, including the enrollment fee. In addition, as more community donations come in, they will be apportioned to your remaining costs.<br /><br />
            Your support in the endeavor of covering the cost of Shabbaton and the community’s partnership in Chidon, allowing it to thrive for grow, mean the world to me. Wishing you continued chassidishe nachas from your children,<br /><br />
            Rabbi Shimmy Weinbaum<br /><br />
            Tzivos Hashem Headquarters              
          </div>
          <div style="page-break-after: always;"></div>
          <?php
        }
      }
    }
    ?>
  </body>
</html>