<?php // import DBS details. Note that composer must be already imported
require_once( __DIR__ . "/../../../includes/globals.php");

$connections = [
    'mashpiadb' => "mysql://$global_db_user:$global_db_pass@$global_db_host/mashpiadb?charset=utf8"
];

// INITIALIZE $pdo
$pdo = new \PDO( "mysql:host=$global_db_host;dbname=mashpiadb", $global_db_user, $global_db_pass );
$pdo->exec( "SET NAMES utf8" ); // fix utf8
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// CONNECT ActiveRecord
ActiveRecord\Config::initialize( function( $cfg ) use ( $connections ) {
    $cfg->set_model_directory( __DIR__ . "/../models" );
    $cfg->set_connections( $connections );
});