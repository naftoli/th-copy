<?php // import database
require_once( $_SERVER['DOCUMENT_ROOT'] . '/db.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php' );
// get the current year
$year = GlobalSettings::getChidonYear();
// allow the api to override the year
if ( isset( $_POST['year'] ) ) $year = mysql_real_escape_string( $_POST['year'] );
// get the campaigns for the current year
$campaign_query = mysql_query(
    "SELECT * FROM line_campaigns WHERE year = " . $year
);
while ( $row = mysql_fetch_assoc( $campaign_query ) ) {
	$campaigns[$row['id']] = strtolower( $row['type'] );
}
// calculate the totals
$totals_query = mysql_query(
     " SELECT campaign_id, SUM( num_lines ) AS total "
    ." FROM bp_user_summary "
    ." WHERE campaign_id IN (" . implode( ", ", array_keys( $campaigns ) ) . ") "
    ." AND user_id != 0 "
    ." GROUP BY campaign_id "
);
// setup the return values
$results = [ "success" => true ];
$grand_total = 0;
// add the totals to the returned result
while( $campaign_total = mysql_fetch_assoc( $totals_query ) ) {
    $results['campaigns'][ $campaigns[ $campaign_total['campaign_id'] ] ] = intval( $campaign_total['total'] );
    $grand_total += $campaign_total['total'];
}
// set the grand total
$results['campaigns']['total'] = $grand_total;
// and send the user the results
echo json_encode( $results );