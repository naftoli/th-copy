<?php
class ReportingEngine {
    private $data = array();
    private $aliases = array();
    private $qry = '';
    private $result = array();
    private $error = '';
    
    public function __construct( $info ) {
        $this->createAliases();
        $this->data = $info;
    }
    
    private function createAliases() {
        $this->aliases = array(
            'users'         =>  'u',
            'admins'        =>  'a',
            'admin_auths'   =>  'aa',
            'medals'        =>  'm',
            'ranks'         =>  'r',
            'medal_marks'   =>  'mm',
            'rank_marks'    =>  'rm',
            'date_tasks'    =>  'dt',
            'date_task_marks'           =>  'dtmarks',
            'date_tasks_missions'       =>  'dtm',
            'date_tasks_mission_marks'  =>  'dtmm'
        );
    }
    
    public function createQry() {
        $sql = 'SELECT ';
        $sql .= $this->generateSelect();
        
        $sql .= "FROM ";
        $sql .= $this->generateFrom();
        
        $sql .= "WHERE ";
        $sql .= $this->generateWhere();
        
        $this->qry = $sql;
    }
    
    private function generateSelect( $data ) {
        $sql = '';
        foreach ($this->data as $table => $columns) {
            foreach ($columns as $column) {
                // add alias with underscore to each column name to ensure there's no conflicting columnn names
                $sql .= $this->aliases[$table] . "." . $this->aliases[$table] . "_" . $column . ", \n";
            }
            // remove last comma
            $sql = substr($sql, 0, strlen($sql) - 2);
        }
        return $sql;
    }
    
    private function generateFrom() {
        // extract tables from data array
        $tables = array();
        foreach ($this->data as $table => $columns) {
            $tables[] = $table;
        }
        
        // figure out root table for joins
        if (in_array('users', $tables)) {
            $root = 'users';
        } else if (in_array('admins', $tables)) {
            $root = 'admins';
        }
        
        if ($root == 'users') {
            $sql = "users u ";
            foreach ($tables as $table) {
                switch ($table) {
                    case 'users':
                        continue;
                        break;
                    case 'admins':
                        $sql .= "join admin_auths aa on aa.id = u.user_id
                                join admins a using (admin_id) ";
                        break;
                    case ''
                }
            }
        } else if ($root == 'admins') {
            foreach ($tables as $table) {
                
            }
        }
    }
    
    private function generateWhere() {
        
    }
    
    public function runQry() {
        if (empty( $this->qry )) return false;
        if ($res = mysql_query( $this->qry )) {
            while ($row = mysql_fetch_assoc( $res )) {
                $this->result[] = $row;
            }
        } else {
            $this->error = mysql_error() . "<br />" . $this->qry;
            return false;
        }
        return true;
    }
    
    public function getResult() {
        return $this->result;
    }
}