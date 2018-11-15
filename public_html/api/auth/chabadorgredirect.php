<?php
require_once( __DIR__ . '/../../../includes/globals.php' );

$domain = 'http' . $_SERVER['HTTPS'] ? 's' : '' . '://' . $_SERVER['HTTP_HOST'];

if ( $development )
    $domain = 'http://localhost:3000';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Chabad.org login redirect</title>

    <script>
        var key = <?= isset($_GET['key']) ? json_encode($_GET['key']) : 'false'; ?>;
        window.opener && window.opener.postMessage({ key: key }, '*');
    </script>

    <style>
        body{ background: #fcfcfc; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0px; }
    </style>
</head>
<body>
    <img src='sending.gif' alt='sending'>
    <strong>Sending key to Tzivos Hashem.</strong>
</body>
</html>
