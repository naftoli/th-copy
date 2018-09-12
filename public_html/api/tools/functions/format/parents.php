<?php

function formatParentName( $father, $mother, $first = 'Mr. and Mrs.' ) {
    if ( $father && $mother )
        return trim("$father and $mother");
    else if ( $father )
        return trim($father);
    else if ( $mother )
        return trim($mother);
    else return $first;
}