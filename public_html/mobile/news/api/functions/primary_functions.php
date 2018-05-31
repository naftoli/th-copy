<?php
// get and clean post parmaters
function post_param( $param_name ) {
    if ( isset( $_POST[ $param_name ] ) )
        return mysql_real_escape_string( $_POST[ $param_name ] );
    else
        return false;
}
// Standard JSON response functions
function render_json_response( $data ) {
    header('Content-type: application/json');
    echo json_encode([
        "success"   => true,
        "data"      => $data
    ]);
}
function render_json_error( $msg, $data = false ) {
    header('Content-type: application/json');
    echo json_encode([
        "success"   => false,
        "error"     => $msg,
        "details"   => $data
    ]);
    die();
}
// Fetch all results from the DBS and return them in an array
function fetch_results_assoc( $query ) {
    // we want to return false if we have nothing to fetch
    if ( !$query || mysql_num_rows( $query ) == 0 )
        return false;

    $results = [];
    while( $row = mysql_fetch_assoc( $query ) ) {
        $results[] = $row;
    }
    return $results;
}