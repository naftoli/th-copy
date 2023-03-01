<?php
$admin_auth = ['school', 'user'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$ultimate_trip = json_decode($_POST['ultimate_trip']);
$ultimate_info = json_decode($_POST['ultimate_info']);

function saveUltimateTripInfo() {
    global $MASHPIA_DB, $year, $ultimate_trip, $ultimate_info;

    $ultimate_info = (array) $ultimate_info;
    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon
        SET
            ultimate_trip = 1, 
            host = :family, 
            host_number = :phone, 
            host_street = :street, 
            host_street_num = :street_num, 
            host_street_num_suffix = :suffix, 
            host_street_apt = :apt, 
            in_zone = :in_zone, 
            between_streets1 = :between1, 
            between_streets2 = :between2, 
            allergies = :allergies, 
            sandwich = :sandwich, 
            walking_zone = :zone, 
            shoe_size = :shoe, 
            walking = :walk_alone, 
            poll = :chidon_answer
        WHERE
            user_id = :user AND year = :year
    ");

    $success = true;
    if ($ultimate_trip && count($ultimate_trip)) {
        foreach ($ultimate_trip as $user_id) {
            if (isset($ultimate_info[$user_id])) {
                $info = $ultimate_info[$user_id];
                $acc = $info->accomodation;
                $res = $stmt->execute([
                    'family' => $acc->family,
                    'phone' => $acc->phone,
                    'street' => $acc->street,
                    'street_num' => $acc->number,
                    'suffix' => $acc->suffix,
                    'apt' => $acc->apt,
                    'in_zone' => $info->in_zone,
                    'between1' => $acc->between1,
                    'between2' => $acc->between2,
                    'allergies' => $info->allergies,
                    'sandwich' => $info->sandwich,
                    'zone' => $acc->zone,
                    'shoe' => $info->shoe,
                    'walk_alone' => $info->walk_alone,
                    'chidon_answer' => $info->chidon_answer,
                    'user' => $user_id,
                    'year' => $year
                ]);
                if (!$res) {
                    $stmt->debugDumpParams();
                    $success = false;
                    break;
                }
            }
        }
    }
    return $success;
}

$saved = saveUltimateTripInfo();
echo $saved ? 0 : 1; // echo 1 to signify error