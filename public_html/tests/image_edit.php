<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if(isset($_POST['action'])){
    $file = $_FILES['user_photo'];
    
    $file_name = $file['name'];
    $target = $file_name;
    
    if(exif_read_data($file['tmp_name']) && $_POST['action'] == "fix"){
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = $exif['Orientation'];
        // print the exif data
        //echo "<pre>";
        //print_r( $exif);
        //die();
    } else if(exif_read_data($file['tmp_name']) && $_POST['action'] == "data") {
        $exif = @exif_read_data($file['tmp_name']);
        // $orientation = $exif['Orientation'];
        // print the exif data
        echo "<pre>";
        print_r( $exif);
        die();
    }

    
    if(move_uploaded_file($file['tmp_name'], $target)){
        $image = new Imagick( $target );
        // create a thumbnail
        $image->thumbnailImage( 250, 0); // this flips the image according to the exif data
        // so we need to see if we have exif rotation data
        if($orientation){ // this will only run if orientation is set which can only happen if $_POST['action'] is set to fix
            switch($orientation) {  
                case 3: // upside down
                    $image->rotateimage("#FFF", 180);
                break;
                // these two cases pulled from stackoverflow: https://stackoverflow.com/questions/4266656/how-to-stop-php-imagick-auto-rotating-images-based-on-exif-orientation-data
                case 6: // rotate 90 degrees CW
                    $image->rotateimage("#FFF", 90);
                break;
                case 8: // rotate 90 degrees CCW
                    $image->rotateimage("#FFF", -90);
                break;
            }
        }
        // fix the image
        $image->writeImage( $target );
        $image->destroy();
        // and redirect to it
        header("Location: http://mashpia.com/tests/".$target);
        die();
    }
}

?>
<h1>Fixed</h1>
<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <INPUT type="file" name="user_photo">
    <INPUT type="hidden" name="action" value="fix">
    <INPUT type="submit" value="Save">
</form>
<h1>Broken</h1>
<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <INPUT type="file" name="user_photo">
    <INPUT type="hidden" name="action" value="test">
    <INPUT type="submit" value="Save">
</form>
<h1>Get Data</h1>
<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <INPUT type="file" name="user_photo">
    <INPUT type="hidden" name="action" value="data">
    <INPUT type="submit" value="Save">
</form>
