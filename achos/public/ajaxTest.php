<html>
    <head>
        
    </head>
    <body>
        <script type="text/javascript">
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);
                } else {
                    alert(xhr.readyState);
                }
            }
            xhr.onprogress = function(e) {
                if (e.lengthComputable) {
                    document.writeln(Math.round((e.loaded / e.total) * 100) + "% Loaded.");
                } else {
                    alert("No progress status available!");
                }
            }
            xhr.open('get', 'ajax/getUserMissionInfo.php?user_id=5670&type=All', true);
            xhr.send(null);
        </script>
    </body>
</html>