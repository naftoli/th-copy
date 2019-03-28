<?
require_once '../class.db.php';
$pdo = DB::getInstance();
//get school image
$id = $_POST['id'];
$sql = "select f.file_data, f.file_content_type   
        from files f 
        join schools s on (s.school_logo_id = f.file_id) 
        where s.school_id = " . $id;        
$stmt = $pdo->query( $sql );
if ( $stmt ) {
    $logo = $stmt->fetch();
    if ( $logo ) {
        header("Content-type: " . $logo['file_content_type'] );
        echo "<img src='" . $logo['file_data'] . "' height='100' />";
    }
}
?>