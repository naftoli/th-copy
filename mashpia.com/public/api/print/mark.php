<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
require_once( '../header/header.php' ); // load header

require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );

$objMissions = [];

// * Get the list of parshos for the dropdown
$year = GlobalSettings::getCurrentYear();
$today = unixtojd();
$parshos = Parsha::all([
    'conditions' => "year = $year AND end < $today",
    'order' => 'end DESC'
]);

if ( !isset( $_POST['user_id'] ) )
    $_POST['user_id'] = 0;

if ( !isset( $_POST['parsha_id'] ) )
    $_POST['parsha_id'] = $parshos[0]->id;

try {
    $soldier = \Soldier::find([ $_POST['user_id'] ]);
    $parsha = \Parsha::find([ $_POST['parsha_id'] ]);
} catch ( ActiveRecord\RecordNotFound $e ) {
    // fallback to first available option
    $parsha = $parshos[0];
    $soldier = $current_user->login->model->soldiers[0];
}

// * Generate the missions using the legacy code
$missions = new Missions( $parsha->start, $parsha->end, $soldier->user_id);
$missions = $missions->getMissions();

foreach ( $missions as $mission ) {
    $type = $mission->pic_mission_type;
    $objMissions[] = MissionDisplay::getInstance( $type, $mission );
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Mark Printed Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
    <style>
        div#marking {
            padding: 4px 25px 15px; height: auto; background: #fff; 
            border-radius: 0px; border: 0px; border-bottom: 3px dashed;
        }
        div#options { display: flex; justify-content: space-evenly; align-items: flex-end; }
        form#lookup { text-align: center; margin-top: 16px; }

        div#scanNext {
            position: fixed;
            background: #ccc;
            padding: 15px;
            width: 75px;
            text-align: center;
            border-radius: 15px;
            bottom: 15px;
            right: 15px;
        }

        div#scanNext:hover {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <input type='hidden' id='user_id' value='<?= $soldier->user_id; ?>'/>
    <div id='marking'>
        <form method='post' id='navigate'>
            <div id='options'>
                <div>
                    <label for='platoon'>Platoon</label>
                    <div>
                        <div class="arrow-left"></div>
                        <select id='platoon'>
                        <?php foreach( $soldier->school->platoons as $platoon ) { ?>
                            <option value='<?=$platoon->class_id?>' <?= $platoon->class_id == $soldier->class_id ? 'selected' : ''?>>
                                <?=$platoon->name()?>
                            </option>
                        <?php } ?>
                        </select>
                        <div class="arrow-right"></div>
                    </div>
                </div>
                <div>
                    <label for='soldier'>Soldier</label>
                    <div>
                        <div class="arrow-left"></div>
                        <select id='soldier' name='user_id'>
                        <?php foreach( $soldier->platoon->soldiers as $student ) { ?>
                            <?php if ( $student->user_registered ) { ?>
                                <option value='<?=$student->user_id?>' <?= $student->user_id == $soldier->user_id ? 'selected' : ''?>>
                                    <?=$student->name()?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                        </select>
                        <div class="arrow-right"></div>
                    </div>
                </div>
                <div>
                    <label for='parsha'>Parsha</label>
                    <div>
                        <div class="arrow-left"></div>
                        <select id='parsha' name='parsha_id'>
                        <?php foreach( $parshos as $option ) { ?>
                            <option value='<?=$option->id?>' <?= $option->id == $parsha->id ? 'selected' : ''?>>
                                <?=$option->name?>
                            </option>
                        <?php } ?>
                        </select>
                        <div class="arrow-right"></div>
                    </div>
                </div>
                <button type='submit'>Load Missions</button>
            </div>
        </form>
        <form id='lookup'>
            <label for='lookup-user'>
                <strong>Enter Serial Number / Barcode Scan:</strong>
            </label>
            <input id='lookup-user' autofocus />
            <button id='lookup-button'>Lookup</button>
        </form>
    </div>

    <div id='scanNext'>
        Scan Next Soldier
    </div>

    <?php
        // * Print the missions just like before
        foreach ( $objMissions as $obj ) {
            $id = $obj->user_id;
            if ($obj->lang_id == 1) {
                echo "<div class='userMission' id='user-" . $id . "' >";
            } else if ($obj->lang_id == 2) {
                echo "<div class='userMission he' id='user-" . $id . "' dir='rtl' >";
            }
            $obj->markMissionCol();
            echo "</div>";
        }
    ?>

    <div style="clear: both"></div>
    <br /><br />
    
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script src='mark.js'></script>

    <?php // ! *************************** Debug *************************** ?>
    <pre>
    <?php
        // print_r([
        //     'post' => $_POST,
        //     'vars' => [
        //         'soldier' => $soldier,
        //         'parsha' => $parsha,
        //     ]
        // ]);
    ?>
    </pre>
</body>
</html>