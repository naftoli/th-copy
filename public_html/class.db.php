<?php
/** 
 * DB class, extends PDO
 * Singleton wrapper for DBS connection.
 * 
 * @method __construct()
 *      creates new database connection
 * 
 * @method getInstance()
 *      returns or creates a new instance of the DBS connection (returns an instance of self....)
 * 
 */ 

class DB extends PDO {
    private static $_instance = null;
    
    public function __construct() {
        require dirname(__FILE__).'/../includes/globals.php';
        $dsn =  'mysql:dbname=mashpiadb;host=' . $global_db_host . ';port=3306';
        $user = $global_db_user;
        $pass = $global_db_pass;
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'');
        parent::__construct( $dsn, $user, $pass, $options );
    }
    
    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new DB;
            self::$_instance->setAttribute( PDO::ATTR_PERSISTENT, true );
        }
        return self::$_instance;
    }
}
?>
