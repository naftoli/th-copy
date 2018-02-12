<?
//create user for blog if doesn't exist
$uname = $_GET['user'];
$pass = $_GET['pass'];

require_once 'blog/wp-load.php';
require_once 'blog/wp-includes/plugin.php';
require_once 'blog/wp-includes/user.php';
if (!username_exists($uname)) 
    wp_create_user($uname, $pass);
?>