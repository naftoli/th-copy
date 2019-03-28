<?
class UserInfo {
    private $year;
    private $users;
    private $newlyJoined;
    
    public function __construct($year = null) {
        $this->year = $year;
        $this->users = array();
        $this->newlyJoined = array();
    }
    
    public function getNewlyRegistered() {
        if (empty($this->users)) {
            $sql = "select * from newly_registered where reg_year = " . $this->year;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->users[$row['user_id']] = $row;
            }
        }
        return $this->users;
    }
    
    public function getNewlyJoined($from) {
        if (empty($this->newlyJoined)) {
            $sql = "select * from newly_joined where joined > " . $from;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->newlyJoined[$row['user_id']] = $row;
            }
        }
        return $this->newlyJoined;
    }
}
?>