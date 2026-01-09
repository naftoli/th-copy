<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class Streaks {
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
        $this->streaks = $this->setStreaks();
    }

    public function getError() {
        return $this->error;
    }

    private function setStreaks() {
        $streaks = [];
        $sql = "SELECT st.*, dt.name, dt.cat 
                FROM streak_tasks st 
                JOIN date_tasks dt ON st.streak_id = dt.grid_id  
                WHERE user_id = :user_id 
                GROUP BY user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $this->user_id
        ]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $streaks[$row['streak_id']] = [
                'cat' => $row['cat'],
                'name' => $row['name'], 
                'num_days' => $row['num_days'], 
                'days_needed' => $row['days_needed'], 
                'task_type' => $row['task_type']
            ];
        }
        foreach ($streaks as $streak_id => $streak) {
            $streaks[$streak_id]['days_done'] = $this->getDaysDone($streak_id);
        }
        return $streaks;
    }

    public function getStreaks() {
        return $this->streaks;
    }

    /**
     * checks the marks from start to end for the streak days
     * returns an array of the streak days
     * the array is truncated at the first day that is not a streak day
     */
    private function getDaysDone($streak_id) {
        $marks = $this->getMarks($streak_id);

        // decide how to calculate the days done
        $task_type = $streak['task_type'];
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
        while ($mark = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $marks[] = (int)$mark['mark_date'];
        }
        return $marks;
    }

    public function setupStreak($streak_id) {
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
                VALUES (:gridId, :userId, :year, :numDays, :daysNeeded, :taskType)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            'gridId' => $streak_id,
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