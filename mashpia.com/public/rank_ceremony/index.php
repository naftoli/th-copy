<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if ( isset($_GET['id']) ) {
    unset($schools);
    $schools = [
        $_GET['id'] => 'School Name'
    ];
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
        let date = '';
        while (date != 'current' && date != 'previous')
            date = prompt("Do you want to generate ranks base on current dates or previous dates? (enter 'current' or 'previous')")
        const prev = date === 'previous' ? 1 : 0

        function createFile(school, prev) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: "createFile.php?prev=" + prev,
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

        let i = 1;
        let p = [];
        const schools = <?= json_encode($schools) ?>;
        for (let school in schools) {
            p[i++] = createFile(school, prev)
        }
        Promise.all([...p])
            .then(values => {
                console.log(values)
                location.href = 'createZip.php'
            })
            .catch(error => {
                console.log(error)
            })
    </script>
</head>
</html>