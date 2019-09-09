<?php
include_once( __DIR__ . "/../header/header.php" );

class ParshaRouter {

    public function index() {

        $year = GlobalSettings::getCurrentYear() + 1;

        $p1 = Parsha::find_by_sql('SELECT * FROM parshos WHERE year = ' . ($year - 1) . ' ORDER BY id DESC LIMIT 2');

        $parshos = Parsha::all([
            'conditions' => 'year = ' . $year,
        ]);

        $parshos = $p1 + $parshos;

        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();