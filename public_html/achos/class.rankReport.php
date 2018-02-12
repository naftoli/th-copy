<?
require_once 'class.report.php';

class RankReport extends Report {
    private $ranks;
    private $rankNames;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function setRanks( $orderType = 'byGrade' ) {
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 
        $sql = "
            SELECT s.school_name, c.class_teacher, c.class_grade, c.class_sub, r.rank_name, u.last, u.first
            FROM rank_marks rm
            JOIN ranks r
            USING ( rank_ord )
            JOIN users u
            USING ( user_id )
            JOIN schools s
            USING ( school_id )
            JOIN classes c
            USING ( class_id )
            WHERE date_promoted >= $start 
            AND date_promoted <= $end ";
        if ( !is_null( $this->school_id ) ) {
            $sql .= "AND s.school_id = $this->school_id ";
        }
        if ( $orderType == 'byGrade' ) {
            $sql .= "ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first, r.rank_ord";
        } else {
            $sql .= "ORDER BY s.school_name, r.rank_ord, c.class_grade, c.class_sub, u.last, u.first";
        }
        
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $school = $row['school_name'];
            $teacher = $row['class_teacher'];
            $grade = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
            $user = $row['first'] . " " . $row['last'];
            $rank = $row['rank_name'];
            if ( $orderType == 'byGrade' )
                $this->ranks[$school][$teacher][$grade][$user] = $rank;
            else if ( $orderType == 'byRank' )
                $this->ranks[$school][$rank][$teacher][$grade] = $user;
        }
    }
    
    public function getRanks() {
        return $this->ranks;
    }
    
    public function setRankNames() {
        $sql = "select * from ranks where medals_required > 0 order by rank_ord";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rank = $row['rank_name'];
            $needed = $row['medals_required'];    
            $this->rankNames[$rank] = $needed;
        }
    }
    
    public function getRankNames() {
        return $this->rankNames;
    }
}
?>