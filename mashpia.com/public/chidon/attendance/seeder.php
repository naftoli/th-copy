<?php
ini_set('display_errors',1);
require 'db-seeder/vendor/autoload.php';
require '/home/mashpia/includes/globals.php';

$pdo = new PDO('mysql:dbname=mashpiadb;host=localhost', $global_db_user, $global_db_pass);
$seeder = new \tebazil\dbseeder\Seeder($pdo);
$generator = $seeder->getGeneratorConfigurator();
$faker = $generator->getFakerConfigurator();

$seeder->table('th_chidon_staff')->columns([
    'staff_id'  =>  $generator->pk,
    'name'      =>  $faker->name,
    'cell'      =>  $faker->phoneNumber,
    'email'     =>  $faker->email,
    'username'  =>  $faker->userName,
    'password'  =>  $faker->password
]);

$seeder->table('th_chidon_buses')->columns([
    'bus_id'    =>  $generator->pk,
    'bus_code'  =>  $faker->unique()->numberBetween(1000,5000),
    'bus_type'  =>  $faker->randomElement(['coach','yellow','open']),
    'staff_id'  =>  $generator->relation('th_chidon_staff', 'staff_id')
]);

$seeder->table('th_chidon_walking_groups')->columns([
    'group_id'      =>  $generator->pk,
    'description'   =>  $faker->text(30),
    'staff_id'      =>  $generator->relation('th_chidon_staff', 'staff_id')
]);

$seeder->table('th_chidon_locations')->columns([
    'location_id'   =>  $generator->pk,
    'description'   =>  $faker->text(30),
    'staff_id'      =>  $generator->relation('th_chidon_staff', 'staff_id')
]);

$seeder->table('th_chidon_tests')->columns([
    'test_id'       =>  $generator->pk,
    'description'   =>  $faker->text(30),
    'staff_id'      =>  $generator->relation('th_chidon_staff', 'staff_id')
]);

$seeder->table('th_chidon_attendance_times')->columns([
    'att_time_id'   =>  $generator->pk,
    'day_of_week'   =>  $faker->dayOfWeek(),
    'att_time'      =>  '2018-02-28 ' . $faker->time(),
    'att_type'      =>  $faker->randomElement(['trip','morning','night','event']),
    'description'   =>  $faker->text(30)
]);

$seeder->table('th_chidon_attendance_lookup')->columns([
    'att_id'        =>  $generator->pk,
    'att_time_id'   =>  $generator->relation('th_chidon_attendance_times', 'att_time_id'),
    'bus_id'        =>  $generator->relation('th_chidon_buses', 'bus_id'),
    'group_id'      =>  $generator->relation('th_chidon_walking_groups', 'group_id'),
    'location_id'   =>  $generator->relation('th_chidon_locations', 'location_id'),
    'test_id'       =>  $generator->relation('th_chidon_tests', 'test_id'),
    'user_id'       =>  $faker->randomElement(['25587','19050','13387','20265','9188','15447','9931','9932','15425','8633'])
]);

$seeder->refill();