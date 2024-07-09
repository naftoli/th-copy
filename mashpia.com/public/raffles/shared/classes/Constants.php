<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 183;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 178
        return [
            2 => 3,
            3 => 2,
            4 => 5,
            5 => 3,
            7 => 6,
            9 => 7,
            11 => 2,
            19 => 5,
            21 => 2,
            30 => 2,
            33 => 2,
            37 => 2,
            40 => 2,
            42 => 5,
            45 => 3,
            48 => 1,
            49 => 3,
            50 => 3,
            54 => 11,
            58 => 3,
            60 => 1,
            61 => 5,
            63 => 1,
            66 => 3,
            80 => 1,
            81 => 3,
            84 => 2,
            86 => 2,
            805 => 2,
            89 => 3,
            105 => 1,
            106 => 4,
            110 => 6,
            112 => 3,
            162 => 3,
            176 => 1,
            185 => 3,
            192 => 2,
            255 => 17,
            263 => 2,
            265 => 1,
            269 => 8,
            427 => 1,
            430 => 2,
            434 => 1,
            470 => 2,
            471 => 3,
            472 => 1,
            480 => 3,
            542 => 1,
            577 => 2,
            614 => 2,
            615 => 4,
            621 => 1,
            690 => 1,
            659 => 3,
            692 => 1,
            693 => 3,
            694 => 2,
            726 => 2,
            739 => 1,
            806 => 1
        ];
    }
    
    public static function get_weekly_task_requirment() {
        return 5;
    }
    
    public static function get_monthly_task_requirment() {
        return 60;
    }
    
    public static function get_yearly_task_requirment() {
        return 180;
    }
    
    public static function get_task_requirment($frequency) {
        if ($frequency === "weekly" ) return self::get_weekly_task_requirment();
        if ($frequency === "monthly") return self::get_monthly_task_requirment();
        if ($frequency === "yearly" ) return self::get_yearly_task_requirment();
        return false;
    }
}
