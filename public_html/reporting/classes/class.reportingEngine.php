<?php
require '../class.points.php';

class ReportingEngine {
    private $data = array();
    private $aliases = array();
    private $result = array();
    private $qry = '';
    private $error = '';
    private $checkPoints = false;
    
    public function __construct( $info ) {
        $this->createAliases();
        $this->data = $info;
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
    
    public function createQry() {
        $sql = $this->generateSelect();
        $sql .= $this->generateFrom();
        $sql .= $this->generateWhere();
        $this->qry = $sql;
    }
    
    private function generateSelect() {
        $sql = 'SELECT ';
        foreach ($this->data as $table => $columns) {
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
        $tables = array();
        foreach ($this->data as $table => $columns) {
            if ( $table != 'miles' ) $tables[] = $table;
            else $this->checkPoints = true;
        }
        
        // figure out root table for joins
        $root = 'users';
        if (!in_array('users', $tables) && in_array('admins', $tables)) {
            $root = 'admins';
        }
        
        $sql = " FROM ";
        if ($root == 'users') {
            $sql .= "users u ";
            foreach ($tables as $table) {
                switch ($table) {
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
                }
            }
        } else if ($root == 'admins') {
            $sql .= "admins a ";
        }
        return $sql;
    }
    
    private function generateWhere() {
        $sql = '';
        if (array_key_exists('users', $this->data)) {
            $sql = " WHERE user_registered > 0";
        }
        return $sql;
    }
    
    public function runQry() {
        echo $this->qry . "<br />";
        if (empty( $this->qry )) return false;
        if ($res = mysql_query( $this->qry )) {
            while ($row = mysql_fetch_assoc( $res )) {
                // check if we need to add points info to result
                if ( $this->checkPoints ) {
                    echo "Checking Points<br />";
                    $p = new Points( $row['user_id'] );
                    // find out which points to get 
                    $pointsToGet = $this->data['miles'];
                    foreach ( $pointsToGet as $type ) {
                        if ( $type == 'store_points' ) {
                            $row['store_miles'] = $p->getStorePoints();
                        } else if ( $type == 'auction_points' ) {
                            $row['auction_miles'] = $p->getAuctionPoints();
                        }
                    }
                }
                $this->result[] = $row;
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