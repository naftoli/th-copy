<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 175;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 178
        return [
            2   =>  3,
            3   =>  2,
            4   =>  5,
            5   =>  3,
            7   =>  6,
            9   =>  8,
            11  =>  1,
            19  =>  5,
            21  =>  2,
            30  =>  2,
            33  =>  2,
            37  =>  1,
            39  =>  1,
            40  =>  1,
            42  =>  5,
            45  =>  3,
            48  =>  1,
            49  =>  3,
            50  =>  2,
            54  =>  13,
            55  =>  1,
            58  =>  4,
            60  =>  1,
            61  =>  4,
            63  =>  1,
            66  =>  3,
            80  =>  1,
            81  =>  2,
            84  =>  2,
            86  =>  2,
            89  =>  2,
            105 =>  1,
            106 =>  4,
            110 =>  7,
            112 =>  4,
            162 =>  4,
            176 =>  1,
            185 =>  2,
            192 =>  3,
            255 =>  18,
            263 =>  1,
            265 =>  1,
            269 =>  7,
            427 =>  1,
            434 =>  1,
            470 =>  2,
            471 =>  3,
            472 =>  1,
            480 =>  3,
            517 =>  1,
            542 =>  1,
            577 =>  1,
            613 =>  1,
            614 =>  1,
            615 =>  2,
            621 =>  1,
            659 =>  2,
            690 =>  1,
            692 =>  1,
            693 =>  2,
            694 =>  2,
            726 =>  2,
            727 =>  1
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
