<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['action'])){
    // get the post data
    $target = "../".$_POST['target'];
    $degrees = doubleval($_POST['degrees']);
    
    // set up the image
    $image = new Imagick( $target );
    
    // flip the image
    $image->rotateimage("#FFF", $degrees);

    // save the image
    $image->writeImage( $target );
    $image->destroy();
    
    //print_r([$target, $degrees]);
    
    if (isset($_POST['redirect'])) { // if there is a redirect command. send the user there
        header( 'Location: '.$_POST['redirect'] ) ;
    }
}

?>
<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <label>Target:</label>
    <INPUT type="text" name="target" autofocus/>
    <label>Degrees:</label>
    <INPUT type="text" name="degrees" value="<?=$degrees?>"/>
    <INPUT type="hidden" name="action" value="fix"/>
    <INPUT type="submit" value="Run"/>
</form>

<img src="<?= $target?>" />

