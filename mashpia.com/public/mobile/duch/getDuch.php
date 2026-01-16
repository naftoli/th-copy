<?php
// ini_set('display_errors',1);
// error_reporting(E_ALL);

// require_once( $_SERVER['DOCUMENT_ROOT'] . '/header.php' ); // load header
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.accomplished.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/duch_task.php' );

$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$end = unixtojd();
$user_id = $_GET['id'];
$user = \Soldier::find([ $user_id ]);
$mission = new Missions( $start, $end, $user_id, 0, 0, true, true, true );
$missions = $mission->getMissions();

// * Generate the printed sheets using the legacy code
$objMissions = [];
foreach ( $missions as $mission ) {
    $type = $mission->pic_mission_type;
    if (in_array($mission->school_type_id, [4,5])) {
        $type = 4;
    }
    $objMissions[] = MissionDisplay::getInstance( $type, $mission );
}

// * Convert the dates for the legacy system
$dates_id = 1;
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Exo:ital,wght@0,100..900;1,100..900&family=Heebo:wght@100..900&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Exo', sans-serif;
    }
    .userDuch {
        width: auto;
        margin: auto;
        page-break-after: always;
    }
    .container {
        /* column-count: 3;
        column-gap: 20px;
        height: auto !important; */
        /* min-height: auto !important; */
        /* page-break-after: avoid !important; */
    }
    .track {
        margin-bottom: 15px;
    }
    .campaign-container, .task-container, .streak-container, .medals-container, .promotions-container, .streaks-container, .streak {
        display: flex;
        flex-direction: row;
        gap: 10px;
        break-inside: avoid; /* Don't split items */
        page-break-inside: avoid; /* Older browser support */
    }
    .container .medals-container, .container .promotions-container, .container .streaks-container {
        margin-bottom: 20px;
    }
    .task-container, .streak-container {
        margin-bottom: 5px;
    }
    .task, .medal, .promotion, .campaign-medals {
        display: flex;
        flex-direction: column;
        width: 2in;
        gap: 2px;
    }
    .campaign-name {
        font-size: 20px;
        line-height: 1;
    }
    .campaign-medals {
        font-size: 12px;
    }
    .task-short-name {
        font-size: 14px;
    }
    .task-name {
        font-size: 9px;
    }
    .campaign-icon, .task-stats {
        width: 50px;
        text-align: center;
        margin-bottom: -8px;
    }
    .task-stats {
        font-size: 9px;
        line-height: 1.2;
        padding-top: 5px;
    }
    .medal {
        width: 0.75in;
    }
    .promotion {
        width: 1in;
    }
    .medal-name, .promotion-name {
        font-size: 12px;
        margin-top: -10px;
        text-align: center;
    }
    .streak .campaign-icon {
        width: 75px;
    }
    .streak-text {
        text-align: center;
        font-size: 14px;
        line-height: 1;
    }
    .streak-fill progress {
        height: 30px;
        margin-left: 5px;
    }
    .streak progress {
        width: 2in;
        margin-left: 0;
    }
    .besuros-tovos {
        margin: 20px auto;
        line-height: 2.5;
        text-align: center;
    }
    footer {
        bottom: 0;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        font-family: 'Heebo', sans-serif;
        width: 80%;
        margin: auto;
        margin-top: 20px;
    }
    .pageFooter {
        padding-top: 5px;
        padding-bottom: 2px;
    }
</style>
<script>
    var school_id = <?= $user->school_id ?>;
    var user_id = <?= $user_id ?>;
    var start = <?= $start ?>;
    var end = <?= $end ?>;
</script>
<?php
    $pages = 0;
    // * Print the missions just like before
    foreach ( $objMissions as $obj ) {
        $obj->setDateDisplay( $dates_id );
        // $obj->setDblSided( $double_sided );

        $class = 'userDuch';
        if ($obj->lang_id == 2) $class .= ' he';
        if (in_array($obj->school_type_id , [4,5])) $class .= ' ds';

        $id = $obj->user_id;
        echo "<div class='$class' id='user-$id'";
        if ($obj->lang_id == 2) echo " dir='rtl' ";
        echo ">";

        // get streaks for the user
        $streaks = new Streaks($obj->user_id, $start, $end);
        $activeStreaks = $streaks->getStreaks();
        // echo "<pre>";
        // print_r($activeStreaks);
        // echo "</pre>";

        $debug = false;
        if (isset($_GET['debug'])) $debug = true;
        $pages += $obj->printDuch($activeStreaks, true);
        echo "</div>";
        // echo "<div style='clear: both; page-break-after: always'></div>";
    }
?>