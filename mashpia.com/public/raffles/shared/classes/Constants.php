<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 186;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 178
        return [
            2 => 3,
            3 => 2,
            4 => 5,
            5 => 3,
            7 => 6,
            9 => 8,
            11 => 1,
            19	=> 6,
            21	=> 1,
            30	=> 2,
            33	=> 2,
            37	=> 1,
            39	=> 0,
            40	=> 1,
            42	=> 5,
            45	=> 3,
            48	=> 1,
            49	=> 3,
            50	=> 3,
            54	=> 13,
            55	=> 0,
            58	=> 3,
            60	=> 1,
            61	=> 4,
            63	=> 1,
            66	=> 3,
            80	=> 1,
            81	=> 3,
            84	=> 1,
            86	=> 2,
            89	=> 3,
            105	=> 1,
            106	=> 4,
            110	=> 7,
            112	=> 4,
            162	=> 4,
            176	=> 2,
            185	=> 3,
            192	=> 3,
            255	=> 19,
            263	=> 1,
            265	=> 1,
            269	=> 8,
            427	=> 1,
            430	=> 2,
            434	=> 1,
            470	=> 2,
            471	=> 3,
            472	=> 1,
            480	=> 3,
            517	=> 0,
            542	=> 1,
            577	=> 1,
            613	=> 0,
            614	=> 2,
            615	=> 3,
            621	=> 1,
            659	=> 2,
            690	=> 1,
            692	=> 1,
            693	=> 3,
            694	=> 2,
            726	=> 3,
            727	=> 0,
            727	=> 0,
            739	=> 1,
            780	=> 0,
            796	=> 1,
            805	=> 2,
            806	=> 1
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
