<?
$admin_auth = array('school','user'); 
require('header.php');

//update user's medal to White
require_once 'classes/medal_updater.php';
$m = new medal_updater;
for ($i = 2; $i < 5; $i++) {
    $m->update_medal_two($i);
}
?>