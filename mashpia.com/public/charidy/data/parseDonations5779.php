<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

chdir('files');
$info = [];

// create json objects from files
if ( ( $handle = fopen("Donations.csv", "r") ) !== FALSE ) {
  while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
    $row = [];
    foreach ( $data as $column ) {
      $json = json_decode( stripslashes( $column ) );
      $row[] = $json;
    }
    $ref_id = $row[0];
    $json_id = $row[1]->get_data;
    //if ( !$json->donor_id ) $info[] = $json;
    if ( $ref_id ) $info[$ref_id] = $json_id;
  }
}

$donations = [];
if ( ( $handle = fopen("KidsTH.csv", "r") ) !== FALSE ) {
  while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
    $row = [];
    foreach ( $data as $column ) {
      $json = json_decode( stripslashes( $column ) );
      $row[] = $json;
    }
    $key = array_search( $row[0], $info );
    if ( $key && $row[6] ) $donations[$key]['json'] = $row[6];
  }
}

if ( ( $handle = fopen("Charidy5779.csv", "r") ) !== FALSE ) {
  while ( ( $data = fgetcsv($handle, 0, ",") ) !== FALSE ) {
    $row = [];
    foreach ( $data as $column ) {
      //$json = json_decode( stripslashes( $column ) );
      $row[] = $column;
    }
    $donations[$row[0]]['info'] = $row;
  }
}


// echo count( $info );
echo "<pre>";
// output to screen json objects
print_r( $donations );
echo "</pre>";

$year = GlobalSettings::getCharidyYear();
$donations_created = 0;
$child_donations_created = 0;

foreach ( $donations as $key => $donation ) {
  $json = $donation['json'];
  $info = $donation['info'];

  $donor_id = $json->donor_id;
  $donation_amount = $json->total_donation_amount > $json->amount ? $json->total_donation_amount : $json->amount;
  $children_amount = 0;
  foreach ( $json->children as $child ) {
    if ( $child->amount > 0 ) {
      $children_amount += $child->amount;
    }
  }
  $date = $json->date_time;
  $date = str_replace('T', ' ', $date);
  $pos = strpos($date, '.');
  $date = substr($date, 0, $pos);

  if ( !$donor_id ) {
    // find out if donor exists 
    // if not, create donor 
    $first = $info[3];
    $last = $info[4];
    $email = $info[7];
    $phone = $info[8];
    $amount = $info[9];
    $address = $info[25] . ' ' . $info[26];
    $city = $info[27];
    $zip = $info[28];
    $state = $info[29];
    $country = $info[30];

    $stmt = $MASHPIA_DB->prepare("select * from mashpia_charidy.donors where email = :email");
    $res = $stmt->execute([':email' => $email]);
    if ( $res ) {
      $row = $stmt->fetch();
      if ( $row ) {
        $donor_id = $row['donor_id'];
      } else {
        // find out if parent exists with this email
        $parent_admin_id = null;
        $stmt = $MASHPIA_DB->prepare("select admin_id from admins where admin_email = :email");
        $res = $stmt->execute([':email' => $email]);
        if ( $res ) {
          $row = $stmt->fetch();
          if ( $row ) {
            $parent_admin_id = $row['admin_id'];
          }
        }

        // create donor
        $stmt = $MASHPIA_DB->prepare("
          insert into mashpia_charidy.donors 
          set parent_admin_id = :parent_id, 
          first_name = :first, 
          last_name = :last, 
          address = :address, 
          city = :city, 
          state = :state, 
          zip = :zip, 
          country = :country, 
          phone = :phone, 
          email = :email
        ");
        $created = $stmt->execute([
          ':parent_id'  =>  $parent_admin_id, 
          ':first'      =>  $first, 
          ':last'       =>  $last, 
          ':address'    =>  $address, 
          ':city'       =>  $city, 
          ':state'      =>  $state, 
          ':zip'        =>  $zip, 
          ':country'    =>  $country, 
          ':phone'      =>  $phone, 
          ':email'      =>  $email
        ]);
        if ( $created ) {
          $donor_id = $MASHPIA_DB->lastInsertId();
        } else {
          echo "<pre>"; print_r( $MASHPIA_DB->errorInfo() ); echo "</pre>";
        }
      }
    }
  }

  if ( $donor_id > 0 ) {
    // check that donation shows up in database, if not add it
    $stmt = $MASHPIA_DB->prepare("select * from mashpia_charidy.donations where donor_id = :donor and year = :year and amount = :amount");
    $res = $stmt->execute([
      ':donor'  => $donor_id, 
      ':year'   => $year, 
      ':amount' => floatval( $donation_amount )
    ]);
    if ( $res ) {
      $rows = $stmt->fetchAll();
      if ( empty( $rows ) ) {
        // figure out if it's only child only donation or not
        $child_only = false;

        if ( $children_amount > 0 ) {
          if ( $children_amount >= $donation_amount ) {
            $child_only = true;
          }
        }

        if ( !$child_only ) {
          // create donation
          $stmt = $MASHPIA_DB->prepare("
            insert into mashpia_charidy.donations 
            set donor_id = :donor, 
            year = :year, 
            amount = :amount, 
            donation_date = :date, 
            dedication_name = :dname, 
            dedication_text = :dtext
          ");
          $created = $stmt->execute([
            ':donor'  =>  $donor_id, 
            ':year'   =>  $year, 
            ':amount' =>  floatval( $donation_amount ), 
            ':date'   =>  $date, 
            ':dname'  =>  $json->dedication_name, 
            ':dtext'  =>  $json->dedication_text
          ]);
          if ( $created ) $donations_created++;
        }

        if ( $children_amount > 0 ) {
          // add children donations
          foreach ( $json->children as $child ) {
            // find out user id
            $user_id = $child->user_id;
            if ( !is_numeric( $user_id ) ) {
              echo $key . ": not a valid user id<br />";
              continue;
            }

            $stmt = $MASHPIA_DB->prepare("
              insert into mashpia_charidy.donations 
              set donor_id = :donor, 
              year = :year, 
              amount = :amount, 
              donation_date = :date, 
              user_id = :user, 
              child_only_donation = 1
            ");
            $created = $stmt->execute([
              ':donor'  =>  $donor_id, 
              ':year'   =>  $year, 
              ':amount' =>  floatval( $child->amount ), 
              ':date'   =>  $date, 
              ':user'   =>  $user_id
            ]);
            if ( $created ) $child_donations_created++;
          }
        }
      }
    }
  } 
}