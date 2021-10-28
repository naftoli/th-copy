<?php
require '../class.points.php';

class ReportingEngine {
    private $data;
    private $aliases;
    private $result;
    private $qry;
    private $error;
    private $school_id;
    private $class_id;
    private $grade;
    private $checkRank;
    private $checkPoints;
    private $checkSM;
    
    public function __construct( $info ) {
        $this->createAliases();
        $this->data = $info;
        $this->result = array();
        $this->qry = '';
        $this->error = '';
        $this->school_id = 0;
        $this->class_id = 0;
        $this->grade = 0;
        $this->checkRank = false;
        $this->checkPoints = false;
        $this->checkSM = false;
    }
    
    private function createAliases() {
        $this->aliases = array(
            'users'         =>  'u',
            'admins'        =>  'a',
            'classes'       =>  'c',
            'schools'       =>  's'
        );
    }

    public function setSchool( $id ) {
        if ( $id > 0 ) $this->school_id = $id;
    }

    public function setClass( $id ) {
        if ( $id > 0 ) $this->class_id = $id;
    }

    public function setGrade( $grade ) {
        if ( $grade > 0 ) $this->grade = $grade;
    }
    
    public function createQry() {
        $sql = $this->generateSelect();
        $sql .= $this->generateFrom();
        $sql .= $this->generateWhere();
        $this->qry = $sql;
    }
    
    private function generateSelect() {
        $sql = "SELECT ";
        foreach ($this->data as $table => $columns) {
            // skip rank, calc, and sm info - we will get that later
            if ( in_array( $table, ['calc','ranks', 'sm'] ) ) continue;

            // make sure to always have user_id when pulling from users table
            if ( $table == 'users' ) {
                $sql .= "u.user_id, ";
            }

            foreach ($columns as $column) {
                // add alias with underscore to each column name to ensure there's no conflicting columnn names
                $sql .= $this->aliases[$table] . "." . $column . " as " . $this->aliases[$table] . "_" . $column . ", ";
            }
        }
        // remove last comma
        $sql = substr($sql, 0, strlen($sql) - 2);
        return $sql;
    }
    
    private function generateFrom() {
        // extract tables from data array 
        $tables = array_keys( $this->data );
        
        // figure out root table for joins
        // by default it's users
        $root = 'users';
        // root table is admins only if no other table is chosen
        if ( count( $tables ) == 1 && in_array('admins', $tables) ) {
            $root = 'admins';
        }
        
        $sql = " FROM ";
        if ( $root == 'users' ) {
            $sql .= "users u ";
            foreach ( $tables as $table ) {
                switch ( $table ) {
                    case 'admins':
                        $sql .= "JOIN admin_auths aa ON aa.id = u.user_id
                                JOIN admins a USING (admin_id) ";
                        break;
                    case 'schools':
                        $sql .= "JOIN schools s ON s.school_id = u.school_id ";
                        break;
                    case 'classes':
                        $sql .= "JOIN classes c ON c.class_id = u.class_id ";
                        break;
                    case 'ranks':
                        $this->checkRank = true;
                        break;
                    case 'calc':
                        $this->checkPoints = true;
                        break;
                    case 'sm':
                        $this->checkSM = true;
                        break;
                }
            }
        } else if ( $root == 'admins' ) {
            $sql .= "admins a ";

            // if we are limiting to school / grade and we only are looking for admin info, we need some joins
            if ( $this->school_id > 0 ) {
                $sql .= " JOIN admin_auths aa USING (admin_id) 
                        JOIN users u ON u.user_id = aa.id ";
            }            
        }

        // if limiting to grade only, and we don't have the classes table in our data array, we need to add a join
        if ( $this->grade > 0 && !in_array( 'classes', array_keys( $this->data ) ) ) {
            $sql .= "JOIN classes c ON c.class_id = u.class_id ";
        }
        return $sql;
    }
    
    private function generateWhere() {
        $sql = " WHERE 1";
        // limit to school / class if there's user info
        if ( $this->school_id > 0 ) {
            $sql .= " AND u.school_id = " . $this->school_id;
            if ( $this->class_id > 0 ) {
                $sql .= " AND u.class_id = " . $this->class_id;
            } else if ( $this->grade > 0 ) {
                $sql .= " AND c.class_grade = '" . $this->grade . "'";
            }
        }

        return $sql;
    }
    
    public function runQry() {
        //echo $this->qry . "<br />";
        if ( empty( $this->qry ) ) return false;
        if ( $res = mysql_query( $this->qry ) ) {
            while ( $row = mysql_fetch_assoc( $res ) ) {
                // check if we need to find out current / highest rank
                if ( $this->checkRank ) {
                    $row['r_rank_name'] = $this->getRank( $row['user_id'] );
                }
                // check if we need to add points info to result
                if ( $this->checkPoints ) {
                    $p = new Points( $row['user_id'] );
                    // find out which points to get 
                    $pointsToGet = $this->data['calc'];
                    foreach ( $pointsToGet as $type ) {
                        switch ( $type ) {
                            case 'store_points':
                                $row['store_points'] = $p->getStorePoints();
                                break;
                            case 'total_points':
                                $row['total_points'] = $p->getTotalPoints();
                                break;
                            case 'total_this_yr':
                                $row['total_this_yr'] = $p->getTotalThisYear();
                                break;
                        }
                    }
                }
                // check if we need to find tehillim quotas
                if ( $this->checkSM ) {
                    $sm = $this->data['sm'];
                    foreach ($sm as $type ) {
                        switch ($type) {
                            case 'kapitlach':
                                $row['kapitlach'] = $this->getKapitlach($row['user_id']);
                                break;
                            case 'minutes':
                                $row['minutes'] = $this->getMinutes($row['user_id']);
                                break;
                        }
                    }
                }
                // make sure there's at least one field that has info that's not blank
                $addToResult = false;
                foreach ( $row as $key => $value ) {
                    if ( !empty( $value ) ) {
                        $addToResult = true;
                        break;
                    }
                }
                if ( $addToResult ) $this->result[] = $row;
            }
        } else {
            $this->error = $this->qry . "<br />" . mysql_error() . "<br />";
            return false;
        }
        return true;
    }
    
    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getRank( $user_id ) {
        $sql = "SELECT rank_name 
                FROM ranks 
                WHERE rank_ord = (
                    SELECT max(rank_ord) FROM rank_marks WHERE user_id = $user_id
                )";
        //echo $sql . "<br />";
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        return $row['rank_name'];
    }

    public function getKapitlach( $user_id ) {
        $sql = "SELECT 
                    school_type_id, lang_id, track_id, level
                FROM
                    users u
                        JOIN
                    user_tracks ut using (user_id)
                WHERE
                    subject_id = 1 AND u.user_id = $user_id";
//        echo $sql . "<br /><br />";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $school_type_id = $row['school_type_id'];
        $track_id = $row['track_id'];
        $level = $row['level'];
        $lang_id = $row['lang_id'];

        $jd = unixtojd();
        $sql = "SELECT 
                    description
                FROM
                    date_tasks
                WHERE
                    date_tasks_mission_id IN (SELECT 
                            date_tasks_mission_id
                        FROM
                            date_tasks_missions
                        WHERE
                            subject_id = 1 AND track_id = $track_id
                                AND level = $level
                                AND lang_id = $lang_id
                                AND school_type_id = $school_type_id
                                AND start_date >= $jd
                                AND end_date <= ($jd + 6))
                        AND ord = 1";
//        echo $sql . "<br />"; exit;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['description'];
    }

    public function getMinutes( $user_id ) {
        $sql = "SELECT 
                    school_type_id, lang_id, track_id, level
                FROM
                    users u
                        JOIN
                    user_tracks ut using (user_id)
                WHERE
                    subject_id = 1 AND u.user_id = $user_id";
//        echo $sql . "<br /><br />";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $school_type_id = $row['school_type_id'];
        $track_id = $row['track_id'];
        $level = $row['level'];
        $lang_id = $row['lang_id'];

        $jd = unixtojd();
        $sql = "SELECT 
                    quantity
                FROM
                    date_tasks
                WHERE
                    date_tasks_mission_id IN (SELECT 
                            date_tasks_mission_id
                        FROM
                            date_tasks_missions
                        WHERE
                            subject_id = 1 AND track_id = $track_id
                                AND level = $level
                                AND lang_id = $lang_id
                                AND school_type_id = $school_type_id
                                AND start_date >= $jd
                                AND end_date <= ($jd + 6))
                        AND ord = 2";
//        echo $sql . "<br />"; exit;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['quantity'];
    }
}