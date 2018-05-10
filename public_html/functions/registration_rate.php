<?php
require_once( dirname(__FILE__) . "/../db.php" );
function registration_rate( $user_id ){
    // the core registration information
    $rate                   = 55;       // base registration rate
    $tuition_rate           = 45;       // rate for schools which partially include it in tuition.
    $early_bird_discount    = 5;        // $5 early bird special
    $early_bird_deadline    = 2458011;  // deadline for early bird special
    $extended_deadline      = 2458018;  // extended deadline for some schools

    // SPECIAL CASES //
    // schools with the $extended_deadline
    $extended_schools = [
        2   =>  50, // Beth Rivkah Montreal
        4   =>  45, // Cheder Menachem LA
        9   =>  45, // Lubavitcher Yeshiva, Crown Heights
        21  =>  45, // Cheder Lubavitch Morristown Boys
        37  =>  45, // Cheder Lubavitch Morristown Girls
        162 =>  50, // Bais Chaya Mushka LA
        192 =>  50  // Cheder Chabad of Monsey Girls
    ];
    // schools with year round discounted rates
    $discount_schools = [
        265 =>   0, // Lubavitch Girls London
        61  =>  45, // MyShliach
        9   =>  45  // Lubavitcher Yeshiva CH
    ];

    // get the schools registration type
    $school_info_query = mysql_query(
        "SELECT reg_type, school_id FROM users JOIN schools USING (school_id) WHERE user_id = '$user_id'"
    );
    $school_info = mysql_fetch_assoc($school_info_query);
    $school_id  = $school_info['school_id'];

    // SPECIAL SCHOOLS
    if ( in_array( $discount_schools, $school_id ) ) {
        return $discount_schools[ $school_id ];
    }

    // EARLY BIRD
    if ( unixtojd() < $early_bird_deadline ) {
        if ( $school_info['reg_type'] == 1) { // included in tuition
            return 0;
        } else if ( $school_info['reg_type'] == 2) { // discounted school rate
            return $tuition_rate;
        } else { // school does not provide any deeper discounts
            return $rate - $early_bird_discount;
        }
    // EXTENDED DEADLINE
    } else if ( unixtojd() < $extended_deadline && isset( $extended_schools[$school_id] ) ) {
        return $extended_schools[ $school_id ];
    }
    // return the default rate
    return $rate;
}