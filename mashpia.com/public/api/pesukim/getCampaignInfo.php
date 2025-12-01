<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.teachTotals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukimTotals.php';

$teach = new TeachTotals();
$taughtByRegion = $teach->getTaughtByRegion();
$taughtBySchool = $teach->getTaughtBySchool();
$goalByRegions = $teach->getGoalByRegions();
$goalBySchools = $teach->getGoalBySchools();

$pesukim = new PesukimTotals();
$recruitsByRegion = $pesukim->getRecruitsByRegion();
$recruitsBySchool = $pesukim->getRecruitsBySchool();
$minutesByRegion = $pesukim->getMinutesByRegion();
$minutesBySchool = $pesukim->getMinutesBySchool();

$data = [];
$rank = 0;
$prevTotal = 0;
foreach ($taughtByRegion as $idx => $row) {
  $total = $row['total'];
  if ($total != $prevTotal) {
    $rank++;
    $prevTotal = $total;
  }
  $data['countries'][] = [
    "id" => $row['region'],
    "name" => $row['region'],
    "logo" => "/assets/images/flags/cheder-lubavitch-morristown.png",
    "rank" => $rank,
    "learn" => [
        "goal" => $goalByRegions[$row['region']] ?? 0,
        "current" => $total
    ],
    "recruit" => [
        "goal" => ceil($goalByRegions[$row['region']] ? ($goalByRegions[$row['region']] * 0.25) : 0),
        "current" => $recruitsByRegion[$row['region']] ?? 0
    ],
    "pesukimTotal" => $minutesByRegion[$row['region']] ?? 0
  ];
}

$rank = 0;
$prevTotal = 0;
foreach ($taughtBySchool as $idx => $row) {
  $total = $row['total'];
  if ($total != $prevTotal) {
    $rank++;
    $prevTotal = $total;
  }
  $data['schools'][] = [
    "id" => $row['school_id'],
    "name" => $row['school_name'],
    "logo" => "/schools/cheder-lubavitch-morristown.png",
    "subtitle" => $row['school_city'] . ', ' . $row['school_state'] . ', ' . $row['school_country'],
    "rank" => $rank,
    "learn" => [
        "goal" => $goalBySchools[$row['school_id']] ?? 0,
        "current" => $total
    ],
    "recruit" => [
        "goal" => ceil($goalBySchools[$row['school_id']] ? ($goalBySchools[$row['school_id']] * 0.25) : 0),
        "current" => $recruitsBySchool[$row['school_id']] ?? 0
    ],
    "pesukimTotal" => $minutesBySchool[$row['school_id']] ?? 0
  ];
}

echo json_encode(['data' => $data]);

// $data = [
//   "countries" => [
//     [
//       "id" => "us",
//       "name" => "United States of America",
//       "logo" => "/assets/images/flags/cheder-lubavitch-morristown.png",
//       "rank" => 1,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 22000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 18000
//       ],
//       "pesukimTotal" => 123456
//     ],
//     [
//       "id" => "ru",
//       "name" => "Russia",
//       "logo" => "/assets/images/flags/RU.png",
//       "rank" => 4,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 10000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 8000
//       ],
//       "pesukimTotal" => 12345
//     ],
//     [
//       "id" => "uk",
//       "name" => "United Kingdom",
//       "logo" => "/assets/images/flags/UK.png",
//       "rank" => 2,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 12000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 9000
//       ],
//       "pesukimTotal" => 34567
//     ],
//     [
//       "id" => "fr",
//       "name" => "France",
//       "logo" => "/assets/images/flags/FR.png",
//       "rank" => 3,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 14000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 11000
//       ],
//       "pesukimTotal" => 23456
//     ],
//     [
//       "id" => "za",
//       "name" => "South Africa",
//       "logo" => "/assets/images/flags/SA.png",
//       "rank" => 5,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 7000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 6500
//       ],
//       "pesukimTotal" => 7890
//     ],
//     [
//       "id" => "au",
//       "name" => "Australia",
//       "logo" => "/flags/cheder-lubavitch-morristown.png",
//       "rank" => 6,
//       "learn" => [
//         "goal" => 50000,
//         "current" => 6000
//       ],
//       "recruit" => [
//         "goal" => 50000,
//         "current" => 5800
//       ],
//       "pesukimTotal" => 6789
//     ]
//   ],
//   "schools" => [
//     [
//       "id" => "ot",
//       "name" => "Oholei Torah",
//       "logo" => "/schools/cheder-lubavitch-morristown.png",
//       "subtitle" => "Brooklyn, NY, United States",
//       "rank" => 1,
//       "learn" => [
//         "goal" => 20000,
//         "current" => 8200
//       ],
//       "recruit" => [
//         "goal" => 8000,
//         "current" => 3500
//       ],
//       "pesukimTotal" => 34567
//     ],
//     [
//       "id" => "uly",
//       "name" => "United Lubavitcher Yeshiva",
//       "logo" => "/schools/cheder-lubavitch-morristown.png",
//       "subtitle" => "Brooklyn, NY, United States",
//       "rank" => 2,
//       "learn" => [
//         "goal" => 18000,
//         "current" => 9100
//       ],
//       "recruit" => [
//         "goal" => 7000,
//         "current" => 4100
//       ],
//       "pesukimTotal" => 31234
//     ],
//     [
//       "id" => "lg",
//       "name" => "Lubavitch Girls",
//       "logo" => "/schools/cheder-lubavitch-morristown.png",
//       "subtitle" => "Crown Heights, NY",
//       "rank" => 4,
//       "learn" => [
//         "goal" => 15000,
//         "current" => 7200
//       ],
//       "recruit" => [
//         "goal" => 5500,
//         "current" => 2600
//       ],
//       "pesukimTotal" => 20123
//     ],
//     [
//       "id" => "om",
//       "name" => "Ohr Menachem",
//       "logo" => "/schools/cheder-lubavitch-morristown.png",
//       "subtitle" => "Brooklyn, NY, United States",
//       "rank" => 3,
//       "learn" => [
//         "goal" => 16000,
//         "current" => 7500
//       ],
//       "recruit" => [
//         "goal" => 6000,
//         "current" => 2900
//       ],
//       "pesukimTotal" => 27890
//     ]
//   ]
// ];
