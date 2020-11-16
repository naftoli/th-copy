<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
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
        function createFile() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: "createGenerals.php",
                    type: 'POST',
                    success: function (data) {
                        resolve(data)
                    },
                    error: function (error) {
                        reject(error)
                    },
                })
            })
        }

        createFile()
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
