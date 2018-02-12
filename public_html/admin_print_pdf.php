<? 
$dual_auth = true;
$admin_auth = array('school');

require('header.php'); 
require_once('file_save.php');
require_once('calendar.php');

if (!empty($admin_user)) {
	assure_id_school('school_id');
	$school_id = gri('school_id', -1);
	$class_id = gri('class_id', -1);
	$user_id = gri('user_id', -1);
	$action = gr('action');
} 
else {
	$school_id = $user['school_id'];
	$class_id = $user['class_id'];
	$user_id = $user['user_id'];
	$action = 'print';
}
if ( isset( $_GET['parsha'] ) ) {
    $parsha = $_GET['parsha'];
}

if ( isset( $_GET['gender'] ) ) {
    $gender = $_GET['gender'];
}

if (($type = gr('type')) && $action == 'print') {
    
    switch ( $type ) {
        case 'certificate_bare':
            header( "Location: tzivos_hashem_cert.php?school=$school_id&class=$class_id&user=$user_id&gender=$gender" );
            break;
        case 'certificate_birthday':
            header( "Location: birthday_cert.php?school=$school_id&class=$class_id&user=$user_id&parsha=$parsha&gender=$gender" );
            break;
    }
    
    /* 
	$localhost = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
	$view_file = $localhost . '/file_view.php?id=';

	$school = mysql_fetch_assoc(mq("SELECT school_name, school_city, school_state, school_number, school_logo_id, class_grade, class_sub FROM schools LEFT JOIN classes USING (school_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '')));

	$sql = "";
	if ($rank_ord == -1) {
		if ($type == "certificate")
			$sql = "SELECT user_id, first, last, first_he, last_he, username, gender, user_code, user_serial, user_photo_id, dob, dob_he_offset, user_start_date, class_id, class_grade, class_sub, class_teacher, rank_ord, rank_name, rank_image_id, rank_color FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id) LEFT JOIN ranks USING (rank_ord) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ' ORDER BY last, first, username';
		$result = mq("SELECT user_id, first, last, first_he, last_he, username, gender, user_code, user_serial, user_photo_id, dob, dob_he_offset, user_start_date, class_id, class_grade, class_sub, class_teacher, rank_ord, rank_name, rank_image_id, rank_color FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id) LEFT JOIN ranks USING (rank_ord) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ' ORDER BY last, first, username');
	}
	else {
		if ($type == "certificate")	
			$sql = "SELECT user_id, first, last, first_he, last_he, username, gender, user_code, user_serial, user_photo_id, dob, dob_he_offset, user_start_date, class_id, class_grade, class_sub, class_teacher, rank_ord, rank_name, rank_image_id, rank_color FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id) LEFT JOIN ranks USING (rank_ord) WHERE rank_ord=" . $rank_ord . " AND school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ' ORDER BY last, first, username';	
		$result = mq("SELECT user_id, first, last, first_he, last_he, username, gender, user_code, user_serial, user_photo_id, dob, dob_he_offset, user_start_date, class_id, class_grade, class_sub, class_teacher, rank_ord, rank_name, rank_image_id, rank_color FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id) LEFT JOIN ranks USING (rank_ord) WHERE rank_ord=" . $rank_ord . " AND school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ' ORDER BY last, first, username');
	}

	switch($type) {
	
		case 'certificate':
			header("Location: admin_print_certificate.php?school_name=" . $school['school_name'] . "&school_number=" . $school['school_number'] . "&sql=" . $sql);
			 
			/*$fmt = 'csv';

			$input = "Certificate\r\n";
			$input .= '"BASE_NAME","BASE_NUMBER","NAME","GENDER","RANK"' . "\r\n";
			while($row = mysql_fetch_assoc($result)) {
				$input .= csvEscape(es($school['school_name'])) . ',';
				$input .= csvEscape("BASE # {$school['school_number']}") . ',';
				$input .= csvEscape(es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last'])) . ',';
				$input .= csvEscape($row['gender'] == 'F' ? 'f' : 'm') . ',';
				$input .= csvEscape(es($row['rank_name']));
				$input .= "\r\n";
			}
			
		break;

		case 'certificate_enrollment':
			$input = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$input .= '<report name="CertificateEnrollment"><certificates>' . "\n";
			$input .= '<base>' . es($school['school_name']) . " BASE # {$school['school_number']}, " . es($school['school_city']) . ', ' . es($school['school_state']) . "</base>\n";
			
			while ($row = mysql_fetch_assoc($result)) {
				$input .= '<cert name="' . es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last']) . '" gender="' . ($row['gender'] == 'F' ? 'f' : 'm') . '" rank="' . es($row['rank_name']) . '" />' . "\n";
			}
			
			$input .= '</certificates></report>' . "\n";
		break;

		case 'certificate_bare':
            
			$input = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$input .= '<report name="CertificateBare"><certificates>' . "\n";
			$input .= '<base>' . es($school['school_name']) . " BASE # {$school['school_number']}, " . es($school['school_city']) . ', ' . es($school['school_state']) . "</base>\n";
			
			while ($row = mysql_fetch_assoc($result)) {
				$input .= '<cert name="' . es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last']) . '" gender="' . ($row['gender'] == 'F' ? 'f' : 'm') . '" rank="' . es($row['rank_name']) . '" />' . "\n";
			}
			
			$input .= '</certificates></report>' . "\n";
            
		break;
/*
		case 'tbp_yearly_progress':
			$input = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$input .= '<report name="tbp_yearly_progress">' . "\n";
			$input .= "<base THBase=\"{$school['school_number']}\" basename=\"" . es($school['school_name']) . '"' . (!is_null($school['school_logo_id']) ? ' logourl="' . $localhost . '/file_view.php?id=' . $school['school_logo_id'] . '"' : '') . " />\n";
			
			while ($row = mysql_fetch_assoc($result)) {
				if (!($tanya = tanyaSettings($row['user_id']))) continue;
				$input .= '<student name="' . es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last']) . '" gender="' . ($row['gender'] == 'F' ? 'f' : 'm') . '" grade="' . es($row['class_grade']) . '-' . es($row['class_sub']) . '" age="' . calcAge(dateToJD($row['dob'])+$row['dob_he_offset']) . '" serial="' . $row['user_serial'] . '" rank="' . es($row['rank_name']) . '" teacher="' . es($row['class_teacher']) . '">' . "\n";
				$input .= '<goals longterm="פרק א-' . $tanya['long_goal_perek'] . '" thisyear="' . T_('Lines') . ' 1-' . $tanya['years_goal'] . '" timeframe="' . round($tanya['length_days']/7, 1) . ' weeks" weeklyquota="' . $tanya['lines_per_week'] . ' Lines" />' . "\n";
				$input .= "</student>\n";
			}
			$input .= "</report>\n";
		break;

		case 'tbp_progress_report':
			$week_num = gri('week_num');
			$input = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$input .= '<report name="tbp_progress_report">' . "\n";
			$input .= "<base number=\"{$school['school_number']}\" name=\"" . es($school['school_name']) . '"' . (!is_null($school['school_logo_id']) ? ' logourl="' . $localhost . '/file_view.php?id=' . $school['school_logo_id'] . '"' : '') . " />\n";
			while($row = mysql_fetch_assoc($result)) {
				if (!($tanya = tanyaSettings($row['user_id'], $week_num))) continue;
				if (is_null($tanya['medal_url'])) list($tanya['medal_url']) = mysql_fetch_array(mq("SELECT subject_image_id FROM subjects WHERE subject_type = 'Tanya'"));
				if ($tanya['lines_done'] < $tanya['years_goal']) {
					$page = mysql_fetch_assoc(mq('SELECT page FROM tanya_lines WHERE line = ' . ($tanya['lines_done']+1)));
					$start_page = max(intval($page['page']), 1);

					$page = mysql_fetch_assoc(mq('SELECT page FROM tanya_lines WHERE line = ' . ceil(min($tanya['lines_done']+$tanya['lines_per_week'], $tanya['years_goal']))));
					$end_page = max(intval($page['page']), $start_page);
				}

				$user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$row['user_id']}")), 0);

				if (!is_null($row['class_id'])) {
					$class_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$row['class_id']} AND user_start_date IS NOT NULL")), 0);
					$class_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND class_id = {$row['class_id']} AND user_start_date IS NOT NULL"), 0);
				}
				
				$base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL")), 0);
				$base_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL"), 0);
				$all_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_start_date IS NOT NULL"), 0);

				$auction = auctionPoints($row['user_id']);
				$input .= '<student name="' . es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last']) . '" gender="' . ($row['gender'] == 'F' ? 'f' : 'm') . '" grade="' . es($row['class_grade']) . '-' . es($row['class_sub']) . '" age="' . calcAge(dateToJD($row['dob'])+$row['dob_he_offset']) . '" serial="' . $row['user_serial'] . '" rank="' . es($row['rank_name']) . '" teacher="' . es($row['class_teacher']) . '"' . (!is_null($tanya['medal_url']) ? ' medalurl="' . $localhost . '/file_view.php?id=' . $tanya['medal_url'] . '"' : '') . ' miles="' . $user_miles . '" report_title="' . T_('Week') . ' ' . (floor($tanya['days_passed']/7)+1) . ' &#8226; ' . es(dateToHebrewNoYear($tanya['start_date'])) . ' - ' . es(dateToHebrewNoYear($tanya['start_date']+6)) .' &#8226; ' . T_('Lines') . ' ' . ($tanya['lines_done'] < $tanya['years_goal'] ? ($tanya['lines_done']+1) . '-' . min($tanya['lines_done']+1+$tanya['lines_per_week'], $tanya['years_goal']) : T_('Goal Reached')) .'" year_progress="' . ($tanya['years_goal'] <= $tanya['lines_offset'] ? 100 : ($tanya['lines_done']-$tanya['lines_offset'])/($tanya['years_goal']-$tanya['lines_offset'])*100) . '" medal_progress="' . (($tanya['medal_progress']-floor($tanya['medal_progress']))*100) . '"' . (!is_null($row['rank_image_id']) ? ' rank_logo="' . $localhost . '/file_view.php?id=' . $row['rank_image_id'] . '"' : '') . ">\n";
				$input .= "<dates>\n";
				
				for ($i = 1; $i <= 7; $i++) {
					$input .= "<date{$i}><![CDATA[" . $weekdays_short[jddayofweek($tanya['start_date']+$i-1)] . "\n" . dateToHebrewNoYear($tanya['start_date']+$i-1) . "]]></date{$i}>\n";
				}
				
				$input .= "</dates>\n";

				if ($tanya['lines_done'] < $tanya['years_goal']) {
					$input .= '<tanya pages="' . (1+$end_page-$start_page) . '">' . "\n";
					$cur_line = $tanya['lines_done'] + 1;
					
					for ($i = $start_page; $i <= $end_page; $i++) {
						$page_lines = mysql_fetch_assoc(mq("SELECT MIN(line) first_line, MAX(line) last_line FROM tanya_lines WHERE page = $i"));
						$input .= '<page number="' . (1+$i-$start_page) . '" url="' . $i . '" start_line="' . ($cur_line - $page_lines['first_line'] + 1) . '" start_pos="0" lines="' . (min($page_lines['last_line'], $tanya['lines_done']+$tanya['lines_per_week']) - $cur_line + 1) . '" />' . "\n";

						$cur_line = min($page_lines['last_line'], $tanya['lines_done']+$tanya['lines_per_week']) + 1;
					}
					
					$input .= "</tanya>\n";
				} 
				else {
					$input .= '<tanya pages="0"><page /></tanya>' . "\n";
				}

				$input .= '<goals long_term="פרק א-' . $tanya['long_goal_perek'] . '" this_year="' . T_('Line') . ' ' . $tanya['years_goal'] . '" time_frame="' . round($tanya['days_left']/7, 1) . ' ' . T_('weeks') . '" weekly_quota="' . $tanya['lines_per_week'] . ' ' . T_('Lines') . '" iknow="' . $tanya['lines_done'] . ' ' . T_('Lines') . '" nextmedal="' . (is_null($tanya['next_medal_name']) ? T_('Highest Medal Achieved') : es(sprintf(T_('%s Lines to earn %s medal'), $tanya['lines_to_next_medal'], $tanya['next_medal_name']))) . '" />' . "\n";
				$input .= '<miles miles="' . $user_miles . '" miles_for_auction="' . $auction['cur'] . '"' . (isset($auction['prev']) ? ' previous_miles="' . $auction['prev'] . '"' : '') . (isset($auction['left']) ? ' miles_needed_previous="' . $auction['left'] . '"' : '') . ' platoon_average="' . (!is_null($row['class_id']) ? @number_format($class_points/$class_count, 2) : '') . '" base_average="' . @number_format($base_points/$base_count, 2) . '" />' . "\n";
				$input .= "<stats>\n";

				$total = mysql_fetch_assoc(mq("SELECT lines_done lines_total, pledges pledges_total FROM tanya_users WHERE user_id = {$row['user_id']}"));
				$input .= '<mine><![CDATA[' . T_('Tanya Mileage') . ': ' . number_format(mysql_result(mq(totalMarks("JOIN subjects USING (subject_id) WHERE subject_type = 'Tanya' AND user_id = {$row['user_id']}")), 0), 2) . '' . T_('Lines Learnt') . ': ' . $total['lines_total'] . '' . T_('Pledges') . ': ' . money_format('%n', $total['pledges_total']) ."]]></mine>\n";
				$input .= '<platoon><![CDATA[';

				if (!is_null($row['class_id'])) {
					$total = mysql_fetch_assoc(mq("SELECT SUM(lines_done) lines_total, SUM(pledges) pledges_total FROM users JOIN tanya_users USING (user_id) WHERE school_id = $school_id AND class_id = {$row['class_id']}"));
					$input .= T_('Soldiers') . ': ' . $class_count . '' . T_('Tanya Mileage') . ': ' . number_format(mysql_result(mq(totalMarks("JOIN subjects USING (subject_id) JOIN users USING (user_id) WHERE subject_type = 'Tanya' AND school_id = $school_id AND class_id = {$row['class_id']}")), 0), 2) . '' . T_('Lines Learnt') . ': ' . '' . T_('Pledges') . ': ' . money_format('%n', $total['pledges_total']);
				} 
				else {
					$input .= T_('N/A');
				}
				
				$input .= "]]></platoon>\n";
				$total = mysql_fetch_assoc(mq("SELECT SUM(lines_done) lines_total, SUM(pledges) pledges_total FROM users JOIN tanya_users USING (user_id) WHERE school_id = $school_id"));
				$input .= '<base><![CDATA[' . T_('Soldiers') . ': ' . $base_count . '' . T_('Tanya Mileage') . ': ' . number_format(mysql_result(mq(totalMarks("JOIN subjects USING (subject_id) JOIN users USING (user_id) WHERE subject_type = 'Tanya' AND school_id = $school_id")), 0), 2) . '' . T_('Lines Learnt') . ': ' . $total['lines_total'] . '' . T_('Pledges') . ': ' . money_format('%n', $total['pledges_total']) ."]]></base>\n";
				$total = mysql_fetch_assoc(mq("SELECT SUM(lines_done) lines_total, SUM(pledges) pledges_total FROM tanya_users"));
				$input .= '<army><![CDATA[' .T_('Soldiers') . ': ' . $all_count . '' . T_('Tanya Mileage') . ': ' . number_format(mysql_result(mq(totalMarks("JOIN subjects USING (subject_id) WHERE subject_type = 'Tanya'")), 0), 2) . '' . T_('Lines Learnt') . ': ' . $total['lines_total'] . '' . T_('Pledges') . ': ' . money_format('%n', $total['pledges_total']) ."]]></army>\n";
				$input .= "</stats>\n";
				$input .= "</student>\n";
			}
			
			$input .= "</report>\n";
		break;

		case 'tbp_progress_report_post':
			$post = true;
		break;

		case 'tbp_monthly_quota_post':
			$post = true;
		break;

		case 'tbp_monthly_quota':
			$fmt = 'csv';
			$input = "tbp_monthly_quota\r\n";
			$input .= '"NAME","MEDAL_URL","DATE_TITLE","LINES_TITLE","LONG_TERM","THIS_YEAR","IKNOW","PAGE_NUM","PAGES","TIME_FRAME","QUOTA","YEARLY_PROGRESS","MEDAL_PROGRESS","MEDAL_TITLE","TANYA_PAGE","START_LINE","START_POS","LINES","WEEK_1_1","WEEK_1_2","WEEK_1_3","WEEK_1_4","WEEK_1_5","WEEK_1_6","WEEK_1_7","WEEK_2_1","WEEK_2_2","WEEK_2_3","WEEK_2_4","WEEK_2_5","WEEK_2_6","WEEK_2_7","WEEK_3_1","WEEK_3_2","WEEK_3_3","WEEK_3_4","WEEK_3_5","WEEK_3_6","WEEK_3_7","WEEK_4_1","WEEK_4_2","WEEK_4_3","WEEK_4_4","WEEK_4_5","WEEK_4_6","WEEK_4_7",' . "\r\n";
			$week_num = gri('week_num');
			
			while ($row = mysql_fetch_assoc($result)) {
				if (!($tanya = tanyaSettings($row['user_id'], $week_num))) 
					continue;

				if (is_null($tanya['medal_url'])) 
					list($tanya['medal_url']) = mysql_fetch_array(mq("SELECT subject_image_id FROM subjects WHERE subject_type = 'Tanya'"));

				if ($tanya['lines_done'] < $tanya['years_goal']) {
					$page = mysql_fetch_assoc(mq('SELECT page FROM tanya_lines WHERE line = ' . ($tanya['lines_done']+1)));
					$start_page = max(intval($page['page']), 1);
					$page = mysql_fetch_assoc(mq('SELECT page FROM tanya_lines WHERE line = ' . ceil(min($tanya['lines_done']+$tanya['lines_per_week']*4, $tanya['years_goal']))));
					$end_page = max(intval($page['page']), $start_page);
				} 
				else {
					$start_page = $end_page = 0;
				}

				$cur_line = $tanya['lines_done'] + 1;
				
				for ($i = $start_page; $i <= $end_page; $i++) {
					$input .= csvEscape($row['rank_name'] . ' ' . ($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . ($row['last_he'] ? $row['last_he'] : $row['last'])) . ',';
					$input .= csvEscape(!is_null($tanya['medal_url']) ? $view_file . $tanya['medal_url'] : $localhost . '/images/blank.png') . ',';
					$input .= csvEscape(T_('Weeks') . ' ' . (floor($tanya['days_passed']/7)+1) . ' - ' . floor(($tanya['days_passed']+28)/7) . ' • ' . dateToHebrewNoYear($tanya['start_date']) . ' - ' . dateToHebrewNoYear($tanya['start_date']+28)) . ',';
					$input .= csvEscape(T_('Lines') . ' ' . ($tanya['lines_done'] < $tanya['years_goal'] ? ($tanya['lines_done']+1) . '-' . min($tanya['lines_done']+1+$tanya['lines_per_week']*4, $tanya['years_goal']) : T_('Goal Reached'))) . ',';
					$input .= csvEscape('פרק א-' . $tanya['long_goal_perek']) . ',';
					$input .= csvEscape(T_('Line') . ' ' . $tanya['years_goal']) . ',';
					$input .= csvEscape($tanya['lines_done'] . ' ' . T_('Lines')) . ',';
					$input .= csvEscape($i - $start_page + 1) . ',';
					$input .= csvEscape($end_page - $start_page + 1) . ',';
					$input .= csvEscape(round($tanya['days_left']/7, 1) . ' ' . T_('weeks')) . ',';
					$input .= csvEscape($tanya['lines_per_week']*4 . ' ' . T_('Lines')) . ',';
					$input .= csvEscape($tanya['years_goal'] <= $tanya['lines_offset'] ? 100 : ($tanya['lines_done']-$tanya['lines_offset'])/($tanya['years_goal']-$tanya['lines_offset'])*100) . ',';
					$input .= csvEscape(($tanya['medal_progress']-floor($tanya['medal_progress']))*100) . ',';
					$input .= csvEscape(is_null($tanya['next_medal_name']) ? T_('Highest Medal Achieved') : sprintf(T_('%s Lines to earn %s medal'), $tanya['lines_to_next_medal'], $tanya['next_medal_name'])) . ',';

					if ($tanya['lines_done'] < $tanya['years_goal']) {
						$page_lines = mysql_fetch_assoc(mq("SELECT MIN(line) first_line, MAX(line) last_line FROM tanya_lines WHERE page = $i"));
						$input .= csvEscape($i) . ',' . csvEscape($cur_line - $page_lines['first_line'] + 1) . ',' . csvEscape(0) . ',' . csvEscape(min($page_lines['last_line'], min($tanya['lines_done']+$tanya['lines_per_week']*4, $tanya['years_goal'])) - $cur_line + 1);
						$cur_line = min($page_lines['last_line'], min($tanya['lines_done']+$tanya['lines_per_week']*4, $tanya['years_goal'])) + 1;
					} 
					else {
						$input .= '"0","0","0","0"';
					}
					
					for($j = 0; $j < 28; $j++) {
						$input .= ',' . csvEscape(dateToHebrewNoYear($j + $tanya['start_date']));
					}
					
					$input .= "\r\n";
				}
			}
		break;

		case 'tbp_growth_planner':
			$goals = mysql_fetch_column(mq('SELECT track, lines_goal FROM tanya_goals ORDER BY track'));
			$input = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$input .= '<report name="tbp_growth_planner">' . "\n";
			
			while ($row = mysql_fetch_assoc($result)) {
				if (!($tanya = mysql_fetch_assoc(mq("SELECT track, year, lines_done, lines_offset, tanya_start_date, length_days, length_days_offset FROM tanya_users WHERE user_id = {$row['user_id']}")))) 
					continue;

				if (($end_date = $tanya['tanya_start_date'] + $tanya['length_days'] + $tanya['length_days_offset']) < unixtojd()) 
					continue; //if end date is earlier than current date
					
				$days_left = $tanya['tanya_start_date'] >= unixtojd() ? $tanya['length_days'] : $end_date - unixtojd();
				$days_passed = $tanya['tanya_start_date'] >= unixtojd() ? 0 : unixtojd() - $tanya['tanya_start_date'] - $tanya['length_days_offset'];

				$input .= '<page name="' . es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last']) . '" platoon="' . es($row['class_grade']) . '-' . es($row['class_sub']) . '" teacher="' . es($row['class_teacher']) . '" completein="' .  round($days_left/7, 1) . '" alreadyknow="' . $tanya['lines_offset'] . '" currentyear="' . $tanya['year'] . '" learntthisyear="' . ($tanya['lines_done']-$tanya['lines_offset']) . '" currentladder="' . $tanya['track'] . '" eightyeargoal="' . ($goals[$tanya['track']] - $tanya['lines_offset']) . '" weeklynew="' . ($goals[$tanya['track']] > $tanya['lines_offset'] ? round(($goals[$tanya['track']]-$tanya['lines_offset'])/8/($tanya['length_days']/7), 1) : '') . '" reportdate="' . es(dateToHebrew(unixtojd())) . '" fromdate="' . es(dateToHebrewNoYear($tanya['tanya_start_date'])) . '" todate="' . es(dateToHebrewNoYear($end_date)) . '" totalweeks="' . round($tanya['length_days']/7, 1) . '" avglinesweek="' . ($days_passed ? round(($tanya['lines_done'] - $tanya['lines_offset'])/($days_passed/7), 1) : '0') . '"' . (!is_null($school['school_logo_id']) ? ' logourl="' . $localhost . '/file_view.php?id=' . $school['school_logo_id'] . '"' : '') . '>' . "\n";
				$input .= "<ladderInfo>\n";
				
				foreach($goals as $track => $lines) {
					$lines_per_year = ($lines-$tanya['lines_offset'])/8;
					$at_end_year = $lines_per_year*$tanya['year']+$tanya['lines_offset'];
					$input .= '<laddersum' . $track . ' ladder="' . $track . '" weeklygoal="' . ($at_end_year > $tanya['lines_done'] ? round(($at_end_year-$tanya['lines_done'])/($days_left/7), 1) . ' ' . T_('NEW Lines') : '') . '" yearlygoal="' . ($at_end_year > $tanya['lines_done'] ? round($lines_per_year, 1) . ' ' . T_('NEW Lines') : ($lines > $tanya['lines_offset'] ? T_('Goal Completed') : '')) . '" finalyeargoal="' . ($lines > $tanya['lines_offset'] ? $lines-$tanya['lines_offset'] . ' ' . T_('NEW Lines') : T_('Already Know')) . '" willknow="' . $lines . ' ' . T_('Lines') . '" yearlynew="' . ($lines > $tanya['lines_offset'] ? T_('Yearly Goal') . ' ' . round($lines_per_year, 1) : '') . '" weeklynew="' . ($lines > $tanya['lines_offset'] ? T_('Weekly Goal') . ' ' . round($lines_per_year/($tanya['length_days']/7), 1) : '') . '" />' . "\n";
				}
				
				$input .= "</ladderInfo>\n";

				for ($repeat = 0; $repeat < count($goals); $repeat+=10) {
				
					for ($year = 8; $year >= 1; $year--) {
						$input .= '<years ynumber="' . $year . '">' . "\n";
						
						foreach ($goals as $track => $lines) {
							if ($lines > $tanya['lines_offset']) {
								$max_line = floor(($lines-$tanya['lines_offset'])/8*$year+$tanya['lines_offset']);
								$milestone = mysql_fetch_assoc(mq('SELECT MAX(line) line, perek FROM tanya_lines GROUP BY perek HAVING line <= ' . $max_line .  ' ORDER BY line DESC LIMIT 1'));
								$milestone = $milestone ? $milestone['perek'] : '';
								$prev_milestone = mysql_fetch_assoc(mq('SELECT MAX(line) line, perek FROM tanya_lines GROUP BY perek HAVING line <= ' . (floor($lines-$tanya['lines_offset'])/8*($year-1)+$tanya['lines_offset']) .  ' ORDER BY line DESC LIMIT 1'));
								$prev_milestone = $prev_milestone ? $prev_milestone['perek'] : '';
								
								if ($milestone == $prev_milestone) 
									$milestone = '';
							} 
							else {
								$max_line = $milestone = '';
							}
							
							$input .= '  <ladder' . $track . ' totallines="' . ($max_line ? T_('Lines') . ' 1-' . $max_line : '') . '" milestone="' . ($milestone ? "פרק $milestone" : '') . '" />' . "\n";
						}
						
						$input .= "</years>\n";
					}
					
				}
				
				$input .= "</page>\n";
				
			}
			
			$input .= "</report>\n";
			
		break;

		case 'mission_cover_sheet_post':
			$post = true;
		break;

		// ********** MISSION COVER SHEET ********** //
		case 'mission_cover_sheet':
		
			if (!($start_date = gri('start_date', 0))) {
				$cal_today = cal_from_jd(unixtojd(), CAL_JEWISH);
				
				switch(gr('starting')) {
					case 'next_month':
						$cal_today = $cal_today['day'] < 20 ? cal_from_jd(unixtojd()+33, CAL_JEWISH) : cal_from_jd(unixtojd()+13, CAL_JEWISH); //add days to jump forward a month
						
					default:
					
					case 'this_month':
						$start_date = cal_to_jd(CAL_JEWISH, $cal_today['month'], 1, $cal_today['year']);
					break;

					case 'this_week':
						$start_date = unixtojd() - jddayofweek(unixtojd(), 0);
					break;

					case 'next_week':
						$start_date = unixtojd() - jddayofweek(unixtojd(), 0) + 7;
					break;
				}
			}
			
			$base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL")), 0);
			$base_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL"), 0);

			$fmt = 'csv';
			$input = "mission_template\r\n";
			$input .= '"RANK","TITLE","TITLE-NAME","DEADLINE","INFO-LEFT","INFO-RIGHT","SUMMARY-SUB","MISSION_1","MISSION_1_F1","MISSION_1_F2","MISSION_2","MISSION_2_F1","MISSION_2_F2","MISSION_3","MISSION_3_F1","MISSION_3_F2","MISSION_4","MISSION_4_F1","MISSION_4_F2","MISSION_5","MISSION_5_F1","MISSION_5_F2","MISSION_6","MISSION_6_F1","MISSION_6_F2","HDR_LOGO_1","HDR_LOGO_2","HDR_LOGO_3","HDR_LOGO_4","HDR_LOGO_5","HDR_LOGO_6","TH_1","TH_2","TH_3","TH_4","TH_5","TH_6","TH_7","TH_8","TH_9","TH_10","TH_11","TH_12","TH_13","TH_14","TH_15","TH_16","TH_17","TH_18","TH_19","TH_20","TH_21","TH_22","TH_23","TH_24","TH_25","TH_26","TH_27","TH_28","TH_29","TH_30","TH_31","TD_1_1","TD_2_1","TD_3_1","TD_4_1","TD_5_1","TD_6_1","TD_1_2","TD_2_2","TD_3_2","TD_4_2","TD_5_2","TD_6_2","TD_1_3","TD_2_3","TD_3_3","TD_4_3","TD_5_3","TD_6_3","TD_1_4","TD_2_4","TD_3_4","TD_4_4","TD_5_4","TD_6_4","TD_1_5","TD_2_5","TD_3_5","TD_4_5","TD_5_5","TD_6_5","TD_1_6","TD_2_6","TD_3_6","TD_4_6","TD_5_6","TD_6_6","TD_1_7","TD_2_7","TD_3_7","TD_4_7","TD_5_7","TD_6_7","TD_1_8","TD_2_8","TD_3_8","TD_4_8","TD_5_8","TD_6_8","TD_1_9","TD_2_9","TD_3_9","TD_4_9","TD_5_9","TD_6_9","TD_1_10","TD_2_10","TD_3_10","TD_4_10","TD_5_10","TD_6_10","TD_1_11","TD_2_11","TD_3_11","TD_4_11","TD_5_11","TD_6_11","TD_1_12","TD_2_12","TD_3_12","TD_4_12","TD_5_12","TD_6_12","TD_1_13","TD_2_13","TD_3_13","TD_4_13","TD_5_13","TD_6_13","TD_1_14","TD_2_14","TD_3_14","TD_4_14","TD_5_14","TD_6_14","TD_1_15","TD_2_15","TD_3_15","TD_4_15","TD_5_15","TD_6_15","TD_1_16","TD_2_16","TD_3_16","TD_4_16","TD_5_16","TD_6_16","TD_1_17","TD_2_17","TD_3_17","TD_4_17","TD_5_17","TD_6_17","TD_1_18","TD_2_18","TD_3_18","TD_4_18","TD_5_18","TD_6_18","TD_1_19","TD_2_19","TD_3_19","TD_4_19","TD_5_19","TD_6_19","TD_1_20","TD_2_20","TD_3_20","TD_4_20","TD_5_20","TD_6_20","TD_1_21","TD_2_21","TD_3_21","TD_4_21","TD_5_21","TD_6_21","TD_1_22","TD_2_22","TD_3_22","TD_4_22","TD_5_22","TD_6_22","TD_1_23","TD_2_23","TD_3_23","TD_4_23","TD_5_23","TD_6_23","TD_1_24","TD_2_24","TD_3_24","TD_4_24","TD_5_24","TD_6_24","TD_1_25","TD_2_25","TD_3_25","TD_4_25","TD_5_25","TD_6_25","TD_1_26","TD_2_26","TD_3_26","TD_4_26","TD_5_26","TD_6_26","TD_1_27","TD_2_27","TD_3_27","TD_4_27","TD_5_27","TD_6_27","TD_1_28","TD_2_28","TD_3_28","TD_4_28","TD_5_28","TD_6_28","TD_1_29","TD_2_29","TD_3_29","TD_4_29","TD_5_29","TD_6_29","TD_1_30","TD_2_30","TD_3_30","TD_4_30","TD_5_30","TD_6_30","TD_1_31","TD_2_31","TD_3_31","TD_4_31","TD_5_31","TD_6_31"' . "\r\n";
			
			while($row = mysql_fetch_assoc($result)) {
				$auction = auctionPoints($row['user_id']);
				
				if (!is_null($row['class_id'])) {
					$class_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$row['class_id']} AND user_start_date IS NOT NULL")), 0);
					$class_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND class_id = {$row['class_id']} AND user_start_date IS NOT NULL"), 0);
				}
				
				$subjects = mq("SELECT subject_id, subject_name, subject_image_id, subject_type, image_id, (SELECT SUM(mission_value) FROM date_tasks_missions WHERE date_tasks_missions.school_type_id = users.school_type_id AND subject_id = subjects.subject_id AND level = user_tracks.level AND track_id = user_tracks.track_id AND start_date <= $start_date+31 AND end_date >= $start_date) missions, (SELECT COUNT(*) FROM date_tasks_missions JOIN date_tasks USING (date_tasks_mission_id) WHERE date_tasks_missions.school_type_id = users.school_type_id AND subject_id = subjects.subject_id AND level = user_tracks.level AND track_id = user_tracks.track_id AND start_date <= $start_date+31 AND end_date >= $start_date) tasks FROM subjects JOIN report_subjects USING (subject_id) JOIN school_subjects USING (subject_id) JOIN school_type_subjects USING (subject_id) JOIN users USING (school_id, school_type_id) JOIN user_tracks USING (user_id, subject_id) WHERE report_type = 'mission_cover_sheet' AND user_id = {$row['user_id']} AND school_id = $school_id AND enrolled = 1");
				$dates = mysql_fetch_column_tuple(mq("SELECT DISTINCT nominal_date, subject_id FROM users JOIN user_tracks USING (user_id) JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id) JOIN date_tasks USING (date_tasks_mission_id) JOIN date_tasks_dates USING (date_task_id) JOIN report_subjects USING (subject_id) JOIN school_subjects USING (school_id, subject_id) WHERE report_type = 'mission_cover_sheet' AND user_id = {$row['user_id']} AND school_id = $school_id AND enrolled = 1 AND start_date <= $start_date+31 AND end_date >= $start_date"));
*/
			//	$input .= csvEscape(is_null($row['rank_image_id']) ? $localhost . '/images/blank.png' : $view_file . $row['rank_image_id']) . ',' . csvEscape(T_('This is a personalized Tishrei 5770 mission for:')) . ',' . csvEscape($row['rank_name'] . ' ' . ($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . ($row['last_he'] ? $row['last_he'] : $row['last'])) .',"Deadline: This report must be brought to the school by the 2nd of Cheshvon.",' . csvEscape(T_('GRADE') . ': ' . $row['class_grade'] . '-' . $row['class_sub'] . '' . T_('AGE') . ': ' . calcAge(dateToJD($row['dob'])+$row['dob_he_offset']) . '' . T_('SERIAL #') . ': ' . $row['user_serial'] . '' . T_('TZIVOS HASHEM BASE #') . ': ' . $school['school_number'] . '' . T_('BASE NAME') . ': ' . $school['school_name'] . '' . T_('TEACHER') . ': ' . $row['class_teacher']) . ',' . csvEscape(T_('MILES AVAILABLE FOR NEXT CHINESE AUCTION') . ': ' . $auction['cur'] .(0 && isset($auction['prev']) ? '' . T_('PAST MILES') . ': ' . $auction['prev'] : '') .(0 && isset($auction['left']) ? '' . T_('MILES NEEDED TO ACTIVATE PAST MILES') . ': ' . $auction['left'] : '') .(!is_null($row['class_id']) ? '' . T_('PLATOON AVERAGE') . ': ' /*. @number_format($class_points/$class_count, 2)*/ : '') .'' . T_('BASE AVERAGE') . ': ' /*. @number_format($base_points/$base_count, 2)*/) . ',"To be completed at the end of Tishrei"';
/*				
				for ($i = 0; $i < 6; $i++) {
					$subject = mysql_fetch_assoc($subjects);
					
					if ($subject)
						$input .= ',' . csvEscape(is_null($subject['image_id']) ? $localhost . '/images/blank.png' : $view_file . $subject['image_id']) . ',' . csvEscape($subject['subject_type'] == 'Tanya' || !floatval($subject['tasks']) ? '' : floatval($subject['tasks'])) . ',' .csvEscape($subject['subject_type'] == 'Tanya' || !floatval($subject['missions']) ? '' : floatval($subject['missions']));
					else
						$input .= ',' . csvEscape($localhost . '/images/blank.png') . ',' . csvEscape('') . ',' .csvEscape('');
				}
				
				@mysql_data_seek($subjects, 0);
				
				for ($i = 0; $i < 6; $i++) {
					$subject = mysql_fetch_assoc($subjects);
					
					if ($subject)
						$input .= ',' . csvEscape(is_null($subject['subject_image_id']) ? $localhost . '/images/blank.png' : $view_file . $subject['subject_image_id']);
					else
						$input .= ',' . csvEscape($localhost . '/images/blank.png');
				}
				
				for ($i = 0; $i < 31; $i++) {
					$input .= ',' . csvEscape($weekdays_short[jddayofweek($i + $start_date)] . ' - ' . dateToHebrewNoYear($i + $start_date));
				}
				
				@mysql_data_seek($subjects, 0);
				
				for ($i = 0; $i < 31; $i++) {
					for($j = 0; $j < 6; $j++) {
						$subject = mysql_fetch_assoc($subjects);
						$input .= ',' . csvEscape($subject && !is_null($subject['subject_image_id']) && ($subject['subject_type'] == 'Tanya' || isset($dates[$i+$start_date][$subject['subject_id']])) ? $view_file . $subject['subject_image_id'] : "");
					}
					@mysql_data_seek($subjects, 0);
				}
				
				$input .= "\r\n";
			}
		break;
		// ********** MISSION COVER SHEET ********** //

		default:
			user_error('unknown type', E_USER_ERROR);
		break;
	
	}
  
	if (!$post && $type != "certificate") {
		sendReport($input, "{$type}s_" . date('YmdGis') . '.pdf', $fmt);
	}
	*/
}

//$types = array('certificate_enrollment'=>T_('Enrollment Certificate'),'certificate'=>T_('Certificate'),'certificate_bare'=>T_('Cheder Tzivos Hashem Certificate'),'tbp_yearly_progress'=>T_('Tanya Baal Peh Yearly Progress Chart'),'tbp_monthly_quota_post'=>T_('Tanya Baal Peh Monthly Quota Report'),'tbp_progress_report_post'=>T_('Tanya Baal Peh Weekly Quota Report'),'tbp_growth_planner'=>T_('Tanya Baal Peh Growth Planner'),'mission_cover_sheet_post'=>T_('Mission Cover Sheet'));
$types = array( 'certificate_bare'=>T_('Cheder Tzivos Hashem Certificate'), 'certificate_birthday'=>T_('Birthday Certificate'));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Soldier's Printable Files"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">

			<H1><?=T_("Soldier's Printable Certificates")?></H1>
			
			<? if (!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>

			<? if(!empty($admin_user) && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)) : ?>
				<? $school_result = mq('SELECT school_id, school_name FROM schools where school_era is null ' . ($admin_user['auth'] != 'super' ? ' and school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' and chayolei = 1 ORDER BY school_name'); ?>

				<FORM action="admin_print_pdf.php" method="get" accept-charset="UTF-8">
					<P>
						<LABEL>
							<?=T_('Select Institution')?>: 
							<SELECT name="school_id">
							    <OPTION value="0">&lt;<?=T_('All')?>&gt;
								<? while($school_row = mysql_fetch_assoc($school_result)): ?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
								<?endwhile;?>
							</SELECT>
						</LABEL>
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>				
				</FORM>
				
				<HR>
			<?endif;?>
			
			<?if($school_id == -1):?>
				<?=T_('Please select an Institution.')?>
			<?else:?>
				<DIV class="noprint">
				<? $class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub"); ?>
				<? $user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username"); ?>


					<FORM action="admin_print_pdf.php" method="get" accept-charset="UTF-8">
						<P>
						<? if (!empty($admin_user)) : ?>
							<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
							<INPUT type="hidden" name="action" value="print">
							    
							<?=T_('Choose Gender')?>: 
                            <select name="gender" />
                            <option value="0" selected="selected">All</option>
                            <option value="m">Boys</option>
                            <option value="f">Girls</option>
                            </select>
                            <br />
                            							
							<?
							//get dates
                            $dates = array();
                            $sql = "SELECT * FROM reports r 
                            		join parshos p on p.start = r.start_date 
                                    WHERE r.report_type='mission_cover_sheet' 
                                    AND r.visibility != 'none' 
                                    and p.year = 5776      
                                    ORDER BY start_date";   
                            $result = mysql_query($sql);
                            while ($row = mysql_fetch_assoc($result)) {
                                $dates[] = $row;
                            }
                            $today = unixtojd();
							?>    
							<?=T_('Choose Parsha')?>: 
                            <SELECT name="parsha">
                                <? foreach( $dates as $date ) { ?>
                                    <? if ( $today >= $date['start_date'] && $today <= $date['end_date'] ) { ?>
                                         <OPTION value="<?=$date['start_date'].':'.$date['end_date']?>" selected='selected'><?=$date['report_name']?></OPTION>                                   
                                    <? } else { ?>
                                        <OPTION value="<?=$date['start_date'].':'.$date['end_date']?>"><?=$date['report_name']?></OPTION>
                                    <? }
                                    } ?>
                            </SELECT>
                            <br />

							<?=T_('Choose Platoon')?>: 
							<SELECT name="class_id">
								<OPTION value="-1">&lt;<?=T_('All')?>&gt;
								<? while ($class_row = mysql_fetch_assoc($class_result)) : ?>
								<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
								<? endwhile; ?>
							</SELECT>

							<BR>

							<?=T_('Choose Soldier')?> 
							<SELECT name="user_id">
								<OPTION value="-1">&lt;<?=T_('All')?>&gt;
								<? while ($user_row = mysql_fetch_assoc($user_result)) : ?>
								<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
								<? endwhile; ?>
							</SELECT>

							<BR>
                            <!--
							<?=T_('Limit to Rank')?>: 
							<SELECT name="rank_ord">
								<OPTION value="-1">&lt;<?=T_('All')?>&gt;
								<? $rank_result = mq('SELECT rank_ord, rank_name FROM ranks ORDER BY rank_ord'); ?>
								<? while ($row = mysql_fetch_assoc($rank_result)) : ?>
								<OPTION value="<?=$row['rank_ord']?>" <?=$row['rank_ord'] == $rank_ord ? 'SELECTED' : ''?>>
									<?=es($row['rank_name'])?>
								</OPTION>
								<? endwhile; ?>
							</SELECT>
                            -->
							<BR>
					<? endif; ?> <!-- if(!empty($admin_user)): -->

							<LABEL>
								<?=T_('File to print')?>: 
								<SELECT name="type">
									<? foreach($types as $this_type => $this_name) : ?>
									<OPTION <?=$this_type == $type ? 'SELECTED' : ''?> value="<?=es($this_type)?>"><?=es($this_name)?></OPTION>
									<? endforeach; ?>
								</SELECT>
							</LABEL>

							<BR>

							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">

						</P>

					</FORM>
<!--
				<? if($post) : ?>
					<FORM action="admin_print_pdf.php" method="get" accept-charset="UTF-8">
<?
						switch($type) {
							case 'tbp_monthly_quota_post':
								if(!isset($new_type)) $new_type = 'tbp_monthly_quota';
								echo T_('4 Week Report Starting with');
								
							case 'tbp_progress_report_post':
								if(!isset($new_type)) $new_type = 'tbp_progress_report';
?>
						<INPUT type="hidden" name="type" value="<?=$new_type?>">
						
						<LABEL><?=T_('Week Number')?>: 						
							<SELECT name="week_num">
								<OPTION value="-1"><?=T_('Current Week')?>
								<OPTION value="-2"><?=T_('Previous Week')?>
								<OPTION DISABLED>--------------
								<? for($i = 0; $i <= 52; $i++): ?>
								<OPTION value="<?=$i?>"><?=$i?>
								<? endfor; ?>
							</SELECT>
						</LABEL>
<?
							break;

							case 'mission_cover_sheet_post':
?>
						<INPUT type="hidden" name="type" value="mission_cover_sheet">
						<LABEL>
							<?=T_('Starting')?>: 
							<SELECT name="starting">
								<OPTION value="this_month"><?=T_('First of this Month')?>
								<OPTION value="next_month"><?=T_('First of next Month')?>
								<OPTION value="this_week"><?=T_('Start of this week')?>
								<OPTION value="next_week"><?=T_('Start of next week')?>
							</SELECT>
						</LABEL>
<?
							break;
						}
?>
						<P>
							<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
							<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
							<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
							<INPUT type="hidden" name="action" value="print">
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						</P>
						
					</FORM>
			<? endif; ?> <!-- if($post) : -->

				</DIV>
				
			<? endif; ?>
			
			</DIV>
			
			<DIV class="noprint">
				<? include('admin_footer.php'); ?>
			</DIV>
		
	</BODY>
	
</HTML>
