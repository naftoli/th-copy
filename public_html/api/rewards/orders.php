<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class OrdersRouter {

    public function index() {
        global $current_user; global $POINTS_DB;

        $login = $current_user->login;

        $status = 'Checked Out';
        if ( isset( $_GET['redeemed'] ) && $_GET['redeemed'] == 'true' ) {
            $status = 'Redeemed';
        }

        if ( $login['code'] == 'BC' ) {
            $filter = 'orders.institution_id = ?';
        } else if ( $login['code'] == 'TEACHER' ) {
            $filter = ' u.class_id = ? ';
        } else return json_error('Access Denied');

        $query = $POINTS_DB->prepare(
             " SELECT user_prize_id, user_serial, first, last, prize_name, p.points, points.points as total, "
            ." quantity, status, class_grade, class_sub, orders.modified "
            ." FROM pointsDB.user_prizes orders "
            ." JOIN pointsDB.prizes p USING ( prize_id ) "
            ." JOIN mashpiadb.users u USING( user_id ) "
            ." JOIN mashpiadb.classes c USING( class_id ) "
            ." JOIN pointsDB.user_points points USING ( user_prize_id ) "
            ." WHERE is_reversed = 0 AND $filter AND status = ? "
            ." ORDER BY status ASC, orders.modified DESC, class_grade ASC, class_sub ASC, first ASC, last ASC, prize_name ASC;"
        );

        $query->execute([ $login['id'], $status ]);
        $orders = [];
        while( $order = $query->fetch() ) {
            $order['platoon'] = Platoon::generateName( $order['class_grade'], $order['class_sub'] );
            unset( $order['class_grade'] ); unset( $order['class_sub'] ); 
            $orders[] = $order;
        }

        json_response( $orders, true, true );
    }

    // public function show( $id ){
    //     $prize = StorePrize::find( $id );
    //     json_response( $prize );
    // }

    // public function create() {
    //     global $current_user;
    //     $login = $current_user->login;

    //     try {
    //         $prize = StorePrize::build( $_POST );

    //         if ( $login['code'] == 'BC' ) {
    //             $prize->institution_id = $login['id'];
    //         } else if ( $login['code'] == 'TEACHER' ) {
    //             $prize->teacher_edit = 1;
    //             $prize->institution_id = $login['school_id'];
    //             $prize->teacher_id = $current_user->admin_id;
    //         }

    //         $prize->prize_type = '';
    //         $prize->created_by = $current_user->admin_id;

    //         if ( !$prize->save() )
    //             return json_error( $prize->errors->full_messages() );

    //         // update prize_classes table
    //         if ( isset( $_POST['platoons'] )
    //             && !$prize->setPlatoons( $_POST['platoons'] ) 
    //         ) return json_error( 'Error limiting to Platoons');

    //         if ( $login['code'] == 'TEACHER'
    //             && !$prize->setPlatoons([ $login['id'] ])
    //         ) return json_error( 'Error connecting to Platoon, Please contact Base Commander');

    //         // return the prize as the response
    //         return json_response( $prize );
    //     // send all errors as text
    //     } catch ( Exception $e ) {
    //         return json_error( $e->getMessage() );
    //     }
    // }

    // public function update( $id ) {
    //     try {
    //         $prize = StorePrize::find( $id );
    //         // update profile picture
    //         if( isset( $_FILES['image'] ) ) {
    //             $prize->setImage( $_FILES['image'] );
    //         }
    //         // blulk update valid params
    //         $prize->bulkUpdate( $_POST );

    //         if ( !$prize->save() )
    //             return json_error( $prize->errors->full_messages() );

    //         // update prize_classes table
    //         if (
    //             isset( $_POST['platoons'] ) && 
    //             !$prize->setPlatoons( $_POST['platoons'] ) 
    //         ) return json_error( 'Could connect Platoons');

    //         // return the prize as the response
    //         return json_response( $prize );
    //     // send all errors as text
    //     } catch ( Exception $e ) {
    //         return json_error( $e->getMessage() );
    //     }
    // }

    // public function uploadImage() {
    //     global $current_user;
    //     if ( isset( $_FILES['image'] ) ) {
    //         $result = StorePrize::uploadImage( $current_user->admin_id, $_FILES['image'] );
    //         json_response([
    //             'image' => StorePrize::IMG_PATH . $result,
    //             'image_id' => $result
    //         ]);
    //     }
    //     json_error('Server did not get the prize image.');
    // }

    // public function setStoreOpen() {
    //     global $current_user;

    //     $school = School::find( $current_user->login['id'] );

    //     $school->school_store = $_POST['school_store'];
    //     $school->save();

    //     json_response([
    //         'school_store' => $school->school_store
    //     ]);
    // }
}

rest_router( new OrdersRouter );
