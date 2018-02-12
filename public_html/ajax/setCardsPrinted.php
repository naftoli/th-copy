<?
require_once '../db.php';
$users = $_POST['users'];
$users = explode( ',', $users[0] );
$admin_id = $_POST['admin_id'];
$type = $_POST['type'];

foreach ( $users as $user ) {
    $sql = "insert into ID_cards 
            set user_id = " . $user . ", 
            printed = now(), 
            admin_id = " . $admin_id . ", 
            type = '" . $type . "'";
    @mysql_query( $sql );
}
?>