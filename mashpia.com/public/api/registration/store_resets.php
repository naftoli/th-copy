<?php
//define( "MASHPIA_AUTH_REQUIRED", true );
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";

$info = [
    [
        'title' => 'The beginning of the summer',
        'jd'    => gregoriantojd(7, 1, 2024),
        'date'  => 'Monday July 1st, 2024',
        'hDate' => 'Chof Hey Sivan, 5784'
    ],
    [
        'title' => 'The beginning of the school year',
        'date'  => 'Tuesday September 3rd, 2024',
        'jd'    => gregoriantojd(9, 3, 2024),
        'hDate' => 'Rosh Chodesh Elul, 5784'
    ]
];

json_response($info);