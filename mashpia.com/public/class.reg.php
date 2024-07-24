<?php
class Reg
{
    private $school_id;
    private $class_id;
    private $user_id;
    private $children;

    public function __construct($school = 0, $grade = 0, $user = 0) {
        $this->school_id = $school;
        $this->class_id = $grade;
        $this->user_id = $user;
        $this->children = [];
    }

    private function setChildren() {
        $sql = "select *, u.first as first_name, u.last as last_name from users u 
                left join schools s using (school_id) 
                left join classes c on u.class_id = c.class_id 
                left join admin_auths aa on aa.id = u.user_id 
                left join admins a on a.admin_id = aa.admin_id 
                where u.user_registered > 0 
                and (aa.auth = 'user' or aa.admin_id IS NULL) ";
        if ($this->school_id > 0) $sql .= " and u.school_id = " . mysql_real_escape_string($this->school_id);
        if ($this->class_id > 0) $sql .= " and u.class_id = " . mysql_real_escape_string($this->class_id);
        if ($this->user_id > 0) $sql .= " and u.user_id = " . mysql_real_escape_string($this->user_id);
//        echo $sql; exit;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->children[] = $row;
            $this->parents[$row['user_id']] = $row;
        }
    }

    public function getChildren() {
        if (empty($this->children)) {
            $this->setChildren();
        }
        return $this->children;
    }

    public function getParents() {
        if (empty($this->parents)) {
            $this->setChildren();
        }
        return $this->parents;
    }
}