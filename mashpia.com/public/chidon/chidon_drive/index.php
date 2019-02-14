<?php
// routing of family names
$url = $_SERVER['REQUEST_URI'];
$pos = strrpos( $url, '/' );
$url_end = substr( $url, $pos + 1 );
if ( $url_end ) header("Location: " . $_SERVER['REMOTE_HOST'] . "/chidon/chidon_drive/site/family-single.html?id=" . $url_end);
else header("Location: /site");
exit;