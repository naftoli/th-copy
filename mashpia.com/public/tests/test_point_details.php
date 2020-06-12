<!DOCTYPE html>
<html>
    <head>
        <script
            src="https://code.jquery.com/jquery-1.12.4.min.js"
            integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
            crossorigin="anonymous"></script>
        <script>
            const params = new URLSearchParams(location.href.search)
            const start = '2019-03-10'
            const end = '2019-05-10'
            const user = params.get('user')
            $.post('/api/points/details.php', { start: start, end: end, user_id: user }, function(result) {
                const res = JSON.parse(result)
                console.log(res)
                $("body").append(res)
            })
        </script>
    </head>
    <body>
    
    </body>
</html>