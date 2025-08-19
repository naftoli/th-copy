<?php
//define( "MASHPIA_AUTH_REQUIRED", true );
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";

$info = [
    [
        'title' => 'The beginning of the summer',
        'jd'    => gregoriantojd(6,29, 2025),
        'date'  => 'Sunday June 29, 2025',
        'hDate' => 'Gimmel Tamuz, 5785'
    ],
    [
        'title' => 'The beginning of the school year',
        'date'  => 'Friday September 5th, 2025',
        'jd'    => gregoriantojd(9, 5, 2025),
        'hDate' => 'Yud Beis Elul, 5785'
    ]
];

json_response($info);