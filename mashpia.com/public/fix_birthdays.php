<?php
require 'db.php';
require 'class.birthday.php';
require 'class.birthdayYi.php';
require 'class.heDob.php';

// $users = [];
// $sql = "select user_id from users where user_registered > 0";
// $result = mysql_query( $sql );
// while ($row = mysql_fetch_assoc( $result )) {
//     $users[] = $row['user_id'];
// }
$users = [ 20945 ];

foreach ($users as $user_id) {
    $b = new Birthday( $user_id );
    $b->enablePrevious();
    $b->setBirthday();
    $errors = $b->getErrors();
    if ( $errors ) {
        foreach ($errors as $info) {
            foreach ($info as $error) {
                echo $error . "<br />";
            }
        }
        echo "<br />";
    }
    $bi = new BirthdayYi( $user_id );
    $bi->enablePrevious();
    $bi->setBirthday();
    $errors = $bi->getErrors();
    if ( $errors ) {
        foreach ($errors as $info) {
            foreach ($info as $error) {
                echo $error . "<br />";
            }
        }
        echo "<br />";
    }
    
    //set dob for syncing with wp
    $hdob = new HeDob( $user_id );
    $hdob->setHeDob();
}
echo "done";