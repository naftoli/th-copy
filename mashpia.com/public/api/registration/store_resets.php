<?php
//define( "MASHPIA_AUTH_REQUIRED", true );
include_once $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";

$info = [
    [
        'title' => 'The beginning of the summer',
        'jd'    => gregoriantojd(7, 9, 2024),
        'date'  => 'Tuesday July 9, 2024',
        'hDate' => 'Gimmel Tamuz, 5784'
    ],
    [
        'title' => 'The beginning of the school year',
        'date'  => 'Tuesday August 27th, 2024',
        'jd'    => gregoriantojd(8, 27, 2024),
        'hDate' => 'Chof Gimmel Menachem-Av, 5784'
    ]
];

json_response($info);