<?php
require_once( '../header/header.php' ); // load header

require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );

$school = School::find( $_POST['school_id'] );
$user_ids = $_POST['user_ids'] ? explode( ',', $_POST['user_ids'] ) : false;
$class_ids = $_POST['class_ids'] ? explode( ',', $_POST['class_ids'] ) : false;
$parsha_ids = $_POST['parsha_ids'] ? explode( ',', $_POST['parsha_ids'] ) : false;

$double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';
$dates = $_POST['dates'];
$pages = $_POST['pages'];

// * Set class_ids and user_ids if not set by client
if ( !$class_ids )
    $class_ids = array_map(function ($p) { return $p->class_id; }, $school->platoons);

if ( !$user_ids ) {
    $users = Soldier::find_all_by_class_id( $class_ids );
    $users = array_filter($users, function ($u) { return $u->user_registered; });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
// make sure the soldiers are in the selected platoons if provided with an array of soldiers.
} else if ( $user_ids ) {
    $users = Soldier::find( $user_ids );
    $users = is_array( $users ) ? $users : [ $users ]; // make sure it is an array so we can filter it
    $users = array_filter($users, function ($u) use ($class_ids) { return in_array( $u->class_id, $class_ids ); });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
}

if ( !$parsha_ids ) {
    echo 'Cannot Print 0 Parshos. Please select at least 1 parsha.'; die();
}

$parshos = Parsha::find( $parsha_ids );
$parshos = is_array( $parshos ) ? $parshos : [ $parshos ]; // make sure it is an array of objects.

// * Generate the missions using the legacy code
$missions = [];
foreach( $user_ids as $user_id ) {
    foreach( $parshos as $parsha ) {
        $mission = new Missions( $parsha->start, $parsha->end, $user_id, 0, 0, true, true );
        $missions[] = $mission->getMissions();
    }
}

// * Generate the printed sheets using the legacy code
$objMissions = [];
foreach ( $missions as $info ) {
    foreach ( $info as $mission ) {
        $type = $mission->pic_mission_type;
        $objMissions[] = MissionDisplay::getInstance( $type, $mission );
    }
}

// * Convert the dates for the legacy system
$dates_id = 1;
if ( $dates == 'none' ) $dates_id = 0;
if ( $dates == 'english' ) $dates_id = 2;

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
</head>

<body>
    <div id='stats'>
        <p>Soldiers Printed: <?= count( $user_ids ) ?> | Parshos Printed: <?= count( $parsha_ids ) ?></p>
        <p id='total'>
            Total Sheets: <?= count( $user_ids ) * count( $parsha_ids ) ?> |
            Total Pages: <span id='total-pages'>&#x25cc;</span> 
        </p>
    </div>

    <?php
        $pages = 0;
        // * Print the missions just like before
        foreach ( $objMissions as $obj ) {
            $obj->setDateDisplay( $dates_id );
            $obj->setDblSided( $double_sided );
            $obj->setMinPages( $pages );

            $id = $obj->user_id;
            if ($obj->lang_id == 1) {
                echo "<div class='userMission' id='user-" . $id . "' >";
            } else if ($obj->lang_id == 2) {
                echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
            }
            $debug = false;
            if (isset($_GET['debug'])) $debug = true;
            $pages += $obj->printMission( $debug );
            echo "</div>";
            echo "<div style='clear: both; page-break-after: always'></div>";
        }
    ?>
    <input type='hidden' id='pages-printed' value='<?=$pages?>' />
    <script>
        document.querySelector('#total-pages').innerText = document.querySelector('#pages-printed').value;
        window.print();
    </script>
    <?php // ! *************************** Debug *************************** ?>
    <!-- <details id='debug'>
        <summary>Debug</summary>
        <pre>
        <?php
            // print_r([
            //     'post' => $_POST,
            //     'vars' => [
            //         'dates' => $dates,
            //         'pages' => $pages,
            //         'school_id' => $school->school_id,
            //         'user_ids' => $user_ids,
            //         'class_ids' => $class_ids,
            //         'parsha_ids' => $parsha_ids,
            //         'double_sided' => $double_sided,
            //     ]
            // ]);
        ?>
        </pre>
    </details> -->
</body>
</html>