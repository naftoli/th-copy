<?php
include_once( __DIR__ . "/../header/header.php" );

class ParshaRouter {

    public function index() {
        global $MASHPIA_DB;

        $start = GlobalSettings::getSummerMissionsStart();
        $end = GlobalSettings::getCurYearDates()['end'];
        $qry = "SELECT * FROM parshos WHERE start >= :start AND end <= :end";
        $stmt = $MASHPIA_DB->prepare( $qry );
        $stmt->execute([
            ':start'  => $start,
            ':end'    => $end
        ]);
        $parshos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        json_response( $parshos, true, true );
    }
}

rest_router( new ParshaRouter );

die();