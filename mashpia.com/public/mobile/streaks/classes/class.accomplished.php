<?php
class Accomplished {
    private $db;
    private $user_id;
    private $start_date;
    private $end_date;
    private $task;
    private $accomplished;

    public function __construct($user_id, $start_date, $end_date, $task) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->task = $task;
        $this->limit = (int)$end_date - (int)$start_date + 1;
        $this->accomplished = [];
    }

    public function setAccomplished() {
        if ($this->task->streak_id > 0) {
            $this->setAccomplishedByStreakId();
        } else {
            $this->setAccomplishedByGridId();
        }
    }

    private function setAccomplishedByGridId() {
        $sql = "SELECT DISTINCT mark_date  
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.grid_id = :grid_id 
                AND dtm.user_id = :user_id 
                AND dtm.mark_date >= :start_date 
                AND dtm.mark_date <= :end_date 
                AND dtm.done_qty > 0 
                ORDER BY mark_date DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':grid_id', $this->task->grid_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $this->start_date, PDO::PARAM_INT);
        $stmt->bindValue(':end_date', $this->end_date, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->execute();
        $this->accomplished[$this->task->grid_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function setAccomplishedByStreakId() {
        $sql = "SELECT DISTINCT mark_date  
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.streak_id = :streak_id 
                AND dtm.user_id = :user_id 
                AND dtm.mark_date >= :start_date 
                AND dtm.mark_date <= :end_date 
                AND dtm.done_qty > 0 
                ORDER BY mark_date DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':streak_id', $this->task->streak_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $this->start_date, PDO::PARAM_INT);
        $stmt->bindValue(':end_date', $this->end_date, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->execute();
        $this->accomplished[$this->task->streak_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccomplished() {
        return $this->accomplished;
    }
}