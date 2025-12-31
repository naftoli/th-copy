<?php
class Accomplished {
    private $db;
    private $user_id;
    private $grid_ids;
    private $start_date;
    private $end_date;
    private $accomplished;

    public function __construct($user_id, $grid_ids, $start_date, $end_date) {
        global $MASHPIA_DB;
        $this->db = $MASHPIA_DB;
        $this->user_id = $user_id;
        $this->grid_ids = $grid_ids;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->limit = (int)$end_date - (int)$start_date;
        $this->accomplished = [];
    }

    public function setAccomplished() {
        $sql = "SELECT DISTINCT mark_date  
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.grid_id = :grid_id 
                AND dtm.user_id = :user_id 
                AND dtm.mark_date >= :start_date 
                AND dtm.mark_date <= :end_date
                ORDER BY mark_date DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        foreach ($this->grid_ids as $grid_id) {
            $stmt->bindValue(':grid_id', $grid_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindValue(':start_date', $this->start_date, PDO::PARAM_INT);
            $stmt->bindValue(':end_date', $this->end_date, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
            $stmt->execute();
            $this->accomplished[$grid_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getAccomplished() {
        return $this->accomplished;
    }
}