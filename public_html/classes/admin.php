<?php
namespace classes;

class admin {
    public $admin_id;
    public $username;
    public $auth;
    public $password;
    public $title;
    public $first;
    public $last;
    public $lang;
    public $admin_address1;
    public $admin_address2;
    public $admin_city;
    public $admin_state;
    public $admin_postal;
    public $admin_country;
    public $admin_phone_work;
    public $admin_phone_home;
    public $admin_phone_mobile;
    public $admin_email;
    public $camp_id;
    public $staff_type_id;
    public $staff_photo_id;
    public $school_id;
    public $is_parent;
    public $reminders;
    public $child_first;
    public $child_last;
    public $user_code;
    public $groups = array();
    public $children = array();
    public $sponsors = array();
    public $auths = array();
    public $schools = array();
    public $ckids;

    public $beta = false;

    public function __construct($row){
        $this->admin_id = $row['admin_id'];
        $this->username = $row['username'];
        $this->auth = $row['auth'];
        $this->password = $row['password'];
        $this->title = $row['title']; 
        $this->first = $row['first']; 
        $this->last = $row['last'];
        $this->lang = $row['lang']; 	
        $this->admin_address1 = $row['admin_address1']; 	
        $this->admin_address2 = $row['admin_address2']; 	
        $this->admin_city = $row['admin_city']; 	
        $this->admin_state = $row['admin_state']; 	
        $this->admin_postal = $row['admin_postal']; 	
        $this->admin_country = $row['admin_country']; 	
        $this->admin_phone_work = $row['admin_phone_work'];
        $this->admin_phone_home = $row['admin_phone_home'];
        $this->admin_phone_mobile = $row['admin_phone_mobile'];
        $this->admin_email = $row['admin_email'];
        $this->camp_id = $row['camp_id'];
        $this->staff_type_id = $row['staff_type_id']; 
        $this->staff_photo_id = $row['staff_photo_id'];
        $this->reminders = $row["reminders"];
        $this->is_parent = $row["is_parent"];
        $this->beta = !!$row["beta"];
        $this->ckids = 0;
    }

    public function get_admin_groups() {
        $sql = "SELECT g.* ";
        $sql = $sql . "FROM staff_groups AS sg ";
        $sql = $sql . "JOIN groups AS g USING (group_id) ";
        $sql = $sql . "WHERE admin_id=" . $this->admin_id;	
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $group = new \group($row);
            $group->get_division();
            $group->division->get_group_type();
            array_push($this->groups, $group);
        }		
    }

    public function update_item($admin_id, $field_name, $value) {
        $sql = "UPDATE admins SET " . $field_name . "='" . mysql_real_escape_string($value) . "' WHERE admin_id=" . $admin_id;
        $query = mysql_query($sql);
        // change to return !!$query - hornbacher
        if ($query)
            return true;
        else
            return false;	
    }

    public function get_children() {
        require_once(__DIR__ . "/user.php");
        require_once(__DIR__ . "/school.php");
        
        $sql = "SELECT u.* FROM admin_auths AS aa JOIN users AS u ON (aa.id=u.user_id) WHERE admin_id=" . $this->admin_id . " AND auth='user'";
        //echo $sql;
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $user = new \user($row);
            if ($user->school_id) {
                $user->get_school();			
                if ($user->school->school_settings != "home_school") {
                    $user->get_school_class();
                    $user->get_school_info();			
                }
                array_push($this->children, $user);
            }
        }
    }

    function get_markable_children()
    {
        require_once(__DIR__ . "/user.php");
        require_once(__DIR__ . "/school.php");
        
        $sql = "SELECT u.* ";
        $sql = $sql . "FROM admin_auths AS aa ";
        $sql = $sql . "JOIN users AS u ON (aa.id=u.user_id) ";
        $sql = $sql . "WHERE admin_id=" . $this->admin_id . " ";
        $sql = $sql . "AND aa.auth='user' ";
        $sql = $sql . "AND u.parent_marking=1 ";
        //$sql .= "and u.school_id is not null and u.class_id is not null";
        //echo "<input type='hidden' name='adminSql' value='$sql' />";
        
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $user = new \user($row);
            $user->get_school();			
            if ($user->school_id && $user->school->school_settings != "home_school") {
                $user->get_school_class();
                $user->get_school_info();			
            }
            array_push($this->children, $user);
        }

    }

    // what does this do?
    public function set_children($row) {
        $this->child_first = $row["child_first"];
        $this->child_last = $row["child_last"];
        $this->user_code = $row["user_code"];
    }

    function get_unregistered_children() {
        $sql = "SELECT u.* FROM admin_auths AS aa JOIN users AS u ON (aa.id=u.user_id) WHERE admin_id=" . $this->admin_id . " AND auth='user' AND u.user_registered IS NULL";
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $user = new \user($row);
            $user->get_school_class();
            $user->get_school_info();			
            array_push($this->children, $user);
        }
    }

    function getUnregisteredForYear($year) {
            $sql = "select u.*, ur.year from users u 
                join admin_auths aa on (aa.id = u.user_id) 
                join admins a using (admin_id) 
                left join user_registration ur using (user_id) 
                where aa.admin_id = " . $this->admin_id . " 
                and aa.auth = 'user' 
                order by u.first"; 
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if ( $row['year'] != $year && is_null($row['user_registered']) ) {
                $user = new \user($row);
                $user->get_school_class();
                $user->get_school_info();			
                array_push($this->children, $user);
            }
        }
    }

    public function get_sponsors() {
        $sql = "SELECT * FROM admin_sponsors WHERE admin_id=" . $this->admin_id;
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $name = $row["name"];
            $is_regular  = $row["is_regular "];
            $sponsor = compact('name', 'is_regular');
            array_push($this->sponsors, $sponsor);
        }
    }

    public function get_school_id() {
        $sql = "SELECT id FROM admin_auths WHERE admin_id=" . $this->admin_id . " AND auth='school'";
        $query = mysql_query($sql);
        $row = mysql_fetch_assoc($query);
        $this->school_id = $row["id"];
    }

    public function check_ckids_school() {
        $sql = "select inst_id from schools where school_id = " . $this->school_id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        if ( $row['inst_id'] == 10 ) $this->ckids = 1;
    }

    public function get_schools() 
    {
        if ($this->auth == "super")
        {
            $sql = "SELECT * FROM schools ORDER BY school_name";
        }
        else
        {
            $sql = "SELECT s.* ";
            $sql = $sql . "FROM admin_auths AS aa ";
            $sql = $sql . "JOIN schools AS s ON (aa.id=s.school_id) ";
            $sql = $sql . "WHERE admin_id=" . $this->admin_id . " ";
            $sql = $sql . "AND auth='school' ";
            $sql = $sql . "ORDER BY s.school_name ";
        }
        
        require_once(__DIR__ . "/school.php");
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $school = new \classes\school($row);
            array_push($this->schools, $school);
        }
    }


    public function get_auths() 
    {
        $sql = "SELECT auth FROM admin_auths WHERE admin_id=" . $this->admin_id;
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
            array_push($this->auths, $row["auth"]);
        }		
    }

    function get_school_auth()
    {
        $sql = "SELECT count(*) AS num_rows FROM admin_auths WHERE admin_id=" . $this->admin_id . " AND auth='school'";
        $query = mysql_query($sql);
        $row = mysql_fetch_assoc($query);
        if ($row['num_rows'] > 0)
            return "school";
        else
            return "";
    }
}
?>