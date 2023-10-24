<?php
// load up the database connection
include_once( __DIR__ . '/public/api/header/db.php' );

// create cron job to delete admin_auth associations that point to missing child
$sql = "delete from admin_auths where auth = 'user' and id not in (select user_id from users)";
echo mysql_query($sql);