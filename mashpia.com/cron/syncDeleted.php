<?php
// load up the database connection
include_once( __DIR__ . '../public/api/header/db.php' );

// create cron job to delete admin_auths associations that point to missing child
$sql = "delete aa.* from admin_auths aa 
        left join users u on u.user_id = aa.id 
        where aa.auth = 'user' 
        and u.user_id is null";
echo mysql_query($sql);