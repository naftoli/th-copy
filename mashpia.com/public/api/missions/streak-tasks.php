<?php
define( "MASHPIA_AUTH_REQUIRED", true );
require_once __DIR__ . '/../header/header.php';
require_once __DIR__ . '/../../class.globalSettings.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../class.tasksCustomizationNew.php';
require_once __DIR__ . '/../../mobile/streaks/classes/class.streaks.php';

class StreakTasksRouter {
    public function index() {
        global $MASHPIA_DB;
        $subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if (!$subject_id || !$user_id) {
            json_error('Missing subject_id or user_id');
        }

        $dates = GlobalSettings::getCurYearDates();
        $start = $dates['start'];
        $end = $dates['end'];

        $sql = "select first, last, user_photo_id, lang_id from users where user_id = " . $user_id;
        $result = $MASHPIA_DB->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $lang = $row['lang_id'] ? $row['lang_id'] : 1;

        $tc = new TasksCustomizationNew;
        $tc->setStart( $start );
        $tc->setEnd( $end );
        $tc->setType( $user_id, 0, 0 );
        $tc->setLang( $lang );
        $tasks['tasks'] = $tc->getTasksForStreak( $subject_id, $user_id );

        $streaks = new Streaks($user_id, $start, $end);
        $tasks['streaks'] = $streaks->getStreaks();

        json_response($tasks);
    }
}

rest_router( new StreakTasksRouter );
die();