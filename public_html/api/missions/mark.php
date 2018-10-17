<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

require_once( API_ROOT . '/../mission_report/classes/missions.php' );

class MarkRouter {

    public function getMissions() {

        $soldier = Soldier::find( $_POST['user_id'] );
        $parsha = Parsha::find( $_POST['parsha_id'] );

        // * Generate the missions using the legacy code
        json_response( $soldier->missions( $parsha ), true, true );
    }
}

rest_router( new MarkRouter );

die();