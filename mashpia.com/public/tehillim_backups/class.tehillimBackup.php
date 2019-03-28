<?php
class TehillimBackup {
    public $dates;

    private $date;
    private $marks;
    private $year;
    private $errors;
    
    public function __construct() {
        require_once(dirname(__FILE__).'/../class.globalSettings.php');
        $this->year = GlobalSettings::getCurrentYear();
        $this->dates = array(
            2458383, // tishrei 5778
            2458404, 
            2458431, 
            2458463,
            2458496,
            2458527,
            2458551,
            2458578,
            2458614,
            2458641,
            2458669,
            2458697
        );
        $this->errors = array();
    }
    
    public function testBackup() {
        $this->getLastSM();
        echo "Date: " . $this->date . "<br />";
        $this->getMarks();
        $this->backup();
    }
    
    public function runBackup() {
        $today = unixtojd();
        if (in_array( $today, $this->dates )) {
            // find out last shabbos mevorchim date
            $this->getLastSM();
            
            // make sure there's no duplicates
            $this->fixDuplicates();
            
            // get all marks from last shabbos mevorchim
            $this->getMarks();
            
            // update db
            $this->backup();
        } else {
            $this->errors[] = "No backup to do today.";
        }
    }
    
    private function getLastSM() {
        $sm = calculateSM( $this->year );
        foreach ($sm as $month => $date) {
            if ($date > unixtojd()) {
                // we found next sm
                // we need previous sm
                $month--;
                break;
            }
        }
        $this->date = $sm[$month];
    }
    
    private function getMarks() {
        // make sure we don't have any errors yet
        if (empty($this->errors)) {
            $marks = array();
            $sql = "select * from date_tasks_marks dtmm 
                    join date_tasks dt using (date_task_id)
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.start_date = " . $this->date . "
                    and dtm.end_date = " . $this->date . "
                    and dtm.subject_id = 1
                    and dt.grid_id in (8001,8002)";
            //echo $sql;
            $result = mysql_query( $sql );
            while ($row = mysql_fetch_assoc( $result )) {
                $marks[] = $row;
            }
            $this->marks = $marks;
        }
    }
    
    private function backup() {
        // make sure we don't have any errors yet 
        if (empty($this->errors)) {
            $success = true;
            mysql_query('set autocommit=0');
            mysql_query('begin');
            
            $i = 1;
            foreach ($this->marks as $mark) {
                $sql = "insert into tehillim_backups
                        set date_task_id = " . $mark['date_task_id'] . ",
                        user_id = " . $mark['user_id'] . ",
                        mark_date = " . $mark['mark_date'] . ",
                        done_qty = " . $mark['done_qty'] . ",
                        mark_description = \"" . $mark['mark_description'] . "\",
                        year = " . $this->year . ",
                        grid_id = " . $mark['grid_id'] . ",
                        sm_date = " . $this->date;
                //echo $i++ . ": " . $sql . "<br />";
                if (! @mysql_query( $sql )) {
                    $success = false;
                    $error = mysql_error();
                    break;
                }
            }
            
            if ($success) {
                mysql_query('commit');
            } else {
                mysql_query('rollback');
                $this->errors[] = $error . "<br />" . $sql;
            }
            mysql_query('set autocommit=1');
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    private function fixDuplicates() {
        $mark_date = $this->date;
        $info = array();
        $sql = "select * from date_tasks_marks dtm
                join date_tasks dt using (date_task_id) 
                where dt.grid_id in (8001,8002) 
                and mark_date = " . $mark_date;
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $task_id = $row['date_task_id'];
            $user_id = $row['user_id'];
            $done_qty = $row['done_qty'];
            $grid_id = $row['grid_id'];
            $info[$user_id][$task_id][$grid_id][] = $done_qty;
        }
        //echo "<pre>"; print_r($info); echo "</pre>";
        
        $qrys = array();
        foreach ($info as $user_id => $other) {
            foreach ($other as $task_id => $more) {
                foreach ($more as $grid_id => $marks) {
                    $qry = '';
                    $qry2 = '';
                    // get highest mark
                    rsort( $marks );
                    $done_qty = $marks[0];
                    
                    // if there's more than one mark in the system
                    if (count($marks) > 1) {
                        // delete all marks
                        $qrys[] = "delete from date_tasks_marks
                                    where date_task_id = " . $task_id . "
                                    and user_id = " . $user_id . "
                                    and mark_date = " . $mark_date;
                        
                        // insert correct mark
                        $qrys[] = "insert into date_tasks_marks
                                    set date_task_id = " . $task_id . ",
                                    user_id = " . $user_id . ",
                                    mark_date = " . $mark_date . ",
                                    done_qty = " . $done_qty . ",
                                    mark_points = 0.5";
                    }
                }
            }
        }
        
        //echo "<pre>"; print_r( $qrys ); echo "</pre>";
        mysql_query('set autocommit=0');
        mysql_query('begin');
        $success = true;
        foreach ($qrys as $qry) {
            if (!mysql_query( $qry )) {
                $success = false;
                break;
            }
        }
        if ($success) {
            mysql_query('commit');
        } else {
            mysql_query('rollback');
            $this->errors[] = "Error fixing duplicates...";
            $this->errors[] = "Aborting...";
        }
        mysql_query('set autocommit=1');
    }
}
?>