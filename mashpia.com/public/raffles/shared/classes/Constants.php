<?

namespace raffles\shared;

class Constants{

    public static function get_num_weekly_prizes() {
        return 150;
    }
    
    public static function get_raffle_school_max_winners() { // array of id => max. Sum of all maxes must equal 150
        return [
            269	=> 6,
            176	=> 1,
            162	=> 2,
            45	=> 3,
            30	=> 2,
            54	=> 14,
            2	=> 2,
            7	=> 6,
            112	=> 2,
            66	=> 1,
            105	=> 1,
            63	=> 2,
            81	=> 3,
            615 => 1,
            613 => 1,
            49  => 3,
            192	=> 2,
            89	=> 2,
            55	=> 1,
            106	=> 4,
            470	=> 1,
            5	=> 2,
            50	=> 1,
            21	=> 1,
            37	=> 1,
            86  => 1,
            4	=> 5,
            60	=> 1,
            33  => 1,
            185 => 3,
            80	=> 1,
            110	=> 4,
            472	=> 1,
            517	=> 1,
            3	=> 2,
            39	=> 1,
            19  => 6,
            42  => 5,
            265	=> 2,
            471	=> 3,
            9	=> 9,
            614 => 1,
            263	=> 1,
            61	=> 4,
            255	=> 15,
            542	=> 1,
            48	=> 1,
            58	=> 3,
            84	=> 1,
            87	=> 1,
            427	=> 1,
            11	=> 1,
            40	=> 1,
            483 => 1,
            659 => 1,
            480 => 3,
            577 => 1,
            643 => 1,
            663 => 1
        ];
    }
    
    public static function get_weekly_task_requirment() {
        return 5;
    }
    
    public static function get_monthly_task_requirment() {
        return 60;
    }
}
