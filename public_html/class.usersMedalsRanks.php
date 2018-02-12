<?
class UsersMedalsRanks {
    private $users;
    private $medals;
    private $ranks;
    private $dates;
    
    public function __construct($users, $dates) {
        $this->users = $users;
        $this->medals = array();
        $this->ranks = array();
        $this->dates = $dates;
    }
    
    public function getMedals() {
        if (empty($this->medals)) {
            $sql = "select mm.user_id, m.medal_name from medals m 
                    join medal_marks mm using (medal_ord) 
                    where mm.user_id in (" . implode(',', $this->users) . ") 
                    and mm.date_awarded >= " . $this->dates['start'] . " 
                    and mm.date_awarded <= " . $this->dates['end'];
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->medals[$row['user_id']] = $row['medal_name'];
            } 
        }
        return $this->medals;
    }
    
    public function getRanks() {
        if (empty($this->ranks)) {
            $sql = "select rm.user_id, r.rank from ranks r 
                    join rank_marks rm using (rank_ord) 
                    where rm.user_id in (" . implode(',', $this->users) . ") 
                    and rm.date_promoted >= " . $this->dates['start'] . " 
                    and rm.date_promoted <= " . $this->dates['end'];
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->ranks[$row['user_id']] = $row['rank'];
            }
        }
        return $this->ranks;
    }
}
?>