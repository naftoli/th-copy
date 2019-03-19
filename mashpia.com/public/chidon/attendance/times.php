<?php
require_once '../../db.php';
require_once '../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ( isset( $_POST['day'] ) ) {
  //echo "<pre>"; print_r( $_POST ); echo "</pre>";

  $gender = strtolower( $_POST['gender'] );
  $day = mysql_real_escape_string( strtolower( $_POST['day'] ) );
  $time = mysql_real_escape_string( $_POST['time'] );
  $desc = mysql_real_escape_string( $_POST['description'] );
  $type = mysql_real_escape_string( $_POST['type'] );
  $bunks = [];
  foreach ( $_POST['bunks'] as $k => $v ) {
    $bunks[] = $k;
  }

  if ( $gender == 'f' ) {
     $dates = [
        'thursday' =>  '2019-03-28', 
        'friday'   =>  '2019-03-29',
        'motzei shabbos'  =>  '2019-03-30', 
        'sunday'   =>  '2019-03-31'
     ];
  } else if ( $gender == 'm' ) {
    $dates = [
       'thursday' =>  '2019-04-04', 
       'friday'   =>  '2019-04-05',
       'motzei shabbos'  =>  '2019-04-06', 
       'sunday'   =>  '2019-04-07'
    ];
  }

  // create attendance time in database
  if ( $type == 'bunk' ) {
    foreach ( $bunks as $bunk ) {
      $qry = "insert into th_chidon_attendance_times 
              set day_of_week = '" . $day . "', 
              att_time = '" . $dates[$day] . " " . $time . ":00', 
              att_type = '" . $type . "', 
              att_type_id = " . $bunk . ", 
              description = '" . $desc . "', 
              gender = '" . strtoupper( $gender ) . "', 
              year = " . $year;
      //echo $qry . "<br />";
      mysql_query( $qry ) or die( mysql_error() . "<br />" . $qry );
    }
  } else {
    $qry = "insert into th_chidon_attendance_times 
              set day_of_week = '" . $day . "', 
              att_time = '" . $dates[$day] . " " . $time . ":00', 
              att_type = '" . $type . "', 
              description = '" . $desc . "', 
              gender = '" . strtoupper( $gender ) . "', 
              year = " . $year;
    //echo $qry . "<br />";
    mysql_query( $qry ) or die( mysql_error() . "<br />" . $qry );
  }
}

// get attendance times
$info = [];
$sql = "select * from th_chidon_attendance_times where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <title>Setup Attendance Times</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="manifest" href="manifest.json">
    <link type="text/css" rel="stylesheet" href="./css/bulma.min.css" />
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 5px;
      }
    </style>
  </head>

  <body>
    <section class="section is-medium">
      <div class="container">
        <div class="title">Create Attendance Times</div>
        <form action="times.php" method="post">

          <div class="field">
            <div class="control">
              <label class="radio">
                <input type="radio" name="gender" value="f" checked>
                Girls Shabbaton
              </label><br />
              <label class="radio">
                <input type="radio" name="gender" value="m">
                Boys Shabbaton
              </label>
            </div>
          </div>

          <div class="field">
            <label class="label">Day of Week</label>
            <div class="control">
              <div class="select is-primary">
                <select name="day">
                  <option>Thursday</option>
                  <option>Friday</option>
                  <option>Motzei Shabbos</option>
                  <option>Sunday</option>
                </select>
              </div>
          </div>
          <br />

          <div class="field">
            <label class="label">Time</label>
            <div class="control">
              <input type="time" name="time" class="input is-primary" style="width: 140px;" />
            </div>
          </div>
          <br />

          <div class="field">
            <label class="label">Type of Attendance</label>
            <div class="control">
              <label class="radio">
                <input type="radio" name="type" class='type' value='walk' checked>
                Walking Groups
              </label>
              <label class="radio">
                <input type="radio" name="type" class='type' value='bunk'>
                Bunks
              </label>
            </div>
          </div>

          <div class="field" id="bunks" style="display: none;">
            <label class="label">Choose Bunks</label>
            <div class="columns is-mobile">
              <?php
              $i = 1; // keep track of when to make a break
              for ( $j = 1; $j <= 120; $j++ ) {
                echo "<div class='column is-one-fifth'><input type='checkbox' class='checkbox' name='bunks[" . $j . "]' /> " . $j . "</div>";
                if ( $i++ % 5 == 0 ) echo "</div><div class='columns is-mobile'>";
              }
              ?>
            </div>
          </div>

          <div class="field">
            <label class="label">Description</label>
            <div class="control">
              <textarea name="description" class="textarea is-primary" placeholder="Description"></textarea>
            </div>
          </div>

          <div class="field is-grouped">
            <div class="control">
              <button class="button is-link">Submit</button>
            </div>
            <div class="control">
              <button class="button is-text">Cancel</button>
            </div>
          </div>

        </form>
      </div>
    </section>
    <hr />
    <section class="section is-medium">
      <div class="container">
        <div class="title">Existing Attendance Times</div>
        <table class="table is-bordered">
          <thead>
            <tr>
              <th>Day of Week</th>
              <th>Date / Time</th>
              <th>Type of Attendance</th>
              <th>Bunk</th>
              <th>Description</th>
              <th>Action</th>
            </tr>
          </thead>
          <?php
          foreach ( $info as $row ) {
            echo "<tr><tbody><td>" . $row['day_of_week'] . "</td><td>" . $row['att_time'] . "</td><td>" . $row['att_type'] . "</td><td>";
            if ( $row['att_type'] == 'bunk' ) echo $row['att_type_id'];
            echo "</td><td>" . $row['description'] . "</td><td><a href='#' onclick='deleteTime(" . $row['att_time_id'] . ");'>delete</a></td></tbody></tr>";
          }
          ?>
        </table>
      </div>
    </section>
  </body>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script>
    $(function() {
      $(".type").click( function() {
        if ( $(this).is(":checked") ) {
          let type = $(this).val();
          if ( type == 'bunk' ) $("#bunks").show();
          else $("#bunks").hide();
        }
      });
    });

    function deleteTime( id ) {
      $.post('ajax/deleteTime.php', { id : id }, function( result ) {
        let res = JSON.parse( result );
        if ( res.success ) {
          alert('Deleted');
          location.href = "times.php";
        } else {
          alert( 'Error deleting: ' + res.error );
        }
      });
    }
  </script>
</html>