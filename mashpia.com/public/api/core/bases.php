<?php
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'tzivos.local') header('Access-Control-Allow-Origin: *'); // CORS
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class BaseRouter {

    public function index(){
        global $MASHPIA_DB;
        $filters = $this->getFilters( true );
        if ( !$filters ) return json_error('Access Deinied');
        $query = $MASHPIA_DB->prepare( 
             " SELECT s.school_number, s.logo, s.school_id, s.school_name, s.school_city, s.school_state, s.school_country, "
            ." s.chayolei, s.chidon, s.tanya, s.tehillim, s.inst_id, "
            ." IFNULL( soldier_count, 0 ) as soldier_count "
            ." FROM schools s LEFT JOIN ( "
                ." SELECT COUNT(*) AS soldier_count, school_id FROM users GROUP BY school_id "
            ." ) soldiers USING (school_id) "
            ." WHERE $filters AND school_id != 612 GROUP BY school_id ORDER BY school_name;"
        );
        $query->execute();
        if ($query->errorCode() !== "00000") {
            json_error("SQL Error: ".implode(', ', $query->errorInfo()), false, 500);
        }
        $bases = $query->fetchAll();
        json_response( $bases );
    }

    public function defaults() {
        $base = new \School();
        // format the response
        json_response( (array)$base );
    }

    public function show( $id ) {
        $base = \School::find([ $id ]);
        json_response( (array)$base );
    }

    public function update( $id ) {
        $base = \School::find([ $id ]);

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

    public function register() {
        global $current_user;

        if ( $_POST['base']['tanya'] == 1 ) $_POST['base']['tehillim'] = 1; // make sure to turn on tehillim if tanya is checked

        $cc = isset( $_POST['cc'] ) ? $_POST['cc'] : false;
        $cart = isset( $_POST['cart'] ) ? $_POST['cart'] : false;
        $total = isset( $_POST['total'] ) ? $_POST['total'] : false;
        $base = isset( $_POST['base'] ) ? $_POST['base'] : false;
        $discount = isset( $_POST['discount'] ) ? $_POST['discount'] : false;

        if ( !$base )
            return json_error('CORE-BASE-63: No Base Information Provided');
        
        if ( $base['school_id'] ) {
            $base = \School::find([ $base['school_id'] ]);
            $base->bulkUpdate( $_POST['base'] );
        } else {
            $base = \School::build( $base );
            $base->save();
            // connect the two
            \AdminAuth::create([
                'admin_id' => $current_user->admin_id,
                'auth' => 'school', 'id' => $base->school_id
            ]);
        }

        $base->register( $current_user->admin_id, $cart, $total, $cc, false, $discount );

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

    public function addCampaigns() {
        $school = $this->getBase();
        switch ($_POST['type']) {
            case 'day school':
                $school->addDaySchoolCampaigns();
                break;
        }
        json_response($school);
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
        try {
            return \School::find([ $id ]);
        } catch ( Exception $e ) {
            return json_error( 'CORE-BASE-85: Base not found' );
        };
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
