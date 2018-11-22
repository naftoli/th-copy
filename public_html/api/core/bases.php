<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class BaseRouter {

    public function index(){
        global $MASHPIA_DB;
        $filters = $this->getFilters( true );
        if ( !$filters ) return json_error('Access Deinied');
        $query = $MASHPIA_DB->prepare( 
             " SELECT s.school_number, s.logo, s.school_id, s.school_name, s.school_city, s.school_state, s.school_country, "
            ." s.chayolei, s.chidon, s.tanya, s.tehillim, "
            ." IFNULL( soldier_count, 0 ) as soldier_count "
            ." FROM schools s LEFT JOIN classes c USING (school_id) LEFT JOIN ( "
                ." SELECT COUNT(*) AS soldier_count, school_id FROM users GROUP BY school_id "
            ." ) soldiers USING (school_id) "
            ." WHERE $filters GROUP BY school_id ORDER BY school_name;"
        );
        $query->execute();
        $bases = $query->fetchAll();
        json_response( $bases, true, true );
    }

    public function show( $id ) {
        $base = School::find( $id );
        json_response( $base );
    }

    public function update( $id ) {
        $base = School::find( $id );

        if ( count( $_FILES ) > 0 ) {
            try {
                if ( isset( $_FILES['logo'] ) )
                    $base->setLogo( 'logo', $_FILES['logo'] );
                if ( isset( $_FILES['logo_boys'] ) )
                    $base->setLogo( 'logo_boys', $_FILES['logo_boys'] );
                if ( isset( $_FILES['logo_girls'] ) )
                    $base->setLogo( 'logo_girls', $_FILES['logo_girls'] );
            } catch ( Exception $e ) {
                json_error( $e->getMessage() );
            }
        }

        $base->bulkUpdate( $_POST );
        if ( !$base->save() ) json_error( 'Could not save base' );

        json_response( $base );
    }

    public function deletePayment() {
        // get the base
        $school = $this->getBase();

        if ( !isset( $_POST['payment_profile_id'] ) || intval( $_POST['payment_profile_id'] ) <= 0 )
            json_error( 'CORE-BASE-89: Invalid Request' );

        $profile = new classes\authorize\PaymentProfile(
            $_POST['payment_profile_id'], $school->authorize_customer_profile_id
        );

        if ( $profile->invalid ) 
            return json_error( 'CORE-BASE-96: Invalid Payment Account. Please contact bugs@tzivoshashem.org' );
        
        if ( !$profile->delete() )
            return json_error( 'CORE-BASE-99: Could Not Delete Payment Account. Please contact bugs@tzivoshashem.org' );

        json_response( $profile );
    }

    public function addPayment() {
        // get the base
        $school = $this->getBase();

        $profile = $school->createPaymentProfile( $_POST['cc'] );
        
        if ( !$profile instanceof classes\authorize\PaymentProfile )
            json_error( $profile );

        json_response( $profile );
    }

    private function getBase( $id = false ) {
        global $current_user;

        if ( !$id ) {
            if ( in_array( $current_user->login->code, ['HQ', 'INST'] ) ) {
                if ( !isset( $_POST['school_id'] ) || intval( $_POST['school_id'] ) <= 0 )
                    json_error( 'CORE-BASE-73 Invalid Request' );
                $id = intval( $_POST['school_id'] );
            } else if ( $current_user->login->code == 'BC' ) {
                $id = $current_user->login->id;
            } else {
                json_error( 'Access Deined' );
            }
        }
        // get the school
        try { return School::find( $id ); } 
        catch ( Exception $e ) { return json_error( 'CORE-BASE-85: Base not found' ); }
    }

    private function getFilters( $all ){
        global $current_user; 
        $filters = [];
        $login = $current_user->login;
        
        if ( $all && !in_array( $login->code, [ 'HQ', 'INST' ] ) ) {
            $school_ids = $current_user->getAuthIds( 'school' );
            $filters[] = 's.school_id IN ('. implode(', ', $school_ids ) .')';
        } else {
            $filters[] = $login->getFilter();
        }
        return implode( ' AND ', $filters );
    }

}

rest_router( new BaseRouter );
