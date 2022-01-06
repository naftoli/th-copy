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
    2459559 => 1,
    2459566 => 2,
    2459573 => 3,
    2459580 => 4,
    2459587 => 5,
    2459594 => 6,
    2459601 => 7,
    2459608 => 8,
    2459615 => 9,
    2459622 => 10,
    2459629 => 11,
    2459636 => 12,
    2459643 => 13,
    2459650 => 14,
    2459657 => 15,
    2459674 => 16,
    2459681 => 17,
    2459688 => 18,
    2459695 => 19,
    2459702 => 20,
    2459709 => 21,
    2459716 => 22,
    2459723 => 23,
    2459730 => 24,
    2459737 => 25,
    2459744 => 26,
    2459751 => 27
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
$raffles = array();
$sql = "select * from raffles where type = 'weekly' and date_ran > 0 and year = " . $year . " order by start_date";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $raffles[$row['raffle_id']] = $row;
    // map raffle id's to week number
    $weeks[$row['name']] = $row;
}

// get winners
$winners = array();
$winners[0] = array(); // we only start counting from 1 so first index (0) needs to be initialized to empty array
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
        <button onclick="window.print()">Print</button>
        <?php
        function renderPoster($week, $issue_number, $winners) {
//            global $start_dates_to_hachayol_issues;
//            $issue_number = array_key_exists($week['start_date'], $start_dates_to_hachayol_issues) ? $start_dates_to_hachayol_issues[$week['start_date']] : false;
//            $issue_number = $start_dates_to_hachayol_issues[$week['start_date']];
//            if (!$issue_number) {
//                echo "<div class='off-page'>missing {$week['name']} posters</div>";
//                return;
//            }
            ?>
                <div class='poster' style='background-image: url( "./templates/<?= $issue_number ?>. Mission Marathon Weekly.jpg" ); '>
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
//                    echo "<pre>"; print_r($weeks[$week_name]) . "<br />"; echo "</pre>";
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