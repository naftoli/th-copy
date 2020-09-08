<?php
include_once( dirname(__FILE__) . "/header.php" );

// GET /, return all schools
if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    $schools_query = mysql_query(
        " SELECT school_id as id, school_name as name FROM schools "
       ." WHERE test_school = 0 AND chidon = 1 " // make sure we only get chidon schools
       ." AND school_era IS NULL " // who are registered for this year
       ." AND school_id != 612 " // remove 'unassigned school'
       ." ORDER BY school_name;" // alphabetically
    );

    $schools = fetch_results_assoc( $schools_query );
    if ( $schools )
        json_response( $schools );
    else
        render_json_error( "Could not load schools" );
}