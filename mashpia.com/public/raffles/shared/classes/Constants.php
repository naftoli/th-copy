<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 175;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 175
        return [
            427	=> 1,
            613	=> 1,
            63	=> 1,
            265	=> 2,
            55	=> 1,
            66	=> 2,
            112	=> 3,
            45	=> 3,
            106	=> 4,
            690	=> 1,
            643	=> 1,
            61	=> 4,
            269	=> 7,
            637	=> 0,
            434	=> 0,
            13	=> 0,
            585	=> 0,
            21	=> 1,
            542	=> 1,
            11	=> 1,
            176	=> 1,
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
            87	=> 1,
            483	=> 0,
            659	=> 1,
            663	=> 1,
            40	=> 1,
            693	=> 3,
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
            480	=> 3,
            162	=> 3,
            2	=> 3,
            192	=> 3,
            58	=> 4,
            4	=> 5,
            110	=> 5,
            42	=> 5,
            7	=> 6,
            19	=> 5,
            9	=> 9,
            54	=> 15,
            255	=> 17,
            430	=> 1,
            621	=> 1,
            3   => 2
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
