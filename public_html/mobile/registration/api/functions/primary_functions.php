<?php
// get and clean post parmaters
function post_param( $param_name ) {
    if ( isset( $_POST[ $param_name ] ) )
        return mysql_real_escape_string( $_POST[ $param_name ] );
    else
        return false;
}

function render_json_error( $msg, $data = false ) {
    return json_error( $msg, $data, 200, true );
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