<?
// use a special namespace for the shipping classes
namespace shipping;
// use a database wrapper so that we can inject tests in the future by casting the interface
require_once(dirname(__FILE__)."/DBAdapter.php");

use \DateTime;
use \DBAdapter;

class Shipment {
    
    //***************** PUBLIC VARIABLES ****************/
    public $shipment_id; // primary key
    // modifiable in the DB
    public $school_id; // the school the shipment is going to
    public $name; // what the shipment is called
    public $date_shipped; // the date that it was shipped
    // value created by DB. just allow access....
    public $date_created; // the date that the shipment was created
    public $status;
    
    public $description;
    
    // filled by functions....
    public $details = []; // by default there are no details
    public $tracking_numbers = [];
    
    //***************** PRIVATE/PROTECTED VARIABLES ****************/
    protected $db; // the database connection.....
    public static $table_name = "shipments";
    
    //***************** CONSTRUCTOR ****************/
    public function __construct($dbAdpater = false) {
        if($dbAdpater) {
            $this->db = $dbAdpater;
        } else {
            $this->db = new DBAdapter();
        }
    }
    
    //************** STATIC CONSTRUCTORS ***************//
    
    /*  shipping\Shipment::load($shipment_id)
     *  
     *  Loads the shipment from the database and returns a new instance loaded from the database row
     *  params:
     *      $shipment_id => the shipment_id in the database
     */
    public static function load($shipment_id, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        
        $query = $dbAdpater->query("SELECT * FROM ".self::$table_name." WHERE shipment_id = $shipment_id"); // select the values from the db
        return $query ? self::loadFromRow($query->fetch_assoc(), $dbAdpater) : $query; // return the query or the falsy value.... (false)
    }
    
    /*  shipping\Shipment::loadAll($filter)
     *  
     *  Loads the shipment from the database according to the filter and returns an array of objects....
     *  params:
     *      $filter => Everything after the SELECT * FROM shipments statement
     */
    public static function loadAll($filter, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        
        $query = $dbAdpater->query("SELECT * FROM ".self::$table_name." ".$filter); // select the values from the db
        if(!$query) return false; // if the query is invalid return false
        
        $results = [];
        while($row = $query->fetch_assoc()){
            $results[] = self::loadFromRow($row, $dbAdpater); // load each one from the row
        }
        return $results; // return the results....
    }
    
    /*  shipping\Shipment::create($props_array)
     *
     *  $props_array => associative array of the values used to create the object
     *
     *  Loads a raffle from the database and returns an instance of the Raffle object
     */
    public static function create($props_array, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        
        $instance = new self($dbAdpater); // the instance used to store the data and return it to the user if successful
        $insert_column_keys = [];   $insert_column_values = [];
        
        foreach($props_array as $prop => $value){
            if($prop == "shipment_id") continue; // prevent id injection....
            // format times correctly
            if ($value instanceof DateTime){ // if a DateTime object was passed in
                $value = $value->format("Y-m-d H:i:s"); // formatted to YYYY-MM-DD HH:MM:SS for mysql database
            }
            if($prop == "status") $value = in_array($value, []) ? $value : 'planned'; // set status to planned by default
            // set the instance property to the value. Good place to throw errors on bad input
            array_push($insert_column_keys, $prop);
            array_push($insert_column_values, "'$value'");
            // set the prop to the value that was passed in
            $instance->{$prop} = $value;
        }
        $sql = "INSERT INTO ".self::$table_name." (". implode(", ", $insert_column_keys) .") VALUES (".implode(", ", $insert_column_values).");"; // generate the sql
        
        $query = $dbAdpater->query($sql); // returns false if an error happens
        
        if($query){ // if it was inserted
            $instance->shipment_id = $dbAdpater->insert_id(); // set the ID
            return $instance; // and return the instance
        } else { // the insert query failed
            //echo 'MySQL error: ('.mysql_errno().') '.mysql_error(); // print the mysql error for debugging (sadly $debug seems to not be set in this context)
            return $sql; // return false
        }
    }
    
    /*  shipping\Shipment::loadFromRow($row_assoc)
     *  
     *  Loads the shipment from associative row and returns a new instance with the params attached...
     *  params:
     *      $row_assoc => associative row pulled from the database
     */
    public static function loadFromRow($row_assoc, $dbAdpater = false){
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        
        $instance = new self($dbAdpater); // new instance to be returned
        // set the params manually as there are not enough to justify metaprogramming
        $instance->shipment_id = $row_assoc['shipment_id'];
        $instance->school_id = $row_assoc['school_id'];
        $instance->name = $row_assoc['name'];
        $instance->date_shipped = $row_assoc['date_shipped'] ? new DateTime($row_assoc['date_shipped']) : $row_assoc['date_shipped']; // if it was shipped then set the variable to a DateTime instance. else, just tie it to the row value
        $instance->date_created = new DateTime($row_assoc['date_created']);
        $instance->status = $row_assoc['status'];
        $instance->description = $row_assoc['description'];
        // return the created instance
        return $instance;
    }
    
    //************** PUBLIC INSTANCE FUNCTIONS ***************//

    public function add_or_move_items( $ajaxArray ) {
        foreach( $ajaxArray as $ajax ) { 
            $success = $this->add_or_move_item( $ajax );
        }
        return $success;
    }
    
    /*  shipment->add_or_move_item()
     *  
     *  Loads the details of the shipment from the database and sets it to the internal $details array.
     */
    public function add_or_move_item($ajax) {
        $item = $this->parseAjax($ajax); // parse the ajax info
        // if this is an hachayol
        if($item == "hachayol"){
            return $this->mark_hachayol( $ajax );
        } else if( $item == "auction" ){
            return $this->mark_auction($ajax);
        }
        // make sure it is array....
        if(!is_array($item) || !$this->shipment_id) return false;
        // check if it exists
        $where_sql = " WHERE type='".$item['type']."' AND item_id = '".$item['item_id']."'";
        // where_sql
        if(isset($item['item_type']))       $where_sql .= " AND item_type = '".     $item['item_type']      ."'";
        if(isset($item['item_ord']))        $where_sql .= " AND item_ord = '".      $item['item_ord']       ."'";
        if(isset($item['item_extra_id']))   $where_sql .= " AND item_extra_id = '". $item['item_extra_id']  ."'";
        // add select to check if it exists
        $exists_query = $this->db->query("SELECT * FROM shipment_details".$where_sql); // run the query
        
        if($exists_query && $exists_query->num_rows() > 0){ // if it exists...
            return !!$this->db->query("UPDATE shipment_details SET shipment_id = '".$this->shipment_id."'".$where_sql); // update the shipping id to this instance...
        } else {
            $insert_sql = "INSERT INTO shipment_details (shipment_id, ".implode(", ", array_keys($item)).
                        ") VALUES (".$this->shipment_id.", '".implode("', '", array_values($item))."')";
            return !!$this->db->query($insert_sql);
        }
    }
    
    private function mark_hachayol($ajax){
        $params = explode(":", $ajax);
        return !!$this->db->query(
            "UPDATE hachayol_shipping SET shipment_id=".$this->shipment_id." WHERE school_id = ".$params[1]. " AND parsha_id = ". $params[2]
        );
    }

    private function mark_auction($ajax){
        $params = explode(":", $ajax);
        return !!$this->db->query(
             " UPDATE auction_winners SET shipment_id=" . $this->shipment_id 
            ." WHERE user_id = " . $params[1]
            ." AND auction_id = ". $params[2]
            ." AND prize_id = ". $params[3]
        );
    }
    
    
    public function update($props){
        if(isset($props['name']) && $props['name']) $this->name = $props['name']; // might be blank...
        if(isset($props['description']) && $props['description']) $this->description = $props['description']; // might be blank...
        if(isset($props['date_shipped'])) $this->date_shipped = $props['date_shipped'];
        if(isset($props['status'])) $this->status = $props['status'];
        
        $sql = "UPDATE ".self::$table_name." SET name=\"".$this->name."\", ";
        if ( $this->date_shipped ) 
            $sql .= " date_shipped='".$this->date_shipped->format("Y-m-d H:i:s") ."', ";
        $sql .= " status='". $this->status . "', description=\"".$this->description."\" WHERE shipment_id=".$this->shipment_id;
                
        //echo $sql;
        return !!$this->db->query($sql);
    }
    
    /*  shipment->get_details()
     *  
     *  Loads the details of the shipment from the database and sets it to the internal $details array.
     */
    public function get_details() {
        $this->details = []; // reset the details....
        // use a nested query to normalize all the data....
        $sql = "SELECT * FROM ("; // open the nested query....
        /*************************** HACHAYOLS (DEPRECIATED...) *********************/
        // Hachayol -> students
        //$sql .= "SELECT shipment_details.*, CONCAT(last, ', ', first) as name, 'Hachayol' as item "
        //    ."FROM shipment_details JOIN users ON type='hachayol' AND item_type = 'user' AND users.user_id = item_id ";
        //// Hachayol -> teachers (classes table)
        //$sql .= "UNION SELECT shipment_details.*, class_teacher as name, 'Hachayol' as item "
        //    ."FROM shipment_details JOIN classes ON type='hachayol' AND item_type = 'teacher' AND classes.class_id = item_id ";
        /*************************** TEHILLIMS *********************/
        // Tehillim -> users
        $sql .= "SELECT shipment_details.shipment_id, CONCAT(last, ', ', first) as name, 'Tehillim' as item "
            ."FROM shipment_details JOIN users ON type='gift' AND item_type = 'user' AND users.user_id = item_id ";
        // Tehillim -> schools
        $sql .= "UNION SELECT shipment_details.shipment_id, principal as name, 'Tehillim' as item "
            ."FROM shipment_details JOIN schools ON type='gift' AND item_type = 'school' AND schools.school_id = item_id ";
        // Tehillim -> teachers and admins (both from the admins table)
        $sql .= "UNION SELECT shipment_details.shipment_id, CONCAT(last, ', ', first) as name, 'Tehillim' as item "
            ."FROM shipment_details JOIN admins ON type='gift' AND (item_type = 'teacher' OR  item_type = 'admin' ) AND admins.admin_id = item_id ";
        // Tehillim -> staff (staff_info table)
        $sql .= "UNION SELECT shipment_details.shipment_id, staff_name as 'name', 'Tehillim' as item "
            ."FROM shipment_details JOIN staff_info ON type='gift' AND item_type = 'staff' AND staff_info.staff_id = item_id ";
        /*************************** MEDALS *********************/
        // Medals -> users
        $sql .= "UNION SELECT shipment_details.shipment_id, CONCAT(last, ', ', first) as name, CONCAT(medal_name, ' Medal For ', subject_name) as item "
            ."FROM shipment_details JOIN users ON type='medal' AND users.user_id = item_id JOIN medals on medals.medal_ord = item_ord JOIN subjects on subjects.subject_id = item_extra_id ";
        /*************************** RANKS *********************/
        // Ranks -> users
        $sql .= "UNION SELECT shipment_details.shipment_id, CONCAT(last, ', ', first) as name, CONCAT(rank_name, ' Rank ', item_type) as item "
            ."FROM shipment_details JOIN users ON type='rank' AND users.user_id = item_id JOIN ranks on ranks.rank_ord = item_ord ";
        /*************************** PRIZES *********************/
        // Prizes -> users table
        $sql .= "UNION SELECT shipment_details.shipment_id, CONCAT(last, ', ', first) as name, prizes.name COLLATE utf8_general_ci as item "
            ."FROM shipment_details JOIN users ON type='prize' AND users.user_id = item_id JOIN raffle_winners ON users.user_id = raffle_winners.user_id "
            ."JOIN prizes ON raffle_winners.prize_id = prizes.prize_id ";
        /*************************** HACHAYOLS (VERSION 2.0) *********************/
        $sql .= "UNION SELECT hs.shipment_id, school_name as name, CONCAT(hs.qty, ' Hachayols For ', parshos.name) as item "
            ."FROM hachayol_shipping hs JOIN schools USING (school_id) JOIN parshos ON hs.parsha_id = parshos.id " ;
        /*************************** HACHAYOLS (VERSION 2.0) *********************/
        $sql .= "UNION SELECT aw.shipment_id, CONCAT(u.last, ', ', u.first) as name, p.prize_name as item "
            ."FROM auction_winners aw JOIN users u USING (user_id) JOIN prizes_auction p using(prize_id) ";
        /*************************** FILTERS *********************/
        $sql .= ") AS SQ WHERE shipment_id = ".$this->shipment_id." ";
        /*************************** SORTING *********************/
        $sql .= "ORDER BY name, item ";

        // if($_GET['debug']) echo $sql;
        
        $query = $this->db->query($sql);
        while ($row = $query->fetch_assoc()){
            $this->details[] = $row;
        }
        return $this->details;
    }
    
    public function get_school(){
        $query = $this->db->query("SELECT * FROM schools WHERE school_id=".$this->school_id);
        $school = $query->fetch_assoc();
        return ($this->school = $school);
    }
    
    public function get_detail_count() {
        $sql = "SELECT COUNT(*) AS total FROM shipment_details WHERE shipment_id = ".$this->shipment_id;
        $total = $this->db->query($sql)->fetch_assoc()['total'];
        
        $hachayol_sql = "SELECT SUM(qty) as total FROM hachayol_shipping WHERE shipment_id = ".$this->shipment_id;

        $total += $this->db->query($hachayol_sql)->fetch_assoc()['total'];
        return $total;
    }
    
    public function get_status(){
        return $this->status;
    }
    
    public function get_tracking_numbers(){
        $query = $this->db->query("SELECT * FROM tracking_numbers WHERE shipment_id='".$this->shipment_id."'");
        while($row = $query->fetch_assoc()){
            if($row['provider'] == "USPS"){
                $row['tracking_link'] = "https://tools.usps.com/go/TrackConfirmAction.action?tLabels=".$row['tracking_number'];
            } elseif($row['provider'] == "UPS") {
                $row['tracking_link'] =  "https://www.ups.com/WebTracking/processInputRequest?tracknum=".$row['tracking_number'];
            } elseif($row['provider'] == "Amazon") {
                $row['tracking_link'] = $row['tracking_number'];
                $row['tracking_number'] = "Amazon Link";
            }
            $this->tracking_numbers[] = $row;
        }
        return $this->tracking_numbers;
    }
    
    //************** PRIVATE INSTANCE FUNCTIONS ***************//
    /*  $shipment->parseAjax($ajax);
     *
     *  Parse and normalize the ajax info from the shipping reports
     */
    private function parseAjax($ajax) {
        $params = explode(":", $ajax);
        $item = ['type' => $params[0]]; // set the type to the type from the AJAX info
        
        // setup the item object based on the params....
        switch($params[0]){
            case 'medal': // medal:<medal_ord>:<user_id>:<date_awarded>:<subject_id>
                $item['item_ord'] = $params[1];
                $item['item_id'] = $params[2];
                $item['item_extra_id'] = $params[4];
                break;
            case 'rank': // rank:<rank_ord>:<user_id>:<date_promoted>:<card|book>
                $item['item_ord'] = $params[1];
                $item['item_id'] = $params[2];
                $item['item_type'] = $params[4];
                break;
            case 'gift': // uses gift:<type>:<id>
                $item['item_type'] = $params[1];
                $item['item_id'] = $params[2];
                break;
            case 'prize': // uses prize:<user_id>:<raffle_id>:<prize_id>
                $item['item_id'] = $params[1];
                $item['item_extra_id'] = $params[2];
                break;
            case 'hachayol': // uses hachayol:<type>:<id>
                $item = "hachayol"; break;
            case 'auction': // uses hachayol:<type>:<id>
                $item = "auction"; break;
            default:
                $item = false;
        }
        
        return $item;
    }
    
    
    //************** STATIC FUNCTIONS ***************//
    
    /*  Shipments::getSchoolShipments($school_id)
     *
     *  Get all the shipments for a single school based on it's ID
     *
     *  params:
     *      $school_id => School id for the school
     */
    public static function getSchoolShipments($school_id, $dbAdpater = false){
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        // run the query
        $query = $dbAdpater->query("SELECT * FROM ".self::$table_name." WHERE school_id = $school_id AND status != 'archived'");
        $shipments = []; // create a blank array
        // if there are results
        if($query && $query->num_rows() > 0){ // make sure the query is valid and there are results.....
            while($row = $query->fetch_assoc()){
                $shipments[] = self::loadFromRow($row);
            } // end loading all the rows
        } // checking for valid query
        return $shipments; // return the shipments/empty array
    } // end getSchoolShipments function
    
    /*  Shipments::getPrizeShipmentDetails($school_id)
     *
     *  Get the prize shipment details for a given school
     *
     *  params:
     *      $school_id => School id for the school
     *
     *  return data structure:
     *      [user_id => raffle_id => [name, shipment_id]] ([] represents array, => represents array nesting)
     */
    public static function getPrizeShipmentDetails($school_id, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        
        $query = $dbAdpater->query("SELECT shipment_details.*, name FROM shipment_details "
                                   ."JOIN ".self::$table_name." USING (shipment_id)"
                                   ."WHERE school_id = $school_id AND type='prize'");
        $shipments = []; // create a blank array
        if($query && $query->num_rows() > 0) { // make sure the query is valid and there are results.....
            while($row = $query->fetch_assoc()) { // get all the rows...
                $shipments[$row['item_id']][$row['item_extra_id']] = ['name' => $row['name'], 'shipment_id' => $row['shipment_id']]; // user_id => raffle_id => [name, shipment_id]
            } // end loading all the rows
        } // checking for valid query
        return $shipments; // return the shipments/empty array
    }
    
    /*  Shipments::getRankShipmentDetails($school_id)
     *
     *  Get the rank shipment details for a given school
     *
     *  params:
     *      $school_id => School id for the school
     *
     *  return data structure:
     *      [user_id => rank_ord => card/book => [name, shipment_id]] ([] represents array, => represents array nesting)
     */
    public static function getRankShipmentDetails($school_id, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        $query = $dbAdpater->query("SELECT shipment_details.*, name FROM shipment_details "
                                   ."JOIN ".self::$table_name." USING (shipment_id)"
                                   ."WHERE school_id = $school_id AND type='rank'");
        
        $shipments = []; // create a blank array
        if($query && $query->num_rows() > 0) { // make sure the query is valid and there are results.....
            while($row = $query->fetch_assoc()) { // get all the rows...
                $shipments[$row['item_id']][$row['item_ord']][$row['item_type']] = ['name' => $row['name'], 'shipment_id' => $row['shipment_id']]; // user_id => rank_ord => card/book => [name, shipment_id]
            } // end loading all the rows
        } // checking for valid query
        return $shipments; // return the shipments/empty array
    }
    
    /*  Shipments::getMedalShipmentDetails($school_id)
     *
     *  Get the rank shipment details for a given school
     *
     *  params:
     *      $school_id => School id for the school
     *
     *  return data structure:
     *      [user_id => medal_ord => subject_id => [name, shipment_id]] ([] represents array, => represents array nesting)
     */
    public static function getMedalShipmentDetails($school_id, $dbAdpater = false) {
        if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
        $query = $dbAdpater->query("SELECT shipment_details.*, name FROM shipment_details "
                                   ."JOIN ".self::$table_name." USING (shipment_id)"
                                   ."WHERE school_id = $school_id AND type='medal'");
        $shipments = []; // create a blank array
        if($query && $query->num_rows() > 0) { // make sure the query is valid and there are results.....
            while($row = $query->fetch_assoc()) { // get all the rows...
                $shipments[$row['item_id']][$row['item_ord']][$row['item_extra_id']] = ['name' => $row['name'], 'shipment_id' => $row['shipment_id']]; // user_id => rank_ord => card/book => [name, shipment_id]
            } // end loading all the rows
        } // checking for valid query
        return $shipments; // return the shipments/empty array
    }
    
    /*  Shipments::getHachayolShipmentDetails($school_id)
     *
     *  Get the rank shipment details for a given school
     *
     *  params:
     *      $school_id => School id for the school
     *
     *  return data structure:
     *      [type (teacher/user) => user_id => [name, shipment_id]] ([] represents array, => represents array nesting)
     */
    //public static function getHachayolShipmentDetails($school_id, $dbAdpater = false) {
    //    if(!$dbAdpater) $dbAdpater = new DBAdapter(); // make sure we have a DB adapter if none was provided.....
    //    $query = $dbAdpater->query("SELECT shipment_details.*, name FROM shipment_details "
    //                               ."JOIN ".self::$table_name." USING (shipment_id)"
    //                               ."WHERE school_id = $school_id AND type='hachayol'");
    //    $shipments = []; // create a blank array
    //    if($query && $query->num_rows() > 0) { // make sure the query is valid and there are results.....
    //        while($row = $query->fetch_assoc()) { // get all the rows...
    //            $shipments[$row['item_type']][$row['item_id']] = ['name' => $row['name'], 'shipment_id' => $row['shipment_id']]; // user_id => rank_ord => card/book => [name, shipment_id]
    //        } // end loading all the rows
    //    } // checking for valid query
    //    return $shipments; // return the shipments/empty array
    //}
}
