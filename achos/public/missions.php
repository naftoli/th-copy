<? 
require('header.php'); 
require_once('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
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

$end_date = unixtojd();
$start_date = max($end_date - 21, intval($user_row['user_start_date']));
$max_entries = 40;

$result = mq("
SELECT subject_id, subject_name, subject_gold_image_id, inst_name, date_tasks_missions.date_tasks_mission_id, date_tasks_missions.mission_name, mission_description, mission_number, date_tasks_mission_marks.date_tasks_mission_id done, (SELECT COUNT(*) FROM user_mission_entries WHERE user_id = {$user['user_id']} AND user_mission_entries.subject_id = subjects.subject_id) num_entries, EXISTS (SELECT * FROM user_mission_entries WHERE user_id = {$user['user_id']} AND entry_type = 'date_tasks_missions' AND entry_id = date_tasks_missions.date_tasks_mission_id) prev_entry
FROM subjects
     JOIN school_subjects USING (subject_id)
     JOIN school_type_subjects USING (subject_id)
     JOIN users USING (school_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN institutions USING (inst_id)
WHERE user_id = {$user['user_id']}
      AND enrolled = 1
      AND start_date <= $end_date
      AND end_date >= $start_date
      AND user_registered IS NOT NULL
      AND (
        NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = {$user['user_id']})
        OR
        EXISTS (SELECT * FROM user_mission_entries WHERE user_id = {$user['user_id']} AND entry_type = 'date_tasks_missions' AND entry_id = date_tasks_missions.date_tasks_mission_id)
      )
      AND NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) JOIN date_tasks_missions date_tasks_missions_alt USING (date_tasks_mission_id) WHERE date_tasks_missions_alt.subject_id = date_tasks_missions.subject_id AND (date_tasks_missions_alt.mission_number = date_tasks_missions.mission_number OR (date_tasks_missions.mission_number IS NULL AND date_tasks_missions_alt.start_date = date_tasks_missions.start_date AND date_tasks_missions_alt.end_date = date_tasks_missions.end_date)) AND date_tasks_missions_alt.date_tasks_mission_id != date_tasks_missions.date_tasks_mission_id AND user_id = {$user['user_id']})
ORDER BY inst_name, subject_name, subject_id, mission_number, start_date, mission_name
");
// TODO  per subject # missions

$user_medals = mysql_fetch_column(mq("SELECT subject_id, medal_ord, subject_name, medal_name, profile_photo_id FROM (SELECT MAX(medal_ord) medal_ord, subject_id FROM medal_marks WHERE user_id = {$user['user_id']} GROUP BY subject_id) medal_cur JOIN subjects USING (subject_id) JOIN medals USING (medal_ord) JOIN medals_subjects USING (subject_id, medal_ord)"));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Current Missions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
</HEAD>
<body class="blue">
<?if(gr('m')):?>
<SCRIPT type="text/javascript">
$(function () {
  $('#wrapper').css("opacity", 0.3);
  $('#carddisplay').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px');
  $(document).bind("keyup.carddisplay", function (e) {
    if(e.keyCode==27) {
      $('#close_pop a').click();
      $(document).unbind("keyup.carddisplay");
    }
  });
});
</SCRIPT>
<div id="carddisplay" style="position: fixed; z-index: 100; width: 100%;">
  <div class="cardpop" style="margin: auto; color: white; background-color: #314239; overflow: auto;">
    <div style="padding: 20px;">
      <div id="close_pop">
        <A HREF="#" onClick="$('#wrapper').fadeTo('normal', 1); document.getElementById('carddisplay').style.display = 'none'; if(document.getElementById('focus')) document.getElementById('focus').focus(); return false;">Close</A>
      </div>
      <BR>
      <?
	  switch(gr('m')) {
        case 'update':
          echo T_('Your account has been updated.');
          break;

        case 'frozen':
          echo T_('Your account has been frozen you are not authorized to enter missions on your own! See your base commander for details.');
          break;

        case 'max':
          echo T_('You can not make any more entries, you need to turn in your paperwork to your Base Commander, and ask them to sign off on your previous entries.');
          break;
        }
        ?>
    </div>
  </div>
</div>
<?endif;?>
	<div id="wrapper">
	
		<div id="header">
		
			<div class="org">
	  
				<? include ("nav.php"); ?>
		
					<div class="org_photo">
						<?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>
					</div>
					
					<?=T_('Base')?>: #<?=$user_row['school_number']?><BR>
					<?=es($user_row['school_name'])?><BR>
					<?=es($user_row['rank_name'])?> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?>
			</div>
			
			<noscript><p class="js_alert">Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.</p>
			</noscript>
			
		</div>

		<div id="main">
		
			<div id="page_title">
				<?=T_('Current Missions')?>
			</div>
			
			<div class="three_column padding_top">
			
				<div class="content">
				
					<div id="slider">
					
						<ul class="upcoming">
						
							<li>
							
								<div class="boxes padding_top">
								<?							
	  echo T_('Mission entry has beed disabled at this time.<br />Only parents and school staff can enter your missions.<br />See your base commander for details.');
      exit;
								?>
									<? if(!$user['registered']) : ?>
										<?=T_('You are not currently registered in Tzivos Hashem.')?><BR>
										<?=T_('Please see the program director at your school.')?>
									<? elseif($user_row['kiosk_edit'] == 'off') : ?>
										<div class="slider_title">
											<?=T_('Kiosk entry is disabled.')?><BR>
											<?=T_('Please see the program director at your school.')?>
										</div>
									<?elseif(!mysql_num_rows($result)):?>
										<?=T_('No Missions are currently available for editing.')?>
									<?else:?>
										<? $old_subject_id = -1; ?>
                    <?while($row = mysql_fetch_assoc($result)):?>
                      <?if($row['subject_id'] != $old_subject_id):?>
                        <?if($old_subject_id != -1):?></div><?endif;?>
                        <div class="icon" style="font-size: 50%;" onClick="$(this).next('div.mission_group').animate({opacity: 'toggle'}, 'slow');"><?=isset($user_medals[$row['subject_id']]) ? (!is_null($user_medals[$row['subject_id']]['profile_photo_id']) ? linkImgFile($user_medals[$row['subject_id']]['profile_photo_id'], NULL, 88) : es($row['subject_name'], ' ', $user_medals[$row['subject_id']]['medal_name'])) : (!is_null($row['subject_gold_image_id']) ? linkImgFile($row['subject_gold_image_id'], NULL, 88) : es($row['subject_name']))?></div>
                        <div class="mission_group" style="display: none;">
                        <?$old_subject_id = $row['subject_id'];?>
                      <?endif;?>
                      <div class="mission">
                          <?if(!$row['prev_entry'] && $row['num_entries'] >= $max_entries):?><!-- <?=T_('You can not make any more entries, you need to turn in your paperwork to your Base Commander, and ask them to sign off on your previous entries.')?> --><?endif;?>
                          <div class="number"><?=is_null($row['mission_number']) ? '' : '#' . floatval($row['mission_number'])?></div>
                          <div class="date"><?=es($row['mission_name'])?></div>
                          <div class="meter" style="background-position:<?=$row['done'] ? '0' : '100'?>% 0;"></div>
                          <a href="date_tasks.php?date_tasks_mission_id=<?=$row['date_tasks_mission_id']?>"></a>
                      </div>
                    <?endwhile;?>
                    </div>
                  <?endif;?>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="content_nav"></div>
      </div>
    </div>

    <div id="footer">
      <div class="footer_logo"></div>
      <div class="footer_logout"></div>
    </div>

  </div>
</body>
</html>
