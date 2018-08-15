<pre>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once( __DIR__ .'/index.php' );

print_r([
    MashpiaEmails::passwordChanged( 'mendelh1537@gmail.com', 'Jon Doe' )
]);
?>
</pre>