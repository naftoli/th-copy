<?php
$admin_auth = ['school', 'user'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

function getSubjectName( $subject_id, $default ) {
    if ( $subject_id == 1 ) 
        return "תהילים";
    else if ( $subject_id == 27 ) 
        return "תניא";
    return $default;
}

function getStickerName( $subject_id ){
    $img_to_subject_id = [
        1 => 'shabbos_mevorchim_tehillim',  4 => 'tefillah',
        12 => 'mivtzoim',   13 => 'niggunim',   16 => 'hiskashrus',
        21 => 'sefer_hamitzvos',    27 => 'tanya',  40 => 'yomei_dipagra',
        41 => 'avos_ubonim',    42 => 'vihalachta_bidrachov',   45 => 'cheshbon_hanefesh',
        90 => 'chitas', 91 => 'tanya', 92 => 'niggunim',    93 => 'mivtzoim',
        94  => 'yomei_dipagra',   100 => 'brias_haguf'
    ];
    if ( isset( $img_to_subject_id[$subject_id ] ) )
        return $img_to_subject_id[$subject_id ];
    return 'cheshbon_hanefesh'; // looks like a checkmark
}

function getSubjectImage( $subject_id ) {
    $campaignLogos = array(
        1	=>	'Tehillim.svg',
        4	=>	'Tefilla.svg',
        12	=>	'Mivtzoim.svg',
        13	=>	'Niggunim.svg',
        16	=>	'hiskashrus.svg',
        21	=>	'sefer-hamitzvos.svg',
        27	=>	'tanya.svg',
        40	=>	'Yom-Dipagra.svg',
        41	=>	'Father-son.svg',
        42	=>	'Footsteps.svg',
        45	=>	'Cheshbon-Hanefesh.svg',
        90	=>	'Chitas.svg',
        100	=>	'Brias-Haguf.svg',
        121 =>  "day-school-Jewish Day 250 px.svg",
        122 =>  "day-school-Jewish Uniform 250px.svg",
        124 =>  "day-school-Health 250px.svg",
        125 =>  "day-school_Torah 250px.svg",
        126 =>  "day-school_Shabbat 250px.svg",
        127 =>  "day-school_Special Days 250px.svg",
        129 =>  "day-school_Kosher 250px.svg",
        130 =>  "day-school_Tefilla.svg",
        131 =>  "day-school-ahavat-yisrael.svg",
        132 =>  "day-school-brachot.svg",
        133 =>  "day-school-Tzedaka.svg",
        134 =>  "day-school-honoring parents 250px.svg",
        135 =>  "day-school-Middot-icon.svg"
    );
    //if ( isset( $campaignLogos[$subject_id ] ) )
        return $campaignLogos[$subject_id ];
    //return 'cheshbon-hanefesh-black-svg.svg'; // looks like a checkmark
}

if ( isset( $_GET['v'] ) && $_GET['v'] == 2) { 
    $user = mysql_real_escape_string( $_POST['user_id'] );

    // figure out which subjects we are showing
    require '../../../class.campaignEnrollment.php';
    $c = new CampaignEnrollment($user);
    $c->setType();
    $subjects = $c->getCampaigns();

    // Get the users info for each subject
    $user_missions = [];
    $user_missions_query = mysql_query(
        "SELECT subject_id, subject_name, subject_details, subject_details_he, SUM( mission_count ) AS total "
        ." FROM date_tasks_mission_marks JOIN subjects USING ( subject_id ) "
        ." WHERE user_id = '$user' AND subject_id IN (" . implode( ',', $subjects ) . ") "
        ." GROUP BY subject_id"
    );
    while( $row = mysql_fetch_assoc( $user_missions_query ) ){
        $row['sticker_name'] = getStickerName( $row['subject_id'] );
        $row['campaign_logo'] = getSubjectImage( $row['subject_id'] );
        $row['subject_name'] = getSubjectName( $row['subject_id'], $row['subject_name'] );
        $user_missions[$row['subject_id']] = $row;
    }

    foreach( $subjects as $subject_id ){
        if ( !isset( $user_missions[$subject_id] ) ) {
            $subject_name_query = mysql_query("SELECT subject_name, subject_details, subject_details_he FROM subjects WHERE subject_id = '$subject_id';");
            $missing_subject_info = mysql_fetch_assoc( $subject_name_query );
            $user_missions[$subject_id] = [
                'sticker_name'  => getStickerName( $subject_id ),
                'campaign_logo' => getSubjectImage( $subject_id ),
                'subject_name'  => getSubjectName( $subject_id, $missing_subject_info['subject_name'] ),
                'subject_details'       => $missing_subject_info['subject_details'],
                'subject_details_he'    => $missing_subject_info['subject_details_he'],
                'subject_id'            => $subject_id,
                'total' => 0
            ];
        }
    }
    ksort( $user_missions );

    $stickers_required_query = mysql_query(
        " SELECT ms.subject_id, ms.medal_ord, medal_name, medal_name_he, missions_required, profile_photo_id, mm.date_awarded "
        ." FROM medals_subjects ms JOIN medals USING ( medal_ord ) "
        ." LEFT JOIN medal_marks mm ON ms.subject_id = mm.subject_id AND ms.medal_ord = mm.medal_ord AND user_id = '$user'"
        ." WHERE ms.subject_id IN (" . implode( ',', $subjects ) . ") "
        ." ORDER BY ms.subject_id, ms.medal_ord;"
    );

    $last_subject_id = 0;
    $running_total = 0;

    while( $row = mysql_fetch_assoc( $stickers_required_query ) ){
        $amount_required = $row['missions_required'] = (int)$row['missions_required'];
        $total = intval( $user_missions[$row['subject_id']]['total'] );
        $subject_id = $row['subject_id'];

        // calculate agragators for row
        if ( $subject_id  == $last_subject_id ) {
            $running_total = $amount_required + $running_total;
        } else {
            if ( isset( $user_missions[ $last_subject_id ] ) && !isset( $user_missions[ $last_subject_id ]['photo'] ) )
                $user_missions[ $last_subject_id ]['photo'] = $last_photo_location;
            $running_total = $amount_required;
            $last_photo_location = '/mobile/reg/medals/images/Empty-Medal-Holder.png';
        }
        $row['running_total'] = $running_total;
        // convert julian date to hebrew utf8
        $row['date_awarded'] = iconv('WINDOWS-1255', 'UTF-8', jdtojewish( $row['date_awarded'], true, CAL_JEWISH_ADD_GERESHAYIM ) );;
        
        // calcuate images for the sticker items
        $medal_name = strtolower( $row['medal_name'] );
        $medal_name = $medal_name == 'gray' ? 'grey' : $medal_name;
        // 95 total missions
        if ( in_array( $subject_id, [ 1, 12, 15, 93 ] ) )
            $row['photo'] = 'images/backs/wwtc/'.$medal_name.'.png';
        // 585 total missions
        else if ( in_array( $subject_id, [ 40, 94 ] ) )
            $row['photo'] = 'images/backs/yd/'.$medal_name.'.png';
        // day schools subjects
        else if ( $subject_id > 120 && $subject_id <= 135 ) {
            if ( !in_array( $subject_id, [ 126, 127, 135 ] ) ) {
                // daily subjects / tasks
                $row['photo'] = 'images/backs/daySchools/daily/' . $medal_name . '.png';
            } else if ($subject_id == 127){
                // yd subjects / tasks
                $row['photo'] = 'images/backs/daySchools/yd/' . $medal_name . '.png';
            } else {
                // weekly subjects / tasks
                $row['photo'] = 'images/backs/daySchools/weekly/' . $medal_name . '.png';
            }
        }
        // 375 missions
        else
            $row['photo'] = 'images/backs/weekly/'.$medal_name.'.png';
        
        // update the user_missions object
        $user_missions[$subject_id ]['medal_info'][] = $row;
        $user_missions[$subject_id ]['subject_total'] = $running_total;

        if ( $running_total > $total && !isset($user_missions[$subject_id ]['photo']) ) {
            $user_missions[ $subject_id ]['photo'] = $last_photo_location;
            $user_missions[ $subject_id ]['left'] = $running_total - $total;
        }

        if ( $row['profile_photo_id'] )
            $last_photo_location = '/file_view.php?id='.$row['profile_photo_id'];
        $last_subject_id = $subject_id;
    }

    $user_missions = array_values( $user_missions );

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode( $user_missions );
} else {
    $subject = mysql_real_escape_string( $_POST['subject'] );
    $info = array();
    $sql = "SELECT medal_ord, missions_required from medals_subjects where subject_id = " . $subject;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['medal_ord']] = (int)$row['missions_required'];
    }
    echo json_encode( $info );
}

?>
