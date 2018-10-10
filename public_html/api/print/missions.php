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
$objMissions = array();
foreach ( $missions as $info ) {
    foreach ( $info as $mission ) {
        $type = $mission->pic_mission_type;
        $objMissions[] = MissionDisplay::getInstance( $type, $mission );
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
</head>

<body>
    <?php
        // * Print the missions just like before
        foreach ( $objMissions as $obj ) {
            // $obj->setDateDisplay( $showDate );
            $obj->setDblSided( $double_sided );
            $id = $obj->user_id;
            if ($obj->lang_id == 1) {
                echo "<div class='userMission' id='user-" . $id . "' >";
            } else if ($obj->lang_id == 2) {
                echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
            }
            $debug = false;
            if (isset($_GET['debug'])) $debug = true;
            $obj->printMission( $debug );
            echo "</div>";
            echo "<div style='clear: both; page-break-after: always'></div>";
        }
    ?>
    <?php // ! *************************** Debug *************************** ?>
    <!-- <details open="open">
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