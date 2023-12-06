<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

if (isset($_POST['type'])) {
    require_once '../api/header/db.php';
    require_once '../api/models/School.php';
    $id = $_POST['id'];
    $school = \School::find([$id]);
    $res = $school->enrollIntoCampaigns();
    if (!$res) {
        echo "Error updating campaigns for school $id";
    } else {
        echo "Successfully updated campaigns for school $id";
    }
} else {
    $users = $_POST['id'];
    if (!is_array($users)) $users = array($users);
    require_once '../db.php';
    require_once '../class.campaignEnrollment.php';
    
    // update child to be enrolled in all campaigns
    foreach ($users as $user_id) {
        try {
            $c = new CampaignEnrollment($user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {
            echo $e->getMessage();
        }
    }
}