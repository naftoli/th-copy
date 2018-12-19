<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class MilesRouter {

    public function index() {
        // Get available Miles (and other info?)
        return json_response([
            'miles' => $this->getMaxMiles(),
        ]);
    }

    public function manual() {
        global $POINTS_DB;

        $action = $_POST['action'];
        $auction = $_POST['auction'];
        $class_id = $_POST['class_id'];
        $user_id = $_POST['user_id'];
        $miles = intval( $_POST['miles'] );

        if ( !$user_id ) {
            $where_column = 'class_id';
            $where_id = $class_id;
        } else {
            $where_column = 'user_id';
            $where_id = $user_id;
        }

        $query = $POINTS_DB->prepare(
             " INSERT INTO pointsDB.user_points "
            ." ( user_id, institution_id, class_id, points,resource_name ) "
            ." SELECT user_id, school_id as institution_id, class_id, "
            ." :miles as points, :resource_name as resource_name "
            ." FROM mashpiadb.users WHERE $where_column = :where_id"
        );

        // by default, transactions are store only
        $resource_name = 'transaction_manager_store';

        // allow users to modify total miles ( auction and homepage as well )
        if ( $auction )
            $resource_name = 'admin_users_manual';
        // invert number if action is subtract
        if ( $action == 'subtract' )
            $miles = $miles * -1;

        $success = $query->execute([
            ':miles' => $miles,
            ':resource_name' => $resource_name,
            ':where_id' => $where_id
        ]);

        if ( !$success )
            return json_error( $query->errorInfo() );
        return json_response( 'Successfully '.$action.'ed miles' );
    }

    private function getMaxMiles(){
        global $current_user;

        if ( $current_user->login->code === 'TEACHER' )
            return $current_user->login->model->miles_balance;
        
        return false;
    }
}

rest_router( new MilesRouter );
