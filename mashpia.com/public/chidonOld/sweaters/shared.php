<?php

function save_image($file, $directory, $old_file_path = null){
    if (is_uploaded_file($file['tmp_name'])) {
        $file_name = $file['name'];
        // trim path (after document root) to 100 chars
        $target = $_SERVER["DOCUMENT_ROOT"].substr($directory.'/'.$file_name, 0, 100);
        // make sure file doesn't exist
        $i = 1;
        while (file_exists($target)) {
            $file_name = 'v' . $i++ .".". $file['name'];
            $target = $_SERVER["DOCUMENT_ROOT"].substr($directory.'/'.$file_name, 0, 100); // move the file to the right place
        }
        if (!is_dir($_SERVER["DOCUMENT_ROOT"] . $directory)) {
            throw new Exception("missing folder ".$_SERVER["DOCUMENT_ROOT"] . $directory);
            return false;
        }
        if (move_uploaded_file($file['tmp_name'], $target)) { // actually move it
            if($old_file_path) {
                unlink($_SERVER["DOCUMENT_ROOT"].$old_file_path);
            }
            return "$directory/$file_name"; // everything went well!
        } // end if file was moved from tmp to disk
    } // end is_uploaded_file
}// end save_image
