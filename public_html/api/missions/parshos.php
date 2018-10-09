<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PrizesRouter {

    public function index() {
        $parshos = Parsha::find('all', [
            'conditions' => 'year = ' . GlobalSettings::getCurrentYear(),
        ]);

        json_response( $parshos, true, true );
    }
}

rest_router( new PrizesRouter );

die();