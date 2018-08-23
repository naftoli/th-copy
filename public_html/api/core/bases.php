<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class BaseRouter {

    public function index(){
        global $pdo;
        $params = [];
        $filters = $this->getFilters(false, $params);
        if ( !$filters ) return json_error('Access Deinied');
        $query = $pdo->prepare( 
             " SELECT s.school_number, s.logo, s.school_id, s.school_name, s.school_city, s.school_state, s.school_country, "
            ." s.chayolei, s.chidon, s.tanya, s.tehillim, s.ckids, "
            ." IFNULL( soldier_count, 0 ) as soldier_count "
            ." FROM schools s LEFT JOIN classes c USING (school_id) LEFT JOIN ( "
                ." SELECT COUNT(*) AS soldier_count, school_id FROM users GROUP BY school_id "
            ." ) soldiers USING (school_id) "
            ." WHERE $filters GROUP BY school_id ORDER BY school_name;"
        );
        $query->execute( $params );
        $bases = $query->fetchAll();
        json_response( $bases );
    }

    public function small() {
        global $pdo;
        $params = [];
        $all = isset( $_POST['all'] ) ? $_POST['all'] : false;
        $filters = $this->getFilters( $all, $params );
        if ( !$filters ) return json_error('Access Deinied');
        // generate the SQL
        $query = $pdo->prepare( 
            "SELECT s.school_id, s.school_name FROM classes c JOIN schools s USING (school_id)"
            ." WHERE $filters GROUP BY school_id ORDER BY school_name;"
        );
        $query->execute( $params );
        $bases = $query->fetchAll();
        json_response( $bases );
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

    private function getFilters( $all, &$params ){
        global $current_user; 
        $filters = [];
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' ) $filters[] = 's.test_school = 1';
        else if ( $login['code'] === 'CKIDS-ADMIN' ) $filters[] = 's.ckids = 1';
        // get all bases on the account
        else if ( $all ) { 
            $school_ids = $current_user->getAuthIds( 'school' );
            $filters[] = 's.school_id IN ('. implode(', ', $school_ids ) .')';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 's.school_id = ?';   $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'c.class_id = ?';   $params[] = $login['id'];
        } else return false;
        // combine the filters
        return implode( ' AND ', $filters );
    }
}

rest_router( new BaseRouter );
