<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class CardsRouter {

    public function index() {
        // Get available Miles (and other info?)
        return json_response([
            'miles' => $this->getMiles(),
        ]);
    }

    public function create() {
        global $current_user;   global $POINTS_DB;
        $login = $current_user->login;

        // try {
        //     $task = new AchievementTask( $_POST );
        // } catch ( Exception $e ) { json_error( 'Invalid Request' ); }

        // if ( $login['code'] == 'BC' ) {
        //     $task->base = $login['id'];
        // } else if ( $login['code'] == 'TEACHER') {
        //     $task->base = Platoon::find( $login['id'] )->school->school_id;
        //     $task->platoon = $login['id'];
        // }

        // if ( !$task->subject_id )
        //     json_error( 'Cannot create a Task without a Campaign' );
        
        // if ( $login['code'] == 'INST' ) {
        //     $subject = Subject::find( $task->subject_id );
        //     if ( $subject->inst_id != $login['id'] )
        //         return json_error('You do not have permission to create Tasks for this Campaign. Please select another one.');
        // }

        // if ( !$task->save() )
        //     json_error( 'Could not create Task.' );
        // json_response( $task );

        # use this query to generate serial numbers
        // SELECT 
        // FLOOR(RAND() * 9999999999999999999) AS random_num
        // FROM
        // pointsDB.achievement_cards
        // WHERE
        // 'random_num' NOT IN (SELECT 
        //         card_serial
        //     FROM
        //         pointsDB.achievement_cards)
        // LIMIT 1
    }

    public function delete() {
        // Delete all cards issued before a given date that have not been reddemed
    }

    private function getMiles(){
        global $current_user;

        if ( $current_user->login['code'] === 'TEACHER' )
            return Platoon::find( $current_user->login['id'] )->miles_balance;
        
        return false;
    }
}

rest_router( new CardsRouter );
