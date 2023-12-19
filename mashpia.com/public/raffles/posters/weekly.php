<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 300);
$admin_auth = array('school'); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$year = GlobalSettings::getCurrentYear();

$start_dates_to_hachayol_issues = [
    2460196 => 1,
    2460203 => 2,
    2460210 => 3,
    2460217 => 4,
    2460224 => 5,
    2460231 => 6,
    2460238 => 7,
    2460245 => 8,
    2460252 => 9,
    2460259 => 10,
    2460266 => 11,
    2460273 => 12,
    2460280 => 13,
    2460287 => 14,
    2460294 => 15,
    2460301 => 16,
    2460308 => 17,
    2460315 => 18,
    2460322 => 19,
    2460329 => 20,
    2460336 => 21,
    2460343 => 22,
    2460350 => 23,
    2460357 => 24,
    2460364 => 25,
    2460371 => 26,
    2460378 => 27,
    2460385 => 28,
    2460392 => 29,
    2460399 => 30,
    2460406 => 31,
    2460413 => 32,
    2460420 => 33,
    2460427 => 34,
    2460434 => 35,
    2460441 => 36,
    2460448 => 37,
    2460455 => 38,
    2460462 => 39,
    2460469 => 40,
    2460476 => 41,
    2460483 => 42
];

// get logos for schools
$logos = array();
foreach ($schools as $school_id => $school) {
    $sql = "select logo from schools where school_id = " . $school_id;
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $logos[$school_id] = $row['logo'];
    }
}

// get raffle id's
$raffles = [];
$weeks = [];
$start = key($start_dates_to_hachayol_issues);
$sql = "select * from raffles where type = 'weekly' and date_ran > 0 and start_date >= $start order by start_date";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $raffles[$row['raffle_id']] = $row;
    // map raffle id's to week number
    $weeks[$row['name']] = $row;
}

// get winners
$winners = [];
$winners[0] = []; // we only start counting from 1 so first index (0) needs to be initialized to empty array
foreach ($schools as $school_id => $school) {
    foreach ($raffles as $id => $raffle) {
        $sql = "select u.first, u.last, u.school_id
                from users u
                join raffle_winners rw using (user_id)
                where rw.raffle_id = " . $id . "
                and u.school_id = " . $school_id;
        $result = mysql_query( $sql ) or die( mysql_error() );
        while ($row = mysql_fetch_assoc( $result )) {
            $winners[$row['school_id']][$raffle['name']][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            @font-face {
                font-family: Baloo;
                src: url('Fonts/Baloo-Regular.ttf');
            }

            @font-face {
                font-family: Gotham;
                src: url('Fonts/GothamNarrow-Bold_1.otf');
            }
            @font-face {
                font-family: School;
                src: url('Fonts/Heebo-Regular.ttf');
            }
            .poster {
                width: 7.5in;
                height: 10in;
                page-break-after: always;
                background-repeat: no-repeat;
                margin-left: 30px;
                position: relative;
                background-size: 700px;
            }
            .names {
                top: 7.59in;
                position: absolute;
                line-height: 1.7;
                width: 100%;
                padding-left: 6px; /* match template text centering */
                color: white;
                font-family: Baloo, Gotham, serif;
                font-size: 28px;
                text-align: center;
            }
            .off-page {
                page-break-after: always;
                margin: 30px;
            }
        </style>
    </head>
    <body>
        <button style="padding: 10px 20px; font-size: large;" onclick="window.print()">Print</button>
        <?php
        function renderPoster($week, $issue_number, $winners) {
            global $year;
            ?>
                <div class='poster' style='background-image: url( "./templates/<?= $year ?>/<?= $issue_number ?> 5M <?= $year ?> 7.5x10.jpg" ); '>
                    <div class='names'>
                        <!-- <div class='name'> <?=$week['name']?> - <?=$week['start_date']?> - <?=$issue_number?> </div> -->
                        <? foreach ($winners as $winner) { ?>
                            <div class='name'> <?= $winner['first'] ?> <?= $winner['last'] ?> </div>
                        <? } ?>
                    </div>
                </div>
            <?
        }

        foreach ($winners as $school => $info) {
            if ( isset( $schools[$school] ) ) echo "<h2 class='off-page'>School: " . $schools[$school] . "</h2>";
            foreach ($info as $week_name => $all_winners) {
                $winners_by_poster = array_chunk($all_winners, 4);
                foreach($winners_by_poster as $winners) {
                    if (array_key_exists($weeks[$week_name]['start_date'], $start_dates_to_hachayol_issues)) {
                        $issue_number = $start_dates_to_hachayol_issues[$weeks[$week_name]['start_date']];
                        renderPoster($weeks[$week_name], $issue_number, $winners);
                    }
                }
            }
        }
        ?>
    </body>
</html>