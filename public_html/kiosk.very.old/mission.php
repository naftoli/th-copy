<?php
include_once ("../header.php");
require_once('../file_save.php');
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, 
       rank_ord, rank_name, rank_image_id, rank_color
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));
$title ='Tasks';
include_once("includes/header.php");
?>
<link href="styles/stylem.css" rel="stylesheet" type="text/css" />
<?php include_once("includes/slider.php"); ?>

<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Upcoming Missions</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div id="slider">
                      <ul class="upcoming">
                            <li>
                            	<div class="boxes">
                                    <div class="icon medal11"></div>
                                    <div class="mission_group">
                                    <div class="mission">
                                        <div class="number">17</div>
                                        <DIV class=date>Week 5-8</DIV>
  <DIV class=date>???? ????</DIV>
  <DIV style="BACKGROUND-POSITION: 20% 0px" class=meter></DIV>
                                        <a href="mission_report.php"></a>
                                    </div>
                                    <div class="mission">
                                        <div class="number">18</div>
  <DIV class=date>Week 9-12</DIV>
  <DIV class=date>???? ????</DIV>
  <DIV style="BACKGROUND-POSITION: 0px 0px" class=meter></DIV>
                                        <a href="mission_report.php"></a>
                                    </div>
                                    </div>
                                    <div class="icon medal32"></div>
                                    <div class="mission_group">
                                    <div class="mission">
                                        <div class="number">17</div>
                                        <DIV class=date>Week 5-8</DIV>
  <DIV class=date>???? ????</DIV>
  <DIV style="BACKGROUND-POSITION: 20% 0px" class=meter></DIV></div>
                                        <a href="mission_report.php"></a>
                                    </div>
                                    </div>
                                </div>
                            </li>
                      </ul>
                    </div>
              </div>
              <div class="content_nav">
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
