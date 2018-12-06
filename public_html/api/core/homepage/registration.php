<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../header/header.php" );

class RegistrationRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $filters = [ $current_user->login->getFilter( 's.', 'u.' ) ];
        if ( !$filters[0] )
            json_error( 'Access Deinied: HOME-REG-26' );

        if ( $current_user->login->code == 'HQ' )
                $filters[] = '( s.chayolei = 1 OR s.chidon = 1 )';

        $filter = implode( ' AND ', $filters );
        
        if ( in_array( $current_user->login->code, ['HQ', 'INST'] ) ) {
            // return registration info for institution
            $year = GlobalSettings::getRegistrationYear();
            $reg_open = false;
            // get the status for instiutions
            $status_query = $MASHPIA_DB->prepare(
                 " SELECT COUNT(*) AS bases, SUM(CASE WHEN date_paid IS NOT NULL THEN 1 ELSE 0 END) AS total "
                ." FROM schools s LEFT JOIN school_registrations sr ON s.school_id = sr.school_id AND sr.year = $year "
                ." WHERE $filter;"
            );

            $status_query->execute();
            $status_numbers = $status_query->fetch();
            $status = $status_numbers['total'] . ' of ' . $status_numbers['bases'] . ' Bases Registered.';
            // Get number of bases registered.
        } else if ( $current_user->login->code === 'BC' ) {
            $school = $current_user->login->model;
            $year = GlobalSettings::getRegistrationYear( $school->school_id );
            $reg_info = $school->registration( $year );
            $reg_open = !$reg_info; // TODO, add option for HQ to disable schools from registering?
            $status = $school->getRegStatus( $year );
        } else if ( $current_user->login->code === 'TEACHER' ) {
            $platoon = $current_user->login->model;
            $year = GlobalSettings::getRegistrationYear( $platoon->school_id );
            $status = $platoon->school->getRegStatus( $year );
            $reg_open = false;
        }

        $query = $MASHPIA_DB->prepare(
             ' SELECT COUNT(*) AS soldiers, SUM(CASE WHEN paid > 0 THEN 1 ELSE 0 END) AS total '
            ." FROM users u JOIN schools s USING (school_id) "
            ." LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = $year "
            .' WHERE u.chayolei = 1 AND ' . $filter
        );
        $query->execute();
        extract( $query->fetch() );

        // year, status, soldiers, total, reg_open
        json_response( [
            'year' => $year,
            'total' => $total,
            'status' => $status,
            'soldiers' => $soldiers,
            'reg_open' => $reg_open
        ], true, true );
    }
}

rest_router( new RegistrationRouter );
