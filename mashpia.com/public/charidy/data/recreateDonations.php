<?php
require dirname(__FILE__) . "/../../db.php";

function createDonor( $info, $admin = [] ) {
  $emailExceptions = [
    'accounting@gmail.com',
    'accounting@tzivoshashem.org',
    'chayazirkind@gmail.com',
    'kaplanmussi@gmail.com',
    'shimmy@jcm.museum',
    'shimmy@tzivoshashem.org',
    'sholomber@jcm.museum'
  ];

  if ( !empty( $admin ) ) {
    if ( in_array( $info['email'], $emailExceptions ) ) $info['email'] = ''; // don't assign email to donor for the ones that are connected to th staff
    $first_name = $admin['first'] ? $admin['first'] : $info['fname'];
    $last_name = $admin['last'] ? $admin['last'] : $info['lname'];
    $address = str_replace('"', '', $admin['admin_address1']);
    $insert = "insert into mashpia_charidy.donors
              set first_name = \"" . $first_name . "\",
              last_name = \"" . $last_name . "\",
              address = \"" . $address . "\",
              city = '" . $admin['admin_city'] . "',
              state = '" . $admin['admin_state'] . "',
              zip = '" . $admin['admin_postal'] . "',
              country = '" . $admin['admin_country'] . "',
              phone = '" . $info['phone'] . "',
              email = '" . $info['email'] . "', 
              needs_call = 1";
  } else {
    $insert = "insert into mashpia_charidy.donors
              set first_name = '" . $info['fname'] . "',
              last_name = '" . $info['lname'] . "',
              phone = '" . $info['phone'] . "',
              email = '" . $info['email'] . "', 
              needs_call = 1";
  }
  if ( $info['parent_admin_id'] > 0 ) $insert .= ", parent_admin_id = " . $info['parent_admin_id'];
  echo $insert . "<br />";
  // return 111;
  if ( mysql_query( $insert ) ) {
    return mysql_insert_id();
  } else {
    echo mysql_error() . "<br />" . $insert . "<br />";
    return false;
  }
}

function createDonation( $donor_id, $info ) {
  if ( $donor_id > 0 ) {
    $sqlDonation = "insert into mashpia_charidy.donations
                    set donor_id = " . $donor_id . ",
                    amount = " . $info['donation'] . ",
                    year = " . $info['year'];
    echo $sqlDonation . "<br />";
    return true;
    // if ( mysql_query( $sqlDonation ) ) return true;
    // else echo mysql_error() . "<br />" . $sqlDonation . "<br />";
  } else {
    echo "Missing donor id for Charidy ID: " . $info['charidy_id'] . "<br />";
  }
  return false;
}

function findDonorByParentID( $parent_id ) {
  if ( $parent_id > 0 ) {
    $sql = "select donor_id from mashpia_charidy.donors where parent_admin_id = " . $parent_id;
    $result = mysql_query( $sql );
    if ( mysql_num_rows( $result ) > 0 ) {
      echo $sql . "<br />";
        $row = mysql_fetch_assoc( $result );
        return $row['donor_id'];
    }
  }
  return 0;
}

function findDonorByEmail( $email ) {
  if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false ) {
    $sql = "select donor_id from mashpia_charidy.donors where email = '" . $email . "'";
    $result = mysql_query( $sql );
    if ( mysql_num_rows( $result ) > 0 ) {
      echo $sql . "<br />";
        $row = mysql_fetch_assoc( $result );
        return $row['donor_id'];
    }
  }
  return 0;
}

function findDonorByName( $last_name, $first_name ) {
  $sql = "select donor_id from mashpia_charidy.donors where last_name = \"" . $last_name . "\" and first_name = \"" . $first_name . "\"";
  $result = mysql_query( $sql );
  if ( mysql_num_rows( $result ) > 0 ) {
    echo $sql . "<br />";
      $row = mysql_fetch_assoc( $result );
      return $row['donor_id'];
  }
  return 0;
}

function findAdminByEmail( $email ) {
  if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false ) {
    $sql = "select * from admins where admin_email = '" . $email . "'";
    $result = mysql_query( $sql );
    if ( mysql_num_rows( $result ) > 0 ) {
        $row = mysql_fetch_assoc( $result );
        return $row;
    }
  }
  return [];
}

function findAdminByName( $last_name, $first_name ) {
  $sql = "select * from admins where last = \"" . $last_name . "\" and first = \"" . $first_name . "\"";
  $result = mysql_query( $sql );
  if ( mysql_num_rows( $result ) > 0 ) { 
    $row = mysql_fetch_assoc( $result );
    return $row;
  }
  return [];
}

$info = [];
$sql = "select * from charidy";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;

$created['donors'] = 0;
$created['donations'] = 0;
foreach ( $info as $row ) {
    // find out if this donor already exists; check by parent_id, email address, name
    $donor_id = findDonorByParentID( $row['parent_admin_id'] );
    if ( !$donor_id ) $donor_id = findDonorByEmail( $row['email'] );
    if ( !$donor_id ) $donor_id = findDonorByName( $row['lname'], $row['fname'] );
    if ( $donor_id ) {
      echo "found donor id: " . $donor_id . " - " . $row['parent_admin_id'] . ' : ' . $row['email'] . ' : ' . $row['lname'] . ' ' . $row['fname'] . "<br />";
      if ( createDonation( $donor_id, $row ) ) $created['donations']++;
    }
    
    // create donor if it doesn't exist
    else {
      $admin = findAdminByEmail( $row['email'] );
      if ( !$admin ) $admin = findAdminByName( $row['lname'], $row['fname'] );
      if ( $donor_id = createDonor( $row, $admin ) ) {
        $created['donors']++;
        if ( createDonation( $donor_id, $row ) ) $created['donations']++;
      }
    }
}
echo "done.";
echo "<pre>"; print_r( $created ); echo "</pre>";