<?php
class TanyaBalPeh {
    private $grid_id;
    private $year;
    private $weeks;
    private $start;
    private $end;
    private $subject_id;
    private $quota;
    private $done;

    public function __construct() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
        $this->year = GlobalSettings::getCurrentYear();
        $sql = "select * from parshos where year = " . $this->year;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->weeks[] = $row;
        }
        $this->grid_id = [21013,21015];
        $this->subject_id = 27;
        $this->start = 0;
        $this->end = 0;
        $this->quota = 0;
        $this->done = 0;
    }

    public function setStart($jd) {
        $this->start = $jd;
    }

    public function setEnd($jd) {
        $this->end = $jd;
    }

    public function setQuota($type, $id = 0) {
        $sql = "select sum(quantity) as total 
                from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, level, track_id) 
                join users u using (user_id) 
                where dtm.subject_id = $this->subject_id 
                and grid_id in (" . implode(',', $this->grid_id) . ")";
        if ($type == 'base') $sql .= " and u.school_id = " . $id;
        else if ($type == 'platoon') $sql .= " and u.class_id = " . $id;
        else if ($type == 'user') $sql .= " and u.user_id = " . $id;
        if ($this->start) $sql .= " and mark_date >= " . $this->start;
        if ($this->end) $sql .= " and mark_date <= " . $this->end;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $this->quota = $row['total'];
    }

    public function setDone($type, $id = 0) {
        $sql = "select sum(done_qty) as total 
                from date_tasks_marks dtmm 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, level, track_id) 
                join users u using (user_id) 
                where dtm.subject_id = $this->subject_id 
                and grid_id in (" . implode(',', $this->grid_id) . ")";
        if ($type == 'base') $sql .= " and u.school_id = " . $id;
        else if ($type == 'platoon') $sql .= " and u.class_id = " . $id;
        else if ($type == 'user') $sql .= " and u.user_id = " . $id;
        if ($this->start) $sql .= " and mark_date >= " . $this->start;
        if ($this->end) $sql .= " and mark_date <= " . $this->end;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $this->done = $row['total'];
    }

    public function getQuota() {
        return $this->quota;
    }

    public function getDone() {
        return $this->done;
    }
}