<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
require_once __DIR__ . '/../../classes/user.php';
require_once __DIR__ . '/../../classes/user_track.php';
require_once __DIR__ . '/../../classes/school_class.php';
require_once __DIR__ . '/../../class.taskExceptions.php';
require_once __DIR__ . '/../../classes/date_tasks_mission.php';
require_once __DIR__ . '/../../classes/daily_task.php';
require_once __DIR__ . '/../../classes/weekly_task.php';
require_once __DIR__ . '/../../classes/shabbos_task.php';
require_once __DIR__ . '/../../classes/no_label_task.php';
require_once __DIR__ . '/../../classes/task.php';
require_once __DIR__ . '/../../classes/date_tasks_mark.php';
require_once __DIR__ . '/../../pesukim/class.pesukim.php';
require_once __DIR__ . '/../../mobile/streaks/classes/class.accomplished.php';

class StreaksRouter {
    public function index() {
        try {
            global $MASHPIA_DB;
            // inputs
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
                json_error('Missing id param (user_id, class_id, or school_id)');
            }
            $subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : -1;
            $start_jd = isset($_GET['start_jd']) ? intval($_GET['start_jd']) : 0;
            $end_jd = isset($_GET['end_jd']) ? intval($_GET['end_jd']) : 0;
            if (!$id || !$start_jd || !$end_jd) {
                json_error('Missing params');
            }

            $users = [];
            // load user(s)
            $stmt = $MASHPIA_DB->prepare("SELECT * FROM users WHERE {$type}_id = :id");
            $stmt->execute([':id' => $id]);
            while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new user($u);
                $user->get_user_tracks($subject_id, $start_jd, $end_jd, [], $user->lang_id);
                $users[] = $user;
            }

            $task_types = ['daily_tasks', 'weekly_tasks', 'shabbos_tasks'];
            $grid_ids = []; // per-user map: user_id => [grid_id...]
            $gridIdToShort = [];
            $gridIdToMeta = [];
            $labelStmt = $MASHPIA_DB->prepare("SELECT label_id FROM date_tasks WHERE grid_id = :grid_id LIMIT 1");
            foreach ($users as $user) {
                $user_id = $user->user_id;
                if (!isset($grid_ids[$user_id])) $grid_ids[$user_id] = [];
                foreach ($user->user_tracks as $user_track) {
                    foreach ($task_types as $task_type) {
                        foreach ($user_track->{$task_type} as $task) {
                            if (! in_array($task->grid_id, $grid_ids[$user_id], true)) {
                                $grid_ids[$user_id][] = $task->grid_id;
                                $gridIdToShort[$user_id][$task->grid_id] = $task->short_name;
                                $labelId = isset($task->label_id) ? intval($task->label_id) : null;
                                if (!$labelId) {
                                    $labelStmt->execute(['grid_id' => $task->grid_id]);
                                    $rowLbl = $labelStmt->fetch(PDO::FETCH_ASSOC);
                                    if ($rowLbl && isset($rowLbl['label_id'])) $labelId = intval($rowLbl['label_id']);
                                }
                                $typeName = $task_type === 'daily_tasks' ? 'daily' : ($task_type === 'weekly_tasks' ? 'weekly' : 'shabbos');
                                $gridIdToMeta[$user_id][$task->grid_id] = [
                                    'short_name'   => $task->short_name,
                                    'label'        => $task->label_name,
                                    'label_id'     => $labelId,
                                    'frequency_id' => isset($task->frequency_id) ? intval($task->frequency_id) : null,
                                    'type'         => $typeName,
                                    'label_ord'    => isset($task->label_ord) ? intval($task->label_ord) : null
                                ];
                            }
                        }
                    }
                }
            }

            $tasks = [];
            foreach ($users as $user) {
                $user_id = $user->user_id;
                if (empty($grid_ids[$user_id])) { $tasks[$user_id] = []; continue; }
                $accomplished = new Accomplished($user_id, $grid_ids[$user_id], $start_jd, $end_jd);
                $accomplished->setAccomplished();
                $accomplished_tasks = $accomplished->getAccomplished();
                foreach ($accomplished_tasks as $gid => $marks) {
                    if (!isset($gridIdToShort[$user_id][$gid])) continue;
                    $jds = [];
                    foreach ($marks as $mark) $jds[] = intval($mark['mark_date']);
                    sort($jds, SORT_NUMERIC);
                    $meta = $gridIdToMeta[$user_id][$gid] ?? [];
                    $tasks[$user_id][] = [
                        'name'        => $gridIdToShort[$user_id][$gid],   
                        'jds'         => $jds,
                        'label'       => $meta['label']        ?? '',
                        'labelId'     => $meta['label_id']     ?? null,
                        'frequencyId' => $meta['frequency_id'] ?? null,
                        'type'        => $meta['type']         ?? null,
                        'labelOrd'    => $meta['label_ord']    ?? null
                    ];
                }
            }

            json_response([
                'startJd' => $start_jd,
                'endJd'   => $end_jd,
                'tasks'   => $tasks
            ], true, true );
        } catch (Exception $e) {
            json_error('Server exception: ' . $e->getMessage());
        } catch (Throwable $t) {
            json_error('Server error: ' . $t->getMessage());
        }
    }
}

rest_router( new StreaksRouter );
die();


