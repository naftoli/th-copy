<?php
include_once( __DIR__ . "/../header/header.php" );

class ParshaRouter {

    public function index() {

        $p1 = Parsha::find_by_sql('SELECT * FROM parshos WHERE year = 5779 ORDER BY id DESC LIMIT 2');

        $parshos = Parsha::all([
            'conditions' => 'year = ' . GlobalSettings::getCurrentYear() + 1,
        ]);

        $parshos = $p1 + $parshos;

        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();