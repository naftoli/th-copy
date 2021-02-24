<?php

function save_image($file, $directory, $old_file_path = null){
    if (is_uploaded_file($file['tmp_name'])) {
        $file_name = $file['name'];
        $target = "{$_SERVER["DOCUMENT_ROOT"]}$directory/$file_name";
        // make sure file doesn't exist
        $i = 1;
        while (file_exists($target)) {
            $file_name = 'v' . $i++ .".". $file['name'];
            $target = "{$_SERVER["DOCUMENT_ROOT"]}$directory/$file_name"; // move the file to the right place
        }
        if(strlen($target) > 200) return false;
        if (move_uploaded_file($file['tmp_name'], $target)) { // actually move it
            if($old_file_path) {
                unlink("{$_SERVER["DOCUMENT_ROOT"]}$old_file_path");
            }
            return "$directory/$file_name"; // everything went well!
        } // end if file was moved from tmp to disk
    } // end is_uploaded_file
    return false; //something went wrong
}// end add_image
