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

//if ($_GET["enrolled"]!='') {$enrolled=$_GET["enrolled"]; };

$subject_name = gr("subject", "");
$campaign_row = mysql_fetch_assoc(mq("SELECT subject_id, subject_gold_image_id, subject_slogan, subject_description, subject_commitments FROM subjects WHERE subject_name = '$subject_name' "));
$enrolled_row = mysql_fetch_assoc(mq("SELECT enrolled FROM user_tracks WHERE user_id = {$user['user_id']} and subject_id = {$campaign_row['subject_id']}"));
$enrolled = ($enrolled_row["enrolled"]==1);
?>
<!--?php include("includes/slider.php"); ?-->
<?php include("includes/scroll.php"); ?>
<?php include("includes/spinner.php"); ?>

<?php
$c_array=array(
	array("0","1",
	"1"),//0
	array("Aleph Champs","1",
	"1"),//1
	array("Chitas","2",
	"2"),//2
	array("Avos Ubonim","To help parents and children bond and grow together through reviewing what they learned in school for 15 minutes each week.",
	41), //3/
	array("V'holacto Bidrochov","To explore the positive Midos of the Avos and Imohos and demonstrate them in our daily lives, adding a new Midda each month.",
	42), //4/
	array("Gemara","5",
	"5"),//5
	array("Hiskashrus","To strengthen our bond with the Rebbe by relating Sichos and stories of the Rebbe, singing his Niggunim, saying his Kapitel and watching Rebbe videos.",
	16), //6/
	array("Mashpia","7",
	"7"),//7
	array("Mishnayos Baal Peh","8",
	17), //8
	array("Mivtzoim","To act in a way that goes beyond fighting one’s own Yetzer Hora, by helping fellow Yidden—in class, at home or around the world.",
	12), //9/
	array("Moshiach","10",
	19),//10
	array("Niggunim","To gain a meaningful appreciation for Chassidishe Niggunim, by learning songs and their stories to be sung in school and at the Shabbos table.",
	13),//11/
	array("Tanya Baal Peh","To assist you in studying the maximum possible amount of Tanya Baal Peh, through a unique method of bite-sized memory exercises and audio CD companions.",
	27),//12/
	array("Tefilla","13",
	4), //13
	array("WWTC","Train yourself to say the entire Tehillim before Davening each Shabbos Mevorchim, (per the Frierdiker Rebbe’s special Takono). Climb a personal ladder that helps you reach you goal at your own pace.","...Saying the entire Tehillim on Shabbos Mevorchim is crucial for you, for your children and your children's children!<span>-HaYom Yom 25 Shevat</span>",
	1), //14/
	array("Yomei Dipagra","To celebrate Yomim Tovim—general and Chassidishe—according to Chassidus, and to bring the appropriate Chassidishe conduct home through mini-Farbrengens, Niggunim, stories and missions",
	40),//15/
	array("Hakhel","16",
	15) //16
);

$backmap = array(
                1=>14,
                4=>13,
                12=>9,
                13=>11,
                15=>16,
                16=>6,
                17=>8,
                19=>10,
                27=>12,
                40=>15,
                41=>3,
                42=>4);

	if ($_GET["c"]!='') {$c=$_GET["c"]; };
?>


<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
        </div>
        <div id="main">
            <div id="page_title"><?=T_($subject_name).' '.T_('Campaign Overview')?></div>
            <div class="three_column padding_top">
              <div class="content">
		<div class="sticker">Sample</div>
                    <div id="slider">
                    <ul class="overview">
                            <li>
                                <div class="slider_title"><?php if($campaign_row['subject_id']==27){?>Overview - Yearly Quota<?php }else{?>Overview - Mission Tasks<?php }?></div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_taskslist"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<?php switch ($campaign_row['subject_id']) {
											    case 1:?>
                                            <p>Sample <?=T_($subject_name)?> mission tasks:</p>
                                            <div class="question icon_book">
                                                Say 3 Kapitlach of Tehillim.</div>
                                            <div class="question icon_stopwatch">
                                                Spend at least 10 minutes saying them.</div>
                                            <p>Note: Choose personal quota options in the "Ladders" section.</p>
											<? break;
											case 12:?>
                                            <p>Sample <?=T_($subject_name)?> mission tasks:</p>
                                            <div class="question icon_shofar">
                                                Help another Yid hear the Shofar.</div>
                                            <div class="question icon_lulav">
                                                Help another Yid shake the Lulav and Esrog.</div>
                                            <p>Please Note: You must complete all your mission tasks in order to complete your mission.</p>
											<? break;
												case 13:?>
                                            <p>Sample <?=T_($subject_name)?> mission task:</p>
                                            <div class="question icon_trumpet">
                                                Choose one of the following Niggunim to sing at your Shabbos table:
												<div class="col2_list">
													<div>Rachamana De'onei.</div>
													<div>Niggun D'veykus.</div>
													<div>Ato V'chartanu</div>
												</div>
											</div>
                                            <p>Note: You must complete your mission task to complete your mission.</p>
											<? break;
											case 16:?>
                                            <p>Sample <?=T_($subject_name)?> mission tasks:</p>
                                            <div class="question icon_book">
                                                Relate (or hear) a Dvar Torah from the Rebbe at your Shabbos table.</div>
                                            <p>Note: You must complete all mission tasks to complete your <?=T_($subject_name)?> mission.</p>
											<? break;
											case 27:?>
                                            <p>Sample <?=T_($subject_name)?> quota:</p>
                                            <div class="question icon_book">
                                                Memorize 8 lines of Tanya each year.</div>
                                            <p>You will choose how much Tanya to memorize.<br />
											See “Ladders” for more.</p>
											<? break;
												
											case 40:?>
                                            <p>Sample <?=T_($subject_name)?> mission tasks:</p>
                                            <div class="question icon_table">
                                                Help prepare, serve or cleanup the Yom Tov Seuda.</div>
                                            <div class="question icon_mic">
                                                Tell (or listen) what happened on Hey Teves (5747).</div>
                                            <div class="question icon_violin">
                                                Sing the Niggun Podo Vesholom on Yud Tes Kislev.</div>
                                            <p>Please Note: You must complete all mission tasks in order to complete your mission.</p>
											<? break;
												case 41:?>
                                            <p>Your <?=T_($subject_name)?> mission task is to:</p>
                                            <div class="question icon_book">
                                                Learn with a parent or grandparent for at least one 15 minute period each week.</div>
                                            <p>Note: You must complete your mission task to complete your mission.</p>
											<? break;
												case 42:?>
                                            <p>Sample <?=T_($subject_name)?> mission tasks:</p>
                                            <p class="small">Sample Theme (Midda): Hakoras Hatov - Gratitude</p>
                                            <div class="question icon_ask">
                                                Ask yourself: What do I do when someone opens a door for me?</div>
                                            <div class="question icon_smile">
                                                Decide: I will always smile and say "Thank You" when someone opens a door for me.</div>
                                            <div class="question icon_thumbs_up">
                                                Keep it up: Work on your new Hachloto in addition to keeping your previous Hachlotos.</div>
											<? break;
												
												
												
												default:
													echo("Error displaying data!");
													break;
											} ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<?php switch ($campaign_row['subject_id']) {
								case 27:?>
                            <li>
                                <div class="slider_title">Overview - Missions</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_taskslist"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<p>Your yearly quota of 8 lines will be broken into 4 missions.</p>
											<div class="task_items mission_boxes task_col">
												<div class="mission">
													<div class="number">#1</div>
													<div class="date">2 Lines</div>
												</div>
											</div>
											<div class="task_items mission_boxes task_col">
												<div class="mission">
													<div class="number">#2</div>
													<div class="date">2 Lines</div>
												</div>
											</div>
											<div class="task_items mission_boxes task_col">
												<div class="mission">
													<div class="number">#3</div>
													<div class="date">2 Lines</div>
												</div>
											</div>
											<div class="task_items mission_boxes task_col">
												<div class="mission">
													<div class="number">#4</div>
													<div class="date">2 Lines</div>
												</div>
											</div>
										</div>
									</div>
								</div>
                            </li>
							<? break;
							} ?>
							<?php switch ($campaign_row['subject_id']) {
								case 1:
								case 27:
								case 42:?>
                            <li>
                                <div class="slider_title">Overview - Completing Your Missions</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_taskslist"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<?php switch ($campaign_row['subject_id']) {
												case 42:?>
                                            <p>You must do all of the following to complete your mission:</p>
											<div class="question icon_review">
                                                Review your evaluation sheet for that Midda.</div>
                                            <div class="question icon_new">
                                                Choose a new Hachloto for this month.</div>
                                            <div class="question icon_previous">
                                                Make sure to keep your previous Hachlotos.</div>
											<? break;
												case 27:?>
                                            <p>To complete each mission, you will be<br />
tested on everything you already know <br />
PLUS your new lines.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="number">2</div>
                                                    <div class="date">Old</div>
                                                    <div class="date">Lines</div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="number">2</div>
                                                    <div class="date">New</div>
                                                    <div class="date">Lines</div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">4</div>
                                                    <div class="date">Lines</div>
                                                    <div class="date">Tested</div>
                                                    <div class="meter" style="background-position:0 0;"></div>
                                                    <div class="check_on"></div>
                                                </div>
                                          	</div>
											<? break;
												case 1: ?>
                                            <p>You must complete all mission tasks each month in order to complete your mission.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="date">Quota</div>
                                                    <div class="number">3</div>
                                                    <div class="date">Kapitlach</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Quota</div>
                                                    <div class="number">0:10</div>
                                                    <div class="date">minutes</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">1</div>
                                                    <div class="date">שבת מברכים</div>
                                                    <div class="date">חשון תשע</div>
                                                    <div class="meter" style="background-position:0 0;"></div>
                                                    <div class="check_on"></div>
                                                </div>
                                          	</div>
											<div class="clear"></div>
                                            <p>If you finish your Tehillim quota before your time quota, continue saying extra Tehillim until you complete your time quota.</p>
											<? break;
												default:
													echo("Error displaying data!");
													break;
											} ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<? break;
							} ?>
                            <li>
                                <div class="slider_title">Overview - Earning Medals</div>
                                <div class="mainbox">
									<?php switch ($campaign_row['subject_id']) {
										case 12:?>
									<p>After completing your first 5 missions, you'll earn your first medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#1</div>
                                            <div class="date">מבצע נש"ק</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#2</div>
                                            <div class="date">מבצע שופר</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#3</div>
                                            <div class="date">מבצע לולב</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#4</div>
                                            <div class="date">מבצע חנוכה</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#5</div>
                                            <div class="date">מבצע בית מלא ספרים</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">5</span></div>
									
									<p>After completing your next 6 missions, you'll earn your second medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#6</div>
                                            <div class="date">מבצע חדר צבאות ה'</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#7</div>
                                            <div class="date">מבצע פורים</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#8</div>
                                            <div class="date">מבצע יום הולדת</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#9</div>
                                            <div class="date">מבצע פסח</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#10</div>
                                            <div class="date">מבצע לג בעומר</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#11</div>
                                            <div class="date">מבצע שבועות</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">6</span></div>
									<? break;
										case 13:
										case 16:
										case 40:
										case 41:
										case 42:?>
										
										<?php if ($campaign_row['subject_id'] == 40) { ?>										
											<p>After completing your first 10 missions, you'll earn your first medal.</p>
										<?php } else { ?>
											<p>After completing your first 15 missions, you'll earn your first medal.</p>
										<?php } ?>
										
                                    <div class="task_items_box small">
										<?php 
											
											if ($campaign_row['subject_id'] == 40) {
												$parsha = Array("ראש השנה", "ראש השנה", "ימי תשובה", "ערב יום כיפור", "יום כיפור", "י''ג תשרי", "סוכות", "חול המועד", "שמיני עצרת", "ז' חשון", "י''א חשון", "כ' חשון", "כ''ה חשון", "ר''ח כסלו", "ב' כסלו", "ט' כסלו", "'י כסלו", "י''ד כסלו", "י''ט כסלו", "כ' כסלו", "חנוכה", "ה' טבת", "עשרה בטבת", "כ' טבת", "כ''ד טבת");
												$maxCount = 10;
											}
											else {
												$parsha = Array("בראשית","נח","לך לך","וירא","חיי שרה","תולדות","ויצא","וישלח","וישב","מקץ","ויגש","ויחי","שמות","וארא","בא","בשלח","יתרו","משפטים","תרומה","תצוה","כי תשא","ויקהל","פקודי","ויקרא","צו","שמיני","תזריע","מצורע","אחרי מות","קדושים","אמור","בהר סיני","בחוקותי","במדבר","נשא","בהעלותך","שלח לך","קרח","חקת","בלק","פינחס","מטות","מסעי","דברים","ואתחנן","עקב","ראה","שופטים","כי תצא","כי תבוא","ניצבים","וילך","האזינו","וזאת הברכה");
												$maxCount = 15;
											}

											for ($i = 0; $i < $maxCount; $i++) { ?>
										<div class="task_items mission_boxes task_col">
											<div class="mission">
												<div class="number">#<?=($i + 1)?></div>
												<?php if ($campaign_row['subject_id'] <> 40) { ?>
												<div class="date">פרשת</div>
												<?php } ?>
												<div class="date"><?=$parsha[$i]?></div>
												<div class="check_on"></div>
											</div>
											<div class="math_big"><? ($i==($maxCount - 1) ? print'=' : print'+')?></div>
										</div>
										<? } ?>
									</div>
									<div class="math_big"></div>	

									<?php if ($campaign_row['subject_id'] == 40) { ?>
										<div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">10</span></div>
									<?php } else { ?>
										<div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">15</span></div>
									<?php } ?>
									
									<? break;
										case 27:?>
									<p>After completing your first 4 missions, you'll earn your first medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#1</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">2 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#2</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">4 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#3</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">6 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#4</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">8 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">4</span></div>
									<div class="clear"></div>
									
									<p>After completing your next 4 missions, you'll earn your second medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#5</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">10 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#6</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">12 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#7</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">14 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#8</div>
                                            <div class="date">2 Lines</div>
                                            <div class="date">16 Total</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">4</span></div>
									<? break;
										case 1:?>
									<p>After completing your first 5 missions, you'll earn your first medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#1</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">חשון תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#2</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">כסלו תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#3</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">טבת תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#4</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">שבט תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#5</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">אדר תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">5</span></div>
									
									<p>After completing your next 6 missions, you'll earn your second medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#6</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">ניסן תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#7</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">אייר תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#8</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">סיון תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#9</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">תמוז תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#10</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">אב תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">+</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#11</div>
                                            <div class="date">שבת מברכים</div>
                                            <div class="date">אלול תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">6</span></div>
									<? break;
										case 15:
										//case 40:
										    ?>
									<p>After completing your first 10 missions, you'll earn your first medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#1</div>
                                            <div class="date">חשון תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">x</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">10</div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">10</span></div>
									<div class="clear"></div>
									
									<p>After completing your next 15 missions, you'll earn your second medal.</p>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">#11</div>
                                            <div class="date">חשון תשע</div>
                                            <div class="meter" style="background-position:0 0;"></div>
                                            <div class="check_on"></div>
                                        </div>
                                        <div class="math_big">x</div>
                                    </div>
                                    <div class="task_items mission_boxes task_col">
                                        <div class="mission">
                                            <div class="number">15</div>
                                        </div>
                                        <div class="math_big">=</div>
                                    </div>
                                    <div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">15</span></div>
									<? ;
											break;
										default:
											echo("Error displaying data!");
											break;
									} ?>
                                </div>
                            </li>
							<?php switch ($campaign_row['subject_id']) {
										case 13:
										case 16:
										case 40:
										case 41:
										case 42:?>
                            <li>
                                <div class="slider_title">Overview - Earning Medals</div>
                                <div class="mainbox">
								
									<?php 
										if ($campaign_row['subject_id'] == 40) { 
											$start = 10;
											$end = 24;
										}
										else {
											$start = 16;
											$end = 35;
										}
									?>
								
									<?php if ($campaign_row['subject_id'] == 40) { ?>
										<p>After completing your next 15 missions, you'll earn your second medal.</p>										
									<?php } else { ?>
										<p>After completing your next 20 missions, you'll earn your second medal.</p>
									<?php } ?>
									
                                    <div class="task_items_box small">
										<?php for ($i = $start; $i <= $end; $i++) { ?>
										<div class="task_items mission_boxes task_col">
											<div class="mission">
											
												<?php if ($campaign_row['subject_id'] == 40) { ?>
													<div class="number">#<?=($i + 1)?></div>
												<?php } else { ?>
													<div class="number">#<?=$i?></div>
												<?php } ?>
												
												<?php if ($campaign_row['subject_id'] <> 40) { ?>
												<div class="date">פרשת</div>
												<?php } ?>
												<div class="date"><?=$parsha[$i]?></div>
												<div class="check_on"></div>
											</div>
											<div class="math_big"><? ($i==$end ? print'=' : print'+')?></div>
										</div>
										<? } ?>
									</div>
									
									<?php if ($campaign_row['subject_id'] == 40) { ?>
										<div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">15</span></div>
									<?php  } else { ?>
										<div class="medalImage medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">20</span></div>
									<?php } ?>
									
                                </div>
                            </li>
							<? break;
							} ?>
                            <li>
                                <div class="slider_title">Overview - Earning Medals</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_medals"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<?php switch ($campaign_row['subject_id']) {
												case 12://9
												case 1://14?>
                                        	<p>You can earn up to 10 medals for <?=T_($subject_name)?>!</p>
                                            <div class="medals">
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">5</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">6</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>3"><span class="badge">7</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>4"><span class="badge">8</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>5"><span class="badge">9</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>6"><span class="badge">10</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>7"><span class="badge">11</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>8"><span class="badge">12</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>9"><span class="badge">13</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>10"><span class="badge">14</span></div>
                                            </div>
                                        	<p>Total: 94 Missions</p>
											<? break;
												case 41:
												case 42:
												case 16:
												case 13:?>
                                        	<p>You can earn up to 10 medals<br />
												for <?=T_($subject_name)?>!</p>
                                            <div class="medals">
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">15</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">20</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>3"><span class="badge">25</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>4"><span class="badge">30</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>5"><span class="badge">35</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>6"><span class="badge">40</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>7"><span class="badge">45</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>8"><span class="badge">50</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>9"><span class="badge">55</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>10"><span class="badge">60</span></div>
                                            </div>
                                        	<p>Total: 375 Missions</p>
											<? break;
												case 27:?>
                                        	<p>You can earn up to 8 medals<br />
												for Tanya Baal Peh!</p>
                                            <div class="medals">
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>3"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>4"><span class="badge">4</span></div>
                                            </div>
                                            <div class="medals">
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>5"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>6"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>7"><span class="badge">4</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>8"><span class="badge">4</span></div>
                                            </div>
                                        	<p>Sample Total: 32 Missions - 63 Lines - 'פרק א</p>
											<? break;
												case 40:?>
                                        	<p>You can earn up to 10 medals for Yomei Dipagra!</p>
                                            <div class="medals">
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>1"><span class="badge">10</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>2"><span class="badge">15</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>3"><span class="badge">30</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>4"><span class="badge">45</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>5"><span class="badge">60</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>6"><span class="badge">75</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>7"><span class="badge">90</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>8"><span class="badge">105</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>9"><span class="badge">120</span></div>
                                                <div class="medal<?=$backmap[$campaign_row['subject_id']]?>10"><span class="badge">135</span></div>
                                            </div>
                                        	<p>Total: 685 Missions</p>
											<? break;
												default:
													echo("Error displaying data!");
													break;
											} ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<?php switch ($campaign_row['subject_id']) {
								case 27:?>
                            <li>
                                <div class="slider_title">Overview - Earning Medals</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_medals_ladders"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<p>Please Note: You can ONLY earn one medal per year.</p>
											<p>If you can learn more lines during the year you should request a ladder upgrade!</p>
										</div>
									</div>
								</div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Getting Tested</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_test"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                        	<p class="dashed_bottom">Every time you memorize a line, scan that line from your Tanya Barcode Sheet.</p>
                                        	<p>Once you complete a mission, you must be tested before you can scan new lines.</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<? break;
							} ?>
                            <li>
                                <div class="slider_title">Overview - Miles for Mission Tasks</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_mileage"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<?php switch ($campaign_row['subject_id']) {
												case 41:
												case 42:
												case 16:
												case 12:
												case 13:
												case 40:?>
												
												<?php if ($campaign_row['subject_id'] == 13 || $campaign_row['subject_id'] == 16 || $campaign_row['subject_id'] == 41 || $campaign_row['subject_id'] == 42) { ?>
													<!--<p>If you miss a mission task, you will still earn miles for each task that you did complete. But this mission will NOT count toward your next medal.</p>-->
													<p>If you miss a mission task, you will still earn miles for each task that you did complete.</p>
												<? } else { ?>
													<p>If you miss a mission task, you will still earn miles for each task that you did complete.</p>
												<? }?>
											
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="date">Task</div>
                                                    <div class="number">1</div>
                                                    <div class="miles"><? if ($campaign_row['subject_id']==12 || $campaign_row['subject_id']==40 || $campaign_row['subject_id']==13){?>1 Mile<? }else if ($campaign_row['subject_id']==16 || $campaign_row['subject_id']==41){?>2 Miles<? }else{?>5 Miles<? }?></div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="date">Task</div>
                                                    <div class="number">2</div>
                                                    <div class="miles">1 Mile</div>
                                                    <div class="check_off"></div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">#5</div>
                                                    <div class="date"><? if ($campaign_row['subject_id']==15){?>חנוכה תשע<? }else if ($campaign_row['subject_id']==13||$campaign_row['subject_id']==41||$campaign_row['subject_id']==42||$campaign_row['subject_id']==16){?>פרשת וירא<? }else{?>אדר תשע<? }?></div>
                                                    <div class="meter" style="background-position:25% 0;"></div>
                                                    <div class="miles"><? if ($campaign_row['subject_id']==12 || $campaign_row['subject_id']==40){?>2 Miles<? }else if ($campaign_row['subject_id']==16 || $campaign_row['subject_id']==41){?>3 Miles<? }else{?>6 Miles<? }?></div>
                                                    <div class="check_off"></div>
                                                </div>
												<div class="clear"></div>
											</div>
											<p>This mission will NOT count toward your next medal.</p>
											<? break;
												case 27:?>
                                        	<p>You will receive 25 miles when you complete a mission.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="mission">
                                                    <div class="number">#1</div>
                                                    <div class="date">2 Lines</div>
                                                    <div class="date">25 Miles</div>
                                                    <div class="meter" style="background-position:0 0;"></div>
                                                    <div class="check_on"></div>
                                                </div>
											</div>
											<? break;
												case 1:?>
                                        	<p>If you miss a mission task, you will still earn miles for each task that you did complete.</p>
                                            <div class="task_items mission_boxes task_row">
                                                <div class="task">
                                                    <div class="number">3</div>
                                                    <div class="date">Kapitlach</div>
                                                    <div class="miles">5 Miles</div>
                                                    <div class="check_on"></div>
                                                </div>
                                                <div class="math_big">+</div>
                                                <div class="task">
                                                    <div class="number">0:06</div>
                                                    <div class="date">minutes</div>
                                                    <div class="miles">1 Mile</div>
                                                    <div class="check_off"></div>
                                                </div>
                                                <div class="math_big">=</div>
                                                <div class="mission">
                                                    <div class="number">#5</div>
                                                    <div class="date">שבת מברכים</div>
                                                    <div class="date">אדר תשע</div>
                                                    <div class="meter" style="background-position:25% 0;"></div>
                                                    <div class="miles">6 Miles</div>
                                                    <div class="check_off"></div>
                                                </div>
												<div class="clear"></div>
											</div>
                                        	<p>This mission will NOT count toward your next medal.</p>
											<? break;
												default:
													echo("Error displaying data!");
													break;
											} ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Miles for Bonus Tasks</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_mileage"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                            <p>You can also earn miles for bonus tasks.</p>
											<?php switch ($campaign_row['subject_id']) {
												case 41:?>
                                            <p>Bonus Task:</p>
											<div class="question icon_book">
												Learn with a parent or grandparent for an additional 10 minutes during the week.<br />
(This can be broken up, e.g., 2 minutes a day, 5 minutes in two days, etc.)
													<span class="miles">2 Miles</span>
											</div>
											<? break;
												case 42:?>
                                            
                                            <p>Choose one of the following to describe being grateful: <span class="small">Sample Theme: "Hakoras Hatov - Gratitude"</span></p>
											<div class="col2_list small">
												<div>Draw a picture.
													<span class="miles">2 Miles</span></div>
												<div>Write an essay.
													<span class="miles">2 Miles</span></div>
												<div>Create a comic.
													<span class="miles">2 Miles</span></div>
												<div>Design a PowerPoint.
													<span class="miles">2 Miles</span></div>
												<div>Compose a song.
													<span class="miles">2 Miles</span></div>
												<div>Write a poem.
													<span class="miles">2 Miles</span></div>
											</div>
											<? break;
												case 16:?>
                                            <p>Sample Bonus Tasks:</p>
											<div class="col2_list small">
												<div>Relate (or hear) a story of the Rebbe at your Shabbos table.
													<span class="miles">4 Miles</span></div>
												<div>Watch a video of the Rebbe.
													<span class="miles">2 Miles</span></div>
												<div>Sing one of the Rebbe's Niggunim at your Shabbos table.
													<span class="miles">2 Miles</span></div>
												<div>Say the Rebbe's Kapital every day.
													<span class="miles">2 Miles</span></div>
												<div>Say "Horachamon..." in Bentching each time you Bentch.
													<span class="miles">2 Miles</span></div>
											</div>
											<? break;
												case 12:?>
                                            <p>Sample Bonus Tasks:</p>
                                            <div class="question icon_lulav">
                                                Go on Mivtza Lulav a second time.
                                                <div class="miles">1 Mile</div></div>
											<? break;
												case 13:?>
                                            <p>Bonus Task:</p>
                                            <div class="question icon_music_notes">
                                                Explain the background of the Niggun that you are singing.
                                                <div class="miles">2 Miles</div></div>
											<? break;
												case 27:?>
                                            <p>Sample Bonus Tasks:</p>
                                            <div class="question icon_stopwatch">
                                                Learn 5 minutes each day.
                                                <div class="miles">½ Mile</div></div>
                                            <div class="question icon_stopwatch">
                                                Double Mile Bonus:<br />
												Learn 5 minutes every day of the week.
                                                <div class="miles">7 Miles</div></div>
											<? break;
												case 1:?>
                                            <p>Sample Bonus Tasks:</p>
                                            <div class="question icon_building">
                                                Say it in Shul.
                                                <div class="miles">2 Miles</div></div>
                                            <div class="question icon_clock">
                                                Say it before Davening.
                                                <div class="miles">1 Mile</div></div>
                                            <div class="question icon_megaphone">
                                                Say it loud enough to hear all the words.
                                                <div class="miles">2 Miles</div></div>
											<? break;
												case 40:?>
                                            <p>Sample Bonus Tasks:</p>
                                            <div class="question icon_dance">
                                                Take part in a Simchas Beis Hashoeiva.
                                                <div class="miles">2 Miles</div></div>
                                            <div class="question icon_walk">
                                                Go on Tahalucha.
                                                <div class="miles">1 Mile</div></div>
											<? break;
												default:
													echo("Error displaying data!");
													break;
											} ?>
                                            <p>These do not count toward medals.</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<?php switch ($campaign_row['subject_id']) {
								case 27:?>
                            <li>
                                <div class="slider_title">Overview - Choosing your Ladder</div>
                                <div class="mainbox">
                                    <div class="col2_image iconl_ladder"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
                                        	<p>You choose your own Tanya quota.</p>
                                        	<p>To help you choose your quota, ask yourself:</p>
											<div class="col2_list">
												<div>How many lines can I memorize each week?</div>
												<div>How many Perokim would I like to know when I graduate this campaign?</div>
											</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Sample Ladders</div>
                                <div class="mainbox ladder">
                                <script type="text/javascript">
                                    $(document).ready(function(){
                                        /*$("#spinner-ladder-sample,#spinner-ladder-sample2,#spinner-ladder-intro,#spinner-year-intro").spinner({
											start:0
										});	*/									
                                    });	
								</script>
	
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											The first ladder is:
											<div class="vertical">
												<div id="spinner-ladder-sample" class="v-spinner">
													<div class="active">Ladder 1</div>
												</div>
											</div>
                                            Your weekly quota will be:
											<div class="ladder_info icon_book">0.15 Lines</div>
											Your yearly quota will be:<br />
											<div class="ladder_info icon_book">8 Lines</div>
											After 8 years you will know:<br />
											<div class="ladder_info">63 Lines - 1 Perek</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											The highest ladder is:
											<div class="vertical">
												<div id="spinner-ladder-sample2" class="v-spinner">
													<div class="active">Ladder 20</div>
												</div>
											</div>
                                            Your weekly quota will be:
											<div class="ladder_info icon_book">9 Lines</div>
											Your yearly quota will be:<br />
											<div class="ladder_info icon_book">471 Lines</div>
											After 8 years you will know:<br />
											<div class="ladder_info">3769 Lines - 53 Perakim</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Sample Ladders</div>
                                <div class="mainbox ladder">
	
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											You will begin on
											<div class="vertical">
												<div id="spinner-ladder-intro" class="v-spinner">
													<div class="active">Ladder 1</div>
												</div>
											</div>
											Your yearly quota is:<br />
											<div class="ladder_info icon_book">8 Lines</div>
											Your weekly quota is:<br />
											<div class="ladder_info icon_book">0.15 Lines</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											By the end of:
											<div class="vertical">
												<div id="spinner-year-intro" class="v-spinner">
													<div class="active">Year 2</div>
												</div>
											</div>
											Your yearly quota will be:<br />
											<div class="ladder_info icon_book">8 New Lines</div>
											Your total will be:<br />
											<div class="ladder_info icon_book">16 Lines</div>
                                        </div>
                                    </div>
									<div class="ladder_box_bottom">
										<div class="ladder_box">
											<div class="icon icon_finish"></div>
											By Year 8 you will know:
											<div class="ladder_text ladder_date">
												63 Lines - 'פרק א
											</div>
										</div>
									</div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Sample Ladders</div>
                                <div class="mainbox ladder">
								<script type="text/javascript">
									var l=1;
									var y=0;
                                    $(document).ready(function(){
                                        $("#spinner-ladder").spinner({
											start:l
										}).bind('spinchange', function(event, element, ui) {
											spin_change("l",ui.value);								 
										});
                                        $("#spinner-year").spinner({
											start:y
										}).bind('spinchange', function(event, element, ui) {
											spin_change("y",ui.value);								 
										});
                                    });	
									
									var A = [8,16,20,30,36,42,53,59,66,73,78,88,98,106,112,131,187,274,365,471];


									function spin_change(w,v) {
										if (w=="l") {
											var prokim = new Array(
												Array(63,"א"),
												Array(126,"ב"),
												Array(162,"ג"),
												Array(233,"ד"),
												Array(285,"ה"),
												Array(341,"ו"),
												Array(422,"ז"),
												Array(469,"ח"),
												Array(528,"ט"),
												Array(584,"י"),
												Array(621,"יא"),
												Array(702,"יב"),
												Array(785,"יג"),
												Array(847,"יד"),
												Array(897,"טו"),
												//Array(1,"טז"),
												//Array(1,"יז"),
												Array(1050,"יח"),
												//Array(1,"יט"),
												//Array(1,"כ"),
												Array(1211,"כא"),
												Array(1255,"כב"),
												Array(1336,"כג"),
												Array(1425,"כד"),
												Array(1492,"כה"),
												Array(1554,"כו"),
												Array(1625,"כז"),
												Array(1667,"כח"),
												Array(1799,"כט"),
												Array(1863,"ל"),
												Array(1951,"לא"),
												Array(1991,"לב"),
												Array(2057,"לג"),
												Array(2103,"לד"),
												Array(2194,"לה"),
												Array(2254,"לו"),
												Array(2424,"לז"),
												Array(2528,"לח"),
												Array(2654,"לט"),
												Array(2765,"מ"),
												Array(2921,"מא"),
												Array(3054,"מב"),
												Array(3121,"מג"),
												Array(3209,"מד"),
												Array(3240,"מה"),
												Array(3337,"מו"),
												Array(3364,"מז"),
												Array(3453,"מח"),
												Array(3535,"מט"),
												Array(3575,"נ"),
												Array(3652,"נא"),
												Array(3735,"נב"),
												Array(3769,"נג"));
											for (i = 0; i < prokim.length; i++) {
												if ((A[v]*8)>prokim[i][0])perek=prokim[i][1];
											}
											$("#ladder_this_1").html(A[v] + " Lines");
											$("#ladder_this_2").html((A[v]/52).toFixed(2) + " Lines");
											$("#ladder_year_1").html(A[v] + " New Lines");
											$("#ladder_year_2").html(A[v]*(y+1) + " Lines");
											$("#ladder_complete_lines").html(A[v]*(8) + " Lines");
											$("#ladder_complete_perek").html(perek);
											l = v;
										} else if (w=="y") {
											$("#ladder_year_2").html(A[l]*(v+1) + " Lines");
											y = v;
										};
                                    };	
                                </script>
	
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											If you upgrade to:
											<div class="vertical">
												<div id="spinner-ladder" class="v-spinner">
													<div>Ladder 1</div>
													<div class="active">Ladder 2</div>
													<div>Ladder 3</div>
													<div>Ladder 4</div>
													<div>Ladder 5</div>
													<div>Ladder 6</div>
													<div>Ladder 7</div>
													<div>Ladder 8</div>
													<div>Ladder 9</div>
													<div>Ladder 10</div>
													<div>Ladder 11</div>
													<div>Ladder 12</div>
													<div>Ladder 13</div>
													<div>Ladder 14</div>
													<div>Ladder 15</div>
													<div>Ladder 16</div>
													<div>Ladder 17</div>
													<div>Ladder 18</div>
													<div>Ladder 19</div>
													<div>Ladder 20</div>
												</div>
											</div>
											Your yearly quota will be:<br />
											<div id="ladder_this_1" class="ladder_info icon_book">16 Lines</div>
											Your weekly quota will be:<br />
											<div id="ladder_this_2" class="ladder_info icon_book">0.3 Lines</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        <div class="ladder_info_box">
											By the end of:
											<div class="vertical">
												<div id="spinner-year" class="v-spinner">
													<div class="active">Year 1</div>
													<div>Year 2</div>
													<div>Year 3</div>
													<div>Year 4</div>
													<div>Year 5</div>
													<div>Year 6</div>
													<div>Year 7</div>
													<div>Year 8</div>
												</div>
											</div>
											Your yearly quota will be:<br />
											<div id="ladder_year_1" class="ladder_info icon_book">16 New Lines</div>
											Your total will be:<br />
											<div id="ladder_year_2" class="ladder_info icon_book">16 Lines</div>
                                        </div>
                                    </div>
									<div class="ladder_box_bottom">
										<div class="ladder_box">
											<div class="icon icon_finish"></div>
											By Year 8 you will know:
											<div class="ladder_text ladder_date">
												<span id="ladder_complete_lines">128 Lines</span> - פרק <span id="ladder_complete_perek">ב</span>
											</div>
										</div>
									</div>
                                </div>
                            </li>
                            <li>
                                <div class="slider_title">Overview - Upgrading your ladder</div>
                                <div class="mainbox ladder">
                                    <div class="col2_image iconl_average"></div>
                                    <div class="scroll-pane">
                                        <div class="col2_text">
											<p>The computer will show you an average of what you memorize each week to help you decide if you are ready to upgrade.</p>
											<div class="ladder_box">
												<div class="ladder_info_box">
													Your weekly quota:
													<div id="ladder_average_1" class="ladder_info icon_book">0.7 Lines</div>
												</div>
											</div>
											<div class="ladder_box">
												<div class="ladder_info_box">
													Your Average:
													<div id="ladder_average_1" class="ladder_info icon_book">2 Lines</div>
												</div>
											</div>
										</div>
									</div>
                                </div>
                            </li>
							<? break;
								case 1:?>
								
<!-- GC March 4 2010								
                            <li>
								<script type="text/javascript">
                                    $(document).ready(function(){
                                        $("#spinner-ladder-overview,#spinner-year-overview").spinner({
										 start:0,
										});
                                    });	
									
                                </script>
                                <div class="slider_title">Overview - Ladders</div>
                                <div class="mainbox ladder">
									<div class="ladder_box">
										<div class="icon icon_target"></div>
										Campaign goal:
										<div class="ladder_text ladder_date">Say entire Tehillim</div>
									</div>
									<div class="ladder_box">
										<div class="icon icon_finish"></div>
										You will reach your goal by:
										<div class="ladder_text ladder_date">
											9th Grade - חשון תתפ"ג
										</div>
									</div>
									<div class="ladder_box">
										You will begin<br />the campaign on:
										<div class="vertical">
											<div id="spinner-ladder-overview" class="v-spinner">
												<div class="active">Ladder 1</div>
											</div>
										</div>
										<div class="ladder_info_box">
											<div id="ladder_this_1" class="ladder_info icon_book">Saying <span class="ladder_bold">3</span> Kapitlach</div>
											<div id="ladder_this_2" class="ladder_info icon_stopwatch">for <span class="ladder_bold">0:10</span> minutes.</div>
										</div>
									</div>
									<div class="ladder_box">
										Your quota will grow every<br />month and by the end of:
										<div class="vertical">
											<div id="spinner-year-overview" class="v-spinner">
												<div class="active">Year 1</div>
											</div>
										</div>
										<div class="ladder_info_box">
											<div id="ladder_this_1" class="ladder_info icon_book">Saying <span class="ladder_bold">15</span> Kapitlach</div>
											<div id="ladder_this_2" class="ladder_info icon_stopwatch">for <span class="ladder_bold">0:25</span> minutes.</div>
										</div>
									</div>
                                </div>
                            </li>
							
							
                            <li>
								<script type="text/javascript">
							<?php
										// GC March 4 2010 echo "var l = "; if ($_GET["l"]!='') {echo $_GET["l"].";"; } else {echo "0;";};
										// GC March 4 2010 echo "var y = "; if ($_GET["y"]!='') {echo $_GET["y"].";"; } else {echo "0;";};
									?>
                                    $(document).ready(function(){
                                        $("#spinner-ladder").spinner({
											 start:l-1
										}).bind('spinchange', function(event, element, ui) {
											 spin_change("l",ui.value);								 
										});
                                        $("#spinner-year").spinner({
											 start:y-1
										})//.bind('spinchange', function(event, element, ui) {
										// });
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
										//$("#ladder_average_1").html(E[v]);
										//$("#ladder_average_2").html(F[v]);
									}
                                </script>
                                <div class="slider_title">Overview - Ladders Upgrade</div>
                                <div class="mainbox">
                                	<p class="ladder_text">If you are saying more Tehillim than your quota demands,<br />select the ladder that matches your average and upgrade to reach your goal faster.</p>
                                    <div class="ladder_box">
                                        Your Average:
                                        <div class="black_btn">
											Request Upgrade
                                        </div>
                                        <div class="ladder_info_box">
                                            <div id="ladder_average_1" class="ladder_info icon_book">א-ג</div>
                                            <div id="ladder_average_2" class="ladder_info icon_stopwatch">0:15</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        If you upgrade to
                                        <div class="vertical">
                                            <div id="spinner-ladder" class="v-spinner">
                                                <div>Ladder 1</div>
                                                <div>Ladder 2</div>
                                                <div>Ladder 3</div>
                                                <div class="active">Ladder 4</div>
                                                <div>Ladder 5</div>
                                                <div>Ladder 6</div>
                                                <div>Ladder 7</div>
                                                <div>Ladder 8</div>
                                                <div>Ladder 9</div>
                                                <div>Ladder 10</div>
                                            </div>
                                        </div>
                                        <div class="ladder_info_box">
                                            your quota will be:
											<div id="ladder_this_1" class="ladder_info icon_book">א-ב</div>
                                            <div id="ladder_this_2" class="ladder_info icon_stopwatch">0:10</div>
                                        </div>
                                    </div>
                                    <div class="ladder_box">
                                        By the end of
                                        <div class="vertical">
                                            <div id="spinner-year" class="v-spinner">
                                                <div class="active">Year 1</div>
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
                                        <div class="ladder_info_box">
                                            your quota will be:
                                            <div id="ladder_year_1" class="ladder_info icon_book">א-י</div>
                                            <div id="ladder_year_2" class="ladder_info icon_stopwatch">0:20</div>
                                        </div>
                                    </div>
									<div class="ladder_box_bottom">
										<div class="ladder_box">
											<div class="icon icon_finish"></div>
											You will reach your goal by:
											<div class="ladder_text ladder_date">
												9th Grade - חשון תתפ"ג
											</div>
										</div>
									</div>
                                </div>
                            </li>
 GC March 4 2010 -->
							
							<? break;
							} ?>
							
							<? if (!$enrolled) {?>
								<li>
									<div class="slider_title">Overview - Enroll</div>
									<div class="mainbox">
										<div class="col2_image iconl_enroll"></div>
										<div class="scroll-pane">
											<div class="col2_text">
												<p>I would like to join the <?=T_($subject_name)?> campaign.</p>
												<p>Please begin the enrollment process.</p>
												<div class="button button_icons">
													<div>
														<a class="icon_enroll" href="camp_enroll_1.php?c=<?=$campaign_row['subject_description']?>">Enroll</a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</li>
							<?php } else { ?>
								<li>
									<div class="slider_title">View Your Current Missions</div>
									<div class="mainbox">
										<div class="col2_image"><?=linkImgFile($campaign_row['subject_gold_image_id'], 128, 128);?></div>
										<div class="scroll-pane">
											<div class="col2_text">
												<p>You are already enrolled in the <?=T_($subject_name)?> campaign.</p>
												<div class="button button_icons">
													<div>
														<a class="icon_back_to" href="../missions.php">View Current Missions</a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</li>
								
							<? } ?>

						</ul>
					
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>

	<input type="hidden" name="USER_ID" value="<? echo $user['user_id']; ?>">
	<input type="hidden" name="SUBJECT_ID" value="<? echo $campaign_row['subject_id']; ?>">
	<input type="hidden" name="ENROLLED" value="<? echo $enrolled; ?>">

</body>

<?php include("includes/footer.php"); ?>



