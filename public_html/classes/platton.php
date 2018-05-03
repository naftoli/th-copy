<?php
require_once( dirname(__FILE__) . "/user.php" );
// stored in the classes table ( Class is a reserved keyword )

class platton implements JsonSerializable {
    public $class_id;
    public $school_id;
    public $class_grade;
    public $class_sub;
    public $teacher;
    public $default_level;
    public $gender_view;
    
    public $era;
    public $gender;

    public $allow_parent_tasks;
    public $print_parent_tasks;

    public $users = [];

    /**
     * Load
     * 
     * Loads a platton from the database
     *
     * @param int/string $class_id
     * @return platton
     * @throws Exception
     */
    public static function Load( $class_id ){
        $class_id = mysql_real_escape_string( $class_id );

        $query = mysql_query(
            "SELECT * FROM classes where class_id = $class_id;"
        );
        if ( mysql_num_rows( $query ) > 0 ) {
            $row = mysql_fetch_assoc( $query );
            return self::LoadFromRow($row);
        } else {
            throw new Exception('Class Not Found');
        }
    }

    /**
     * LoadFromRow
     *
     * @param array $row
     * @return platton
     */
    public static function LoadFromRow( $row ){
        $instance = new self(); // create instance to return
        // load them all in raw for now
        foreach ($row as $prop => $val) {
            $instance->{ $prop } = $val;
        }
        // return a new instance
        return $instance;
    }

    /**
     * getUsers
     * 
     * populate the users array with the information we want ( or new user objects? )
     *
     * @return array
     */
    public function getUsers() {
        $users_query = mysql_query(
            "SELECT * FROM users WHERE class_id = '" . $this->class_id . "';"
        );
        while( $row = mysql_fetch_assoc( $users_query ) ){
            // $this->users[] = new user( $row ); // create a new user and add him to our array
            $this->users[] = [
                "user_id" => $row['user_id'],
                "last" => $row['last']
            ];
        }

        return $this->users;
    }

    /**
     * jsonSerialize
     * 
     * part of the JsonSerializable interface to support json_encode on the object
     *
     * @return array
     */
    public function jsonSerialize() {
        return get_object_vars( $this );
    }
}