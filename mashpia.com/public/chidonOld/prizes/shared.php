<?php

function save_image($file, $directory, $old_file_path = null){
    global $logger;
    $logger->debug("saving image");
    if (is_uploaded_file($file['tmp_name'])) {
        $logger->debug(__LINE__);
        $file_name = $file['name'];
        $target = "{$_SERVER["DOCUMENT_ROOT"]}$directory/$file_name";
        // make sure file doesn't exist
        $i = 1;
        while (file_exists($target)) {
            $file_name = 'v' . $i++ .".". $file['name'];
            $target = "{$_SERVER["DOCUMENT_ROOT"]}$directory/$file_name"; // move the file to the right place
        }
        
        $logger->debug(__LINE__);
        if(strlen($target) > 200) return false; // varchar is limited to 100 chars
        if (move_uploaded_file($file['tmp_name'], $target)) { // actually move it
            $logger->debug(__LINE__);
            if($old_file_path) {
                $logger->debug(__LINE__);
                unlink("{$_SERVER["DOCUMENT_ROOT"]}$old_file_path");
            }
            $logger->debug(__LINE__);
            return "$directory/$file_name"; // everything went well!
        } // end if file was moved from tmp to disk
    } // end is_uploaded_file
    $logger->debug(__LINE__);
    return false; //something went wrong
}// end add_image
