<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class CardsRouter {

    public function index() {
        // $card = AchievementCard::find( 3497391 );
        // Get available Miles (and other info?)
        return json_response([
            'miles' => $this->getMiles(),
            // 'card' => $card,
        ]);
    }

    public function create() {
        global $current_user;   global $POINTS_DB;
        $login = $current_user->login;

        if ( !isset( $_POST['card_count'] ) )
            return json_error( 'Cannot print unknown amount of cards.' );

        $fake_codes = $POINTS_DB->prepare(
            "SELECT card_serial FROM ("
                ." SELECT CAST(CONCAT(4, ROUND(RAND() * 999999999), ROUND(RAND() * 9999999999)) AS CHAR ) AS card_serial "
                ." FROM pointsDB.achievement_cards WHERE 'card_serial' NOT IN ("
                    ." SELECT card_serial FROM pointsDB.achievement_cards "
                .") "
            .") AS numbers HAVING LENGTH( card_serial ) = 20 LIMIT ?;"
        );

        $card_count = intval( $_POST['card_count'] );
        $cards = []; $data = [];
        $fake_codes->execute([ $card_count ]);
        $subject = Subject::find( intval( $_POST['subject_id'] ) );
        $task = AchievementTask::find( intval( $_POST['task_id'] ) );
        $miles = $this->getMiles(); 
        $miles_spent = $task->points * $card_count;

        // validate miles
        if ( $miles === 0 ) {
            return json_error( "You do not have any miles left. Please come back at the begining of the month when you will be given more miles." );
        } else if ( $miles !== false && $miles_spent > $miles ) {
            $can_print = floor( $miles / $task->points );
            if ( $can_print > 1 )
                return json_error( "You only have enough miles to print $can_print of these cards. Please try again." );
            return json_error( "You do not have enough miles to print these cards." );
        }
        // $cards = $fake_codes->fetchAll();

        while ( $code = $fake_codes->fetch() ) {
            AchievementCard::create([
                'institution_id' => $login['school_id'] ? $login['school_id'] : 0,
                'campaign_id' => $subject->subject_id,
                'task_id' => $task->achievement_task_id,
                'class_id' => $login['class_id'],
                'card_serial' => $code['card_serial'],
                'card_type' => $login['code'] == 'TEACHER' ? 'Teacher' : 'Institution Administrator',
                'card_points' => $task->points,
                'created_by' => $current_user->admin_id
            ]);
            $cards[] = [
                'card_serial' => $code['card_serial'],
                'campaign' => $subject->subject_name,
                'task' => $task->task,
                'miles' => $task->points,
                'campaignLogo' => $subject->logoPath()
            ];
        }
        // subtract miles
        if ( $miles !== false ) {
            $platoon = Platoon::find( $current_user->login['id'] );
            $platoon->miles_balance = $miles - $miles_spent;
            $platoon->save();
        }

        return json_response([
            'cards' => $cards,
            'data' => $data,
            'miles' => $miles ? $miles - $miles_spent : false
        ]);
    }

    public function delete() {
        global $current_user; global $POINTS_DB;

        if ( !isset( $_POST['delete_to']) )
            return json_error('Invalid Request');
        
        $date = ( new DateTime( $_POST['delete_to'] ) )->format( 'Y-m-d' );
    }

    private function getMiles(){
        global $current_user;

        if ( $current_user->login['code'] === 'TEACHER' )
            return Platoon::find( $current_user->login['id'] )->miles_balance;
        
        return false;
    }
}

rest_router( new CardsRouter );
