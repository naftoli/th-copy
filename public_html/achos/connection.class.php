<?php
class Connection {
    private $host;
    private $database;
    private $username;
    private $password;
    private $connection;   
    private $result;
    protected $log;
   

    public function __construct(){    
        $this->host = 'localhost';
        $this->database = 'mashpiadb';
        $this->username = 'mashpia';
        $this->password = 'ShJ1uWcT89Ek6E';
    }   
   
    public function get_connection(){
        try{
            $this->connection = @mysql_connect($this->host, $this->username, $this->password );
            if( $this->connection === false ){           
                throw new Exception(mysql_error());
            }           
            if(!mysql_select_db($this->database)){
                echo "no db selected error";
                throw new Exception(mysql_error());
            }
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
    }
   
    public function close_connection(){
        mysql_close( $this->connection );
        $this->connection = NULL;
    }
   
    public function query( $query ){
        $this->get_connection();
       
        try {
            if( empty($query) ){
                throw new Exception("Bam Query error: empty query string");
            }
           
            $this->result = @mysql_query($query, $this->connection);
            if(!$this->result){
                throw new Exception(mysql_error());
            } else {
                return $this->result;
            }
        } catch(Exception $e){
            echo $e->getMessage();
        }
        $this->close_connection();
    }   
}
?>