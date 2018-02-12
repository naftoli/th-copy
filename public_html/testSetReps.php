<html>
    <head>
        <script type="text/javascript" src="scripts\jquery-1.8.3.js"></script>
        <script>
            $(function() {
                var year = 5778;
                var school = 82;
                $.post('ajax/setReps.php', { year : year, school : school }, function( info ) {
                    console.log( info );
                });
            });
        </script>
    </head>
    <body>
        
    </body>
</html>