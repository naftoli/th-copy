<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

// get campaigns for current year
$sql = "SELECT * FROM line_campaigns WHERE year = " . GlobalSettings::getChidonYear();
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
    else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
}

$marks = [];
$sql = "select dtmm.*, dt.short_name from date_tasks_marks dtmm 
        join date_tasks dt using (date_task_id) 
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        where dt.grid_id in (21001,21002,21003,21004,21005,21006,21007,21008,21013,21014) 
        and dtm.start_date >= 2459027";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $marks[] = $row;
}

$updated = 0;
foreach ($marks as $row) {
    $mark = $row['done_qty'];
    $user_id = $row['user_id'];
    $short_name = $row['short_name'];
    $campaign = $tanyaCampaign;
    if (in_array($short_name, ['Mishna Testing','מבחן משנה'])) $campaign = $mishnaCampaign;

    $campaign_qry = "SELECT mission_sheet_amount AS t FROM lines_learned WHERE campaign_id = " . $campaign . " AND user_id = " . $user_id;
    $exists_query = mysql_query($campaign_qry);
    if (mysql_num_rows($exists_query) > 0) {
        $exists_row = mysql_fetch_assoc($exists_query);
        if ( $mark > 0 ) {
            $update_sql = "UPDATE lines_learned"
                ." SET mission_sheet_amount = " . $mark
                ." WHERE campaign_id = " . $campaign
                ." AND user_id = " . $user_id;
        } else {
            $update_sql = "DELETE FROM lines_learned"
                ." WHERE campaign_id = " . $campaign
                ." AND user_id = " . $user_id;
        }
        if (mysql_query($update_sql)) $updated++;
    } else {
        $user_info_query = mysql_query("SELECT school_id, class_id FROM users WHERE user_id = " . $user_id);
        $user_info = mysql_fetch_assoc($user_info_query);
        $insert_sql = "INSERT INTO lines_learned SET "
            ."campaign_id = " . $campaign . ", "
            ."user_id = " . $user_id . ", "
            ."mission_sheet_amount = " . $mark . ", "
            ."school_id = " . $user_info['school_id'] . ", "
            ."class_id = " . $user_info['class_id'];
        if (mysql_query($insert_sql)) $updated++;
    }
}
echo "Updated: " . $updated;