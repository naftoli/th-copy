<?php
require_once($_SERVER['DOCUMENT_ROOT']."/class.campaignEnrollment.php");
// returns an array of campaigns (set $all to false for only unenrolled campaigns)
function getCampaigns($user_id, $all = true){
    
    $campaign_enrollment = new CampaignEnrollment($user_id);
    // get the campaigns from the static array....
    $default_campaigns = $campaign_enrollment->getEligibleCampaigns();
    
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