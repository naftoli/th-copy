<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);
//$type = $_POST['type'];
//ini_set('display_errors',1);
if (isset($_POST['type'])) {
    require_once '../api/header/db.php';
    require_once '../api/models/School.php';
    $id = $_POST['id'];
    $school = School::find_by_pk($id);
    $res = $school->enrollIntoCampaigns();
    if (!$res) {
        echo "Error updating campaigns for school $id";
    } else {
        echo "Successfully updated campaigns for school $id";
    }
} else {
    $users = $_POST['id'];
    if (!is_array($users)) $users = array($users);
    require_once '../db.php';
    require_once '../class.campaignEnrollment.php';
    
    // update child to be enrolled in all campaigns
    foreach ($users as $user_id) {
        try {
            $c = new CampaignEnrollment($user_id);
            $c->enroll();
        } catch (EnrollmentException $e) {
            echo $e->getMessage();
        }
    }
}

/*

if ( $type == 'school' ) {
    $sql = "select inst_id from schools where school_id = " . $id;
} else if ( $type == 'student' ) {
    $sql = "select school_type_id as inst_id from users where user_id = " . $id;
}
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$inst = $row['inst_id'];

if ($type == 'school') { 
    switch( $inst ) {
        case '2':
            $sql = "select subject_id, subject_name from subjects s 
                    join school_type_subjects sts using (subject_id) 
                    where s.subject_type in ('', 'WWTC') 
                    and sts.school_type_id in (2,3) 
                    group by s.subject_id 
                    order by s.subject_name";
            break;
        case '4':
            $sql = "select subject_id, subject_name from subjects s 
                    join school_type_subjects sts using (subject_id) 
                    where s.subject_type in ('', 'WWTC') 
                    and sts.school_type_id in (12,13) 
                    group by s.subject_id 
                    order by s.subject_name";
            break; 
    }
} else if ($type == 'student') {
    $sql = "select subject_id, subject_name from subjects s 
            join school_type_subjects sts using (subject_id) 
            where s.subject_type in ('', 'WWTC') 
            and sts.school_type_id = $inst  
            group by s.subject_id 
            order by s.subject_name";
}
$campaigns = array();
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $campaigns[] = $row['subject_id'];
}

$years = array(
    'Pre1a' =>  6, 
    '1'     =>  7, 
    '2'     =>  8, 
    '3'     =>  9, 
    '4'     =>  10, 
    '5'     =>  11, 
    '6'     =>  12, 
    '7'     =>  13, 
    '8'     =>  14
);
    
foreach( $campaigns as $campaign ) {
    if ( $type == 'school' ) { 
        $sql = "insert ignore into school_subjects values( $id, $campaign )";
        mysql_query( $sql );
    } else if ( $type == 'student' ) {
        //find out which year student is suppose to be on
        $sql = "select c.class_grade from classes c 
                join users u using (class_id) 
                where u.user_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $grade = $row['class_grade'];
        $year = $years[$grade];
        
        //find out if user already has entry in user_tracks for this campaign or not 
        $sql = "select * from user_tracks where user_id = $id and subject_id = $campaign";
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) > 0 ) {
            $sql = "update user_tracks 
                    set level = $year, ";
            if ( $campaign != 1 ) 
                $sql .= "track_id = 1, ";
            $sql .= "enrolled = 1 
                    where user_id = $id 
                    and subject_id = $campaign";
        } else {
            if ($campaign == 1) {
                //find out user type
                if (in_array($inst, array(2,3))) {
                    $sql = "insert into user_tracks values( $id, $campaign, 5, $year, 1 )";
                } else if (in_array($inst, array(12,13))) {
                    $sql = "insert into user_tracks values( $id, $campaign, 3, $year, 1 )";
                }
            } else {
                $sql = "insert into user_tracks values( $id, $campaign, 1, $year, 1 )";
            }
        }
        mysql_query( $sql );
        
        //update newly_registered and newly_joined tables
        $sql = "insert into newly_registered set reg_year = 5774, user_id = $id";
        mysql_query($sql);
        
        $sql = "select user_start_date from users where user_id = " . $id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        if ($row['user_start_date'] > 2456522) {
            $sql = "insert into newly_joined values($id, $row[user_start_date], null, null)"; 
            mysql_query($sql);
        }
    }
}
*/
?>