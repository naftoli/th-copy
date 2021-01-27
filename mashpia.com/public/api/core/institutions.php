<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class InstRouter {

    public function index(){
        $institutions = \Institution::all();
        $schools = [];
        foreach ( $institutions as $institution ) {
            switch ($institution->inst_id) {
                case 2:
                    $institution->inst_name = 'Chabad School (Chayolei Tzivos Hashem)';
                    break;
                case 10:
                    $institution->inst_name = 'Chabad Hebrew School (CKids)';
                    break;
            }
            if ($institution->inst_id != 12) $schools[] = $institution;
        }
        json_response( $schools, true, true );
    }
}

rest_router( new InstRouter );
