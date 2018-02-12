<html>
    <head>
        <script src="camps/scripts/jquery.tools.min.js"></script>       
        <script>
            var id = 82;
            $.post('ajax/enrollIntoCampaigns.php', {type : 'student', id : school_id}, function( success ) {
                alert( success );
            }); 
        </script>
    </head>
</html>
