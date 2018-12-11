<?php
include_once( __DIR__ . "/../header/header.php" );

class TehillimRouter {

    public function index() {

        $sm = $this->calculateSM();

        json_response( $sm, true, true );
    }

    private	function calculateSM() {
        global $MASHPIA_DB;

        $dates = GlobalSettings::getCurYearDates();
        
        $sm_query = $MASHPIA_DB->prepare(
             ' SELECT start_date as date, REPLACE(mission_description, \'שבת מברכים \', \'\') as month '
            .' FROM date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) '
            .' WHERE subject_id = 1 AND start_date >= :start_date AND end_date <= :end_date '
            .' AND personal = 0 GROUP BY start_date;'
        );
        
        $sm_query->execute([
            ':start_date' => $dates['start'],
            ':end_date' => $dates['end']
        ]);
        
        return $sm_query->fetchAll();
    }
}

rest_router( new TehillimRouter );

die();