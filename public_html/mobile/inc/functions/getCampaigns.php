<?php
// returns an array of campaigns (set $all to false for only unenrolled campaigns)
function getCampaigns($user_id, $all = true){
    // internal static information...
    $default_campaigns = array(
        'chabad' => array(
            1,4,12,13,15,16,21,27,40,41,42,45,90,100
        ),
        'frum' => array(
            1,4,15,16,93,92,21,27,94,41,42,45,90,100
        )
    );
    // get the school type and gender for the student
    $student_info = mysql_fetch_assoc(mysql_query("SELECT school_type_id, gender FROM users WHERE user_id = $user_id"));
    $school_type_id = $student_info['school_type_id'];
    $gender = $student_info['gender'];
    
    switch ($school_type_id) {
        case 12: case 13: // 12 and 13 are just frum
            $default_campaigns = $default_campaigns['frum'];
            break;
        case 2: case 3: default: // 2 and 3 and others are chabad
            $default_campaigns = $default_campaigns['chabad'];
            break;
    }
    
    if($all){
        return $default_campaigns;
    }
    
    $campaigns = [];
    $unenrolled_query = mysql_query("SELECT * FROM user_tracks ut JOIN subjects USING (subject_id) WHERE user_id = $user_id AND enrolled = 0 AND ut.subject_id IN (".implode(", ", $default_campaigns).")"); // get all campaigns that the user is not enrolled in...
    while ($row = mysql_fetch_assoc($unenrolled_query)){
        $campaigns[] = $row;
    } // add all the rows into the array....
    
    return $campaigns;
}