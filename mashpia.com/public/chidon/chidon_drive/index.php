<?php
// routing of family names
$url = $_SERVER['REQUEST_URI'];
$pos = strpos( $url, '/' );
$url_end = substr( $url, $pos + 8 );
$info = explode('/', $url_end);

header("Location: " . $_SERVER['REMOTE_HOST'] . "/chidon/chidon_drive/site/family-single.html?id=" . $info[2]);
exit;