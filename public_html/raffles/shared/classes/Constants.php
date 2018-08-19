<?

namespace raffles\shared;

class Constants{
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 100
        return [
            2 => 3,     3 => 2,     4 => 4,     5 => 2,     7 => 4,     9 => 5, 
            11 => 1,    19 => 4,    21 => 1,    30 => 2,    33 => 1,    37 => 1, 
            39 => 1,    40 => 1,    42 => 4,    45 => 2,    48 => 1,    49 => 2, 
            50 => 2,    54 => 7,    55 => 1,    58 => 2,    60 => 1,    61 => 2, 
            63 => 1,    66 => 1,    80 => 1,    81 => 1,    82 => 0,    84 => 1, 
            86 => 1,    87 => 1,    89 => 1,    105 => 1,   106 => 2,   110 => 3, 
            112 => 2,   162 => 2,   176 => 1,   185 => 2,   192 => 2,   194 => 1,
            255 => 7,   263 => 1,   264 => 0,   265 => 1,   269 => 3,   427 => 1, 
            466 => 0,   470 => 1,   471 => 2,   472 => 1,   474 => 0,   475 => 1, 
            480 => 1,   517 => 1
        ];
    }
    
    public static function get_weekly_task_requirment() {
        return 5;
    }
    
    public static function get_monthly_task_requirment() {
        return 20;
    }
    
}
