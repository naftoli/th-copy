<?php

// Illuminate Database docs:
// Usage outside of laravel: https://github.com/illuminate/database/tree/6.x
// Query Builder: https://laravel.com/docs/6.x/queries
// Eloquent ORM: https://laravel.com/docs/6.x/eloquent

require_once __DIR__ . '/../vendor/autoload.php';
use Illuminate\Database\Capsule\Manager as Capsule;

// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;

if (!isset($capsule)) {
    if (!isset($global_db_host, $global_db_user, $global_db_pass)) {
        throw new Exception("missing db globals");
    }
    $capsule = new Capsule;

    // default (mashpidb) connection
    // accessible as Capsule::some_query or Capsule::connection('default')->some_query
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $global_db_host,
        'database'  => 'mashpiadb',
        'username'  => $global_db_user,
        'password'  => $global_db_pass,
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);

    // pointsDB connection
    // accessible as `Capsule::connection('pointsDB')->some_query`
    // 
    // add `protected $connection = 'pointsDB';` to any models that belong to this connection
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $global_db_host.":3306",
        'database'  => 'pointsDB',
        'username'  => $global_db_user,
        'password'  => $global_db_pass,
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ], "pointsDB");

    // // Set the event dispatcher used by Eloquent models... (optional)
    // $capsule->setEventDispatcher(new Dispatcher(new Container));

    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();

    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent();
    // $logger->debug("setup orm", [Capsule::table('users')->where('user_registered', '>', '2020-02-28')->limit(10)->get()[0]]);
}
