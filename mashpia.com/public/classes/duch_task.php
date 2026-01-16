<?php
class DuchTask {
    private $db;
    public $user_id;
    public $streak_id;
    public $task_name;
    public $date_task_id;

    public function __construct($user_id, $task) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->streak_id = $task->streak_id;
        $this->task_name = $task->streak_duch_name;
        $this->date_task_id = $task->date_task_id;
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
                return "My Kapitalach quota was " . $quota . " and I said " . $done . " Kapitlach";
                break;
            case 8002:
                return "My Minutes quota was " . $quota . " Minutes and I said " . $done . " Minutes";
                break;
            default:
                return $this->task_name;
                break;
        }
    }

    private function getQuota() {
        $stmt = $this->db->prepare("
            SELECT description FROM date_tasks 
            WHERE date_task_id = :date_task_id
        ");
        $stmt->execute([
            'date_task_id' => $this->date_task_id
        ]);
        // $stmt->debugDumpParams();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['description'];
    }

    private function getAmountDone() {
        $stmt = $this->db->prepare("
            SELECT IFNULL(done_qty, 0) as done_qty from date_tasks_marks 
            WHERE date_task_id = :date_task_id 
            AND user_id = :user_id
        ");
        $stmt->execute([
            'date_task_id' => $this->date_task_id,
            'user_id' => $this->user_id
        ]);
        // $stmt->debugDumpParams();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['done_qty'];
    }

    private function personalizeTanya() {
        return "Tanya";
    }

    private function personalizeChidon() {
        return "Chidon";
    }
}