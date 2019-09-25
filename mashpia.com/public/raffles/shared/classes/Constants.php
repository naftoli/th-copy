<?

namespace raffles\shared;

class Constants{
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 100
        return [
            269	=> 6,
            176	=> 1,
            162	=> 3,
            45	=> 3,
            30	=> 2,
            54	=> 13,
            2	=> 2,
            7	=> 6,
            475	=> 1,
            112	=> 2,
            66	=> 1,
            105	=> 1,
            63	=> 1,
            81	=> 3,
            49	=> 3,
            192	=> 2,
            89	=> 2,
            55	=> 1,
            106	=> 4,
            470	=> 1,
            5	=> 2,
            50	=> 1,
            21	=> 1,
            37	=> 1,
            4	=> 5,
            60	=> 1,
            86	=> 1,
            33	=> 1,
            185	=> 4,
            80	=> 1,
            110	=> 4,
            472	=> 1,
            194	=> 1,
            517	=> 1,
            3	=> 2,
            39	=> 1,
            480	=> 3,
            19	=> 6,
            42	=> 5,
            265	=> 2,
            471	=> 2,
            9	=> 9,
            263	=> 1,
            61	=> 4,
            255	=> 14,
            542	=> 1,
            48	=> 1,
            58	=> 3,
            84	=> 1,
            87	=> 1,
            427	=> 1,
            11	=> 1,
            40	=> 1,
            554	=> 1,
            474	=> 0 
        ];
    }
    
    public static function get_weekly_task_requirment() {
        return 5;
    }
    
    public static function get_monthly_task_requirment() {
        return 20;
    }
    
}
