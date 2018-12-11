<?php
include_once( dirname(__FILE__) . "/header.php" );
include_once( dirname(__FILE__) . "/../../../classes/platton.php" );

// GET /, return all schools
if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    // we expect ?school_id to be set to a valid school_id
    $school_id = isset( $_GET['school_id'] ) ? mysql_real_escape_string( $_GET['school_id'] ) : false;
    $class_id  = isset( $_GET['class_id'] )  ? mysql_real_escape_string( $_GET['class_id'] )  : false;
    
    if ( $school_id )
        index( $school_id );
    else if ( $class_id )
        show( $class_id );
    else {
        render_json_error( "Invalid Paramaters" );
    }
}

/**
 * index
 * 
 * PATH: GET /?school_id
 * 
 * Returns to client a list of all classes in a given school
 *
 * @param string $school_id
 * @return void
 */
function index( $school_id ) {
    $classes_query = mysql_query(
        " SELECT class_id as id, class_grade, class_sub FROM classes "
       ." WHERE school_id = $school_id " // limit to school
       ." ORDER BY class_grade, class_sub;" // alphabetically
    );

    $classes = fetch_results_assoc( $classes_query );
    if ( !$classes )
        render_json_error( "Could not load classes" );
    
    foreach( $classes as $index => $class ){
        $classes[$index]['name'] = $class['class_grade'] . ($class['class_sub'] ? " - " . $class['class_sub'] : "");
    }

    return json_response( $classes );
}

/**
 * show
 * 
 * PATH: GET /?class_id
 * 
 * Returns to client details on a given class
 *
 * @param string $class_id
 * @return void
 */
function show( $class_id ){
    try {
        $class = platton::Load( $class_id );
        $class->getUsers();
        return json_response( $class );
    } catch( Exception $e ){
        render_json_error( "Could not load class" );
    }
};