<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
/*
 *  mysql => PDO adapter
 *
 *  Written for unit tests by Menachem Hornbacher on 11/7/2017
*/

// *********************** INTERFACES ********************* //
// interface for PDO object
interface DBConnAdapter {
    public function query($sql);
    public function insert_id();
}
// interface for PDOStatement object
interface DBQueryAdapter {
    public function num_rows();
    public function fetch_assoc();
}

// *********************** CLASS OBJECTS ********************* //

/*
 *  DBAdapter class
 *
 *  Provides a basic shared interface for PDO and mysql connection objects/functions
 *
 *  Implaments the DBConnAdapter interface. See list of functions there
 *
 *  Constructor: new DBAdapter([$pdo_conn])
 *      $pdo_conn => PDO connection (optional)
 *
*/

class DBAdapter implements DBConnAdapter {
    
    private $conn = false; // the interal pdo database connection if present (not present by default)
    
    public function __construct($pdo_conn = false){
        if ($pdo_conn){
            $this->conn = $pdo_conn; // set the connection if set
        }
    }
    
    public function query($sql){
        if($this->conn){
            $query = $this->conn->query($sql);
        } else {
            $query = mysql_query($sql);
        }
        return $query ? new QueryAdapter($query) : false; // return a new query adapter or false. Just like PDO (http://php.net/manual/en/pdo.query.php <= Return Values)
    }
    
    public function insert_id(){
        return mysql_insert_id();
    }
    
}

/*
 *  QueryAdapter class
 *
 *  Provides a basic shared interface for PDO and mysql query objects
 *
 *  Implaments the DBQueryAdapater interface. See list of functions there
 *
 *  Constructor: new QueryAdapter($query)
 *      $query => PDO or mysql query
 *
 *      Detects PDO based on the type of query provided
 *
*/

class QueryAdapter implements DBQueryAdapter {
    
    private $query;
    private $isPDO;
    // create the adapter with the query instance (from mysql or PDO)
    public function __construct($query) {
        $this->query = $query;
        $this->isPDO = $query instanceof PDOStatement; // check if it is an instance of PDOStatemnet
    }
    // mock PDOStatement::num_rows
    public function num_rows(){
        if($this->isPDO){
            return $this->query->numRows(); // pass to the pdo version
        } else {
            return mysql_num_rows($this->query); // pass to the mysql library
        }
    }
    // mock PDOStatement::fetch_assoc
    public function fetch_assoc() {
        if($this->isPDO){
            return $this->query->fetch(); // pass to the pdo version
        } else {
            return mysql_fetch_assoc($this->query); // pass to the mysql library
        }
    }
    
}

