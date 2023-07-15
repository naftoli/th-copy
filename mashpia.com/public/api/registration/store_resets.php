<?php
//define( "MASHPIA_AUTH_REQUIRED", true );
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$summer_start = GlobalSettings::getCurYearDates()['start'];

$info = [
    [
        'title' => 'The beginning of the summer',
        'jd'    => $summer_start,
        'date'  => 'Sunday July 2nd, 2023',
        'hDate' => 'Yud Gimmel Tamuz, 5783'
    ],
    [
        'title' => 'The beginning of the school year',
        'date'  => 'Tuesday September 5th, 2023',
        'jd'    => gregoriantojd(9, 5, 2023),
        'hDate' => 'Yud Tes Elul, 5783'
    ]
];

json_response($info);