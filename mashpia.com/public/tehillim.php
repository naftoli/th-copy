<?php
require 'db.php';
$date = $_GET['d'];
$info = array();
$sql = "select s.school_name, c.class_grade, c.class_sub, u.first, u.last, dtmm.done_qty from date_tasks_marks dtmm 
        join date_tasks dt using (date_task_id)
        join date_tasks_missions dtm using (date_tasks_mission_id)
        join users u using (user_id)
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id 
        where dtm.start_date = " . $date . " 
        and dtm.end_date = " . $date . " 
        and dtm.subject_id = 1
        and dt.grid_id = 64
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>"; 
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Shabbos Mevorchim Tehillim Shvat</title>
      <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
      <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.css">
      <style>
        body {
            font-size: 12px;
        }
        .container {
            padding-left: 4%;
            padding-right: 5%;
        }
      </style>
   </head>
   <body>

      <br>
      <div class="container">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Shabbos Mevorchim Tehillim Shvat</h3>
            </div>
            <div class="panel-body">
               <table id="table" class="table table-striped table-condensed">
                  <thead>
                    <tr>
                        <th>School</th>
                        <th>Class</th>
                        <th>Student</th>
                        <th>Kapitelach</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($info as $child) {
                        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                        echo "<tr><td>" . $child['school_name'] . "</td><td>" . $grade . "</td><td>" .
                            $child['first'] . ' ' . $child['last'] . "</td><td>" . $child['done_qty'] . "</td></tr>";
                    }
                    ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
      <script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
      <script>
         $('#table').DataTable({
            paging : false
        });
      </script>
   </body>
</html>