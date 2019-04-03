<?php
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";
require __DIR__ . "/../../class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

function extractList( $list ) {
  if ( strpos( $list, ',' ) !== false ) {
    $extracted = explode(',', $list);
  } else if ( strpos( $list, '-' ) !== false ) {
     $nums = explode('-', $list);
     $extracted = [];
     for ( $i = $nums[0]; $i <= $nums[1]; $i++ ) {
       $extracted[] = $i;
     }
  }
  return $extracted;
}

$qrys = [];
$positions = [];
$headers = ['first_name', 'last_name', 'sweater_size', 'school_bus', 'coach_bus', 'open_air_bus', 'cell', 'gender'];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
  $row = 0;
  $numFields = count( $headers );
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if ( $row++ < 1 ) continue;
    if ( empty( $data[0] ) ) continue;
    $qry = "insert into th_chidon_staff set ";
    for ( $i = 0; $i < $numFields; $i++ ) {
      if ( empty( $data[$i] ) ) continue;
      $qry .= $headers[$i] . " = \"" . $data[$i] . "\",";
    }

    $username = str_replace("'", '', ucfirst( trim( $data[0] ) ) . ucfirst( trim( $data[1] ) ) );
    $username .= $year;
    $password = 'shabbaton';
    $qry .= " username = '" . $username . "', password = '" . $password . "', year = " . $year;
    $qrys[$row] = $qry;

    // figure out positions and groups
    $max = $numFields + 3;
    for ( $f = $numFields; $f <= $max; $f += 2 ) {
      if ( $data[$f] ) {
        $group = $data[$f+1];
        if ( strpos( $group, ',' ) !== false || strpos( $group, '-' ) !== false ) {
          $groups = extractList( $group );
          foreach ( $groups as $group_number ) {
            $sql = "insert into th_chidon_staff_assignments 
                    set staff_type_id = " . $data[$f] . ", 
                    group_number = '" . $group_number . "', 
                    staff_id = ";
            $positions[$row][] = $sql;
          }
        } else {
          $sql = "insert into th_chidon_staff_assignments 
                  set staff_type_id = " . $data[$f] . ", 
                  group_number = '" . $group . "', 
                  staff_id = ";
          $positions[$row][] = $sql;
        }
      }
    }
  }
  fclose($handle);
}

//echo "<pre>"; print_r( $qrys ); print_r( $positions ); echo "</pre>"; exit;
mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;

$ids = [];
foreach ( $qrys as $key => $qry ) {
  if ( !mysql_query( $qry ) ) {
    echo "There was an error - " . $qry . "<br />" . mysql_error();
    $success = false;
    break;
  } else {
    $ids[$key] = mysql_insert_id();
  }
}

if ( $success ) {
  foreach ( $positions as $key => $rows ) {
    foreach ( $rows as $sql ) {
      $sql .= '' . $ids[$key];
      if ( !mysql_query( $sql ) ) {
        echo "There was an error - " . $sql . "<br />" . mysql_error();
        $success = false;
        break;
      }
    }
  }
}

if ( $success ) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
  <body>
    <form action="staff_upload.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>