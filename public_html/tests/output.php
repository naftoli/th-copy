<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//
function disable_ob() {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);
    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);
    // Clear, and turn off output buffering
    while (ob_get_level() > 0) {
        // Get the curent level
        $level = ob_get_level();
        // End the buffering
        ob_end_clean();
        // If the current level has not changed, abort
        if (ob_get_level() == $level) break;
    }
    // Disable apache output buffering/compression
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
        apache_setenv('dont-vary', '1');
    }
}

disable_ob();

echo "test";

//https://stackoverflow.com/questions/3218895/php-how-to-read-a-file-live-that-is-constantly-being-written-to
$file='/home/mashpia/youfile.txt';
$lastpos = 0;
$i = 0;
while ($i < 5) {
    //usleep(300000); //0.3 s
    sleep(1);
    clearstatcache(false, $file);
    $len = filesize($file);
    if ($len < $lastpos) {
        //file deleted or reset
        $lastpos = $len;
    }
    elseif ($len > $lastpos) {
        $f = fopen($file, "rb");
        if ($f === false)
            die();
        fseek($f, $lastpos);
        while (!feof($f)) {
            $buffer = fread($f, 4096);
            echo $buffer;
            flush();
        }
        $lastpos = ftell($f);
        fclose($f);
    }
    $i++;
}
