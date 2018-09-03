<?php
http_response_code( 302 );
header( 'Location: https://mashpia.com/v2/images/imgsrepo/'. $_GET['img'] );
?>