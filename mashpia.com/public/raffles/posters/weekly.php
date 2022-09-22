<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 300);
$admin_auth = array('school'); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$year = GlobalSettings::getRegistrationYear();

$start_dates_to_hachayol_issues = [
    2459825 => 1,
    2459832 => 2,
    2459839 => 3,
    2459846 => 4,
    2459853 => 5,
    2459860 => 6,
    2459867 => 7,
    2459874 => 8,
    2459881 => 9,
    2459888 => 10,
    2459895 => 11,
    2459902 => 12,
    2459909 => 13,
    2459916 => 14,
    2459923 => 15,
    2459930 => 16,
    2459937 => 17,
    2459944 => 18,
    2459951 => 19,
    2459958 => 20,
    2459965 => 21,
    2459972 => 22,
    2459979 => 23,
    2459986 => 24,
    2459993 => 25,
    2460000 => 26,
    2460007 => 27,
    2460014 => 28,
    2460021 => 29,
    2460028 => 30,
    2460035 => 31,
    2460042 => 32,
    2460049 => 33,
    2460056 => 34,
    2460063 => 35,
    2460070 => 36,
    2460077 => 37,
    2460084 => 38,
    2460091 => 39,
    2460098 => 40,
    2460105 => 41
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
$sql = "select * from raffles where type = 'weekly' and date_ran > 0 and year = " . $year . " order by start_date";
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
            ?>
                <div class='poster' style='background-image: url( "./templates/<?= $issue_number ?> 5M 5783.jpg" ); '>
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