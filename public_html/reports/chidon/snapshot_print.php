<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            @media print {
               .noPrint {
                   display: none;
                }
            }
            body {
                font-family: Verdana;
            }
            tr, th, td {
                font-size: 14px;
                padding-top: 10px;
                padding-bottom: 10px;
                font-family: Verdana;
            }
        </style>
    </head>
    <body>
        <div id="printedReport"></div>
    </body>
    <script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
    <script>
        $(function() {
            // get data from url
            var url = location.href;
            var pos = url.indexOf('=');
            if (pos) {
                var info = url.substr( pos+1 );
                var data = JSON.parse( decodeURI( info ) );
                data.printed = 1;
                $.post('ajax/snapshot.php', data, function( data ) {
                    $("#printedReport").html( data );
                });
            }
        });
    </script>
</html>