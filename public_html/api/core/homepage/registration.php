<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../header/header.php" );

class RegistrationRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        // define $filters and $params;
        extract( $this->getFilters( $current_user->login ) );
        
        if ( in_array( $current_user->login['code'], ['HQ', 'INST'] ) ) {
            // return registration info for institution
            $year = GlobalSettings::getRegistrationYear();
            $reg_open = false;
            // get the status for instiutions
            $status_query = $MASHPIA_DB->prepare(
                 " SELECT COUNT(*) AS bases, SUM(CASE WHEN date_paid IS NOT NULL THEN 1 ELSE 0 END) AS total "
                ." FROM schools s LEFT JOIN school_registrations sr ON s.school_id = sr.school_id AND sr.year = $year "
                ." WHERE ". implode( ' AND ', $filters ) . ';'
            );
            $status_query->execute( $params );
            $status_numbers = $status_query->fetch();
            $status = $status_numbers['total'] . ' of ' . $status_numbers['bases'] . ' Bases Registered.';
            // Get number of bases registered.
        } else if ( $current_user->login['code'] === 'BC' ) {
            $school = School::find( $current_user->login['id'] );
            $year = GlobalSettings::getRegistrationYear( $school->school_id );
            $reg_info = $school->getRegInfo( $year );
            $reg_open = !$reg_info->default && !$reg_info->date_paid;
            $status = $school->getRegStatus( $year );
        } else if ( $current_user->login['code'] === 'TEACHER' ) {
            $platoon = Platoon::find( $current_user->login['id'] );
            $year = GlobalSettings::getRegistrationYear( $platoon->school_id );
            $status = $platoon->school->getRegStatus( $year );
            $reg_open = false;
        }

        $query = $MASHPIA_DB->prepare(
             ' SELECT COUNT(*) AS soldiers, SUM(CASE WHEN paid > 0 THEN 1 ELSE 0 END) AS total '
            ." FROM users u JOIN schools s USING (school_id) LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = $year "
            .' WHERE ' . implode( ' AND ', $filters ) . ';'
        );
        $query->execute( $params );
        extract( $query->fetch() );

        // year, status, soldiers, total, reg_open
        json_response( [
            'year' => $year,
            'total' => $total,
            'status' => $status,
            'soldiers' => $soldiers,
            'reg_open' => $reg_open,
        ], true, true );
    }

    private function getFilters( $login ){
        // filters and params for the filters
        $filters = [];   $params = [];
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
            $filters[] = '( s.chayolei = 1 OR s.chidon = 1 )';
        } else if ( $login['code'] === 'INST' ) {
            $filters[] = 's.inst_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'u.school_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'u.class_id = ?'; $params[] = $login['id'];
        } else { json_error( 'Access Deinied: HOME-REG-26' ); }
        
        return [ 'filters' => $filters, 'params' => $params ];
    }
}

rest_router( new RegistrationRouter );
