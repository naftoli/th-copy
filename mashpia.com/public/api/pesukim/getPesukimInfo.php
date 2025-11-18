<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukimTotals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.teachTotals.php';

$p = new PesukimTotals();
$t = new TeachTotals();
$taught = $t->getTotalTaught();
$recruits = $p->getTotalRecruits();
$todayMinutes = $p->getTodayMinutes();
$totalMinutes = $p->getTotalMinutes();

$hebDate = jdtojewish(unixtojd(), true, CAL_JEWISH_ADD_GERESHAYIM);
$str1 = iconv ('WINDOWS-1255', 'UTF-8', $hebDate);
$str2 = explode(' ', $str1);
$data = [
    "learnTeach" => [
        "goal" => 50000,
        "taught" => $taught
    ],
    "armyRecruitment" => [
        "goal" => 50000,
        "recruited" => $recruits
    ],
    "pesukim" => [
        "date" => [
            "dow" => (new DateTime())->format('l'),
            "hebrew" => $str2[0] . ' ' . $str2[1],
            "gregorian" => (new DateTime())->format('F d')
        ],
        "today" => $todayMinutes,
        "total" => $totalMinutes
    ]
];

echo json_encode(['data' => $data]);