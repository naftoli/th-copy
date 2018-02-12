<?php 
include_once ("../header.php");
require_once('../file_save.php');
$title ='Campaign Overview';
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_city, school_state, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color
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
include("includes/header.php"); 
include("includes/slider.php");
include("includes/scroll.php"); 
include("includes/spinner.php");

$subject_name = gr("subject", "");
?>
<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
        </div>
        <div id="main">
            <div id="page_title"><?=T_($subject_name)?> Campaign Overview</div>
            <div class="three_column padding_top">
              <div class="content">
                    <div id="slider">
                      <ul class="overview">
                            <li>
                                <div class="slider_title">Mission Tasks</div>
                                <div class="mainbox">
                                    <div class="col2_image"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                            <p>You must complete all of your tasks each week in order to complete your mission.</p>
                                            <p>Here are your tasks for this mission:</p>
                                            <div class="question icon_book">
                                                Say 3 Kapitlach of Tehillim.</div>
                                            <div class="question icon_stopwatch">
                                                Spend 10 minutes saying Tehillim.</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Missions</div>
                                <div class="mainbox">
                                    <div class="col2_image"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                        	<p>If you do your tasks for 4 weeks you have completed your mission.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">1</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">2</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">3</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">4</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">1</div>
                                                    <div class="date">Week 1-4</div>
                                                    <div class="date">ניסן תשעו</div>
                                                    <div class="meter" style="background-position:0 0;"></div>
                                                    <div class="check_on"></div>
                                                </div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Medals</div>
                                <div class="mainbox">
                                    <div class="task_items mission_boxes task_col">
                                        <div class="task">
                                            <div class="date">Week</div>
                                            <div class="number">1</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">2</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">3</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">4</div>
                                            <div class="math">=</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="mission">
                                          <div class="number">1</div>
                                            <div class="date">Week 1-4</div>
                                            <div class="date">ניסן תשעו</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="math_big">+</div>
                                  </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="task">
                                            <div class="date">Week</div>
                                            <div class="number">5</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">6</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">7</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">8</div>
                                            <div class="math">=</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="mission">
                                          <div class="number">2</div>
                                            <div class="date">Week 5-8</div>
                                            <div class="date">ניסן תשעו</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="math_big">+</div>
                                  </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="task">
                                            <div class="date">Week</div>
                                            <div class="number">9</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">10</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">11</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">12</div>
                                            <div class="math">=</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="mission">
                                          <div class="number">3</div>
                                            <div class="date">Week 9-12</div>
                                            <div class="date">ניסן תשעו</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="math_big">+</div>
                                  </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="task">
                                            <div class="date">Week</div>
                                            <div class="number">13</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">14</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">15</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">16</div>
                                            <div class="math">=</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="mission">
                                          <div class="number">4</div>
                                            <div class="date">Week 12-16</div>
                                            <div class="date">ניסן תשעו</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="math_big">+</div>
                                  </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="task">
                                            <div class="date">Week</div>
                                            <div class="number">17</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">18</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">19</div>
                                            <div class="math">+</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="task">
                                          <div class="date">Week</div>
                                            <div class="number">20</div>
                                            <div class="math">=</div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="mission">
                                          <div class="number">5</div>
                                            <div class="date">Week 17-20</div>
                                            <div class="date">ניסן תשעו</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                      </div>
                                        <div class="math_big">=</div>
                                  </div>
                                    <div class="overview_text">
                                    After completing 5 missions you deserve a medal.</div>
                                    <div class="medalImage medal11"></div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Miles</div>
                                <div class="mainbox">
                                    <div class="col2_image"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                        	<p>If you missed a mission task the mission will not be counted towards your next medal.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">1</div>
                                                    <div class="miles">5 Miles</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">2</div>
                                                    <div class="miles">5 Miles</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">3</div>
                                                    <div class="miles">2 Miles</div>
                                                    <div class="check_off"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Week</div>
                                                    <div class="number">4</div>
                                                    <div class="miles">5 Miles</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">1</div>
                                                    <div class="date">Week 1-4</div>
                                                    <div class="date">ניסן תשעו</div>
                                                    <div class="meter" style="background-position:25% 0;"></div>
                                                    <div class="check_off"></div>
                                                </div>
                                                <div class="clear"></div>
                                          </div>
                                            <p>You still get miles for any task that you did.</p> 
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Bonus Tasks</div>
                                <div class="mainbox">
                                    <div class="col2_image"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                            <p>Bonus tasks allow you to get extra mileage.</p>
                                            <p>Here are your bonus tasks for this mission:</p>
                                            <div class="question icon_building">
                                                Say it in Shul.
                                                <div class="miles">2 Miles</div></div>
                                            <div class="question icon_clock">
                                                Start on time.
                                                <div class="miles">1 Mile</div></div>
                                            <div class="question icon_megaphone">
                                                Hear all the words.
                                                <div class="miles">2 Miles</div></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
								<script type="text/javascript">
									<?php
										echo "var l = "; if ($_GET["l"]!='') {echo $_GET["l"].";"; } else {echo "0;";};
										echo "var y = "; if ($_GET["y"]!='') {echo $_GET["y"].";"; } else {echo "0;";};
									?>
                                    $(document).ready(function(){
                                        $("#spinner-ladder").spinner({
											 start:l-1,
										}).bind('spinchange', function(event, element, ui) {
											 spin_change("l",ui.value);								 
										});
                                        $("#spinner-year").spinner({
											 start:l-1,
										}).bind('spinchange', function(event, element, ui) {
											alert(ui.value+1);								 
										});
                                    });	
									
									var A = ["א-ב","א-ג","א-ד","א-ה","א-ו","א-ז","א-ח","א-ט","א-י","א-י''א"];
									var B = ["0:05","0:10","0:15","0:20","0:25","0:30","0:35","0:40","0:45","0:50"];
									var C = ["א-ב","א-ג","א-ד","א-ה","א-ו","א-ז","א-ח","א-ט","א-י","א-י''א"];
									var D = ["0:05","0:10","0:15","0:20","0:25","0:30","0:35","0:40","0:45","0:50"];
									var E = ["א-ב","א-ג","א-ד","א-ה","א-ו","א-ז","א-ח","א-ט","א-י","א-י''א"];
									var F = ["0:05","0:10","0:15","0:20","0:25","0:30","0:35","0:40","0:45","0:50"];
									
									function spin_change(w,v) {
										$("#ladder_this_1").html(A[v]);
										$("#ladder_this_2").html(B[v]);
										$("#ladder_year_1").html(C[v]);
										$("#ladder_year_2").html(D[v]);
										$("#ladder_average_1").html(E[v]);
										$("#ladder_average_2").html(F[v]);
									}
                                </script>
                                <div class="slider_title">Ladders</div>
                                <div class="mainbox">
                                	<p>Change the ladder and year to see how much you can accomplish.</p>
                                    <div class="ladder_box">
                                        For this mission:
                                        <div class="ladder_info_box">
                                            <div id="ladder_this_1" class="ladder_info icon_book">א-ב</div>
                                            <div id="ladder_this_2" class="ladder_info icon_stopwatch">0:10</div>
                                        </div>
                                        <div class="vertical">
                                            <div id="spinner-ladder" class="v-spinner">
                                                <div>Ladder 1</div>
                                                <div>Ladder 2</div>
                                                <div>Ladder 3</div>
                                                <div>Ladder 4</div>
                                                <div>Ladder 5</div>
                                                <div>Ladder 6</div>
                                                <div>Ladder 7</div>
                                                <div>Ladder 8</div>
                                                <div>Ladder 9</div>
                                                <div>Ladder 10</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        Goal for end of year:
                                        <div class="ladder_info_box">
                                            <div id="ladder_year_1" class="ladder_info icon_book">א-י</div>
                                            <div id="ladder_year_2" class="ladder_info icon_stopwatch">0:20</div>
                                        </div>
                                        <div class="vertical">
                                            <div id="spinner-year" class="v-spinner">
                                                <div>Year 1</div>
                                                <div>Year 2</div>
                                                <div>Year 3</div>
                                                <div>Year 4</div>
                                                <div>Year 5</div>
                                                <div>Year 6</div>
                                                <div>Year 7</div>
                                                <div>Year 8</div>
                                                <div>Year 9</div>
                                                <div>Year 10</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        My Average:
                                        <div class="ladder_info_box">
                                            <div id="ladder_average_1" class="ladder_info icon_book">א-ג</div>
                                            <div id="ladder_average_2" class="ladder_info icon_stopwatch">0:15</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        Campaign goal:
                                        <div class="ladder_text">Say entire Tehillim</div>
                                        To be completed by:
                                        <div class="ladder_text ladder_date">
                                            9th Grade - חשון תתפ"ג
                                        </div>
                                    </div>
                                </div>
                            </li>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
