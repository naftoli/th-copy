<html>
    <head>
        <meta charset="UTF-8"> 
        <style type="text/css">
            tr, th, td {
                font-family: Helvetica;
                font-size: 12px;
                border: 1px grey solid;
            }
        </style>   
    </head>
    <body>
    <?
    $ladder = 5;
    require_once 'ladder.php';
    generateReport();
    ?>   
    </body>
</html>