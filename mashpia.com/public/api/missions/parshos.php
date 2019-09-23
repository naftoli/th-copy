<?php
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../header/db.php" );

class ParshaRouter {

    public function index() {
        global $MASHPIA_DB;

        // $year = GlobalSettings::getCurrentYear();

        // // get lowest parsha id
        // $stmt = $MASHPIA_DB->prepare("
        //     SELECT id FROM parshos 
        //     WHERE year = :year 
        //     ORDER BY id DESC 
        //     LIMIT 12
        // ");
        // $res = $stmt->execute([':year' => $year - 1]);
        // if ( $res ) {
        //     $rows = $stmt->fetchAll();
        //     // get last row info
        //     $id = $rows[count($rows) - 1]['id'];
        // }

        $dates = GlobalSettings::getCurYearDates();
        $parshos = Parsha::all([
            'conditions' => [
                'start > ' . $dates['start'], 
                'end > ' . $dates['end']
            ]
        ]);

        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();