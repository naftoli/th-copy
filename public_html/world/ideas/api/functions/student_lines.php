<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.bpSummary.php' );

function student_lines( $campaigns, $class_id ){
    // load the users for the classs
    $users = [];
    $result = mysql_query( 
         " SELECT user_id, first, last from users "
        ." WHERE class_id = " . $class_id 
        ." AND ( user_registered > 0 or yan = 1 )"
    );
    while ($row = mysql_fetch_assoc( $result )) {
        $users[] = [
            'id' => $row['user_id'],
            'name' => $row[ 'first' ] . ' ' . $row[ 'last' ]
        ];
    }
    // load the info in the campaigns for each student
    $lineInfo = [];
    foreach ($campaigns as $id => $campaign) {
        $bps = new BpSummary( $id, 'user' );
        foreach ( $users as $user ) {
            $learned = intval( $bps->getSummary( $user['id'] ) );
            $lineInfo[ $user['id'] ][$campaign]['learned'] = $learned;
        }
    }

    $results = [];
    foreach( $users as $user ) {
        // make sure we have info to send
        if( !isset( $lineInfo[ $user['id'] ] ) ) continue;

        $lines = $lineInfo[ $user['id'] ];
        $results[] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'campaigns' => $lines
        ];
    }

    return $results;
}