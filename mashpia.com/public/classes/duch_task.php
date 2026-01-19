<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.balPehCampaign.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.tanya.php';

class DuchTask {
    private $db;
    public $user_id;
    public $start;
    public $end;
    public $streak_id;
    public $task_name;
    public $grid_id;
    public $date_task_id;
    public $track_info;

    public function __construct($user_id, $task, $start, $end, $track_info) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->start = $start;
        $this->end = $end;
        $this->streak_id = $task->streak_id;
        $this->task_name = $task->streak_duch_name;
        $this->grid_id = $task->grid_id;
        $this->date_task_id = $task->date_task_id;
        $this->track_info = $track_info;
    }

    public function needsPersonalization() {
        $streaks = [8001, 8002, 21013, 20002];
        return in_array($this->streak_id, $streaks);
    }

    public function getPersonalizedTask() { 
        switch ($this->streak_id) {
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
            default:
                return $this->task_name;
                break;
        }
    }

    private function personalizeTehillim() {
        $quota = $this->getQuota();
        $done = $this->getAmountDone();
        switch ($this->streak_id) {
            case 8001:
                return "My total Kapitalach quota was " . $quota . " kapitelach and I said " . $done . " Kapitlach";
                break;
            case 8002:
                return "My total Minutes quota was " . $quota . " minutes and I said " . $done . " Minutes";
                break;
            default:
                return $this->task_name;
                break;
        }
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
        ");
        $stmt->execute([
            'grid_id' => $this->grid_id,
            'start' => $this->start,
            'end' => $this->end,
            'track_id' => $this->track_info->track_id,
            'level' => $this->track_info->level,
            'lang_id' => $this->track_info->lang_id
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
        $stmt->bindValue(':grid_id', $this->grid_id, PDO::PARAM_INT);
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
        $total = $t->getUsersTotalLearned([$this->user_id]);
        return "I know a total of " . $total . " lines of Tanya Baal Peh";
    }

    private function personalizeChidon() {
        return "Chidon";
    }
}