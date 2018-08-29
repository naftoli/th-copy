<?php
include_once( dirname(__FILE__) . "/header.php" );

// GET /, return all schools
if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    $schools_query = mysql_query(
        " SELECT school_id as id, school_name as name FROM schools "
       ." WHERE test_school = 0 AND ( chayolei = 1 OR chidon = 1 ) " // make sure we only get chayolei and chidon schools
       ." AND school_era IS NULL " // who are registered for this year
       ." ORDER BY school_name;" // alphabetically
    );

    $schools = fetch_results_assoc( $schools_query );
    if ( $schools )
        render_json_response( $schools );
    else
        render_json_error( "Could not load schools" );
}