<?php
/**
 * getZone
 * 
 * Returns Shipping Zone based on array of country and zip
 *
 * @param array $zone_info
 * @return int
 */
function getZone( $country ){
    if ( $country == '' || $country == 'USA' )
        return 1;
    else if ( $country == "Canada" )
        return 2;
    else
        return 3;
}