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
            ." quantity, status, class_grade, class_sub, orders.created "
            ." FROM pointsDB.user_prizes orders "
            ." JOIN pointsDB.prizes p USING ( prize_id ) "
            ." JOIN mashpiadb.users u USING( user_id ) "
            ." JOIN mashpiadb.classes c USING( class_id ) "
            ." JOIN pointsDB.user_points points USING ( user_prize_id ) "
            ." WHERE is_reversed = 0 AND $filter AND status = ? "
            ." ORDER BY orders.created DESC, class_grade ASC, class_sub ASC, first ASC, last ASC, prize_name ASC;"
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

    // get store for a single user
    public function store() {
        if ( !isset( $_POST['user_id']) )
            return json_error('Missing user id');

        $user = User::find( $_POST['user_id'] );

        $prizes = StorePrize::find('all', [
            'select' => 'prizes.*, COUNT(user_prize_id) AS ordered ',
            'conditions' => 'is_active = 1 AND prize_count > 0 '
                .' AND prizes.institution_id = '.$user->school_id
                .' AND (class_id IS NULL ' . ( $user->class_id ? 'OR class_id = ' . $user->class_id : '' ) . ') ',
            'joins' => 'LEFT JOIN prize_classes USING (prize_id) '
                .'LEFT JOIN user_prizes ON prizes.prize_id = user_prizes.prize_id '
                .'AND is_reversed = 0 AND user_id = ' . $user->user_id,
            'group' => 'prizes.prize_id',
            'having' => '(one_per_user = 0 OR ordered = 0)',
            'order' => 'points, prize_name',
            'include' => [ 'school' ]
        ]);

        json_response([
            'miles' => $user->storeMiles(),
            'prizes' => $prizes
        ]);
    }

    // redeem orders
    public function redeem() {
        global $POINTS_DB; global $current_user;
        $ids = $_POST['user_prize_ids'];

        $query = $POINTS_DB->prepare(
            'UPDATE pointsDB.user_prizes SET status="Redeemed", redeemed_by = ? WHERE user_prize_id IN ( ? )'
        );
        if ( !$query->execute([ $current_user->admin_id, implode( ', ', $ids ) ]) )
            return json_error( 'Server Error: Could Not Redeem Prizes. (REWARDS-ORDERS-54)' );
        return json_response( false );
    }

    // unredeem orders
    public function unredeem() {
        global $POINTS_DB; global $current_user;
        $ids = $_POST['user_prize_ids'];
        
        $query = $POINTS_DB->prepare(
            'UPDATE pointsDB.user_prizes SET status="Checked Out", redeemed_by = null WHERE user_prize_id IN ( ? )'
        );
        if ( !$query->execute([ implode( ', ', $ids ) ]) )
            return json_error( 'Server Error: Could Not Redeem Prizes. (REWARDS-ORDERS-66)' );
        return json_response( false );
    }

    // reverse orders
    public function reverse() {
        global $POINTS_DB; global $current_user;

        $ids = $_POST['user_prize_ids'];
        $qMarks = str_repeat('?,', count($ids) - 1) . '?';

        // get the user_points row for the orders
        $user_points = [];
        $user_points_query = $POINTS_DB->prepare(
             ' SELECT p.user_prize_id, p.user_point_id, p.points, o.prize_id, '
            .' o.user_id, o.quantity, o.institution_id FROM pointsDB.user_points p '
            .' JOIN pointsDB.user_prizes o USING ( user_prize_id ) WHERE user_prize_id IN ( '.$qMarks.' ) '
        );
        $user_points_query->execute( $ids );
        while( $row = $user_points_query->fetch() ) {
            $user_points[ $row['user_prize_id'] ] = $row;
        }

        // reverse the orders
        $reverse_orders_query = $POINTS_DB->prepare(
            'UPDATE pointsDB.user_prizes SET is_reversed = 1, reversed_by = ? WHERE user_prize_id IN ( '.$qMarks.' )'
        );
        if ( !$reverse_orders_query->execute( array_merge([ $current_user->admin_id ], $ids )) )
            return json_error( 'Server Error: Could Not Reverse Orders. (REWARDS-ORDERS-87)' );
        
        // return the points and update the stock for each prize
        $reverse_points_query = $POINTS_DB->prepare(
             ' INSERT INTO user_points '
            .' (reversed_user_point_id, points, prize_id, user_prize_id, user_id, institution_id, resource_name) '
            .' VALUES (?, ?, ?, ?, ?, ?, "transaction_manager_store")'
        );
        foreach( $user_points as $row ) {
            $POINTS_DB->query(
                'UPDATE pointsDB.prizes SET prize_count = prize_count + '.$row['quantity'] . ' WHERE prize_id = '.$row['prize_id']
            );
            $reverse_points_query->execute([
                $row['user_point_id'], $row['points'] * -1, $row['prize_id'],
                $row['user_prize_id'], $row['user_id'], $row['institution_id']
            ]);
        }

        return json_response( false );
    }
}

rest_router( new OrdersRouter );
