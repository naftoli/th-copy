<?php
include_once( dirname(__FILE__) . "/header.php" );

// GET /, return all schools
if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    $schools_query = mysql_query(
        " SELECT school_id as id, school_name as name FROM schools "
       ." WHERE chayolei = 1 AND test_school = 0 " // make sure we only get chayolei schools
       ." AND school_era IS NULL " // who are registered for this year
       ." ORDER BY school_name;" // alphabetically
    );

    $schools = fetch_results_assoc( $schools_query );
    if ( $schools )
        render_json_response( $schools );
    else
        render_json_error( "Could not load schools" );
}