<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    select u.*, c.*, aa.admin_id from users u 
    join admin_auths aa on aa.id = u.user_id 
    join classes c using (class_id) 
    where u.school_id = :id
    order by class_grade, class_sub, hachayol desc, last, first
");

$users = [];
foreach ($schools as $id => $name) {
    $stmt->execute(['id' => $id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $users[$id][$row['class_grade']][$row['class_sub']][] = $row;
    }
}

$stmt = $MASHPIA_DB->prepare("
    select u.*, s.school_name from users u 
    join schools s using (school_id) 
    join admin_auths aa on aa.id = u.user_id 
    where u.hachayol = 1 
    and u.user_registered > 0 
    and aa.admin_id = :admin_id
");

$hachayols = [];
foreach ($users as $school_id => $more) {
    foreach ($more as $grade => $other) {
        foreach ($other as $sub => $more) {
            foreach ($more as $user) {
                $receives_hachayol = intval($user['hachayol']) ? 'yes' : 'no';
                if ($receives_hachayol == 'no') {
                    // find out which child(ren) do get it
                    $stmt->execute(['admin_id' => $user['admin_id']]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $hachayols[$row['user_id']] = $row['first'] . ' ' . $row['last'] . ' (' . $row['school_name'] . ')';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
      <title>Hachayol Report</title>
<!--      <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>-->
<!--      <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>-->
<!--      <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>-->
<!--      <link-->
<!--        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"-->
<!--        rel="stylesheet"-->
<!--        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"-->
<!--        crossorigin="anonymous" />-->
<!--      <script-->
<!--        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"-->
<!--        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"-->
<!--        crossorigin="anonymous"></script>-->
      <style>
        #main {
          margin-left: 2%;
          margin-right: 2%;
        }
        tr, th, td {
          font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
          font-size: 12px;
          padding: 5px;
          border-bottom: 1px solid lightgrey;
        }
      </style>
    </head>
    <body>
    <div id="main">
    <?php
    foreach ($users as $school_id => $more) {
        foreach ($more as $class_grade => $other) {
            foreach ($other as $class_sub => $more) {
                $grade = $class_grade . ($class_sub ? '-' . $class_sub : '');
                echo "<h3>" . $schools[$school_id] . "</h3><hr />";
                ?>
                <table>
                    <tr>
                        <th>Grade</th>
                        <th>Hebrew Name</th>
                        <th>Student</th>
                        <th>Family ID</th>
                        <th>Receives Hachayol</th>
                        <th>Who gets Hachayol in Family</th>
                    </tr>
                <?php
                $total = 0;
                foreach ($more as $user) {
                    $children = [];
                    $receives_hachayol = intval($user['hachayol']) ? 'yes' : 'no';
                    if ($receives_hachayol == 'no') {
                      // find out which child(ren) do get it
                      $stmt->execute(['admin_id' => $user['admin_id']]);
                      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach ($rows as $row) {
                        $children[] = $row['first'] . ' ' . $row['last'] . ' (' . $row['school_name'] . ')';
                      }
                    }
                    else $total++;
                    echo "<tr><td>" . $grade . "</td><td>" . $user['first_he'] . ' ' . $user['last_he'] . "</td><td>" .
                        $user['first'] . ' ' . $user['last'] . "</td><td>" . $user['admin_id'] . "</td><td>" .
                        $receives_hachayol . "</td><td>" . implode("<br />", $children) . "</td></tr>";
                }
                echo "<tr><th>Total:</th><th>$total</th><th colspan='4'></th></tr></table>";
                echo "<div style='page-break-after: always;'></div>";
            }
        }
    }
    ?>
    </div>
    </body>
<!--    <script type="text/babel">-->
<!--      const { useState, useEffect } = React-->
<!--      const { createRoot } = ReactDOM-->
<!---->
<!--      const Table = () => {-->
<!--        const schools = --><?php //= json_encode($schools) ?>//;
//        const users = <?php //= json_encode($users) ?>//;
//        const hachayols = <?php //= json_encode($hachayols) ?>//;
//
//        return (
//          {Object.keys(users).map(school_id => (
//            Object.keys(users[school_id]).map(grade => (
//              Object.keys(users[school_id][grade]).map(sub => (
//                <div style={{ pageBreakAfter: 'always' }}>
//                  <h3 className="mt-4">{schools[school_id]}</h3><hr />
//                  <table className="table table-striped">
//                    <thead>
//                      <tr>
//                        <th>Grade</th>
//                        <th>Hebrew Name</th>
//                        <th>Student</th>
//                        <th>Family ID</th>
//                        <th>Receives Hachayol</th>
//                        <th>Who gets Hachayol in Family</th>
//                      </tr>
//                    </thead>
//                    <tbody>
//                    {users[school_id][grade][sub].map(user) (
//                      <tr>
//                        <td>
//                          {grade}{sub ? '-' + sub : ''}
//                        </td>
//                        <td>
//                          {user.first_he} {user.last_he}
//                        </td>
//                        <td>
//                          {user.first} {user.last}
//                        </td>
//                        <td>
//                          {user.admin_id}
//                        </td>
//                        <td>
//                          {user.hachayol ? 'yes' : 'no'}
//                        </td>
//                        <td>
//                          {user.hachayol ? '' : hachayols[user.user_id].join('<br />')}
//                        </td>
//                      </tr>
//                    )}
//                    </tbody>
//                  </table>
//                </div>
//              ))
//            ))
//          ))}
//        )
//      }
//
//      const container = document.getElementById('main')
//      const root = createRoot(container)
//      root.render(<Table/>) // render the app
//    </script>
</html>
