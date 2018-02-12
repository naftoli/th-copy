<?php
class DB {
        
    private static $handle;
    
    public static function getInstance() {
        require '/home/mashpia/includes/globals.php';
        if (!self::$handle) {
            try {
                $dsn = 'mysql:dbname=mashpiadb;host=localhost';
                self::$handle = new PDO($dsn, $global_db_user, $global_db_pass);
            }
            catch (PDOException $e) {
                echo "Connection failed" . $e->getMessage();
            }
        }
        return self::$handle;
    }
}
?>
