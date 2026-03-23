<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SubjectsRouter {
    public function index() {
        global $current_user; global $MASHPIA_DB;

        if (isset($_GET['user_id'])) {
            $id = intval($_GET['user_id']);
            $type = 'user';
        } else if (isset($_GET['class_id'])) {
            $id = intval($_GET['class_id']);
            $type = 'class';
        } else if (isset($_GET['school_id'])) {
            $id = intval($_GET['school_id']);
            $type = 'school';
        } else {
            $school_id = $current_user->login->school_id;
            $type = 'school';
        }

        $exceptions = [12, 40, 136];
        if (isset($_GET['for_streak'])) {
            $exceptions = [12, 15, 40, 136];
        }

        if ($id > 0) {
            $sql = "SELECT DISTINCT s.subject_id, s.subject_name 
                    FROM users u 
                    JOIN user_tracks ut USING (user_id)
                    JOIN subjects s USING (subject_id)
                    WHERE u.{$type}_id = :id
                      AND ut.enrolled = 1
                      AND s.subject_id NOT IN (" . implode(',', $exceptions) . ")
                    GROUP BY s.subject_id
                    ORDER BY s.subject_name";
            $stmt = $MASHPIA_DB->prepare($sql);
            $stmt->execute([ ':id' => $id ]);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response($subjects, true, true);
        }

        json_error('No ID provided');
    }
}

rest_router( new SubjectsRouter );
