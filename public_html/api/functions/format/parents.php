<?php

function formatParentName( $father, $mother ) {
    if ( $father && $mother )
        return trim("$father and $mother");
    else if ( $father )
        return trim($father);
    else if ( $mother )
        return trim($mother);
}