<?php
// routing of family names
$url = $_SERVER['REQUEST_URI'];
$host = isset( $_SERVER['REMOTE_HOST'] ) ? $_SERVER['REMOTE_HOST'] : 'tzivos.local';
$pos = strrpos( $url, '/' );
$url_end = substr( $url, $pos + 1 );
if ( $url_end ) header("Location: https://" . $_SERVER['REMOTE_HOST'] . "/site/family-single.html?id=" . $url_end);
else header("Location: https://" . $_SERVER['REMOTE_HOST'] . "/site");
exit;