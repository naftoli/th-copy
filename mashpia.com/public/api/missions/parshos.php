<?php
include_once( __DIR__ . "/../header/header.php" );

class ParshaRouter {

    public function index() {
        $parshos = Parsha::all([
            'conditions' => 'year = ' . GlobalSettings::getCurrentYear(),
        ]);

        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();