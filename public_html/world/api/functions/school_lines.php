<?php
require_once( dirname(__FILE__) . '/../../../class.bpSummary.php' );
require_once( dirname(__FILE__) . "/get_reg_info.php" );
/**
 * school_lines
 * 
 * returns array containing the tanya and mishna totals for each school, 
 * 
 * provide $grade to limit to a single grade
 * 
 * @param array $campaigns
 * @param string $grade
 */
function school_lines( $campaigns, $grade ) {
    $ids = [];
    $info = [];

    // load the 
    if ( $grade ) {
        $query = mysql_query(
            "SELECT c.class_id, c.class_grade, c.class_sub, s.school_name, s.logo FROM classes c
            JOIN schools s USING (school_id)
            WHERE class_era = 0 AND s.school_era is null AND s.tanya = 1
            AND class_grade = '" . $grade . "'
            ORDER BY class_grade, class_sub, school_name"
        );
        while ( $class = mysql_fetch_assoc( $query ) ) {
            $info[] = [
                "id"    => $class['class_id'],
                "name"  => $class['class_grade'] . ( empty( $class['class_sub'] ) ? '' : '-' . $class['class_sub'] ) . " : " . $class['school_name'],
                "logo"  => $class['logo'], 
                "child_count" => 0
            ];
            $ids[] = $class['class_id'];
        }
    } else {
        $query = mysql_query(
            "SELECT school_id, school_name, chayolei, logo, COUNT(*) as classes FROM schools s
            LEFT JOIN classes c USING (school_id) 
            WHERE s.school_era IS NULL AND tanya = 1
            GROUP BY s.school_id
            ORDER BY s.school_name"
        );
        while ( $school = mysql_fetch_assoc( $query ) ) {
            $info[] = [
                "id"    => $school['school_id'],
                "name"  => $school['school_name'],
                "logo"  => $school['logo'],
                "chayolei" => $school['chayolei'],
                "classes" => $school['classes'],
                "child_count" => 0
            ];
            $ids[] = $school['school_id'];
        }
    }

    // arrays for generating the results
    $regInfo = get_reg_info( $ids, !!$grade );
    $lineInfo = [];

    foreach ($campaigns as $id => $campaign) {
        // create the BpSummary that we need.
        $bps = new BpSummary( $id, $grade ? 'class' : 'school' );

        foreach ( $info as &$row ) {
            $id = $row['id'];
            $learned = intval( $bps->getSummary( $id ) );

            if ( !$grade && !$row['chayolei'] ) {
                $child_count = $bps->getChildCount( $id );
            } else {
                $child_count = isset($regInfo[$id]) ? $regInfo[$id] : 0;
            }
            // convert to number
            $child_count = intval( $child_count );

            // make sure we have kids in the class/school
            if ( $child_count == 0 ) continue;
            else $row['child_count'] = $child_count;
            
            $lineInfo[$id][$campaign]['learned'] = $learned;
            $lineInfo[$id][$campaign]['avg'] = $learned > 0 ? floor( $learned / $child_count ) : 0;
        }
    }

    $results = [];

    // format the data for the result
    foreach( $info as $row ) {
        // make sure we have info to send
        if( !isset( $lineInfo[ $row['id'] ] ) ) continue;

        $lines = $lineInfo[ $row['id'] ];
        $results[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'campaigns' => $lines,
            'lastLevel' => isset($row['classes']) && $row['classes'] == 1,
            'logo'  => $row['logo'],
            'child_count' => $row['child_count']
        ];
    }

    return $results;
}