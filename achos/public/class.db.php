<?
require '/home/mashpia/includes/globals.php';
class DB extends PDO {
    private static $_instance = null;
    
    public function __construct() {
        $dsn =  'mysql:dbname=mashpiadb;host=localhost';
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
