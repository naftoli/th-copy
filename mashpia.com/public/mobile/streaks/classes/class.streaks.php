<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class Streaks {
    const MAX_USER_IDS_IN_QUERY = 500;

    private $db;
    private $user_id;
    private $start;
    private $end;
    private $streaks;
    private $error;

    public function __construct($user_id, $start, $end) {
        global $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->start = $start;
        $this->end = $end;
        $this->error = null;
        $this->db = $MASHPIA_DB;
        $this->setStreaks();
    }

    public function getError() {
        return $this->error;
    }

    private function setStreaks() {
        $this->streaks = [];
        $sql = "SELECT 
                    st.*,
                    dt.name,
                    dt.cat,
                    dt.streak_duch_cat,
                    dt.streak_duch_name
                FROM
                    streak_tasks st
                        JOIN
                    date_tasks dt USING (streak_id)
                        JOIN
                    users u USING (user_id)
                        JOIN
                    user_tracks ut USING (user_id)
                        JOIN
                    date_tasks_missions dtm USING (date_tasks_mission_id , school_type_id , track_id , level)
                WHERE
                    st.user_id = :user_id
                        AND dt.streak_show = 1
                GROUP BY st.streak_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $this->user_id
        ]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->streaks[$row['streak_id']] = [
                'cat' => $row['cat'],
                'name' => $row['name'], 
                'num_days' => $row['num_days'], 
                'days_needed' => $row['days_needed'], 
                'task_type' => $row['task_type'], 
                'duch_cat' => $row['streak_duch_cat'] ?? $row['cat'],
                'duch_name' => $row['streak_duch_name'] ?? $row['name']
            ];
        }
        foreach ($this->streaks as $streak_id => $streak) {
            $this->streaks[$streak_id]['days_done'] = $this->getDaysDone($streak_id, $streak['task_type']);
        }
    }

    public function getStreaks() {
        return $this->streaks;
    }

    /**
     * Load streaks for multiple users in batch. Returns [ user_id => [ streak_id => streak_data, ... ], ... ].
     * Chunks user_ids to avoid huge IN clauses for entire schools.
     */
    public static function getStreaksForUsers(array $user_ids, $start, $end) {
        global $MASHPIA_DB;
        if ( empty( $user_ids ) ) return [];
        $db = $MASHPIA_DB;
        $by_user = [];
        $streak_ids = [];

        $chunks = array_chunk( array_values( $user_ids ), self::MAX_USER_IDS_IN_QUERY );
        foreach ( $chunks as $chunk ) {
            $placeholders = implode( ',', array_fill( 0, count( $chunk ), '?' ) );
            $sql = "SELECT st.user_id, st.streak_id, st.num_days, st.days_needed, st.task_type,
                        dt.name, dt.cat, dt.streak_duch_cat, dt.streak_duch_name
                    FROM streak_tasks st
                    JOIN date_tasks dt USING (streak_id)
                    JOIN users u USING (user_id)
                    JOIN user_tracks ut USING (user_id)
                    JOIN date_tasks_missions dtm USING (date_tasks_mission_id, school_type_id, track_id, level)
                    WHERE st.user_id IN ($placeholders) AND dt.streak_show = 1
                    GROUP BY st.user_id, st.streak_id";
            $stmt = $db->prepare( $sql );
            $stmt->execute( $chunk );
            while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
                $uid = (int) $row['user_id'];
                $sid = (int) $row['streak_id'];
                if ( ! isset( $by_user[ $uid ] ) ) $by_user[ $uid ] = [];
                $by_user[ $uid ][ $sid ] = [
                    'cat' => $row['cat'],
                    'name' => $row['name'],
                    'num_days' => $row['num_days'],
                    'days_needed' => $row['days_needed'],
                    'task_type' => $row['task_type'],
                    'duch_cat' => $row['streak_duch_cat'] ?? $row['cat'],
                    'duch_name' => $row['streak_duch_name'] ?? $row['name'],
                    'days_done' => 0
                ];
                $streak_ids[ $sid ] = true;
            }
        }

        $streak_ids = array_keys( $streak_ids );
        if ( empty( $streak_ids ) ) return $by_user;

        $marks_by_user_streak = [];
        $ph_s = implode( ',', array_fill( 0, count( $streak_ids ), '?' ) );
        foreach ( $chunks as $chunk ) {
            $ph_u = implode( ',', array_fill( 0, count( $chunk ), '?' ) );
            $params = array_merge( $chunk, $streak_ids, [ $start, $end ] );
            $sql_marks = "SELECT dtm.user_id, dt.streak_id, dtm.mark_date
                    FROM date_tasks_marks dtm
                    JOIN date_tasks dt USING (date_task_id)
                    WHERE dtm.user_id IN ($ph_u) AND dt.streak_id IN ($ph_s)
                    AND dtm.mark_date >= ? AND dtm.mark_date <= ?
                    ORDER BY dtm.user_id, dt.streak_id, dtm.mark_date DESC";
            $stmt = $db->prepare( $sql_marks );
            $stmt->execute( $params );
            while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
                $uid = (int) $row['user_id'];
                $sid = (int) $row['streak_id'];
                $d = (int) $row['mark_date'];
                if ( ! isset( $marks_by_user_streak[ $uid ] ) ) $marks_by_user_streak[ $uid ] = [];
                if ( ! isset( $marks_by_user_streak[ $uid ][ $sid ] ) ) $marks_by_user_streak[ $uid ][ $sid ] = [];
                $marks_by_user_streak[ $uid ][ $sid ][] = $d;
            }
        }

        foreach ( $by_user as $uid => $streaks ) {
            foreach ( $streaks as $sid => $data ) {
                $marks = isset( $marks_by_user_streak[ $uid ][ $sid ] ) ? $marks_by_user_streak[ $uid ][ $sid ] : [];
                $by_user[ $uid ][ $sid ]['days_done'] = self::computeDaysDone( $marks, (int) $start, (int) $end, $data['task_type'] );
            }
        }
        return $by_user;
    }

    private static function computeDaysDone(array $marks, $start, $end, $task_type) {
        $needed_interval = ( $task_type === 'weekly' ) ? 7 : ( ( $task_type === 'monthly_tasks' ) ? 28 : 1 );
        $days = 0;
        $day = $end;
        $ctr = 0;
        $found = false;
        while ( $day >= $start ) {
            $ctr++;
            if ( in_array( $day, $marks, true ) ) {
                $days++;
                if ( ! $found ) $found = true;
            } else {
                if ( $ctr <= $needed_interval ) {
                    $day--;
                    continue;
                }
                if ( $found ) {
                    $ctr = 0;
                    $found = false;
                } else {
                    break;
                }
            }
            $day--;
        }
        return $days;
    }

    /**
     * checks the marks from start to end for the streak days
     * returns an array of the streak days
     * the array is truncated at the first day that is not a streak day
     */
    private function getDaysDone($streak_id, $task_type) {
        $marks = $this->getMarks($streak_id);

        // decide how to calculate the days done
        switch ($task_type) {
            case 'weekly':
                $needed_interval = 7;
                break;
            case 'monthly_tasks':
                $needed_interval = 28;
                break;
            default:
                $needed_interval = 1;
                break;
        }

        $days = [];
        $end = (int)$this->end;
        $ctr = 0;
        $found = false;
        // start from the end date (today) and work backwards to find streak days
        for (; $end >= $this->start; $end--) {
            $ctr++;
            if (in_array($end, $marks)) {
                $days[] = $end;
                if (!$found) $found = true;
            } else {
                if ($ctr <= $needed_interval) {
                    continue; // continue to the next day
                } else if ($found) {
                    $ctr = 0; // reset the counter
                    $found = false; // reset the found flag
                } else {
                    break; // no more days to check b/c streak is broken
                }
            }
        }
        return count($days);
    }

    private function getMarks($streak_id) {
        $marks = [];
        $sql = "SELECT DISTINCT mark_date  
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.streak_id = :streak_id 
                AND dtm.user_id = :user_id 
                AND dtm.mark_date >= :start 
                AND dtm.mark_date <= :end 
                ORDER BY mark_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'streak_id' => $streak_id,
            'user_id' => $this->user_id,
            'start' => $this->start,
            'end' => $this->end
        ]);
        // $stmt->debugDumpParams();
        while ($mark = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $marks[] = (int)$mark['mark_date'];
        }
        return $marks;
    }

    public function setupStreak($streak_id) {
        if (! $streak_id || $streak_id == 0 || $streak_id == '0') return false;
        $year = GlobalSettings::getCurrentYear();
        // find out if task is daily, weekly, monthly, none
        $task_type = $this->getTaskType($streak_id);
        $num_days = (int)$this->end - (int)$this->start;
        $days_needed = $num_days;
        if ($task_type == 'weekly') {
            $days_needed = floor($num_days / 7);
        } else if ($task_type == 'monthly') {
            $days_needed = floor($num_days / 28);
        }
        if ($days_needed < 1) $days_needed = 1;
        $sql = "INSERT INTO streak_tasks (streak_id, user_id, year, num_days, days_needed, task_type) 
                VALUES (:streakId, :userId, :year, :numDays, :daysNeeded, :taskType)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            'streakId' => $streak_id,
            'userId' => $this->user_id,
            'year' => $year,
            'numDays' => $num_days, 
            'daysNeeded' => $days_needed,
            'taskType' => $task_type
        ]);
        if (!$res) {
            $this->error = $stmt->errorInfo()[2];
        }
        return $res;
    }

    private function getTaskType($streak_id) {
        $sql = "SELECT daily_task, subject_id 
                FROM date_tasks dt 
                JOIN date_tasks_missions dtm using (date_tasks_mission_id) 
                WHERE dt.streak_id = :streak_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'streak_id' => $streak_id
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $task_type = '';
        if ($row['daily_task']) {
            $task_type = 'daily';
        } else if ($row['subject_id'] == 1) {
            $task_type = 'monthly';
        } else {
            $task_type = 'weekly';
        }
        return $task_type;
    }
}