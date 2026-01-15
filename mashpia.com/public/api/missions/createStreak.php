<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
require_once __DIR__ . '/../../mobile/streaks/classes/class.streaks.php';

class CreateStreakRouter {
    public function index() {
        $streakId = intval($_GET['streakId']);
        $userId = intval($_GET['userId']);
        $end = unixtojd();
        $start = $end - 90;
        $streaks = new Streaks($userId, $start, $end);
        $success = $streaks->setupStreak($streakId);
        if ($success) {
            json_response('Streak set up successfully');
        } else {
            // get the error from the database
            $error = $streaks->getError();
            json_error($error[2]);
        }
    }
}

rest_router( new CreateStreakRouter );
die();