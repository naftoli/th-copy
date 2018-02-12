<?
function verify_code($table, $fields) 
{
	global $user;
	global $scan_code;
	global $code_row; 
	global $code_subject;
	
	if($user['registered'])
	{
		$sql = "SELECT school_id, subject_id, TO_DAYS(expiration_date)+1721060 expiration_date " . $fields . " ";
		$sql = $sql . "FROM " . $table . " ";
		$sql = $sql . "WHERE code_id=" . substr($scan_code, 1);
		//print $sql;exit;
		$code_row = mysql_fetch_assoc(mq($sql));

		if(!$code_row) 
			return sprintf(T_('Code %s could not be found, perhaps it was already used, or expired.'), $scan_code);
		elseif($code_row['school_id'] != $user['school_id']) 
			return sprintf(T_('Code %s is from a different school, and can not be used by this account.'), $scan_code);
		else 
		{
			$sql = "SELECT subject_name, school_type_id, subject_image_id ";
			$sql = $sql . "FROM subjects ";
			$sql = $sql . "LEFT JOIN school_type_subjects ";
			$sql = $sql . "ON (subjects.subject_id=school_type_subjects.subject_id AND school_type_id=" . $user['school_type_id'] . ") ";
			$sql = $sql . "WHERE subjects.subject_id=" . $code_row['subject_id'];
			
			echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
			
			$code_subject = mysql_fetch_assoc(mq($sql));
			
			if (!$code_subject) 
				return sprintf(T_('There was an error processing code %s. The subject it applies to could not be found.'), $scan_code);
			elseif ($code_subject['school_type_id'] != $user['school_type_id']) 
				return sprintf(T_('Code %s could not be processed for this account. The subject "%s" is not applicable for this account.'), $scan_code, es($code_subject['subject_name']));
			else 
				return FALSE;
		}
	} 
	else
	{
		$code_row = mysql_fetch_assoc(mq("SELECT camp_id, task_id, TO_DAYS(expiration_date)+1721060 expiration_date $fields FROM $table WHERE code_id = " . $scan_code. ' AND expiration_date >= FROM_DAYS(' . unixtojd() . '-1721060)'));
		if ($code_row) 
		{
			$code_camp = mysql_fetch_assoc(mq("SELECT camp_name, camp_logo_id FROM camps WHERE camp_id = {$code_row['camp_id']}"));
			
			if (!$code_camp) 
				return sprintf(T_('There was an error processing code %s. The camp it applies to could not be found.'), $scan_code);
			else 
				return sprintf(T_('Testing5 %s.'), $scan_code);
		}
		else 
			return sprintf(T_('Code %s could not be found, perhaps it was already used, or expired.'), $scan_code);  
	}
}

function process_code($scan_code) {
	global $user, $code_row, $code_subject, $user_row;
	

	if (preg_match('/[^0-9]/', $scan_code)) {
		return sprintf(T_('Code %s failed, must contain only numbers.'), $scan_code);
	} 
	else
	{
		switch(strlen($scan_code)) {
  
		case 19: // camp miles card: Code length is 19 chrs. Pre-append "9" before the string. Code '9' is used for camp miles.
 			 
			$scan_code = '9' . $scan_code;

		case 20: // length
		
			switch($scan_code[0]) {
			
				case '1': // point code for school

					$message = verify_code('points_codes', ', points, left_circle, right_circle, description, series');

					if($message !== FALSE) {
						return($message);
					} 
					else {
						mq("INSERT INTO points (user_id, award_date, subject_id, award_points, award_left_circle, award_right_circle, award_description, award_series) VALUES ({$user['user_id']}, " . unixtojd() . ", {$code_row['subject_id']}, {$code_row['points']}, " . ms($code_row['left_circle']) . ', ' . ms($code_row['right_circle']) . ', ' . ms($code_row['description']) . ', ' . nullif($code_row['series']) . ") ON DUPLICATE KEY UPDATE award_points = award_points + {$code_row['points']}");
						mq('DELETE FROM points_codes WHERE code_id = ' . substr($scan_code, 1));
						mq("DELETE FROM user_codes WHERE code_id_prefix = 1 AND user_id = {$user['user_id']} AND code_id = " . substr($scan_code, 1));
						return array('code'=>$scan_code, 'points'=>$code_row['points'], 'subject_id'=>$code_row['subject_id'], 'subject_name'=> $code_subject['subject_name'], 'subject_image_id'=>$code_subject['subject_image_id'], 'expiration_date'=>$code_row['expiration_date'], 'school_id'=>$code_row['school_id'], 'left_circle'=>$code_row['left_circle'], 'right_circle'=>$code_row['right_circle'], 'description'=>$code_row['description'], 'series'=>$code_row['series']);
					}
					
					break;
/*
				case '9': // point code for camp0
            //return sprintf(T_('Testing3 %s.'), $scan_code);
 
					$message = verify_code('camp_card_codes', ', points, left_circle, right_circle');					 
 
					if($message !== FALSE) {
						return($message);
					} 
					else {
						//mq("INSERT INTO points (user_id, award_date, subject_id, award_points, award_left_circle, award_right_circle, award_description, award_series) VALUES ({$user['user_id']}, " . unixtojd() . ", {$code_row['subject_id']}, {$code_row['points']}, " . ms($code_row['left_circle']) . ', ' . ms($code_row['right_circle']) . ', ' . ms($code_row['description']) . ', ' . nullif($code_row['series']) . ") ON DUPLICATE KEY UPDATE award_points = award_points + {$code_row['points']}");
						mq('DELETE FROM camp_card_codes WHERE code_id = ' . substr($scan_code, 1));
						//mq("DELETE FROM user_codes WHERE code_id_prefix = 1 AND user_id = {$user['user_id']} AND code_id = " . substr($scan_code, 1));
						return array('code'=>$scan_code, 'points'=>$code_row['points'], 'camp_id'=>$code_row['camp_id'], 'camp_name'=> $code_camp['camp_name'], 'camp_logo_id'=>$code_camp['camp_logo_id'], 'expiration_date'=>$code_row['expiration_date'], 'task_id'=>$code_row['task_id'], 'left_circle'=>$code_row['left_circle'], 'right_circle'=>$code_row['right_circle']);
					}
					
					break;

				case '4': // date task mission code0
					$message = verify_code('date_tasks_mission_codes', ', mission_number, include_bonus_task');
					if($message !== FALSE) {
					return($message);
					} 
					else {
						$mission_row = mysql_fetch_assoc(mq("SELECT date_tasks_missions.mission_name, date_tasks_missions.mission_value, date_tasks_mission_id, mission_description, mark_date FROM date_tasks_missions JOIN user_tracks USING (subject_id, track_id, level) LEFT JOIN date_tasks_mission_marks USING (date_tasks_mission_id, user_id, subject_id) WHERE user_id = {$user['user_id']} AND school_type_id = {$user['school_type_id']} AND subject_id = {$code_row['subject_id']} AND mission_number = {$code_row['mission_number']}"));
						
						if(!$mission_row) {
							return sprintf(T_('Mission #%s for %s is not enabled for this account, and could not be processed.'), $code_row['mission_number'], es($code_subject['subject_name']));
						} 
						elseif(!is_null($mission_row['mark_date'])) {
							return sprintf(T_('You already completed mission #%s for %s-%s on: %s.<BR>Perhaps you were given a card with the wrong mission number.'), $code_row['mission_number'], es($code_subject['subject_name']), es($mission_row['mission_name']), dateToHebrew($mission_row['mark_date']));
						} 
						else {
							mq("INSERT IGNORE INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity) SELECT date_task_id, {$user['user_id']}, " . unixtojd() . ", mandatory_qty, IF(description = '', name, description) description, mandatory_qty*points, quantity*mandatory_qty FROM date_tasks WHERE date_tasks_mission_id = {$mission_row['date_tasks_mission_id']} AND mandatory_qty >= 1 AND (is_bonus = 0 OR is_bonus = {$code_row['include_bonus_task']})");
							mq("INSERT IGNORE INTO date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, mark_date) VALUES ({$user['user_id']}, {$mission_row['date_tasks_mission_id']}, {$code_row['subject_id']}, {$mission_row['mission_value']}, " . ms($mission_row['mission_name']) . ', ' . unixtojd() . ')');
							mq("INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) SELECT medal_ord, missions_done.subject_id, user_id, date_awarded FROM (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks WHERE user_id = {$user['user_id']} AND subject_id = {$code_row['subject_id']} GROUP BY subject_id, user_id) missions_done JOIN medals_subjects_totals ON (missions_done.subject_id = medals_subjects_totals.subject_id AND missions >= missions_required_total)");
							mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_printed FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
							mq('DELETE FROM date_tasks_mission_codes WHERE code_id = ' . substr($scan_code, 1));
							mq("DELETE FROM user_codes WHERE code_id_prefix = 4 AND user_id = {$user['user_id']} AND code_id = " . substr($scan_code, 1));
							$points = mysql_result(mq("SELECT SUM(mark_points) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) WHERE user_id = {$user['user_id']} AND date_tasks_mission_id = {$mission_row['date_tasks_mission_id']}"), 0);
							return array('code'=>$scan_code, 'points'=>$points, 'subject_id'=>$code_row['subject_id'], 'subject_name'=> $code_subject['subject_name'], 'subject_image_id'=>$code_subject['subject_image_id'], 'expiration_date'=>$code_row['expiration_date'], 'school_id'=>$code_row['school_id'], 'left_circle'=>$code_row['mission_number'], 'right_circle'=>$code_row['mission_number'], 'description'=>$mission_row['mission_name'], 'series'=>'');
						}
					}
					
					break;

				case '5': //date_tasks_marks card0
					$date_tasks_mission_id = substr(substr($scan_code, 1), 9, 10);
					$code_row = mysql_fetch_assoc(mq("SELECT COUNT(*) num, SUM(mark_points) points, mission_number, mission_name, MAX(mark_date)+180 expiration_date, school_id, school_number, school_name, school_city, school_state, school_logo_id, subject_id, subject_name, subject_image_id FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE user_id = {$user['user_id']} AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 1 GROUP BY date_tasks_mission_id"));
					
					if	($user['user_id'] != substr(substr($scan_code, 1), 0, 9)) {
						return T_('This card is not for you.');
					} 
					elseif(!$code_row['num']) {
						return T_('You have no completed tasks to enter for this mission. Perhaps you entered them already.');
					} 
					else {
						mq("UPDATE date_tasks_marks JOIN date_tasks USING (date_task_id) SET mark_inactive = 0 WHERE user_id = {$user['user_id']} AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 1");
						mq("DELETE FROM user_codes WHERE code_id_prefix = 5 AND user_id = {$user['user_id']} AND code_id = " . substr($scan_code, 1));
						return array('code'=>$scan_code, 'points'=>$code_row['points'], 'subject_id'=>$code_row['subject_id'], 'subject_name'=> $code_row['subject_name'], 'subject_image_id'=>$code_row['subject_image_id'], 'expiration_date'=>$code_row['expiration_date'], 'school_id'=>$code_row['school_id'], 'left_circle'=>$code_row['mission_number'], 'right_circle'=>$code_row['mission_number'], 'description'=>$code_row['mission_name'], 'series'=>'');
					}
					
					break;

				case '7': //tanya release card0
					$message = verify_code('tanya_medal_cards', ', medal_stage');
					
					if($message !== FALSE) {
						return($message);
					} 
					elseif(!($tanya = tanyaSettings($user['user_id']))) {
						return T_('Your account is not active for tanya.');
					} 
					elseif(($tanya['medal_ord'] - floor($tanya['medal_ord'])) * 100 >= $code_row['medal_stage'] || ($tanya['medal_ord'] - floor($tanya['medal_ord'])) * 100 < ($code_row['medal_stage']-25)) {
						return sprintf(T_('You are not at the %%%d checkpoint, your next checkpoint should be the %%%d checkpoint.'), $code_row['medal_stage'], ($tanya['medal_ord'] - floor($tanya['medal_ord'])) * 100 + 25);
					} 
					else {
						mq('UPDATE tanya_users SET medal_ord = ' . (floor($tanya['medal_ord']) + $code_row['medal_stage']/100) . " WHERE user_id = {$user['user_id']}");
						if($code_row['medal_stage'] == 100) {
							mq('INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) VALUES (' . ceil($tanya['medal_ord']) . ", {$code_row['subject_id']}, {$user['user_id']}, " . unixtojd() . ')');
							mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_promoted FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
						}
						mq("INSERT INTO points (user_id, award_date, subject_id, award_points) VALUES ({$user['user_id']}, " . unixtojd() . ", {$code_row['subject_id']}, 25) ON DUPLICATE KEY UPDATE award_points = award_points + 25");
						mq('DELETE FROM tanya_medal_cards WHERE code_id = ' . substr($scan_code, 1));
						mq("DELETE FROM user_codes WHERE code_id_prefix = 7 AND user_id = {$user['user_id']} AND code_id = " . substr($scan_code, 1));
						$tanya_medal = mysql_fetch_assoc(mq("SELECT tanya_users.medal_ord, medal_name FROM tanya_users LEFT JOIN medals ON (medals.medal_ord = CEIL(tanya_users.medal_ord)) WHERE user_id = {$user['user_id']}"));
						return array('code'=>$scan_code, 'points'=>25, 'subject_id'=>$code_row['subject_id'], 'subject_name'=> $code_subject['subject_name'], 'subject_image_id'=>$code_subject['subject_image_id'], 'expiration_date'=>$code_row['expiration_date'], 'school_id'=>$code_row['school_id'], 'medal_ord'=>$tanya_medal['medal_ord'], 'medal_name'=>$tanya_medal['medal_name'], 'left_circle'=>$code_row['medal_stage']/25, 'right_circle'=>$code_row['medal_stage']/25, 'description'=>T_("Checkpoint"), 'series'=>'');
					}
					
					break;

				case '8': //auction code0
					$prize_number = intval(substr($scan_code, 2, 8));
					
					if($scan_code[1] != '1') { //other codes are for the future, perhaps for a cancel card, or override card, etc
						return sprintf(T_('Unknown subcode %s'), $scan_code[1]);
					} 
					elseif(!($auction = auctionCurrent())) {
						return T_('There is no auction currently active.');
					} 
					elseif(!($prize = mysql_fetch_assoc(mq("SELECT prize_id, prize_name, prize_number, prize_points, prize_image_id, min_grade, min_grade+0 min_grade_ord, max_grade, max_grade+0 max_grade_ord FROM prizes_auction JOIN auction_prizes USING (prize_id) WHERE prizes_auction.school_id IS NULL AND prize_number = $prize_number AND auction_id = {$auction['auction_id']}")))) {
						return sprintf(T_('Unable to locate prize number %s in the active auction.'), $prize_number);
					} 
					elseif(!is_null($auction['max_prize_points']) && $prize['prize_points'] > $auction['max_prize_points']) {
						return T_('This prize costs too many miles for the current auction.');
					} 
					elseif(!is_null($prize['min_grade_ord']) && (!($class = mysql_fetch_assoc(mq("SELECT class_grade+0 class_grade_ord FROM classes WHERE class_id = {$user['class_id']}"))) || $prize['min_grade_ord'] > $class['class_grade_ord'])) {
						return T_('This prize is not available for your grade.');
					} 
					elseif(!is_null($prize['max_grade_ord']) && (!($class = mysql_fetch_assoc(mq("SELECT class_grade+0 class_grade_ord FROM classes WHERE class_id = {$user['class_id']}"))) || $prize['max_grade_ord'] < $class['class_grade_ord'])) {
						return T_('This prize is not available for your grade.');
					} 
					elseif($prize['prize_points'] + ($auction_points = mysql_result(mq("SELECT IFNULL(SUM(prize_points*quantity), 0) FROM auction_user_prizes JOIN auction_prizes USING (prize_id, auction_id) JOIN prizes_auction USING (prize_id) WHERE auction_id = {$auction['auction_id']} AND user_id = {$user['user_id']}"), 0)) > ($user_points = array_search_key('cur', auctionPoints($user['user_id'], $auction)))) {
						return sprintf(T_("You don't have enough miles left for this prize.<BR><BR>You have %s miles in total available for this auction, and have spent %s miles so far on the currect auction."), number_format($user_points), number_format($auction_points));
					} 
					else {
						mq("INSERT INTO auction_user_prizes SET auction_id = {$auction['auction_id']}, user_id = {$user['user_id']}, prize_id = {$prize['prize_id']}, quantity = 1 ON DUPLICATE KEY UPDATE quantity = quantity + 1");

						return array('code'=>$scan_code, 'prize_number'=>$prize_number, 'prize_name'=>$prize['prize_name'], 'auction_id'=>$auction['auction_id'], 'prize_image_id'=>$prize['prize_image_id'], 'prize_points'=>$prize['prize_points'], 'user_points'=>$user_points, 'auction_points'=>$auction_points+$prize['prize_points']);
					}
					
					break;

				case '3': //login code
					return T_('This is a login code, and can not be scanned here. Please logout first, and then use this code.');
					break;

				default:
					return sprintf(T_('Unknown type of scan code %s'), $scan_code);
					break;
 * 
 */
		  }
		  break;

		case 6: // length 6
		
			switch($scan_code[0]) {
			
				case '3': //logout, for now everything starting with a 3, but later other types of admin codes can be made, official logout code is 310607
					header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/logout.php?n=statement.php');
					exit;
					break;

				case '7': //tanya
				
					switch($scan_code[1]) {
					
						case '1': //tanya line
							if(!($line = mysql_fetch_assoc(mq('SELECT line, page, perek, text FROM tanya_lines WHERE line = ' . intval(substr($scan_code, 2, 6)))))) {
								return T_('Unable to find this line of tanya.');
							} 
							elseif(!($tanya = tanyaSettings($user['user_id']))) {
								return T_('Your account is not active for tanya, perhaps the program has ended.');
							} 
							elseif($line['line'] <= $tanya['lines_done'] || $line['line'] > $tanya['max_line'] ) {
								return sprintf(T_('You are not up to line %d. The last line you entered was line %d.'), $line['line'], $tanya['lines_done']);
							} 
							elseif($line['line'] > $tanya['test_limit']) {
								return sprintf(T_('You can not enter any more lines of tanya until you get tested and scan the release card. You were last tested at the %d%% mark.'), ($tanya['medal_ord'] - floor($tanya['medal_ord'])) * 100);
							} 
							elseif($line['line'] > $tanya['years_goal']) {
								return sprintf(T_('Mazal Tov, You have completed your yearly goal of %d lines. You can not scan any additional lines this year, so it is time to review your growth planner with your base commander.'), $tanya['years_goal']);
							} 
							else {
								mq("UPDATE tanya_users SET lines_done = {$line['line']} WHERE user_id = {$user['user_id']}");
								return array('line' => $line['line'], 'page' => $line['page'], 'perek' => $line['perek'], 'text' => $line['text'], 'lines_per_week' => $tanya['lines_per_week'], 'days_left' => $tanya['days_left'], 'years_goal' => $tanya['years_goal']);
							}
							
							break;

						case '3': //tanya pledge
							if(!($pledges = mysql_fetch_assoc(mq("SELECT pledges FROM tanya_users WHERE user_id = {$user['user_id']}")))) {
								return T_('Your account is not active for tanya.');
							} 
							else {
								mq('UPDATE tanya_users SET pledges = pledges + ' . intval(substr($scan_code, 2, 6)) . "/100 WHERE user_id = {$user['user_id']}");
								return array('old_pledges' => $pledges['pledges'], 'new_pledge' => trim(substr($scan_code, 2, 6)/100));
							}
							
							break;

						default:
							return sprintf(T_('Unknown subcode %s'), $scan_code[1]);
							break;
							
					} // switch($scan_code[1]) {
					
					break;

				default:
					return sprintf(T_('Unknown type of scan code %s'), $scan_code);
					break;
					
			} // switch($scan_code[0]) {
		  
			break;

			default:
				return sprintf(T_('Code %s failed, invalid number of digits. Perhaps the scanner did not get a good read.'), $scan_code);
				break;
				
		} // switch(strlen($scan_code))
	}
}

if($scan_code = gr('scan_code')) {

  $message = process_code($scan_code);

array('code'=>$scan_code, 
'points'=>$code_row['points'], 
'subject_id'=>$code_row['subject_id'], 
'subject_name'=> $code_subject['subject_name'], 
'subject_image_id'=>$code_subject['subject_image_id'], 
'expiration_date'=>$code_row['expiration_date'], 
'school_id'=>$code_row['school_id'], 
'left_circle'=>$code_row['left_circle'], 'right_circle'=>$code_row['right_circle'], 
'description'=>$code_row['description'], 
'series'=>$code_row['series']);

?>
<!--Copyright Ariel Shkedi 2007-2010-->
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
<div id="page_title"><?=T_('Card Scanned')?></div>
<? if(is_array($message)): ?>
<? if(isset($message['prize_number'])): ?>
  <DIV class="message">
  <?=sprintf(T_('Available balance remaining for this auction: %s'), number_format($message['user_points'] - $message['auction_points'], 2));?>
  </DIV>
  <TABLE class="card" style="clear: both; margin: auto; float: none;">
  <TR><TD>
    <DIV class="border">
    <P>#<?=$message['prize_number']?> - <?=es($message['prize_name'])?></P>
    <P><?=!is_null($message['prize_image_id']) ? linkImgFile($message['prize_image_id']) : ''?></P>
    <P class="barcode"><IMG SRC="barcode.php/<?=$message['code']?>" alt=""><BR><?=$message['code']?></P>
    <DIV class="points"><TABLE><TR><TD><DIV class="border"><?=$message['prize_points'], ' ', T_('Miles')?></DIV></TD></TR></TABLE></DIV>
    </DIV>
  </TD></TR>
  </TABLE>
  <DIV class="message" style="clear: both;">
  <?=T_('Your prize selection has been entered.');?>
  </DIV>
  <? $result = mq("SELECT prize_name, prize_points, quantity, prize_number FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = {$message['auction_id']} AND user_id = {$user['user_id']} ORDER BY prize_number"); ?>
  <BR style="clear: both;">
  <? if(mysql_num_rows($result)): ?>
  <BR>
  <TABLE class="transactions">
  <TR>
    <TH><?=T_('Prize #')?></TH>
    <TH><?=T_('Prize')?></TH>
    <TH><?=T_('Quantity')?></TH>
    <TH><?=T_('Miles Each')?></TH>
    <TH><?=T_('Miles Total')?></TH>
    <TH><?=T_('Balance')?></TH>
  </TR>
  <? $toggle = true; ?>
  <? $running_balance = $message['user_points'] - $message['auction_points'];?>
  <? while($row = mysql_fetch_assoc($result)): ?>
  <TR class="<?=($toggle = !$toggle) ? 'even' : 'odd'?>">
    <TD><?=$row['prize_number']?></TD>
    <TD><?=es($row['prize_name'])?></TD>
    <TD><?=number_format($row['quantity'])?></TD>
    <TD><?=number_format($row['prize_points'])?></TD>
    <TD><?=number_format($row['prize_points']*$row['quantity'])?></TD>
    <TD><?=number_format($running_balance, 2)?></TD>
  </TR>
  <? $running_balance += ($row['prize_points']*$row['quantity']); ?>
  <? endwhile; ?>
  </TABLE>
  <? endif; ?>
<? elseif(isset($message['line'])): ?>
  <DIV class="message">
  <?=sprintf(T_('You have recorded that you learned line <B>%d</B> of tanya:<BR><B>%s</B><BR>From page %d, פרק %s'), $message['line'], $message['text'], $message['page'], $message['perek'])?>
  <BR>
  <? if($message['line'] == $message['years_goal']): ?>
    <?=T_('<DIV>Mazal Tov</DIV> You have completed your yearly goal. It is time to review your growth planner with your base commander.')?>
  <? else: ?>
    <?=sprintf(T_('You need to learn %s lines per week for the next %s weeks in order to reach this years goal of %s lines.'), $message['lines_per_week'], round($message['days_left']/7, 1), $message['years_goal'])?>
  <? endif; ?>
  </DIV>
<? elseif(isset($message['new_pledge'])): ?>
  <DIV class="message">
    <?=sprintf(T_("Your pledges have been increased<BR>from %s to %s."), money_format('%n', $message['old_pledges']), money_format('%n', $message['old_pledges'] + $message['new_pledge']))?>
  </DIV>
<? else: ?>

  <?$row = mysql_fetch_assoc(mq("SELECT school_name, school_city, school_state, school_logo_id, school_number FROM schools WHERE school_id = {$message['school_id']}")); ?>

  <DIV style="float: left;" class="card_shadow">
    <?=display_card_back($message['code'], floatval($message['points']), '', $message['left_circle'], $message['right_circle'], $message['description'], $message['subject_name'], $message['subject_image_id'], $message['series'])?>
  </DIV>

<DIV class="message">
<?=sprintf(T_("<B>Mazal Tov on your achievement.</B><BR>You've earned<DIV>%s</DIV>Miles for %s."), floatval($message['points']), $message['subject_name'])?>
<? if(isset($message['medal_name'])): ?>
  <BR>
  <? if($message['medal_ord'] == floor($message['medal_ord'])): ?>
    <?=sprintf(T_("And you have just been awarded the %s medal."), es($message['medal_name']))?>
  <? else: ?>
    <?=sprintf(T_("And you are %%%d of the way to the %s medal."), ($message['medal_ord']-floor($message['medal_ord']))*100, es($message['medal_name']))?>
  <? endif; ?>
<? endif; ?>
</DIV>
<? endif; ?>
<? else: ?>
  <P><?=$message;?></P>
<? endif; ?>
    </div>
  </div>
</div>
<?
}
?>
