<?php
include_once( __DIR__ . "/../header/header.php" );

class ParshaRouter {

    public function index() {
        $dates = GlobalSettings::getCurYearDates();
        $parshos = Parsha::all([
            'order' => 'start',
            'conditions' => [
                'start >= ' . $dates['start'], 
                'end <= ' . $dates['end'],
            ]
        ]);
        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();