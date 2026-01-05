<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class Streaks {
    private $num_days;
    private $user_id;
    private $db;
    private $streaks;
    private $grid_ids;
    private $streak_tasks;

    public function __construct($user_id, $num_days = 90) {
        global $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->num_days = $num_days;
        $this->db = $MASHPIA_DB;
        $this->streaks = [];
        $this->grid_ids = [];
        $this->streak_tasks = [];
        $this->setStreaks();
    }

    private function setStreaks() {
        $sql = "SELECT st.grid_id, st.num_days, dt.name, dt.cat 
                FROM streak_tasks st 
                JOIN date_tasks dt USING (grid_id) 
                WHERE user_id = :user_id 
                GROUP BY user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $this->user_id
        ]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->grid_ids[] = $row['grid_id'];
            $this->streak_tasks[$row['grid_id']] = [
                'cat' => $row['cat'],
                'name' => $row['name']
            ];
        }
        foreach ($this->grid_ids as $grid_id) {
            $days = $this->getStreakDays($grid_id);
            $this->streaks[$grid_id] = [
                'cat' => $this->streak_tasks[$grid_id]['cat'],
                'name' => $this->streak_tasks[$grid_id]['name'],
                'days' => $days
            ];
        }
    }

    public function getStreaks() {
        return $this->streaks;
    }

    public function getGridIds() {
        return $this->grid_ids;
    }

    /**
     * checks the last $num_days days for the streak days
     * starts from yesterday and goes back $num_days days
     * returns an array of the streak days
     * the array is truncated at the first day that is not a streak day
     */
    private function getStreakDays($grid_id) {
        $sql = "SELECT DISTINCT mark_date  
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.grid_id = :grid_id 
                AND dtm.user_id = :user_id 
                ORDER BY mark_date DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':grid_id', $grid_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$this->num_days, PDO::PARAM_INT);
        $stmt->execute();
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $marks = array_column($marks, 'mark_date');
        
        $today = unixtojd();
        $day = $today - 1;
        $end = $day - $this->num_days;
        $days = [];
        for (; $day >= $end; $day--) {
            if (in_array($day, $marks)) {
                $days[] = $day;
            } else {
                break;
            }
        }
        return count($days);
    }

    public function setupStreak($grid_id) {
        $year = GlobalSettings::getCurrentYear();
        $sql = "INSERT INTO streak_tasks (grid_id, user_id, year, num_days) 
                VALUES (:gridId, :userId, :year, :numDays)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            'gridId' => $grid_id,
            'userId' => $this->user_id,
            'year' => $year,
            'numDays' => $this->num_days
        ]);
        if (!$res) {
            $this->error = $stmt->errorInfo();
        }
        return $res;
    }

    public function getError() {
        return $this->error;
    }
}