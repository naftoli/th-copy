<?
require_once( dirname(__FILE__) . '/../../../db.php' );

function getStickerName( $subject_id ){
    $img_to_subject_id = [
        '41' => 'avos_ubonim',
        '100' => 'brias_haguf',
        '45' => 'cheshbon_hanefesh',
        '90' => 'chitas',
        '16' => 'hiskashrus', 
        '12' => 'mivtzoim',
        '13' => 'niggunim',
        '21' => 'sefer_hamitzvos',
        '1' => 'shabbos_mevorchim_tehillim',
        '27' => 'tanya', '91' => 'tanya',
        '4' => 'tefillah',
        '42' => 'vihalachta_bidrachov',
        '40' => 'yomei_dipagra'
    ];
    if ( isset( $img_to_subject_id[$subject_id ] ) )
        return $img_to_subject_id[$subject_id ];
    return 'cheshbon_hanefesh'; // looks like a checkmark
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
        "SELECT subject_id, subject_name, subject_description, COUNT(*) AS total "
        ." FROM date_tasks_mission_marks JOIN subjects USING ( subject_id ) "
        ." WHERE user_id = '$user' AND subject_id IN (" . implode( ',', $subjects ) . ") "
        ." GROUP BY subject_id"
    );
    while( $row = mysql_fetch_assoc( $user_missions_query ) ){
        $row['sticker_name'] = getStickerName( $row['subject_id'] );
        if ( $row['subject_id'] == 1 ) 
            $row['subject_name'] = "תהילים";
        else if ( $row['subject_id'] == 27 ) 
            $row['subject_name'] = "תניא";
        $user_missions[$row['subject_id']] = $row;
    }

    $stickers_required_query = mysql_query(
        " SELECT subject_id, medal_ord, medal_name, missions_required, profile_photo_id "
        ." FROM medals_subjects JOIN medals USING ( medal_ord ) "
        ." WHERE subject_id IN (" . implode( ',', $subjects ) . ") AND profile_photo_id IS NOT NULL "
        ." ORDER BY subject_id, medal_ord;"
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
            $running_total = $amount_required;
            $last_photo_location = '/kiosk/images/medals/holder.png';
        }
        $row['running_total'] = $running_total;
        
        // calcuate images for the sticker items
        $medal_name = strtolower( $row['medal_name'] );
        $medal_name = $medal_name == 'gray' ? 'grey' : $medal_name;
        if ( in_array( $subject_id, [ 1, 12, 21, 15, 93 ] ) )
            $row['photo'] = 'images/backs/wwtc/'.$medal_name.'.gif';
        else if ( $subject_id == 40 )
            $row['photo'] = 'images/backs/yd/'.$medal_name.'.gif';
        else
            $row['photo'] = 'images/backs/weekly/'.$medal_name.'.gif';
        
        // update the user_missions object
        $user_missions[$subject_id ]['medal_info'][] = $row;
        $user_missions[$subject_id ]['subject_total'] = $running_total;

        if ( $running_total > $total && !isset($user_missions[$subject_id ]['photo']) ) {
            $user_missions[$subject_id ]['photo'] = $last_photo_location;
            $user_missions[$subject_id ]['left'] = $running_total - $total;
        }

        $last_photo_location = '/file_view.php?id='.$row['profile_photo_id'];
        $last_subject_id = $subject_id ;
    }

    $user_missions = array_values( $user_missions );

    header('Content-Type: application/json');
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

