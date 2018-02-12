<?php
// load the interface
require_once(dirname(__FILE__)."/../classes/DBAdapter.php");
// mock a db connection
class DBMock implements DBConnAdapter {
    
    public $queries = []; // array of queries already executed
    public $sql_queries = [];
    public $results = []; // array of precomputed results. (key is SQL and value is the returned result (nested array))
    
    public function query($sql) {
        $sql_queries[] = $sql;
        if(isset($this->results[$sql])){
            $query = new DBQueryMock($sql, $this->results[$sql]);
            $queries[] = $query; // add the query to the array
            return $query; // return the query
        }
        $queries[] = false; // set the query at this index to false;
        return false; // we do not know the statement so lets pretend it failed
    }
    // add a result to the list
    public function add_single_result($sql, $result){
        // check if the index was set, if not make an array there
        if(!isset($this->results[$sql])) $this->results[$sql] = [];
        // add the result to the end of the array
        $this->results[$sql][] = $result; 
        // return its index
        return count($this->results[$sql]) - 1;
    }
    
    public function clear_results($sql){
        if(isset($this->results[$sql])) unset($this->results[$sql]);
    }
    
}
// mock a db query
class DBQueryMock implements DBQueryAdapter {
    
    public $sql;
    public $result;
    public $result_index = 0;
    
    public function __construct($sql, $result){
        $this->sql = $sql;
        $this->result = $result;
    }
    // from interface
    public function num_rows() {
        return count($this->result); // count the result and return that for now ¯\_(ツ)_/¯
    }
    // from interface
    public function fetch_assoc() {
        if(count($this->result) > $this->result_index){
            return $this->result[$this->result_index++]; // return the result at that index and increment to the next index
        } else {
            return false; // we are out of results
        }
    }
}