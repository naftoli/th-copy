<html>
    <head>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
    </head>
    <body>
        <input type='text' name='test' class='test' disabled />
    </body>
    <script>
        $(".test").hover( function() {
            alert($(this).attr('disabled'));
            $(this).attr('disabled',false);
        });
    </script>
</html>