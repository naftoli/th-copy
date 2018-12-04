<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.parshos.php';

class Mivtzoim {
    private $id;
    private $name;
    private $start;
    private $end;
    private $short_names;
    private $subject_id;

    /*
    * attempts to create object based on row in dbs
    * if no row is found, throws exception
    */
    public function __construct( $id ) {
        global $MASHPIA_DB;
        $this->id = $id;
        $sth = $MASHPIA_DB->prepare("select * from mivtzoim where mivtzoim_id = :id");
        $sth->execute([
            ':id'   =>  $id
        ]);
        $row = $sth->fetch();
        if ( $row ) {
            $this->name = $row['name'];
            $this->start = $row['start'];
            $this->end = $row['end'];
            $this->subject_id = 12;
            $this->setShortNames();
        } else {
            throw new \Exception("Could not find any mivtzoim record with this ID.");
        }
    }

    public function getName() {
        return $this->name;
    }

    /*
    * finds the short names associated with this mivtzoim object
    */    
    private function setShortNames() {
        global $MASHPIA_DB;
        $sth = $MASHPIA_DB->prepare("select short_name from mivtzoim_tasks where mivtzoim_id = :id");
        $sth->execute([
            ':id'   =>  $this->id
        ]);
        if ( $rows = $sth->fetchAll() ) {
            foreach ( $rows as $row ) {
                $this->short_names[] = $row['short_name'];
            }
        } 
    }

    public function getShortNames() {
        return $this->short_names;
    }

    /*
    * saves short names to dbs for given mivtzoim id
    */
    public function saveShortNames( array $short_names ) {
        global $MASHPIA_DB;
        
        $sth1 = $MASHPIA_DB->prepare("delete from mivtzoim_tasks where mivtzoim_id = :id");
        $sth2 = $MASHPIA_DB->prepare("insert into mivtzoim_tasks set mivtzoim_id = :id, short_name = :name");
        try {
            if ( $MASHPIA_DB->beginTransaction() ) {
                $success = true;

                // first delete any existing short names that are already saved
                $result = $sth1->execute([
                    ':id' => $this->id
                ]);
                if ( $result ) {
                    foreach ( $short_names as $name ) {
                        $result = $sth2->execute([
                            ':id'   =>  $this->id, 
                            ':name' =>  $name
                        ]);
                        if ( !$result ) {
                            $success = false;
                            break;
                        }
                    }
                } else {
                    $success = false;
                }
                if ( $success ) {
                    $MASHPIA_DB->commit();
                    return true;
                } else {
                    print_r( $sth2->errorInfo() );
                    $MASHPIA_DB->rollBack();
                    return false;
                }
            } 
        } catch ( PDOException $e ) {
            throw new \Exception("Could not begin transaction.");
            return false;
        }        
    }

    public function getDates() {
        return [
            'start' => $this->start,
            'end'   => $this->end
        ];
    }

    /*
    * sets start/end to narrow down tasks to given week
    */
    public function setDates( $start, $end ) {
        $this->start = $start;
        $this->end = $end;
    }

    /*
    * gets list of grid ids based on short names / dates for marking
    */
    public function getTasks() { 
        global $MASHPIA_DB;
        if ( empty( $this->short_names ) ) {
            throw new \Exception("Could not find any tasks connected with the " . $this->name . " campaign. Please speak to HQ to have them set it up.");
        }

        $tasks = [];
        $lang_id = 1; 

        foreach ( $this->short_names as $name ) {
            $sth = $MASHPIA_DB->prepare("
                SELECT 
                    grid_id, name, quantity    
                FROM
                    date_tasks dt 
                        JOIN
                    date_tasks_missions dtm USING (date_tasks_mission_id)
                WHERE
                    short_name = :short_name 
                        AND dtm.start_date >= :start 
                        AND dtm.end_date <= :end 
                        AND dtm.subject_id = :subject 
                        AND dtm.lang_id = :lang 
                GROUP BY name                
            ");
            $sth->execute([
                ':short_name'   =>  $name, 
                ':start'    =>  $this->start, 
                ':end'      =>  $this->end, 
                ':subject'  =>  $this->subject_id, 
                ':lang'     =>  $lang_id 
            ]);
            if ( $rows = $sth->fetchAll() ) {
                foreach ( $rows as $row ) {
                    $tasks[$name][] = $row;
                }
            } else {
                //print_r( $sth );
                //print_r( $sth->errorInfo() );
            }
        }
        return $tasks;
    }

    /*
    * returns marks found in date_tasks_marks table for passed in grid ids
    */
    public function getMarks( array $grid_ids ) {
        global $MASHPIA_DB;
        $marks = [];
        $sth = $MASHPIA_DB->prepare("
            SELECT DISTINCT
                dt.grid_id, dtm.user_id, dtm.done_qty
            FROM
                date_tasks dt
                    JOIN
                date_tasks_missions dtmm USING (date_tasks_mission_id)
                    LEFT JOIN
                date_tasks_marks dtm USING (date_task_id)
            WHERE
                dt.grid_id IN (:grid)
                    AND dtmm.start_date >= :start
                    AND dtmm.end_date <= :end
        ");
        $sth->execute([
            ':grid'     =>  implode(',', $grid_ids), 
            ':start'    =>  $this->start, 
            ':end'      =>  $this->end
        ]);
        if ( $rows = $sth->fetchAll() ) {
            foreach ( $rows as $row ) {
                if ( $row['user_id'] && $row['done_qty'] ) {
                    $marks[$row['grid_id']][$row['user_id']] = $row['done_qty'];
                }
            }
        }
        return $marks;
    }

    /*
    * takes in array of users and figures out which task id to mark for that user 
    * based on grid ids provided
    */
    public function markTasks( array $marks ) {
        global $MASHPIA_DB;

        //echo "<pre>"; print_r( $marks ); echo "</pre>";
        foreach ( $marks as $grid_id => $users ) {
            // get personal information on each user
            $user_info = $this->getUserInfo( $users );
            
            foreach ( $users as $id => $mark ) {
                // find out if mark already exists for user
                // using grid id instead of finding task id 
                // b/c user may have marked in different lang/level/etc
                $sth2 = $MASHPIA_DB->prepare("
                    SELECT 
                        dtm.date_task_id 
                    FROM
                        date_tasks_marks dtm
                            JOIN
                        date_tasks dt USING (date_task_id)
                    WHERE
                        dt.grid_id = :grid 
                            AND dtm.mark_date >= :start
                            AND dtm.mark_date <= :end
                            AND dtm.user_id = :user
                ");
                $sth2->execute([
                    ':grid'     =>  $grid_id, 
                    ':start'    =>  $this->start, 
                    ':end'      =>  $this->end, 
                    ':user'     =>  $id 
                ]);
                if ( $sth2->rowCount() > 0 ) {
                    $row = $sth2->fetch();
                    $task_id = $row['date_task_id'];

                    if ( $mark > 0 ) {
                        // do an update
                        $sth3 = $MASHPIA_DB->prepare("update date_tasks_marks set done_qty = :qty where date_task_id = :task and user_id = :user");
                        $sth3->execute([
                            ':qty'  =>  $mark, 
                            ':task' =>  $task_id, 
                            ':user' =>  $id
                        ]);
                    } else if ( $mark == 0 ) {
                        // delete mark
                        $sth3 = $MASHPIA_DB->prepare("delete from date_tasks_marks where user_id = :user and date_task_id = :task");
                        $sth3->execute([
                            ':user' =>  $id, 
                            ':task' =>  $task_id
                        ]);
                    }
                } else {
                    // do an insert
                    if ( $mark > 0 ) {
                        $school_type_id = $user_info[$id]['school_type_id'];
                        $lang_id = $user_info[$id]['lang_id'];
                        $level = $user_info[$id]['level'];
                        
                        // find out what the correct task id is 
                        $sth = $MASHPIA_DB->prepare("
                            SELECT 
                                dt.date_task_id
                            FROM
                                date_tasks dt
                                    JOIN
                                date_tasks_missions dtm USING (date_tasks_mission_id)
                            WHERE
                                dt.grid_id = :grid 
                                    AND dtm.start_date >= :start
                                    AND dtm.end_date <= :end
                                    AND dtm.subject_id = :subject
                                    AND dtm.level = :level
                                    AND dtm.lang_id = :lang
                                    AND dtm.school_type_id = :type
                        ");
                        $sth->execute([
                            ':grid'     =>  $grid_id, 
                            ':start'    =>  $this->start, 
                            ':end'      =>  $this->end, 
                            ':subject'  =>  $this->subject_id, 
                            ':level'    =>  $level, 
                            ':lang'     =>  $lang_id, 
                            ':type'     =>  $school_type_id
                        ]);
                        if ( $row = $sth->fetch() ) {
                            $task_id = $row['date_task_id'];
                            $mark_date = unixtojd();
                            if ( $mark_date < $this->start ) $mark_date = $this->start;
                            if ( $mark_date > $this->end ) $mark_date = $this->end;
                            $sth3 = $MASHPIA_DB->prepare("insert into date_tasks_marks set done_qty = :qty, date_task_id = :task, user_id = :user, mark_date = :date, mark_points = :points");
                            $result = $sth3->execute([
                                ':qty'  =>  $mark, 
                                ':task' =>  $task_id, 
                                ':user' =>  $id,
                                ':date' =>  $mark_date,
                                ':points' => 0.5
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function getUserInfo( array $users ) {
        global $MASHPIA_DB;
        $info = [];
        $sth = $MASHPIA_DB->prepare("
            SELECT 
                school_type_id, lang_id, level
            FROM
                users u
                    JOIN
                user_tracks ut USING (user_id)
            WHERE
                ut.subject_id = :subject AND user_id = :user
        ");
        foreach ( $users as $user_id => $mark ) {
            $sth->execute([
                ':subject'  =>  $this->subject_id, 
                ':user'     => $user_id
            ]);
            $row = $sth->fetch();
            $info[$user_id] = $row;
        }
        return $info;
    }
}

class MivtzoimSetup {
    private $name;
    private $start;
    private $end;
    private $subject_id;

    public function __construct( $name, $start, $end ) {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->subject_id = 12;
    }

    /* 
    * create a mivtzoim entry in the dbs and return a mivtzoim object based on row in dbs
    * returns false if fails
    */
    public function createMivtzoim() {
        global $MASHPIA_DB;
        $sth = $MASHPIA_DB->prepare("insert into mivtzoim set name = :name, start = :start, end = :end");
        $result = $sth->execute([
            ':name'     =>  $this->name, 
            ':start'    =>  $this->start, 
            ':end'      =>  $this->end
        ]);
        if ( $result ) {
            $id = $MASHPIA_DB->lastInsertId();
            if ( $id ) return new Mivtzoim( $id );
        } 
        return false;
    }

    /*
    * finds short names available to choose from when setting up the markable mivtzoim campaigns
    */
    public function availableShortNames() {
        global $MASHPIA_DB;

        $short_names = [];
        $lang_id = 1;

        $sth = $MASHPIA_DB->prepare("
            SELECT 
                dt.short_name
            FROM
                date_tasks dt
                    JOIN
                date_tasks_missions dtm USING (date_tasks_mission_id)
            WHERE
                dtm.subject_id = :subject
                    AND dtm.lang_id = :lang
                    AND dtm.start_date >= :start
                    AND dtm.end_date <= :end
                    AND dtm.personal = 0
            GROUP BY short_name
        ");
        $sth->execute([
            ':subject'  =>  $this->subject_id, 
            ':lang'     =>  $lang_id, 
            ':start'    =>  $this->start, 
            ':end'      =>  $this->end
        ]);
        if ( $rows = $sth->fetchAll() ) {
            foreach ( $rows as $row ) {
                $short_names[] = $row['short_name'];
            }
        }
        return $short_names;
    }
}

class MivtzoimReport {
    private $m;

    public function __construct( Mivtzoim $m ) {
        $this->m = $m;
    }
}