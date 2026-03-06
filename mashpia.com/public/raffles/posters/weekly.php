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
    2460924 => 619,
    2460931 => 620,
    2460938 => 621,
    2460945 => 622,
    2460952 => 623,
    2460959 => 624,
    2460966 => 625,
    2460973 => 626,
    2460980 => 627,
    2460987 => 628,
    2460994 => 629,
    2461001 => 630,
    2461008 => 631,
    2461015 => 632,
    2461022 => 633,
    2461029 => 634,
    2461036 => 635,
    2461043 => 636,
    2461050 => 637,
    2461057 => 638,
    2461064 => 639,
    2461071 => 640,
    2461078 => 641,
    2461085 => 642,
    2461092 => 643,
    2461099 => 644,
    2461106 => 645,
    2461113 => 646,
    2461120 => 647,
    2461127 => 648,
    2461134 => 649,
    2461141 => 650,
    2461148 => 651,
    2461155 => 652,
    2461162 => 653,
    2461169 => 654,
    2461176 => 655,
    2461183 => 656,
    2461190 => 657,
    2461197 => 658,
    2461204 => 659
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
$sql = "select * from raffles where type = 'weekly' and date_ran > 0 and show_for_bc = 1 and start_date >= $start order by start_date";
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
                <div class='poster' style='background-image: url( "./templates/<?= $year ?>/<?= $issue_number ?>.jpg" ); '>
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