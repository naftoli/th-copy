<?php
class Reg
{
    private $school_id;
    private $class_id;
    private $user_id;
    private $registered_children;
    private $unregistered_children;
    private $parents;

    public function __construct($school = 0, $grade = 0, $user = 0) {
        $this->school_id = $school;
        $this->class_id = $grade;
        $this->user_id = $user;
        $this->registered_children = array();
        $this->unregistered_children = array();
    }

    private function setChildren($registered = true) {
        $sql = "select * from users u 
                left join schools s using (school_id) 
                left join classes c on u.class_id = c.class_id";
        if ($registered) $sql .= " where u.user_registered > 0";
        else $sql .= " where (u.user_registered = 0 or u.user_registered is null)";
        if ($this->school_id) $sql .= " and u.school_id = $this->school_id";
        if ($this->class_id) $sql .= " and u.class_id = $this->class_id";
        if ($this->user_id) $sql .= " and u.user_id = $this->user_id";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if ($row['user_registered'] > 0) {
                $this->registered_children[] = $row;
            } else {
                $this->unregistered_children[] = $row;
            }
        }
    }

    public function getParent($user_id) {
        $sql = "select a.* from admins a 
                left join users u on a.user_id = u.user_id 
                where u.user_id = " .  $user_id;
        $result = mysql_query($sql);
        return mysql_fetch_assoc($result);
    }

    public function getRegisteredChildren() {
        if (empty($this->resistered_children)) {
            $this->setChildren();
        }
        return $this->registered_children;
    }

    public function getUnregisteredChildren() {
        if (empty($this->unregistered_children)) {
            $this->setChildren();
        }
        return $this->unregistered_children;
    }
}