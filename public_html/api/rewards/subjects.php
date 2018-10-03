<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SubjectsRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $inst_id = 2; // default institution id.
        $base_filter = 't.base = 1 ';
        $platoon_filter = 't.platoon = 1 ';
        $login  = $current_user->login;

        try {
            if ( $login->code == 'INST' ) {
                $inst_id = $login->id;
                $base_filter .= 'OR t.base IN ( SELECT school_id FROM schools WHERE inst_id = :inst_id ) ';
            } else if ( $login->code == 'BC' ) {
                $inst_id = School::find( $login->id )->inst_id;
                $base_filter .= 'OR t.base = ' . $login->id;
            } else if ( $login->code == 'TEACHER' ) {
                $platoon = Platoon::find( $login->id );
                $inst_id = $platoon->school->inst_id;
                $base_filter .= 'OR t.base = ' . $platoon->school->school_id;
                $platoon_filter .= 'OR t.platoon = ' . $platoon->class_id;
            } 
        } catch ( Exception $e ) {};
        
        $subjects = $MASHPIA_DB->prepare(
             " SELECT s.subject_id, s.subject_name, s.inst_id, count(t.achievement_task_id) AS tasks "
            ." FROM subjects s LEFT JOIN achievement_tasks t ON s.subject_id = t.subject_id "
            ." AND ( $base_filter ) AND ( $platoon_filter ) "
            ." WHERE inst_id IN (0, :inst_id) "
            ." AND subject_type IN ('' , 'WWTC', 'Tanya', 'achievement') "
            ." GROUP BY subject_id;"
        );
        $subjects->execute([ ':inst_id' => $inst_id ]);

        json_response( $subjects->fetchAll(), true, true );
    }
}

rest_router( new SubjectsRouter );
