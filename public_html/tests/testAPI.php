<?php
// $curl = curl_init('192.168.56.6/lumen/public');
// curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
// curl_setopt( $curl, CURLOPT_HTTPHEADER, array
//     (
//         "Token: 183HXNi6q4Zcl7z0Z8uiJrEgihkklnsz"
//     )
// );
// $result = curl_exec( $curl );
// curl_close( $curl );
// echo $result;
?>
<!DOCTYPE html>
<html>
    <head>
        <script
            src="https://code.jquery.com/jquery-1.12.4.min.js"
            integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
            crossorigin="anonymous"></script>
        <script>
            $.ajax({
                url: 'https://192.168.56.6/symfony/public',
                crossDomain: true, 
                method: "GET",
                success: function( res ) {
                    console.log( res );
                }
            });
        </script>
    </head>

    <body>

    </body>
</html>