<?
class Stickers {
    private $medals;
    private $missions;
    
    public function __construct() {
        $this->setMedals();
    }
    
    private function setMedals() {
        $this->medals = array();
        $sql = "SELECT subject_id, medal_ord, missions_required
                FROM medals_subjects";
        $result = mysql_query( $sql );
        $subject_id = 0;
        while ( $row = mysql_fetch_assoc( $result ) ) {
            //make sure to reset total when moving to new subject
            if ( $row['subject_id'] != $subject_id ) {
                $subject_id = $row['subject_id'];
                $total = 0;
            } 
            $total += $row['missions_required'];
            $this->medals[$row['subject_id']][$row['medal_ord']] = $total;
        }
        /*
        echo "<pre>";
        print_r( $this->medals );
        echo "</pre>";
         * 
         */
    }
    
    public function calculateSticker( $subject_id, $missionsDone ) {
        //find current medal
        foreach( $this->medals[$subject_id] as $medal => $total ) {
            if ( $total > $missionsDone ) {
                //find medal equal to or greater than missions done
                //calculate how many missions done into new medal
                //for first medal just need to show missions done 
                if ( $medal == 1 ) {
                    $sticker[$medal] = $missionsDone;
                } else {
                    $sticker[$medal] = $missionsDone - $this->medals[$subject_id][$medal-1];
                }
                break;
            }
        }
        return $sticker;
    }    
}
?>