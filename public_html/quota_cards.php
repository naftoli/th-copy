<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                background-image: url('WWTC-Cards-4-up.jpg');
                background-repeat: no-repeat;
            }
            .card {
                position: absolute;
            }
            .card_1 {
                margin-left: 20px;
                margin-top: 150px;
            }
            .card_2 {
                margin-left: 420px;
                margin-top: 150px;
            }
            .card_3 {
                margin-left: 20px;
                margin-top: 650px;
            }
            .card_4 {
                margin-left: 420px;
                margin-top: 650px;
            }
        </style>
    </head>

    <body>
        <? for ($i = 1; $i < 5; $i++) : ?>
            <div class="card card_<?=$i?>">
                Grade: <?=$grade?><br />
                Name: <?=$name?><br />
                Quota: <?=$quota?><br />
                Minutes: <?=$minutes?><br />
                <br />
                Month of <?=$month?><br />
                Parent's Signature:<br /><br /><br />___________________________________
            </div>
        <? endfor; ?>
    </body>
</html>