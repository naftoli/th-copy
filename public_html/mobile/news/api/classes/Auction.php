<?php

class Auction {

    public $auction_id;
    public $raffle_id;
    public $name;

    public $run_date;
    public $start_date;
    public $end_date;
    public $date_ran;

    public $type = "auction";

    public $hebrew_dates = [];
    public $winner_info = [];

    /**
     * new Auction( $auction_id )
     * 
     * Loads an auction from the DBS in an interface that mimics a raffle
     *
     * @param int/string $auction_id
     */
    public function __construct( $auction_id ) {
        $auction_id = mysql_real_escape_string( $auction_id );
        $query = mysql_query(
            "SELECT * FROM auctions WHERE auction_id = '$auction_id';"
        );
        $row = mysql_fetch_assoc( $query );

        $this->auction_id = $auction_id;
        $this->raffle_id = 'a' . $auction_id;
        $this->name = $row['auction_name'];
        $this->run_date = new DateTime( '@' . jdtounix( $row['auction_run_date'] ) );
        $this->start_date = $row['auction_points_start_date'];
        $this->end_date = $row['auction_date'];
        $this->date_ran = $this->run_date;
    }

    public function get_winner_info( $school_id = false, $separate_genders = true, $sorting="school", $shipping_info = true ){
        $this->winner_info = [];
        if( $separate_genders ) $this->winner_info = [ "boys" => [], "girls" => [] ];

        $winners_query = mysql_query(
            "SELECT first, last, prize_id, prize_name, prize_image_id, school_name, u.school_id, "
            ."user_id, hachayol_name, class_grade, class_sub, u.gender FROM auction_winners "
            ."JOIN users u USING (user_id) JOIN prizes_auction USING (prize_id) "
            ."JOIN schools s ON u.school_id = s.school_id JOIN classes USING (class_id) "
            ."WHERE auction_id = '" . $this->auction_id . "'"
        );

        while ( $row = mysql_fetch_assoc( $winners_query ) ){
            $rank_sql = "SELECT rank_name FROM ranks WHERE rank_ord = (SELECT max(rank_ord) FROM rank_marks WHERE user_id = " . $row['user_id'] . ")";
            $rank_result = mysql_query($rank_sql);
            $rank_row = mysql_fetch_assoc($rank_result);

            $data = [
                'rank'          => $rank_row['rank_name'],
                'name'          => $row['first'] . ' ' . $row['last'],
                'first_name'    => $row['first'],
                'last_name'     => $row['last'],
                'prize_id'      => $row['prize_id'], 
                'prize_name'    => $row['prize_name'],
                'prize_picture' => [
                    "full" => "/file_view.php?id=".$row['prize_image_id'], 
                    "thumb" => "/file_view.php?id=".$row['prize_image_id']
                ],
                'school'        => $row['school_name'],
                'school_id'     => $row['school_id'],
                'user_id'       => $row['user_id'],
                'hachayol_name' => $row['hachayol_name'], 
                'grade' => $row['class_grade'] . ($row['class_sub'] ? " - " .$row['class_sub'] : ""),
            ];
    
            if( $separate_genders && $row['gender'] == "M" ){
                $this->winner_info["boys"][] = $data;
            } elseif($separate_genders && $row['gender'] == "F"){
                $this->winner_info["girls"][] = $data;
            } else {
                $this->winner_info[] = $data; // just add it to the array
            }
        }

        return $this->winner_info; // return the array
    }

    /**
     * $auction->get_hebrew_dates
     *
     * sets the hebrew_dates array
     * 
     * @return array
     */
    public function get_hebrew_dates(){
        $auction_from = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->start_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
        $auction_from = $auction_from[0] . ' ' . $auction_from[1];

        $auction_to = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->end_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
        $auction_to = $auction_to[0] . ' ' . $auction_to[1];

        return $this->hebrew_dates = [
            "from"  => $auction_from,
            "to"    => $auction_to
        ];
    }
}