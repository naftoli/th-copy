<html>
<head>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        const data = {
            'new_account': 1,
            'serial_number': 0,
            'first_name': 'naft',
            'last_name': 'raps',
            'email_address': 'nafto@gmail.com',
            'tasks': [3, 5, 7]
        }
        $.post('createAccounts.php', { data }, function( result ) {
            const res = JSON.parse(result)
            if (!res.success) alert(res.error)
        })
    </script>
</head>
</html>
