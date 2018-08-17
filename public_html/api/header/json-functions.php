<?php
/**
 * json_response
 * 
 * renders the data as json
 *
 * @param any $data
 * @param boolean $success ( true )
 * @param boolean $die ( true )
 * @return void
 */
function json_response( $data, $success = true, $die = true ) {
    header("Content-Type: application/json; charset=utf-8;");
    echo json_encode([
        "success" => $success,
        "data"    => $data
    ], JSON_NUMERIC_CHECK);
    if ( $die ) die();
}

/**
 * json_error
 * 
 * render a quick json error to the client
 * 
 * @param string $msg
 * @param any $data
 * @param integer $code
 * @param boolean $die
 * @return void
 */
function json_error( $msg, $data = false, $code = 400, $die = true ) {
    // set the correct headers
    http_response_code( $code );
    header("Content-Type: application/json; charset=utf-8;");
    // set success to false
    echo json_encode([
        "success" => false,
        "error"    => $msg,
        "message"  => $msg,
        "data"   => $data
    ], JSON_NUMERIC_CHECK);
    if ( $die ) die();
}