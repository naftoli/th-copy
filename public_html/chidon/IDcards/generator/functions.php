<?php
// function to calculate the ordinal of a number. source: https://stackoverflow.com/questions/3109978/display-numbers-with-ordinal-suffix-in-php
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}