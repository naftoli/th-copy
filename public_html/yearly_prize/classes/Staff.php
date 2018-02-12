<?php
class Staff {
    
    public $staff_id;
    public $school_id;
    public $class_id;
    public $staff_name;
    public $staff_position;
    public $staff_email;
    public $staff_number;
    public $staff_work_number;
    public $date_added;
    
    public static function load($staff_id){
        $sql = "SELECT * FROM staff_info WHERE staff_id = $staff_id;"; // there should only be one object with this pk
        $query = mysql_query($sql); // run the query
        
        if ($query && mysql_num_rows($query) > 0){ // if there is a result
            $row = mysql_fetch_assoc($query); // get the row
            return self::loadFromRow($row); // load the item from a row
        } else {
            //echo '<br/>MySQL error: ('.mysql_errno().') '.mysql_error();
            return false;
        }
    }
    
    public static function loadAll($filter=""){
        //$filter = mysql_real_escape_string($filter); // protect against SQL injection
        $staff = []; // the array to return
        
        $sql = "SELECT * FROM staff_info $filter;"; // include the user filter in the query
        $query = mysql_query($sql);
        
        if(!$query) return false; // return false if the query fails
        //echo mysql_error();
        
        while($row = mysql_fetch_assoc($query)){
            $staff[] = self::loadFromRow($row); // generate the raffe instance and add it to the array
        }
        return $staff; // return the array
    }
    
    public static function loadFromRow($row){
        $instance = new self(); // create a new instance
        
        foreach ($row as $prop => $val) { // for each column returned from the database
            // convert the times to time values
            if($prop === "date_added" && $val) {
                $val = new DateTime($val); // set it to a datetime object for cool features
            }
            $instance->{$prop} = $val; // set the property on the instance
        } // end foreach
        
        return $instance; // return the instance that we created
    }
    
    public static function create($props){
        
        $data = self::set_props($props);
        
        $instance = $data['instance']; // the instance used to store the data and return it to the user if successful
        // some variables to make the insert command simpler
        $insert_column_keys = $data['keys']; // the keys used for the insert statement
        $insert_column_values = $data['values']; // the values that will be inserted
        
        // compute the keys to insert
        $sql = "INSERT INTO staff_info (". implode(", ", $insert_column_keys) .") VALUES (".implode(", ", $insert_column_values).");"; // generate the sql
        $query  = mysql_query($sql); // returns false if an error happens
        
        if($query){ // if it was inserted
            $instance->staff_id = mysql_insert_id(); // set the ID
            return $instance; // and return the instance
        } else { // the insert query failed
            //echo 'MySQL error: ('.mysql_errno().') '.mysql_error(); // print the mysql error for debugging (sadly $debug seems to not be set in this context)
            return false; // return false
        }
    }
    
    public function update($props){
        self::set_props($props, $this); // update the instace
        // generate the query
        $sql = "UPDATE staff_info SET staff_name='".$this->staff_name."', staff_email='".$this->staff_email."', staff_position='".$this->staff_position."', ";
        $sql .= "staff_number='".$this->staff_number."', staff_work_number='".$this->staff_work_number."', class_id = '".$this->class_id."' WHERE staff_id=".$this->staff_id.";";
        
        $query = mysql_query($sql); // run the query
        
        return !!$query; // return true or false. No objects;
        
    }
    // set the props from an array
    private static function set_props($props, $instance = false){
        if(!$instance) $instance = new self(); // create a new instance if one was not passed in
        $insert_column_keys = []; // the keys used for the insert statement
        $insert_column_values = []; // the values that will be inserted
        
        $props_array = array_intersect_key($props, get_object_vars($instance) ); // remove any keys that are not propertys of this object
        
        foreach($props_array as $prop => $value){
            // format times correctly
            if ($value instanceof DateTime){ // if a DateTime object was passed in
                $value = $value->format("Y-m-d H:i:s"); // formatted to YYYY-MM-DD HH:MM:SS for mysql database
            }
            // set the instance property to the value. Good place to throw errors on bad input
            array_push($insert_column_keys, mysql_real_escape_string($prop));
            array_push($insert_column_values, "'".mysql_real_escape_string($value)."'");
            // set the prop to the value that was passed in
            $instance->{$prop} = mysql_real_escape_string($value); // prevent sql injection
        }
        
        return ["instance" => $instance, "keys" => $insert_column_keys, "values" => $insert_column_values];
    }
    
    public function destroy(){
        $sql = "DELETE FROM staff_info WHERE staff_id=".$this->staff_id.";";
        
        return !!mysql_query($sql); // return a boolean of if the query ran or not.
    }
}