<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

if ( isset($_GET['id']) ) {
    unset($schools);
    $schools = [
        $_GET['id'] => 'School Name'
    ];
}

$submit = false;
if (isset($_POST['submit'])) {
    $submit = true;
    $from = explode('-', $_POST['fromDate']);
    $to = explode('-', $_POST['toDate']);
    $start = gregoriantojd($from[1], $from[2], $from[0]);
    $end = gregoriantojd($to[1], $to[2], $to[0]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
    <script>
      // let date = '';
      // while (date != 'current' && date != 'previous')
      //     date = prompt("Do you want to generate ranks base on current dates or previous dates? (enter 'current' or 'previous')")
      // const prev = date === 'previous' ? 1 : 0
      const submit = <?= $submit ?>;

      function createFile(school) {
        const start = <?= $start ?>;
        const end = <?= $end ?>;
        return new Promise((resolve, reject) => {
          $.ajax({
            url: `createFileOT.php?start=${start}&end=${end}`,
            type: 'POST',
            data: {
              school: school,
            },
            success: function (data) {
              resolve(data)
            },
            error: function (error) {
              reject(error)
            },
          })
        })
      }

      if (submit) {
        let i = 1;
        let p = createFile(255)
        p.then(values => {
          console.log(values)
          location.href = 'createZip.php'
        })
        .catch(error => {
          console.log(error)
        })
      }
    </script>
</head>
<?php if (!isset($_POST['submit'])) : ?>
    <form action="" method="post">
        From date: <input type="date" name="fromDate" /><br />
        To date: <input type="date" name="toDate" />
        <input type="submit" name="submit" value="submit" />
    </form>
<?php endif; ?>
</html>
