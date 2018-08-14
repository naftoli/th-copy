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
    private $checkPoints;
    
    public function __construct( $info ) {
        $this->createAliases();
        $this->data = $info;
        $this->result = array();
        $this->qry = '';
        $this->error = '';
        $this->school_id = 0;
        $this->class_id = 0;
        $this->checkPoints = false;
    }
    
    private function createAliases() {
        $this->aliases = array(
            'users'         =>  'u',
            'admins'        =>  'a',
            'classes'       =>  'c',
            'schools'       =>  's',
            'medals'        =>  'm',
            'ranks'         =>  'r'
        );
    }

    public function setSchool( $id ) {
        if ( $id > 0 ) $this->school_id = $id;
    }

    public function setClass( $id ) {
        if ( $id > 0 ) $this->class_id = $id;
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
            // skip calc info
            if ( $table == 'calc' ) continue;

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
        $root = 'users';
        if ( !in_array('users', $tables) && in_array('admins', $tables) ) {
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
                    case 'ranks':
                        $sql .= "JOIN rank_marks rm USING (user_id) 
                                JOIN ranks r USING (rank_ord) ";
                        break;
                    case 'schools':
                        $sql .= "JOIN schools s ON s.school_id = u.school_id ";
                        break;
                    case 'classes':
                        $sql .= "JOIN classes c ON c.class_id = u.class_id ";
                        break;
                    case 'calc':
                        $this->checkPoints = true;
                        break;
                }
            }
        } else if ( $root == 'admins' ) {
            $sql .= "admins a ";
            // if we are limiting to school / grade and we only are looking for admin info, we need some joins
            if ( $this->school_id > 0 ) {
                $sql .= "JOIN admin_auths aa USING (admin_id) 
                        JOIN users u ON u.user_id = aa.id";
            }
            
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
            }
        }

        return $sql;
    }
    
    public function runQry() {
        //echo $this->qry . "<br />";
        if ( empty( $this->qry ) ) return false;
        if ( $res = mysql_query( $this->qry ) ) {
            while ( $row = mysql_fetch_assoc( $res ) ) {
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
}