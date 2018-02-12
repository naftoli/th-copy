<? 
require 'db.php';
require 'class.taskExceptions.php';
$e = new TaskExceptions();
if ( $e->isException( 1061560, 8273 ) ) {
	echo "Exception found";
} else {
	echo "No Exception";	
}
?>