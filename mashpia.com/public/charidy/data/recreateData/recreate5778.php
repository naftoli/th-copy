<?php
require_once '../../../db.php';

function findParentID( $user_id ) {
  $sql = "select admin_id from admin_auths where auth = 'user' and id = " . $user_id;
  $result = mysql_query( $sql );
  if ( mysql_num_rows( $result ) > 0 ) {
    $row = mysql_fetch_assoc( $result );
    $admin_id = $row['admin_id'];
  }
  return 0;
}

function findDonorFromChild( $user_id ) {
  $parentID = findParentID( $user_id );
  if ( $parentID ) {
    $sql = "select donor_id from mashpia_charidy.donors where parent_admin_id = " . $parentID;
    $result = mysql_query( $result );
    if ( mysql_num_rows( $result ) > 0 ) {
      $row = mysql_fetch_assoc( $result );
      return $row['donor_id'];
    }
  }
  return 0;
}

function findDonorByPhone( $phone ) {

}

function findDonorByEmail( $email ) {

}

function findDonorByName( $name ) {

}

function createDonor( $info ) {

}

function createDonation( $donor_id, $donation ) {

}

function createChildDonation( $donor_id, $user_id, $donation ) {
  
}

// load record from charidy_temp_donations
$donations = [];
$sql = "select * from charidy_temp_donations";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donations[$row['relation_id']] = $row;
}

// load json info connected to record
$json_info = [];
foreach ( $donations as $id => $donation ) {
  $sql = "select data from charidy_temp_data where id = " . $id;
  $result = mysql_query( $sql );
  if ( mysql_num_rows( $result ) > 0 ) {
    $row = mysql_fetch_assoc( $result );
    $json_info[$id] = $row['data'];
  }
}

foreach ( $donations as $id => $more ) {
  
}

// if there's children, find donor id by children

// if no donor id is found, find out if we can find donor id based on phone / email / name

// if no donor id is found, create donor

// create donation

// create children donations if exists