<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 185;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 178
        return [
            518 => 1, 
            434 => 1, 
            21 => 1, 
            63 => 1, 
            796 => 1, 
            621 => 1, 
            192 => 1, 
            265 => 1, 
            40 => 1, 
            542 => 1, 
            738 => 1, 
            427 => 1, 
            80 => 1, 
            806 => 1, 
            690 => 1, 
            105 => 1, 
            692 => 1, 
            60 => 1, 
            472 => 1, 
            517 => 1, 
            614 => 1, 
            263 => 1, 
            48 => 1, 
            84 => 1, 
            49 => 2, 
            86 => 2, 
            33 => 2, 
            3 => 2, 
            11 => 2, 
            45 => 2, 
            30 => 2, 
            66 => 2, 
            37 => 2, 
            805 => 2, 
            176 => 2, 
            430 => 2, 
            470 => 2, 
            694 => 2, 
            577 => 2, 
            112 => 3, 
            693 => 3, 
            5 => 3, 
            185 => 3, 
            471 => 3, 
            162 => 3, 
            2 => 3, 
            81 => 3, 
            50 => 3, 
            726 => 3, 
            89 => 3, 
            480 => 3, 
            106 => 4, 
            58 => 4, 
            659 => 4, 
            61 => 4, 
            615 => 4, 
            4 => 5, 
            19 => 6, 
            7 => 6, 
            42 => 6, 
            269 => 7, 
            110 => 7, 
            9 => 8, 
            54 => 12, 
            255 => 19
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
