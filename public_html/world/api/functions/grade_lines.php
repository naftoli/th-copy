<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.schoolClasses.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.bpSummary.php' );
require_once( dirname(__FILE__) . "/get_reg_info.php" );

function grade_lines( $campaigns, $school_id ){
    $sc = new SchoolClasses( $school_id );
    $grades = $sc->getClasses();
    $ids = [];
    // get all the class ID's into one array
    foreach ($grades as $grade) {
        $ids[] = $grade['class_id'];
    }

    $regInfo = get_reg_info( $ids, true );
    $lineInfo = [];

    // get the info for each campaign
    foreach ($campaigns as $id => $campaign) {
        $bps = new BpSummary( $id, 'class' );

        foreach ( $grades as &$grade ) {
            $learned = intval( $bps->getSummary( $grade['class_id'] ) );

            $child_count = isset( $regInfo[ $grade['class_id'] ] ) ? $regInfo[ $grade['class_id'] ] : 0;
            // cast to a number
            $child_count = intval( $child_count );
            if ( $child_count == 0 ) continue;
            $grade['child_count'] = $child_count;

            $lineInfo[ $grade['class_id'] ][$campaign]['learned'] = $learned;
            $lineInfo[ $grade['class_id'] ][$campaign]['avg'] = $learned > 0 ? floor( $learned / $child_count ) : 0;
        }
    }

    // set and return the results
    $results = [];
    foreach( $grades as $grade ) {
        // make sure we have info to send
        if( !isset( $lineInfo[ $grade['class_id'] ] ) ) continue;

        $lines = $lineInfo[ $grade['class_id'] ];
        $results[] = [
            'id'    => $grade['class_id'],
            'name'  => $grade['class_grade'] . ( empty( $grade['class_sub'] ) ? '' : '-' . $grade['class_sub'] ),
            'child_count' => $grade['child_count'],
            'campaigns' => $lines
        ];
    }

    return $results;
}