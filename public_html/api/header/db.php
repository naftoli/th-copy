<?php
require_once( __DIR__ . "/../../../includes/globals.php");
require_once( __DIR__ . "/../vendor/autoload.php" );

$_GLOBALS['log'] = new SimpleLogger( __DIR__ . '/../simpleLogger.log' );
$connections = [
    'mashpiadb' => "mysql://$global_db_user:$global_db_pass@$global_db_host/mashpiadb?charset=utf8"
];

// Connect to legacy MySQL
try {
    @mysql_connect($global_db_host.":3306", $global_db_user, $global_db_pass);
    mysql_query('SET NAMES utf8');
    mysql_query('SET CHARACTER_SET utf8');
    mysql_select_db('mashpiadb');
} catch ( Exception $e ) {
    $_GLOBALS['log']->log( "mysql_connect Failed. Error: " . $e );
}

// Connect $pdo to PDO
$pdo = new \PDO( "mysql:host=$global_db_host;dbname=mashpiadb", $global_db_user, $global_db_pass );
$pdo->exec( "SET NAMES utf8" ); // fix utf8
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// connect ActiveRecord to DBS
ActiveRecord\Config::initialize( function( $cfg ) use ( $connections ) {
    $cfg->set_model_directory( __DIR__ . "/../models" );
    $cfg->set_connections( $connections );
    $cfg->set_default_connection('mashpiadb');
});

// log all SQL queries in development
if ( $development ) {
    $log = new SimpleLogger();
    ActiveRecord\Config::instance()->set_logging(true);
    ActiveRecord\Config::instance()->set_logger($log);
}

class SimpleLogger {
    private $file_name;

    public function __construct( $file_name = './mysql.log' ){
        $this->file_name = $file_name;
    }

    public function log( $msg ) {
        if ( is_array( $msg ) ) $msg = json_encode( $msg );
        $msg = date("F j, Y, g:i a") . "\t" . print_r( $msg, true ) . PHP_EOL;
        file_put_contents( $this->file_name, $msg, FILE_APPEND );
    }
}