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
        } else if ($host == 'mashpia.com') {
            header("Location: https://" . $host . "/chidonOld/chidon_drive/site/login.html");
            exit;
        }
        header("Location: https://" . $host . "/site/login.html?a=" . $_GET['a']);
        exit;
    }
    // Don't redirect when request is already for enrollNew.html — serve the file to avoid redirect loop
    if ( strpos($url_path, '/site/enrollment/enrollNew.html') !== false ) {
        $file = __DIR__ . '/site/enrollment/enrollNew.html';
        if ( is_file( $file ) ) {
            header( 'Content-Type: text/html; charset=utf-8' );
            readfile( $file );
            exit;
        }
    }
    switch ( $url ) {
        case '/site/intro.html':
        case '/site/setup.html':
            header("Location: https://" . $host . $url);
            break;
        // case '/site/enrollment/enrollNew.html':
        //     header("Location: https://" . $host . $url);
        //     break;
        default:
            header("Location: https://" . $host . "/site");
            break;
    }
}
exit;

