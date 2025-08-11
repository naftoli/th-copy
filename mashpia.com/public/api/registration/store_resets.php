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
        'date'  => 'Tuesday September 23rd, 2025',
        'jd'    => gregoriantojd(9, 23, 2025),
        'hDate' => 'Rosh Hashanah, 5786'
    ]
];

json_response($info);