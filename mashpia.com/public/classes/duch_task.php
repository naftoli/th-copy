<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.balPehCampaign.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.tanya.php';

class DuchTask {
    private $db;
    public $start;
    public $end;
    public $task;
    public $track_info;
    public $user_id;
    public $school_type_id;

    public function __construct($user, $task, $start, $end, $track_info) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->user_id = $user->user_id;
        $this->start = $start;
        $this->end = $end;
        $this->task = $task;
        $this->track_info = $track_info;
        $this->school_type_id = $user->school_type_id;
    }

    // public function needsPersonalization() {
    //     $streaks = [8001, 8002, 21013, 20002];
    //     return in_array($this->streak_id, $streaks);
    // }

    public function getPersonalizedTask() { 
        switch ($this->task->streak_id) {
            case 8001:
            case 8002:
                return $this->personalizeTehillim();
                break;
            case 21013:
                return $this->personalizeTanya();
                break;
            case 20002:
                return $this->personalizeChidon();
                break;
        }
        return $this->task->streak_duch_name;
    }

    private function personalizeTehillim() {
        $quota = $this->getQuota();
        $done = $this->getAmountDone();
        switch ($this->task->streak_id) {
            case 8001:
                return "My total Kapitalach quota was " . $quota . " kapitelach and I said " . $done . " Kapitlach";
                break;
            case 8002:
                return "My total Minutes quota was " . $quota . " minutes and I said " . $done . " Minutes";
                break;
        }
        return $this->task->streak_duch_name;
    }

    private function getQuota() {
        $stmt = $this->db->prepare("
            SELECT IFNULL(SUM(quantity), 0) as quantity
            FROM
                date_tasks dt
                JOIN date_tasks_missions dtm USING (date_tasks_mission_id) 
            WHERE
                dt.grid_id = :grid_id
                AND dtm.start_date >= :start
                AND dtm.end_date <= :end 
                AND dtm.track_id = :track_id
                AND dtm.level = :level
                AND dtm.lang_id = :lang_id 
                AND dtm.school_type_id = :school_type_id
        ");
        $stmt->execute([
            'grid_id' => $this->task->grid_id,
            'start' => $this->start,
            'end' => $this->end,
            'track_id' => $this->track_info->track_id,
            'level' => $this->track_info->level,
            'lang_id' => $this->track_info->lang_id,
            'school_type_id' => $this->school_type_id
        ]);
        // $stmt->debugDumpParams();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['quantity'];
    }

    private function getAmountDone() {
        $limit = (int)$this->end - (int)$this->start;
        $stmt = $this->db->prepare("
            SELECT IFNULL(SUM(done_qty), 0) as done_qty
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
            WHERE
                dt.grid_id = :grid_id
                    AND dtm.user_id = :user_id
                    AND dtm.mark_date >= :start
                    AND dtm.mark_date <= :end
            ORDER BY mark_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':grid_id', $this->task->grid_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':start', $this->start, PDO::PARAM_INT);
        $stmt->bindValue(':end', $this->end, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        // $stmt->debugDumpParams();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['done_qty'];
    }

    private function personalizeTanya() {
        $t = new Tanya();
        $total = $t->getTotalLearnedForDuch($this->user_id);
        return "I know a total of " . $total . " lines of Tanya Baal Peh";
    }

    private function personalizeChidon() {
        return "Chidon";
    }
}