<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
require 'db.php';

require 'class.globalSettings.php';
$year = GlobalSettings::getBirthdayYear();

$info = array();
$sql = "select s.school_name, u.first, u.last, u.gender, d.*
        from he_dob d
        join users u using (user_id)
        join schools s using (school_id)
        where d.wp_synced = 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;
$arrPosts = array();
foreach ($info as $child) {
    //figure out date of birthday
    if (intval($child['he_mm']) > 0 && (intval($child['he_dd']) > 0 && intval($child['he_dd']) < 31) ) {
        // jewishtojd counts tishre as 1, cheshvon as 2, etc.
        //if ($child['he_mm'] > 6) $child['he_mm'] -= 6;
        //else $child['he_mm'] += 6;
        $child['age'] = $year - intval($child['he_yy']);
        //echo $child['he_mm'] . ':' . $child['he_dd'] . ':' . $year . "<br />";
        $jd = floor(jewishtojd($child['he_mm'], $child['he_dd'], $year));
        $dob = jdtogregorian($jd);
        //echo $dob . "<br />"; continue;
        $arrDob = explode('/', $dob);
        // add 0 padding to single digit mm and dd
        if (strlen($arrDob[0]) == 1) $arrDob[0] = '0' . $arrDob[0];
        if (strlen($arrDob[1]) == 1) $arrDob[1] = '0' . $arrDob[1];
        $postDate = $arrDob[2] . '-' . $arrDob[0] . '-' . $arrDob[1];
        
        $arrPosts[] = array(
            'info' => $child,
            'post' => array(
                'post_date'     =>  $postDate,
                'post_title'	=>	ucwords($child['first'] . ' ' . $child['last']), 
                'post_content'	=>	'', 
                'post_status'	=>	'future', 
                'post_type'		=>	'birthday', 
                'post_author'	=>	1 
            ),
        );
        //echo "<pre>"; print_r($post); echo "</pre>"; continue;
    }
}
//echo "<pre>"; print_r($arrPosts); echo "</pre>"; exit;

require "blog/wp-blog-header.php";
foreach ($arrPosts as $arrPost) {
    //print_r($arrPost); continue;
    $id = wp_insert_post( $arrPost['post'], true );
    if ( is_wp_error( $id ) ) {
        echo $id->get_error_message() . "<br />";
    } else {
        $gender = strtolower($arrPost['info']['gender']);
        if ($gender == 'm') $gender = 'boy';
        else if ($gender == 'f') $gender = 'girl';
        add_post_meta( $id, 'user_id', $arrPost['info']['user_id'] );
        add_post_meta( $id, 'school', $arrPost['info']['school_name'] );
        add_post_meta( $id, 'gender', $gender );
        add_post_meta( $id, 'age', $arrPost['info']['age'] );
        add_post_meta( $id, 'registered', 1 );
        /*
        $term = get_term_by( 'name', $arrPost['info']['school_name'], 'school' );
        if ( $term ) wp_set_object_terms( $id, (int)$term->term_id, 'school' );
        $term2 = get_term_by( 'name', $arrPost['info']['gender'], 'gender' );
        if ( $term2 ) wp_set_object_terms( $id, (int)$term2->term_id, 'gender' );
        $term3 = get_term_by( 'name', $ranks[$arrPost['info']['rank']], 'rank' );
        if ( $term3 ) wp_set_object_terms( $id, (int)$term3->term_id, 'rank' );
        */
        $sqlUpdate = "update mashpiadb.he_dob set wp_synced = 1 where user_id = " . $arrPost['info']['user_id'];
        mysql_query($sqlUpdate);
    }    
}
echo "Done.";