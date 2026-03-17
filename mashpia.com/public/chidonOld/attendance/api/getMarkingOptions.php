<?php
require_once __DIR__ . '/bootstrap.php';

$sm = attendance_require_staff();
$groups = attendance_get_post_array('groups');
if (empty($groups)) {
    attendance_json_error('No groups provided');
}

if (!$sm->assertGroupsAllowed($groups)) {
    attendance_json_error('Not authorized for one or more selected groups');
}

$times = [];
$timesInfo = $sm->getTimes($groups);
foreach ( $timesInfo as $time_entry ) {
    $time = $time_entry['att_time'];
    $timeDetails = explode(' ', $time);
    $time = ucfirst( $time_entry['day_of_week'] ) . ' ' . $timeDetails[1];
    $times[] = [
        "key" => $time_entry['att_time_id'],
        "type" => $time_entry['att_type'],
        "description" => $time_entry['short_name'], 
        "time"  =>  $time
    ];
}

attendance_json_ok([
    'times' => $times,
]);