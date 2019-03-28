<?php
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";

$headers = [
  'first_name',
  'last_name',
  'grade',
  'sweater_size',
  'team',
  'bowling_lane',
  'school_bus',
  'open_air_bus',
  'coach_bus', 
  'sunday_pm_bus',
  'email',
  'cell',
  'address', 
  'walking_counselor', 
  '',
  'walking_group', 
  'counselor', 
  'counselor_bunks', 
  'runner', 
  'runner_bunks', 
  'head_runner',
  '',
  'head_runner_bunks',
  'safety_officer',
  'safety_officer_bunks',
  'director',
  'safety_coordinator',
  'head_counselor',
  'head_counselor_bunks',
  'chaperone',
  'hq'
];

$staff_types = [
  'walking_counselor'   =>  3, 
  'counselor'           =>  6, 
  'runner'              =>  8, 
  'head_runner'         =>  7, 
  'safety_officer'      =>  9, 
  'director'            =>  4, 
  'safety_coordinator'  =>  10, 
  'head_counselor'      =>  5
];

$grade_bunks = [
  'boys'  => [
    4 =>  [1,24],
    5 =>  [25,52],
    6 =>  [53,80], 
    7 =>  [81,92],
    8 =>  [93,102]
  ],
  'girls' => [
    4 =>  [1,28],
    5 =>  [29,60],
    6 =>  [61,80], 
    7 =>  [81,100],
    8 =>  [101,119]
  ]  
];

$qrys = [];
$staff_groups = [];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
  $row = 0;
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    //echo "<pre>"; print_r( $data ); echo "</pre>";
    if ( $row++ < 1 ) continue;
    $username = $data[0] . '' . $data[1];
    $qry = "insert into th_chidon_staff set year = 5779, gender = 'girls', username = '" . $username . "', password = 'shabbaton', ";
    for ( $i = 0; $i < 13; $i++ ) {
      if ( $i == 4 ) continue; // skip teams for now
      $qry .= $headers[$i] . " = '" . $data[$i] . "',";
    }
    $qry = substr( $qry, 0, strlen( $qry ) - 1 );
    $qrys[] = $qry;
    
    if ( $data[13] == 'Yes' ) $staff_groups[] = [$headers[13] => $data[15]];
    if ( $data[16] == 'Yes' ) $staff_groups[] = [$headers[16] => $data[17]];
    if ( $data[18] == 'Yes' ) $staff_groups[] = [$headers[18] => $data[19]];
    if ( $data[20] == 'Yes' ) $staff_groups[] = [$headers[20] => $data[22]];
    if ( $data[23] == 'Yes' ) $staff_groups[] = [$headers[23] => $data[25]];
    if ( $data[27] == 'Yes' ) $staff_groups[] = [$headers[27] => $data[28]];

    if ( $data[26] == 'Yes' ) {
      $grade = $data[2];
      if ( $grade ) {
        $gradeList = '';
        for ( $i = $grade_bunks['girls'][$grade][0]; $i < $grade_bunks['girls'][$grade][1]; $i++ ) {
          $gradeList .= $i . ',';
        }
        $gradeList = substr( $gradeList, 0, strlen( $gradeList ) - 1 );
        $staff_groups[] = [$headers[26] => $gradeList];
      }
    }
  }
  fclose($handle);
}
echo "<pre>"; 
// print_r( $qrys ); 
// print_r( $staff_groups );
echo "</pre>";

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;

foreach ( $qrys as $key => $qry ) {
  //echo $qry . "<br />";
  if ( !mysql_query( $qry ) ) {
    echo "There was an error - " . $qry . "<br />" . mysql_error();
    $success = false;
    break;
  }
  $id = mysql_insert_id();
  //$id = 111;
  if ( isset( $staff_groups[$key] ) ) {
    $groups = $staff_groups[$key];
    $position = key( $groups );
    $list = explode(',', $groups[$position]);
    foreach ( $list as $group ) {
      if ( $group ) {
        $sql = "insert into th_chidon_staff_assignments 
                set staff_type_id = " . $staff_types[$position] . ", 
                group_number = " . $group . ", 
                staff_id = " . $id;
        //echo $sql . "<br />";
        if ( !mysql_query( $sql ) ) {
          echo "There was an error - " . $sql . "<br />" . mysql_error();
          $success = false;
          break;
        }
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
    <form action="staff.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
      <label>Upload your spreadsheet
      <br /><input type="file" name="file" class="file"></label>
      <br /><input type="submit" name="submit" value="upload" />
    </form>
  </body>
</html>