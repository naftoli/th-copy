<?php
// routing of family names
$url = $_SERVER['REQUEST_URI'];
$host = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'tzivos.local';
$pos = strrpos( $url, '/' );
if ( $pos !== false ) {
    $url_end = substr( $url, $pos + 1 );
    header("Location: https://" . $host . "/site/family-single.html?id=" . $url_end);
    exit;
} else {
    switch ( $url ) {
        case '/site/intro.html':
        case '/site/setup.html':
            header("Location: https://" . $host . $url );
            break;
        default:
            header("Location: https://" . $host . "/site");
            break;
    }
}
exit;