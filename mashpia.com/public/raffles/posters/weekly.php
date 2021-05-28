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
    2459111 => 420,
    2459118 => 421,
    2459125 => 422,
    // skips week for succos
    2459139 => 423,
    2459146 => 424,
    2459153 => 425,
    2459160 => 426,
    2459167 => 427,
    2459174 => 428,
    2459181 => 429,
    2459188 => 430,
    2459195 => 431,
    2459202 => 432,
    2459209 => 433,
    2459216 => 434,
    2459223 => 435,
    2459230 => 436,
    2459237 => 437,
    2459244 => 438,
    // 2459251 => 439, used as demo with placeholder names
    2459258 => 440,
    2459265 => 441,
    2459272 => 442,
    2459279 => 443,
    2459286 => 444,
    2459293 => 445,
    2459300 => 446,
    // skips week for pesach
    2459314 => 447,
    2459321 => 448,
    2459328 => 449,
    2459335 => 450,
    // 2459342 => 451, missing file
    2459349 => 452,
    2459356 => 453
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
$k = 1;
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
            }
            .names {
                top: 7.6in;
                position: absolute;
                line-height: 1.6;
                text-transform: uppercase;
                width: 100%;
                padding-left: 6px;
                color: white;
            }
            .name {
                font-family: Gotham;
                font-size: 25px;
                text-align: center;
            }
            .off-page {
                page-break-after: always;
                margin: 30px;
            }
        </style>
    </head>
    <body>
        <?php
        function renderPoster($school, $week, $winners) {
            global $start_dates_to_hachayol_issues;
            $issue_number = array_key_exists($week['start_date'], $start_dates_to_hachayol_issues) ? $start_dates_to_hachayol_issues[$week['start_date']] : false;
            if (!$issue_number) {
                echo "<div class='off-page'>missing {$week['name']} posters</div>";
                return;
            }
            ?>
                <div class='poster' style='background-image: url( "./templates/Mission Marathon Prizes 5781-<?=$issue_number?>.jpg" ); '>
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
                    renderPoster($schools[$school], $weeks[$week_name], $winners);
                }
            }
        }
        ?>
    </body>
</html>