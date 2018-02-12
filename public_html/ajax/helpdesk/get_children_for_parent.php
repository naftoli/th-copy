<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/db.php'); // connect to the database

// get the username and password
$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);

// make sure a username and password where submitted
if(!$username || !$password){
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Paramaters"
    ]);
    die(); // end the script
}

// get the admin_id
$admin_query = mysql_query("SELECT admin_id FROM admins WHERE username = '$username' AND password = '$password' LIMIT 1");

// if the username and password are invalid
if (mysql_num_rows($admin_query) !== 1){
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Credentials"
    ]);
    die(); // end the script
}
// load the admin_id
$admin_id = mysql_fetch_assoc($admin_query)['admin_id'];

// get all the admins children
$child_query = mysql_query("SELECT first, last FROM users u JOIN admin_auths aa ON aa.id = u.user_id AND aa.auth = 'user' WHERE aa.admin_id = '$admin_id'");

// return the following error if there are no children
if(mysql_num_rows($child_query) === 0) {
    echo json_encode([
        "success"   => false,
        "error"     => "No children found"
    ]);
    die(); // end the script
}

$chidren = []; // array to hold children
while($child = mysql_fetch_assoc($child_query)){
    $children[] = $child;
}

echo json_encode([
    "success"   => true,
    "children"  => $children
]);
die(); // make sure the script ends
?>