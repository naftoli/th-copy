<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
    </head>
    <body>
        
    </body>
    <script src="scripts/jquery.min.js"></script>
    <script>
        var html = [];
        /*
        for (var i = 1000; i < 2000; i++) {
            $.post('mobile/reg/ajax/getUser.php', { user : i }, function( success  ) {
                if (success != 0) {
                    html.push( success );
                }
            });
        }
        */
        setTimeout(function() {
            if (i == 2000) {
                var l = html.length;
                for (var n = 0; n < l; n++) {
                    $("body").append( html[n] + '<br />' );
                }
                i++;
            }
        }, 1000);
    </script>
</html>