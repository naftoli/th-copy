<?php
class HooReport
{
    private $start;
    private $end;
    private $users;
    private $debug;
    
    public function __construct($start, $end, array $users)
    {
        $this->start = $start;
        $this->end = $end;
        $this->users = $users;
        $this->debug = false;
    }
    
    public function setDebug($val)
    {
        $this->debug = $val;
    }
    
    public function calcVisits()
    {
        $sql = "select count(*) as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.done_qty >= 60 
                and dtmm.start_date > " . $this->start . "
                and dtm.user_id in (" . implode(',', $this->users) . ")";
        $result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }
    
    public function calcPoints()
    {
        $sql = "select sum(dtm.done_qty) as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtm.done_qty >= 60 
                and dtmm.start_date > " . $this->start . "
                and dtm.user_id in (" . implode(',', $this->users) . ")";
        $result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $points = floor($row['total'] / 60);
            return $points;
        } else {
            return 0;
        }
    }
    
    public function calcMinutes()
    {
        $sql = "select sum(dtm.done_qty) as total
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id)
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                where dtmm.start_date > " . $this->start . "
                and dtm.user_id in (" . implode(',', $this->users) . ")";
        $result = mysql_query($sql);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $points = floor($row['total']);
            return $points;
        } else {
            return 0;
        }
    }
}