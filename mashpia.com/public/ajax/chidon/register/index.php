<?php
ini_set('display_errors',1);
require_once '../headers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ( !isset( $_REQUEST['key'] ) || $_REQUEST['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

$data = $_POST['body']['data'];
$children = $data['children'];
$admin_id = $data['parent']['admin_id'];
$to = $data['parent']['admin_email'];
$zip = $data['parent']['admin_postal'];
$authorization = $data['parent']['authorize_conf'];
$total = $data['totals']['grand_total'];
$reg_total = $data['totals']['registration_total'];
$year = GlobalSettings::getChidonYear();

// $myshliach = false;
// $user_ids = [];
// foreach ( $children as $child ) {
//     $user_ids[] = $child['user_id'];
//     if ( $child->school_id == 61 ) $myshliach = true;
// }

// // create transaction
// $description = "Chidon Registration " . $year . " - Parent ID: " . $admin_id;
// $transaction_query = $MASHPIA_DB->prepare(
//     "INSERT INTO transactions (trans_date, admin_id, description, amount, reg_amount, ship_amount, zip, users_registered, response) "
//     ."VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
// );
// $transaction_query->execute([
//     $admin_id, $description, $total, $reg_total, 0, $zip, implode( ', ', $user_ids ), $authorization
// ]);
// $trans_id = $MASHPIA_DB->lastInsertId();

// foreach ( $children as $child ) {
//     $user = \Soldier::find([ $child['user_id'] ]);
//     // echo "<pre>"; print_r( $user ); echo "</pre>"; 

//     $fields = [ 'gender', 'first', 'last', 'first_he', 'last_he', 'dob', 'non_th_school' ];
//     foreach ( $fields as $field ) {
//         $user->{ $field } = $child[ $field ];
//     }

//     // flag to know if we need to update the birthday missions
//     if ( $user->attribute_is_dirty('dob') ) $updateBirthday = true;
//     else $updateBirthday = false;

//     if ( !$user->is_valid() || !$user->save() ) {
//         echo json_encode([
//             'success'   =>  false, 
//             'error'     =>  'Could not update soldier. Please check to make sure that the data is valid.'
//         ]);
//         exit;
//     } else {
//         // update the birthday missions if dob was changed
//         if ( $updateBirthday ) {
//             $user->setupBirthdayMissions();
//         }
//     }

//     $year = GlobalSettings::getChidonYear();
//     $recruited_by = intval( $child['recruited_by'] );
//     $recruited = $recruited_by > 0 ? 1 : 0;

//     if ( !$user->registerChidon( $year, strtolower( $child['sweater_size'] ), $child['book'], $data['parent']['admin_id'], 
//             $data['totals']['grand_total'], $trans_id, $recruited, $recruited_by, $child['studying_with'] ) ) {
//         echo json_encode([
//             'success'   =>  false, 
//             'error'     =>  "Could not register ".$user->user_id." for chidon"
//         ]);
//         exit;
//     } else {
//         // add book purchased info to db
//         if ( intval( $child['already_purchased'] ) == 1 ) {
//             $location = $child['purchased_where'];
//             // $store_name = $child['store']['store_name'];
//             // $store_city = $child['store']['store_city'];
//             $user->addBookPurchase( --$year, $user->user_id, $location, '', '', '' );
//         } else if ( intval($child['purchasing_book']) ) {
//             $user->registrationCharge(
//                 'yahaudus',
//                 floatval( $child['yahadus_book_cost'] ),
//                 $trans_id, $year
//             );
//             // add book purchase to db
//             $user->addBookPurchase( $year, $user->user_id, 'parent_account', $trans_id );
//         }
//     }
// }

// // send email to parents
// $headers[] = 'MIME-Version: 1.0';
// $headers[] = 'Content-type: text/html; charset=iso-8859-1';
// $headers[] = 'From: Chidon Office <chidon@tzivoshashem.org>';

// $subject = "Chidon Registration Confirmation";
// $message = "Mazal Tov! Your child(ren) is / are enrolled in the Chidon Limmud program for 5780.
//             <br /><br/>
//             We hope you will take full advantage from the resources available for this phenomenal journey, and utilize the opportunities to study and bond with your child.
//             <br /><br />
//             In order to begin learning, your child will need the Yahadus book corresponding to their grade (Grade 4 - Book 1; Grade 5 - Book 2; Grade 6- Book 3; Grade 7 - Book 4; Grade 8 - Book 5)
//             along with the accompanying study guide, that will help them optimize their study with information needed from each unit, corrections and study aids.
//             <br /><br />
//             Please speak to your school's Chidon coordinator to order these items. (The study guide is also available online.)
//             <br /><br />
//             To download a copy of the study guide and to view important dates for Chidon tests and the Shabbaton, visit <a href='www.thechidon.com'>www.thchidon.com</a>.";
// if ( $myshliach ) {
//     $message = "
//     Mazal Tov! Your child(ren) are enrolled in the Chidon Hamitzvos International Competition for 5780.
//     <br /><br />
//     We hope you will take advantage from the resources available for this phenomenal journey, and utilize the opportunities to study and bond with your child.
//     <br /><br />
//     This is also an opportunity to have a Bubby or Zaidy learn with your child weekly. 
//     <br /><br />
//     MyShliach's online classes is very popular and will help keep your child/ren on a schedule as well as connect with like minded friends throughout this journey. Click <a href='https://merkos302.formstack.com/forms/chidon_shiurim_registration'>Here</a> to sign up for the classes.
//     In order to begin learning, your child/ren will need the Yahadus book corresponding to their grade (Grade 4 - Book 1; Grade 5 - Book 2; Grade 6- Book 3; Grade 7 - Book 4; Grade 8 - Book 5) along with the accompanying study guide, that will help them optimize their study with information needed for each unit, as well as corrections and study aids. 
//     <br /><br />
//     Study guides will be shipped to your home. To download a copy of the study guide and to view important dates for the Chidon tests and Shabbaton, visit <a href='http://www.thchidon.com'>www.thchidon.com</a>.
//     <br /><br /><br />
//     Wishing you Much Hatzlocho!
//     <br /><br />
//     For any questions throughout the duration of the Chidon Zman please be in touch with your Chidon Coordinator at MyShliach.";
//     $headers[] = "Cc: chidon@myshliach.com";
// }

// if ( $to ) {
//     if ( !mail( $to, $subject, $message, implode("\r\n", $headers) ) ) {
//         $to = "naftoli@tzivoshashem.org";
//         $subject = "Error in chidon email";
//         $message .= "<br /><b>Sent to " . $email . "</b>";
//         @mail( $to, $subject, $message, implode("\r\n", $headers) );
//     }
// }

echo json_encode([
    'success'   =>  true
]);