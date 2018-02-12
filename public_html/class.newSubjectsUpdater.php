<?
require_once 'db.php';

class NewSubjectsUpdater {
    
    private $subject_id;
    
    public function __construct( $subject ) {
        $this->subject_id = $subject;
    }
    
    public function updateMedals( $user_id, $total = 0 ) {
        //find out how many missions are needed for each medal
        $medals = array();
        $sql = "select medal_ord, missions_required from medals_subjects where subject_id = " . $this->subject_id . " order by medal_ord ";
        $result = mysql_query( $sql ) or die( mysql_error() );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $medals[$row['medal_ord']] = (int)$row['missions_required'];
        }
        
        if ( $this->subject_id == 21 ) {
            //find out how many missions where accomplished for this user
            $sql = "select count(*) as total from user_sefer_hamitzvos where user_id = " . $user_id;
            $result = mysql_query( $sql ) or die( mysql_error() );
            $row = mysql_fetch_assoc( $result );
            $total = $row['total'];
        }
        
        //find out how many medals where awarded to this user
        $medals_given = array();
        $sql = "select medal_ord from medal_marks where user_id = " . $user_id . " and subject_id = " . $this->subject_id . " order by medal_ord ";
        $result = mysql_query( $sql ) or die( mysql_error() );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $medals_given[] = $row['medal_ord'];
        }
        
        //award medals according to missions earned
        $needed = 0;
        foreach ( $medals as $medal => $required ) {           
            //variable to keep track of total required
            $needed += $required;
            if ( $total >= $needed ) {
                if ( !in_array( $medal, $medals_given ) ) {              
                    //insert into medal marks
                    $jdate = unixtojd();
                    $sql3 = "insert into medal_marks values ( $medal, $this->subject_id, $user_id, $jdate, null, 0, 0, 0)";
                    mysql_query( $sql3 ) or die( mysql_error() );
                }
            } else { //check if medal needs to be deleted
                if ( in_array( $medal, $medals_given ) ) {
                    //delete medal
                    $sql3 = "delete from medal_marks where user_id = " . $user_id . " and subject_id = " . $this->subject_id . " and medal_ord = " . $medal;
                    mysql_query( $sql3 ) or die( mysql_error() );
                }
            }
        }
    }

    public function updateTanyaMedals( $user_id, $medalName ) {
        $sql = "select medal_ord from medals where medal_name = '$medalName'";
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $medal = $row['medal_ord'];
        $jdate = unixtojd();
        
        //find out it user already has medal with all previous medals in db - if not add it
        $sql = "select medal_ord from medal_marks where user_id = $user_id and subject_id = $this->subject_id orcer by medal_ord";
        //echo $sql . "<br />";
        $result = mysql_query( $sql );
        if ( mysql_num_rows($result) > 0 ) {
            //check that all medals have been put in
            $medals = array();
            while ( $row = mysql_query( $result ) ) {
                $medals[] = $row['medal_ord'];
            }
            for ( $i = 1; $i <= $medal; $i++ ) {
                if ( !in_array( $i, $medals ) ) {
                    $sql = "insert into medal_marks ( medal_ord, subject_id, user_id, date_awarded ) 
                            values( $i, $this->subject_id, $user_id, $jdate )";
                    //echo $sql;
                    mysql_query( $sql );
                }
            }          
        } else {
            //put all medals until current medal into db 
            for ( $i = 1; $i <= $medal; $i++ ) {
                $sql = "insert into medal_marks ( medal_ord, subject_id, user_id, date_awarded ) 
                        values( $i, $this->subject_id, $user_id, $jdate )";
                //echo $sql;
                mysql_query( $sql );
            }
        }
    }

    public function updateRanks( $user_id ) {
        //find out how many medals are needed for each rank
        $ranks = array();
        $sql = "select rank_ord, medals_required from ranks";
        $result = mysql_query( $sql ) or die( mysql_error() );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $ranks[$row['rank_ord']] = $row['medals_required'];
        }
        
        //find out how many medals user has
        $sql = "select count(*) as total from medal_marks where user_id = " . $user_id;
        $result = mysql_query( $sql ) or die( mysql_error() );
        $row = mysql_fetch_assoc( $result );
        $total = $row['total'];
        
        //find out how many ranks user has been awarded
        $ranks_given = array();
        $sql = "select rank_ord from rank_marks where user_id = " . $user_id;
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $ranks_given[] = $row['rank_ord'];
        }
        
        $needed = 0;
        foreach ( $ranks as $rank => $required ) {
            $needed += $required;
            if ( $total >= $required ) {
                if ( !in_array( $rank, $ranks_given ) ) {
                    //insert into ranks
                    $jdate = unixtojd();
                    $sql2 = "insert into rank_marks values ( $rank, $user_id, $jdate, null, null, null, 0 )";
                    mysql_query( $sql2 ) or die( mysql_error() );
                }
            } else { //check if rank needs to be deleted
                if ( in_array( $rank, $ranks_given ) ) {
                    //delete from ranks
                    $sql2 = "delete from rank_marks where user_id = " . $user_id . " and rank_ord = " . $rank;
                    mysql_query( $sql2 ) or die( mysql_error() );
                }
            }
        }        
    }
}
?>