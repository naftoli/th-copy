<!DOCTYPE html>
<html>
    <head>
        <script src="scripts/jquery-1.8.3.js"></script>
        <script>
            var users = [];
            for (var i = 15; i < 30; i++) users.push(i);
            $.post('http://www.mashpia.com/ajax/enrollIntoCampaigns.php', { id : users }, function (success) {
                if (success != '') alert(success);
            });
        </script>
    </head>
    <body>
        
    </body>
</html>