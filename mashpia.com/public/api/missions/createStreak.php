<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
require_once __DIR__ . '/../../mobile/streaks/classes/class.streaks.php';

class CreateStreakRouter {
    public function index() {
        $gridId = intval($_POST['gridId']);
        $userId = intval($_POST['userId']);
        $numDays = 90;
        $streaks = new Streaks($userId, $numDays);
        $success = $streaks->setupStreak($gridId);
        json_response(['success' => $success]);
    }
}

rest_router( new CreateStreakRouter );
die();