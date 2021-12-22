<?php
// routing of family names
$url = $_SERVER['REQUEST_URI'];
if ($_SERVER['HTTP_HOST'] == 'localhost') $host = "localhost";
else $host = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'tzivos.local';
$pos = strrpos( $url, '/' );
$url_end = substr( $url, $pos + 1 );
if ( $url_end && is_numeric( $url_end ) ) {
    header("Location: https://" . $host . "/site/family-single.html?id=" . $url_end);
    exit;
} else {
    if ( strpos($url, '/setup') !== false ) {
        if ($host == 'tzivos.local') {
            header("Location: http://" . $host . "/chidonOld/chidon_drive/site/login.html");
            exit;
        }
        header("Location: https://" . $host . "/site/login.html");
        exit;
    }
    switch ( $url ) {
        case '/site/intro.html':
        case '/site/setup.html':
            header("Location: https://" . $host . $url);
            break;
        default:
            header("Location: https://" . $host . "/site");
            break;
    }
}
exit;

