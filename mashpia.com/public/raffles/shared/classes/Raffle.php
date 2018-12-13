<?php

namespace raffles\weekly;

require_once( __DIR__ . '/Constants.php' );
require_once( __DIR__ . '/DBAdapter.php' );

use \DateTime;
use \DBAdapter;
use raffles\shared\Constants as Constants; // was created later and has correct namespace

class Raffle {
    //***************************
    // variables
    // **************************
    
    // columns in the database
    public $raffle_id; // the id for the collumn in the database
    public $name; // the name of the raffle (up to 40 chars)
    public $run_date; // the date it should run
    public $start_date; // the date it should start
    public $end_date; // the date it should end
    public $type; // the type of raffle (weekly, monthly, or yearly)
    public $date_created; // the date it was created
    public $date_ran; // the date it actually ran
    public $show_on_mobile; // if it should be shown on the mobile website
    public $year; // if it should be shown on the mobile website
    
    // not in DBS
    public $prizes = []; // by default the prizes is an empty array
    public $prize_count = 0; // by default there are no prizes
    public $eligable_user_ids = [];
    public $raffle_number = false;
    // private variables
    private $db_conn; // database connection object
    
    public $hebrew_dates = [];
    
    // ************************
    // constructors
    // ************************
    
    public function __construct($db_conn = false){
        if(!$db_conn) $db_conn = new DBAdapter(); // create a new DB adapter if one is not provided
        $this->db_conn = $db_conn; // set the db_connection
    }
    
    /*
     * Raffle::load($raffle_id)
     *
     * $raffle_id => the raffle_id in the database
     *
     * Loads a raffle from the database and returns an instance of the Raffle object
     */
    public static function load($raffle_id, $db_conn = false){
        
        if(!$raffle_id) return false; // return false if there is no ID
        
        if(!$db_conn) $db_conn = new DBAdapter(); // create a new DB adapter if one is not provided
        
        //$raffle_id = mysql_real_escape_string($raffle_id); <= this line makes testing almost impossible
        
        $sql = "SELECT * FROM raffles WHERE raffle_id = $raffle_id LIMIT 1"; // there should only be one object with this pk
        $query = $db_conn->query($sql);
        
        if ($query && $query->num_rows($query) > 0){
            $row = $query->fetch_assoc();
            return Raffle::loadFromRow($row, $db_conn); // create the instance and return it
        } else {
            //echo '<br/>MySQL error: ('.mysql_errno().') '.mysql_error();
            return false;
        }
    }
    
    /*
     * Raffle::loadFromRow($row[, $db_conn])
     *
     * $row => the row in the database
     * $db_conn => DBAdapter database conneciton (Optional)
     *
     * Takes a row from the database and returns an instance of a Raffle object
     */
    public static function loadFromRow($row, $db_conn = false){
        $instance = new self($db_conn); // create a new instance (pass the database connection if provided in above funciton)
        
        foreach($row as $prop => $val){
            // convert the times to time values
            if($prop === "run_date" || $prop === "date_created" || $prop === "date_ran" && $val) {
                $val = new DateTime($val); // set it to a datetime object for cool features
            }
            $instance->{$prop} = $val; // set the property on the instance
        }
        return $instance;
    }
    
    /*
     * Raffle::loadAll($filter[, $db_conn])
     *
     * $filter => SQL filter string
     * $db_conn => DBAdapter database conneciton (Optional)
     *
     * retruns all the raffles within that filter
     */
    public static function loadAll($filter="", $db_conn = false){
        if(!$db_conn) $db_conn = new DBAdapter(); // create a new DB adapter if one is not provided
        //$filter = mysql_real_escape_string($filter); // protect against SQL injection
        $raffles = []; // the array to return
        
        $sql = "SELECT * FROM raffles $filter;"; // include the user filter in the query
        $query = $db_conn->query($sql);
        
        if ( !$query ) {
            return $raffles;
        }
        
        while($row = $query->fetch_assoc()){
            $raffles[] = Raffle::loadFromRow($row); // generate the raffe instance and add it to the array
        }
        return $raffles; // return the array
    }
    
    /*
     * Raffle::create($props_array)
     *
     * $props_array => associative array of the values used to create the object
     *
     * Loads a raffle from the database and returns an instance of the Raffle object
     */
    public static function create($props_array){
        $instance = new self(); // the instance used to store the data and return it to the user if successful
        // some variables to make the insert command simpler
        $insert_column_keys = []; // the keys used for the insert statement
        $insert_column_values = []; // the values that will be inserted
        
        $props_array = array_intersect_key($props_array, get_object_vars($instance) ); // remove any keys that are not propertys of this object
        
        foreach($props_array as $prop => $value){
            // format times correctly
            if ($value instanceof DateTime){ // if a DateTime object was passed in
                $value = $value->format("Y-m-d H:i:s"); // formatted to YYYY-MM-DD HH:MM:SS for mysql database
            }
            // set the instance property to the value. Good place to throw errors on bad input
            array_push($insert_column_keys, mysql_real_escape_string($prop));
            array_push($insert_column_values, "'".mysql_real_escape_string($value)."'");
            // set the prop to the value that was passed in
            $instance->{$prop} = $value;
        }
        
        // compute the keys to insert
        $sql = "INSERT INTO raffles (". implode(", ", $insert_column_keys) .") VALUES (".implode(", ", $insert_column_values).");"; // generate the sql
        $query  = mysql_query($sql); // returns false if an error happens
        
        if($query){ // if it was inserted
            $instance->raffle_id = mysql_insert_id(); // set the ID
            return $instance; // and return the instance
        } else { // the insert query failed
            //echo 'MySQL error: ('.mysql_errno().') '.mysql_error(); // print the mysql error for debugging (sadly $debug seems to not be set in this context)
            return false; // return false
        }
    }
    
    // ************************
    // functions
    // ************************
    
    // persist the instance into the database
    public function update(){
        // generate the mysql
        $sql = "UPDATE raffles SET name='".$this->name."', run_date='".$this->run_date->format("Y-m-d H:i:s").
                "', start_date='".$this->start_date."', end_date='".$this->end_date."', type='".$this->type.
                "', show_on_mobile='".$this->show_on_mobile."'";
        if($this->date_ran instanceof DateTime){ // if there is a datetime add it to the array
            $sql .= ", date_ran='".$this->date_ran->format("Y-m-d H:i:s")."'";
        }
        $sql .= " WHERE raffle_id=".$this->raffle_id;
        // run the query
        $query = mysql_query($sql);
        
        return ($query ? true : false);
    }
    
    // remove the current object from the database
    public function destroy() {
        $sql = "DELETE FROM raffles WHERE raffle_id=".$this->raffle_id.";";
        // run the query
        $query = mysql_query($sql);
        // and return the result
        return ($query ? true : false);
    }
    
    /*
     * get_raffle_eligable_user_ids()
     *
     * returns an array with all the eligible user id's as both the keys and the values
     *
     * THIS ONLY CHECKS THE raffle_eligibility TABLE. IT DOES NOT LOOK FARTHER
     */
    public function get_raffle_eligable_user_ids($user_id=false){
        $this->raffle_eligable_user_ids = [];
        // first add the users that where marked as eligibile (note, in the final run users that have won prizes will not be marked as eligible)
        $sql = "SELECT u.user_id, u.school_id, aa.admin_id FROM raffle_eligibility re JOIN users u USING (user_id) ".
                "LEFT JOIN admin_auths aa ON u.user_id = aa.id WHERE re.raffle_id = ".$this->raffle_id." AND re.eligible = 1 AND (aa.auth = 'user' OR aa.auth IS NULL) ";
        if($user_id){
           $sql .= "AND u.user_id=$user_id ";
        }
        $sql .= "GROUP BY u.user_id;";
        
        $query = mysql_query($sql);
        while($row = mysql_fetch_assoc($query)){ // for each row
             $this->raffle_eligable_user_ids[$row['user_id']] = ["user_id" => $row['user_id'], "school_id" => $row['school_id'], "admin_id" => $row['admin_id']];   // use the user_id as the key for quick detection ( for example if($this->eligable_user_ids[$user_id]) )
        }
        return $this->raffle_eligable_user_ids;
    }
    
    /*
     * get_eligable_user_ids()
     *
     * returns an array with all the eligible user id's as both the keys and the values
     *
     * TODO: check even if the user is not in the table based off of doc
     */
    public function get_eligable_user_ids($user_id=false, $log=false, $group_by_school=false, $report = false ){
        $year = $this->year;
        $this->eligable_user_ids = [];
        // first add the users that where marked as eligibile - excluding users that have won in the past. should we bring them in if they have been marked?
        $sql = "SELECT u.user_id, u.school_id, aa.admin_id ".
            " FROM raffle_eligibility re".
            " JOIN users u USING (user_id) ".
            " LEFT JOIN admin_auths aa ON u.user_id = aa.id and aa.auth='user' ".
            " WHERE re.raffle_id = ".$this->raffle_id." ".
            " AND re.eligible = 1 ";
        // on non-reports we need to hide the people who already won
        if ( !$report )
            $sql .= " AND u.user_id NOT IN (SELECT user_id FROM raffle_winners JOIN raffles USING (raffle_id) WHERE year = $year )";
        // add the sorting by the user_id
        if($user_id) $sql .= "AND u.user_id=$user_id ";
        $sql .= "GROUP BY u.user_id;";
        // print out some logs
        if($log) echo "Getting users manually marked as eligible\n";
        // run the query
        $query = mysql_query($sql);
        if($log) $user_count = 0; // count the total number of marked users if it needs to be logged
        
        while($row = mysql_fetch_assoc($query)){ // for each row
            $this->append_to_user_ids($row['user_id'], ["user_id" => $row['user_id'], "school_id" => $row['school_id'], "admin_id" => $row['admin_id']], $group_by_school ? $row['school_id'] : false);
            if($log) $user_count++;
            //$this->eligable_user_ids[$row['user_id']] = ["user_id" => $row['user_id'], "school_id" => $row['school_id'], "admin_id" => $row['admin_id']];   // use the user_id as the key for quick detection ( for example if($this->eligable_user_ids[$user_id]) )
        }
        // print out some logs
        if($log) echo "Got $user_count manually marked eligibile users\n";
        if($log) echo "Loading remaining users...\n";
        // get all users not marked in raffle_eligibility and who are registerd
        $sql = "SELECT u.user_id, u.school_id, aa.admin_id FROM users u LEFT JOIN admin_auths aa ON aa.auth = 'user' AND u.user_id = aa.id ".
            " LEFT JOIN admins a USING ( admin_id ) ".
            " WHERE u.user_registered IS NOT NULL AND u.school_id IS NOT NULL ";
        // on reports we need to hide the soldiers who already won
        if ( !$report )
            $sql .= " AND u.user_id NOT IN (SELECT user_id FROM raffle_winners JOIN raffles USING (raffle_id) WHERE year = $year )";
        // if a user is passed in, then limit it to this user
        if( $user_id ) $sql .= " AND u.user_id=$user_id";
        // sort by the user_id
        $sql .= " GROUP BY u.user_id ORDER BY u.user_id;"; // LIMIT 250 only for testing
        $query = mysql_query($sql); // run the query
        // log the total users that we have to manually check
        if($log) echo "Checking ".mysql_num_rows($query)." remaining users";
        if($log) $log_count = 0;
        
        // for each of the users not marked in the table
        while ($row = mysql_fetch_assoc($query)){
            if($log && ++$log_count == 50){echo "."; $log_count = 0;} // log a . for every 50 users checked
            $user_id = $row['user_id']; // get the user id
            // select the total number of days that the user was marked between the start date and end date for daily tasks with the following querry (transalated to SQL)
            // get the total number of rows that are returned from the following query as `total`
            // get the mark date from date_tasks_marks where the user_track and the mark have the users user id, the task is a daily task, and the mark date is within the raffles boundries
            //      join all the dates together to avoid duplicates (the group by at the end)
            $daily_sql = 'select count(*) as total from (select dtmarks.mark_date from user_tracks ut'.
                        ' join date_tasks_missions dtm on ut.level = dtm.level and ut.track_id = dtm.track_id and ut.subject_id = dtm.subject_id'.
                        ' join date_tasks dt using (date_tasks_mission_id) join date_tasks_marks dtmarks using (date_task_id)'.
                        ' where dtmarks.user_id = '.$user_id.' and ut.user_id = '.$user_id. ' and dt.daily_task = 1'.
                        ' and dtmarks.mark_date >= '. $this->start_date .' and dtmarks.mark_date <= ' . $this->end_date .
                        ' group by dtmarks.mark_date) AS SQ';
            //if($log) echo $daily_sql."\n"; // if you want to debug...
            $daily_query = mysql_query($daily_sql); // run the query
            $daily_row = mysql_fetch_assoc($daily_query); // get the row
            $total = $daily_row['total']; // get the value in the defined total field
            // log the user id and the total
            //if($log) echo "user_id: $user_id\t total daily marks: $total\t";
            
            if($this->type == 'weekly'){ // if this is a weekly raffle: do the weekly checks
                // give the user a chance
                if($total == Constants::get_weekly_task_requirment() - 1){ // if it is only (4) we can check for some marks that are not tied to any specific dates
                    // get a total count of all the non daily missions marked between the start and end dates of this raffle
                    $update_total_sql = "SELECT COUNT(*) AS `total` FROM date_tasks dt JOIN date_tasks_marks dtmarks USING (date_task_id) WHERE dtmarks.user_id = $user_id".
                            " AND dtmarks.mark_date >= ". $this->start_date ." AND dtmarks.mark_date <= ". $this->end_date .
                            " AND daily_task = 0 AND ((dt.quantity IS NOT NULL AND dtmarks.done_qty >= dt.quantity) OR dt.quantity IS NULL)";
                    $update_total_query = mysql_query($update_total_sql);
                    $update_total_row = mysql_fetch_assoc($update_total_query);
                    // if the user did at least one task then add him to the list (as it brings his total from 4 to 5)
                    if($update_total_row['total'] > 0) {
                        $total = 5; // set the total to 5
                    }
                }
                // check if they are eligible
                if($total >= Constants::get_weekly_task_requirment())
                    $this->append_to_user_ids($user_id, ["user_id" => $user_id, "school_id" => $row['school_id'], "admin_id" => $row['admin_id']], $group_by_school ? $row['school_id'] : false);
                    //$this->eligable_user_ids[$user_id] = ["user_id" => $user_id, "school_id" => $row['school_id'], "admin_id" => $row['admin_id']];

                // log uneligible if it is less then 5
                //if($log && $total >= 5) echo "eligible\n";
                //if($log && $total < 5) echo "uneligible\n";
                
            } else if ($this->type == 'monthly'){ // if it is monthly, do the monthly checks.
                if($total >= Constants::get_monthly_task_requirment()){ // we need 20 days
                    $this->append_to_user_ids($user_id, ["user_id" => $user_id, "school_id" => $row['school_id'], "admin_id" => $row['admin_id']], $group_by_school ? $row['school_id'] : false);
                    //$this->eligable_user_ids[$user_id] = ["user_id" => $user_id, "school_id" => $row['school_id'], "admin_id" => $row['admin_id']];
                } else if($total >= Constants::get_monthly_task_requirment() - 4){ // if it is between 20 and 16 we can check each week to see if we get 20.
                    $start_date = $this->start_date; // default to this start date
                    $end_date = $this->start_date + 6; // get the end date for the first week
                    // TODO, iterate and check for additional marks...
                    $i = 0; // set $i to 0
                    while($total < 20 && $i < 4){ // while we are still below 20 and have not checked all 4 weeks yet.
                        $update_total_sql = "SELECT COUNT(*) AS `total` FROM date_tasks dt JOIN date_tasks_marks dtmarks USING (date_task_id) WHERE dtmarks.user_id = $user_id
                            AND dtmarks.mark_date >= $start_date AND dtmarks.mark_date <= $end_date AND daily_task = 0
                            AND ((dt.quantity IS NOT NULL AND dtmarks.done_qty >= dt.quantity) OR dt.quantity IS NULL)";
                        $update_total_query = mysql_query($update_total_sql);
                        $update_total_row = mysql_fetch_assoc($update_total_query);
                        // cleanup
                        $i++; // increment $i
                        $start_date = $end_date + 1; // go to the next day for the next start date
                        $end_date = $start_date + 6; // get the end date for the first week
                        
                        if($update_total_row['total'] > 0) $total++; // if the total from the query is greater then 0, add one more "day"
                        if($total == Constants::get_monthly_task_requirment()) {
                            // if it reaches 20, add it to the user_id
                            $this->append_to_user_ids($user_id, ["user_id" => $user_id, "school_id" => $row['school_id'], "admin_id" => $row['admin_id']], $group_by_school ? $row['school_id'] : false); 
                        }
                    }
                }
                
                //if($log && $total < 20) echo "uneligible\n";
                //if($log && $total >= 20) echo "eligible\n";
            }
        }
        if($log) echo "\n";
        
        return $this->eligable_user_ids;
    }
    
    private function append_to_user_ids($user_key, $value, $school_key = false){
        if($school_key){
            $this->eligable_user_ids[$school_key][$user_key] = $value;
        } else {
            $this->eligable_user_ids[$user_key] = $value;
        }
    }
    
    
    /*
     * $raffle->get_winner_info()
     *
     * returns an array of all the winners ($this->winner_info);
     */
    public function get_winner_info($school_id = false, $separate_genders = true, $sorting="school", $shipping_info = true){ // defaults
        $this->winner_info = []; // create the flat array
        if($separate_genders) $this->winner_info = ["boys" => [], "girls" => []]; // create the boys/girls structure
        
        /***************** GENERATE THE SQL *****************/
        if ($this->type == "monthly" && $this->raffle_id != 10) { // make sure it is a monthly raffle and is not the Tishrei Exception then pull it from the prizes_auciton_table
            $sql = "SELECT prize_name as 'name', prize_image_id, prize_id,  ";
        } else { // pull it from the prizes table
            $sql = "SELECT prizes.prize_id, prizes.name, prizes.picture, prizes.thumbnail, ";
        }
        // shared sql
        $sql .= "users.user_id, users.first, users.last, users.gender, schools.school_id, schools.school_name, schools.hachayol_name, classes.class_sub, classes.class_grade, "
            ."admins.admin_address1, admin_city, admin_state, admin_postal, admin_country FROM raffle_winners ";
        // specific joins
        if ($this->type == "monthly" && $this->raffle_id != 10) {
            $sql .= "JOIN prizes_auction USING (prize_id) ";
        } else {
            $sql .= "JOIN prizes USING (prize_id) ";
        }
        // back to shared SQL
        $sql .= "JOIN users USING (user_id) JOIN schools ON users.school_id = schools.school_id JOIN classes USING (class_id) "
            ."LEFT JOIN admin_auths ON user_id = id LEFT JOIN admins USING (admin_id)"
            ."WHERE raffle_winners.raffle_id = ".$this->raffle_id." ";
        
        // if a school_id was provided. Pass it in
        if($school_id) $sql .= "AND users.school_id = " . $school_id . " ";
        $sql .= "GROUP BY user_id ";
        // sort by the school and then the kids names
        if($sorting =="school") $sql .= "ORDER BY schools.school_name, classes.class_grade, users.last, users.first";
        if($sorting =="name") $sql .= "ORDER BY users.last, users.first"; // just sort by name
        if($sorting == "prize_id") $sql .= "ORDER BY prize_id, users.last, users.first"; // added by Naftoli 1/3/18
        
        $query = mysql_query($sql);
        
        // for every winner
        while($row = mysql_fetch_assoc($query)){
            // get the rank of the winner from the database
            $rank_sql = "SELECT rank_name FROM ranks WHERE rank_ord = (SELECT max(rank_ord) FROM rank_marks WHERE user_id = " . $row['user_id'] . ")";
            $rank_result = mysql_query($rank_sql);
            $rank_row = mysql_fetch_assoc($rank_result);
            
            // get the pictures
            if ($this->type == "monthly" && $this->raffle_id != 10) {
                $prize_picture = ["full" => "/file_view.php?id=".$row['prize_image_id'], "thumb" => "/file_view.php?id=".$row['prize_image_id'] ];
            } else {
                $prize_picture = ["full" => $row['picture'], "thumb" => $row['thumbnail'] ];
            }
            
            // format the data with the correct details
            $data = [
                'rank'          => $rank_row['rank_name'],
                'name'          => $row['first'] . ' ' . $row['last'],
                'first_name'    => $row['first'],
                'last_name'     => $row['last'],
                'prize_id'      => $row['prize_id'], 
                'prize_name'    => $row['name'],
                'prize_picture' => $prize_picture,
                'school'        => $row['school_name'],
                'school_id'     => $row['school_id'],
                'user_id'       => $row['user_id'],
                'hachayol_name' => $row['hachayol_name'], 
                'grade' => $row['class_grade'] . ($row['class_sub'] ? " - " .$row['class_sub'] : ""),
            ];

            if ( $shipping_info ) {
                $data['address'] = [
                    'street' => $row['admin_address1'], 
                    'city' => $row['admin_city'], 
                    'state' => $row['admin_state'], 
                    'zip' => $row['admin_postal'], 
                    'country' => $row['admin_country']
                ];
            }
            
            // sort it into the correct subsection (gender)
            if($separate_genders && $row['gender'] == "M"){
                $this->winner_info["boys"][] = $data;
            } elseif($separate_genders && $row['gender'] == "F"){
                $this->winner_info["girls"][] = $data;
            } else {
                $this->winner_info[] = $data; // just add it to the array
            }
        }
        return $this->winner_info; // return the array
    }
    
    /*
     * $raffle->get_prizes()
     *
     * returns an array of all the prizes ($this->prizes);
     */
    public function get_prizes(){
        if($this->type == "weekly") {
            $sql = "SELECT * FROM raffle_prizes WHERE raffle_id=".$this->raffle_id.";";
            $query = mysql_query($sql);
            while($row = mysql_fetch_assoc($query)){ // for each row
                // if it is weekly load up the weekly prize and add it to the array...
                $prize = Prize::Load($row['prize_id']); // load the prize
                    if($prize){ // if there is a prize
                    $prize->qty = $row['qty']; // add the quantity to the object
                    $this->prize_count += $prize->qty;
                    $this->prizes[$prize->prize_id] = $prize; // push it into the array
                }
            }
        } else {
            $sql = "SELECT rm.prize_id, prize_name, rm.school_id, school_name FROM raffles_monthly rm "
                ."JOIN prizes_auction USING (prize_id) JOIN schools s ON rm.school_id = s.school_id "
                ."WHERE raffle_id=".$this->raffle_id." ORDER BY s.school_name;";
            $query = mysql_query($sql);
            while($row = mysql_fetch_assoc($query)){ // for each row
                $this->prizes[] = $row;
            };
        }
        return $this->prizes;
    }
     /*
     * $raffle->add_prize($prize, $qty) => adds/updates a prize and its quantity
     *
     * $prize => a Prize object
     * $qty => the quantity of the prize
     */
    public function add_prize($prize, $qty){
        // return false if we did not get a Prize object
        if (!$prize instanceof Prize) return false;
        
        $sql = "SELECT * FROM raffle_prizes WHERE raffle_id = ". $this->raffle_id . " AND prize_id = " . $prize->prize_id;
        $query = mysql_query($sql); // run the query
        // generate the correct SQL
        if(mysql_num_rows($query) > 0){ // if the prize already exists then just update the qty
            $sql = "UPDATE raffle_prizes SET qty=$qty WHERE raffle_id = ". $this->raffle_id . " AND prize_id = " . $prize->prize_id.";";
        } else { // otherwsie insert a new row into the database
            $sql = "INSERT INTO raffle_prizes VALUES (".$this->raffle_id.", ".$prize->prize_id.", $qty);";
        }
        $query = mysql_query($sql); // run the query
        if($query){ // if it worked
            $prize->qty = $qty; // update the qty on the prize object
            $this->prizes[$prize->prize_id] = $prize; // and add it to the interal prize array using the prize id
            return true;
        } else {
            return false; // it did not work
        }
    }
    /*
     * $raffle->remove_prize($prize)
     *
     * removes a prize.
     *
     * $prize => a Prize object
     */
    public function remove_prize($prize){
        // delete the row
        $sql = "DELETE FROM raffle_prizes WHERE raffle_id = ". $this->raffle_id . " AND prize_id = " . $prize->prize_id;
        $query = mysql_query($sql);
        if($query){ // if it worked
            unset($this->prizes[$prize->prize_id]); // remove it from the array
            return true; // and return true
        }
        return false; // something did not work
    }

    /**
     * $raffle->get_hebrew_dates
     *
     * sets the hebrew_dates array
     * 
     * @return array
     */
    public function get_hebrew_dates(){
        $raffle_from = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->start_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
        $raffle_from = $raffle_from[0] . ' ' . $raffle_from[1];

        $raffle_to = explode(' ', iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->end_date, true, CAL_JEWISH_ADD_GERESHAYIM)));
        $raffle_to = $raffle_to[0] . ' ' . $raffle_to[1];

        return $this->hebrew_dates = [
            "from"  => $raffle_from,
            "to"    => $raffle_to
        ];
    }
}

