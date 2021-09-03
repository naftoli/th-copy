<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 125;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 150
        return [
            613 => 1,
            63  =>2,
            21  => 1,
            542	=> 1,
            11	=> 1,
            176	=> 2,
            105	=> 1,
            692	=> 1,
            60	=> 1,
            80	=> 1,
            472	=> 1,
            517	=> 1,
            39	=> 1,
            614	=> 1,
            263	=> 1,
            48	=> 1,
            84	=> 1,
            483	=> 1,
            659	=> 1,
            663	=> 1,
            40	=> 1,
            693	=> 2,
            33	=> 2,
            615	=> 2,
            89	=> 2,
            470	=> 2,
            86	=> 2,
            694	=> 2,
            577	=> 2,
            30	=> 2,
            81	=> 2,
            50	=> 2,
            37	=> 2,
            5	=> 3,
            49	=> 3,
            471	=> 3,
            185	=> 3,
            480	=> 2,
            192	=> 3,
            4	=> 5,
            42	=> 4,
            7	=> 6,
            19	=> 5,
            9	=> 9,
            54	=> 15,
            255	=> 17
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
