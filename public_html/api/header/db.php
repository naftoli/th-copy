<?php // import DBS details. Note that composer must be already imported
require_once( __DIR__ . "/../../../includes/globals.php");

$connections = [
    'mashpiadb' => "mysql://$global_db_user:$global_db_pass@$global_db_host/mashpiadb?charset=utf8"
];

// Connect to legacy MySQL
mysql_connect($global_db_host.":3306", $global_db_user, $global_db_pass);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb');

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