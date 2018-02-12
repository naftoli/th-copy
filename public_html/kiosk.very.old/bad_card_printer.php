<?
function code_details($code_prefix, $code, $user_id) {
  switch($code_prefix) {
    case '1':
      $row = mysql_fetch_assoc(mq("SELECT points, '' bonus, left_circle, right_circle, description, series, TO_DAYS(expiration_date)+1721060 expires, school_number, school_name, school_city, school_state, school_logo_id, subject_name, subject_image_id FROM points_codes JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE code_id = $code"));
      break;

    case '4':
      $row = mysql_fetch_assoc(mq("SELECT code_id id, (SELECT IFNULL(SUM(points*mandatory_qty), 0) FROM date_tasks JOIN date_tasks_missions pm USING (date_tasks_mission_id) WHERE pm.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND is_bonus = 0) points, (SELECT IFNULL(SUM(points*mandatory_qty), 0) FROM date_tasks JOIN date_tasks_missions pm USING (date_tasks_mission_id) WHERE pm.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND is_bonus != 0 AND is_bonus = date_tasks_mission_codes.include_bonus_task) bonus, mission_number left_circle, mission_number right_circle, mission_name description, NULL series, TO_DAYS(expiration_date)+1721060 expires, school_number, school_name, school_city, school_state, school_logo_id, subject_name, subject_image_id FROM date_tasks_mission_codes JOIN user_tracks USING (subject_id) JOIN users USING (user_id, school_id) JOIN date_tasks_missions USING (mission_number, school_type_id, subject_id, level, track_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE code_id = $code AND user_id = $user_id"));
      break;

    case '5':
      $code_user_id = substr($code, 0, 9);
      $date_tasks_mission_id = substr($code, 9, 10);
      if($code_user_id != $user_id) return false;

      $row = mysql_fetch_assoc(mq("SELECT $code id, SUM(mark_points) points, '' bonus, mission_number left_circle, mission_number right_circle, mission_name description, NULL series, MAX(mark_date)+180 expires, school_number, school_name, school_city, school_state, school_logo_id, subject_name, subject_image_id FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE user_id = $user_id AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 1 GROUP BY date_tasks_mission_id"));
     break;

    case '7':
      $row = mysql_fetch_assoc(mq("SELECT code_id id, 25 points, '' bonus, ROUND(medal_stage/25) left_circle, ROUND(medal_stage/25) right_circle, " . ms(T_('Checkpoint')) . " description, NULL series, TO_DAYS(expiration_date)+1721060 expires, school_number, school_name, school_city, school_state, school_logo_id, subject_name, subject_image_id FROM tanya_medal_cards JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE code_id = $code"));
      break;

    default:
      $row = false;
      break;
  }

  return $row;
}

function display_card_front($expires, $school_number, $school_name, $school_city, $school_state, $school_logo_id) {
  global $align_start, $align_end;

  return '
<DIV class="card_front card_front_' . $align_start . '">
<P>
  <IMG src="/images/Chayolei_Tzivos_Hashem.png" alt="' . T_('Chayolei Tzivos Hashem') . '"><BR>
  <B><IMG src="/images/Achievement_Card.png" alt="' . T_('Achievement Card') . '"></B>
</P>
<P>
' . T_('This card is only valid in') . ':
</P>
<TABLE>
<TR>
<TD>' . (!is_null($school_logo_id) ? linkImgFile($school_logo_id) : '') . '</TD>
<TD>
<B>' . T_('BASE'). ' #' . $school_number . '</B><BR>
<B>' . es($school_name) . '</B><BR>
' . es($school_city) . ', ' . es($school_state) . '
</TD>
</TR>
</TABLE>
<P>' . T_('This card expires'). ': <B>' . dateToHebrewCommaYear($expires) . '</B></P>

</DIV>
';
}

function display_card_back($id, $points, $bonus, $left_circle, $right_circle, $description, $subject_name, $subject_image_id, $series) {
  global $align_start, $align_end;

  if($left_circle === '') $left_circle = '&nbsp;';
  if($right_circle === '') $right_circle = '&nbsp;';
  $bonus = floatval($bonus) ? ' + ' . floatval($bonus) . ' ' . T_('Bonus') : '';
  return '
<DIV class="card_back" style="margin-right:80px;">
<DIV class="border">
<TABLE style="width: 100%;">
<TR>
<TD style="width: 33%;">
  <DIV class="circle">' . $left_circle . '</DIV>
  ' . es($description) . '
</TD>
<TH>' . (!is_null($subject_image_id) ? linkImgFile($subject_image_id) : '') . '</TH>
<TD style="width: 33%;">
  <DIV class="circle">' . $right_circle . '</DIV>
  ' . es($subject_name) . ($series !== '' && !is_null($series) ? " #$series" : '') . '
</TD>
</TR>
</TABLE>
<DIV class="barcode"><IMG SRC="/barcode.php/' . $id . '" alt=""><BR>' . $id . '</DIV>
<DIV class="points"><TABLE><TR><TD><DIV class="border">' . floatval($points) . ' ' . T_('Miles') . '0' . '</DIV></TD></TR></TABLE></DIV>
</DIV>
</DIV>
';
}
?>
