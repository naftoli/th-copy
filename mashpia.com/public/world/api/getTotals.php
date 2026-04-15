<?php // import database
require_once( $_SERVER['DOCUMENT_ROOT'] . '/db.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php' );
// get the current year
$year = GlobalSettings::getCurrentYear();
// allow the api to override the year
if ( isset( $_POST['year'] ) ) $year = mysql_real_escape_string( $_POST['year'] );

$cache_key = "world:totals:{$year}";
$results = Cache::remember( $cache_key, 120, function () use ( $year ) {
    $campaigns = [];
    $campaign_query = mysql_query(
        "SELECT * FROM line_campaigns WHERE year = " . $year
    );
    while ( $row = mysql_fetch_assoc( $campaign_query ) ) {
    	$campaigns[$row['id']] = strtolower( $row['type'] );
    }

    $results = [ "success" => true ];
    $results['campaigns'] = [];
    if ( empty( $campaigns ) ) {
        $results['campaigns']['total'] = 0;
        return $results;
    }

    $totals_query = mysql_query(
         " SELECT campaign_id, SUM( num_lines ) AS total "
        ." FROM bp_user_summary "
        ." WHERE campaign_id IN (" . implode( ", ", array_keys( $campaigns ) ) . ") "
        ." AND user_id != 0 "
        ." GROUP BY campaign_id "
    );

    $grand_total = 0;
    while( $campaign_total = mysql_fetch_assoc( $totals_query ) ) {
        if ( intval( $campaign_total['total'] ) > 0 ) {
            $results['campaigns'][ $campaigns[ $campaign_total['campaign_id'] ] ] = intval( $campaign_total['total'] );
            $grand_total += $campaign_total['total'];
        }
    }

    $results['campaigns']['total'] = $grand_total;
    return $results;
} );

echo json_encode( $results );