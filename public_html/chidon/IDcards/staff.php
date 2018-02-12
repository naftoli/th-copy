<?php
ini_set('display_errors',1);
$staff = array(
    /*
    'kitchen_manager'   =>  array(
        'title'     =>  'Kitchen Manager',
        'location'  =>  'Chidon Shabbaton Staff',
        'names'      =>  array(
            'Mordy Zirkind',
            'Shaya Shpielman',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        )
    ),
    */
    'photographer'  =>  array(
        'title'     =>  'Photographer',
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            '',
            ''
        )
    ),
    'director'  =>  array(
        'title'     =>  'On Site Director',
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            'Rabbi Zash Itkin',
            'Shaya Itkin',
            'Mendy Heber',
            'Mendy Flamer'
        )
    ),
    /*
    'logistics'  =>  array(
        'title'     =>  'Logistics', 
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            'Shaya Shpileman',
            'Mendy Zirkind',
            'Dovy Brickman'
        )
    ),
    */
    'head_waiters'  =>  array(
        'title'     =>  'Head Waiters', 
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            'Leibel Kaplan',
            'Shimon Partouche'
        )
    ),
    /*
    'waiters'  =>  array(
        'title'     =>  'Waiters', 
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            'Mordy Zirkind',
            'Shaya Shpielman',
            'Shimon Partouch',
            'Leibel Kaplan',
            'Dovi Brickman',
            'Yisroel friend'
        )
    ),
    */
    'heads'     =>  array(
        'title'     =>  'Head Counselors',
        'location'  =>  'Chidon Shabbaton Staff',
        'names'     =>  array(
            'Shmuli Ceitlin - 4th',
            'Shmuli Bronstein - 5th',
            'Aba Raichik - 6th',
            'Tzviki Pruss - 7th',
            'Levi Mishulovin - 8th'
        )
    )
)
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>ID Cards</title>
        <style>
            @font-face {
                font-family: gotham;
                src: url('fonts/GOTHAM-BOOK.OTF');
            }
            @font-face {
                font-family: gotham-med;
                src: url('fonts/GOTHAM-MEDIUM.OTF');
            }
            @font-face {
                font-family: heb;
                src: url('fonts/FbReforma-Medium.otf');
            }
            .card {
                width: 4.5in;
                height : 5.5in;
                margin: auto;
                text-align: center;
                font-family: gotham;
            }
            .topSec {
                width: 4.25in;
                height: 1in;
                text-align: center;
                vertical-align: middle;
                margin: auto;
                font-size: 24pt;
                padding-top: 10px;
                color: #fff;
                background-color: #000;
            }
            .personal {
                height: 0.7in;
                margin-top: 30px;
                font-size: 14pt;
            }
            .name {
                color: #7e0000;
                font-face: gotham-med;
                font-size: 14pt;
            }
            .bottomSec {
                width: 4.25in;
                height: 0.5in;
                margin: auto;
                font-size: 14pt;
                background-color: #000;
                margin-top: 1in;
            }
            .grade4 {
                background-color: #120941;
                color: #FFF;
            }
            .grade5 {
                background-color: #00270a;
                color: #FFF;
            }
            .grade6 {
                background-color: #e89221;
                color: #FFF;
            }
            .grade7 {
                background-color: #febe10;
                color: #FFF;
            }
            .grade8 {
                background-color: #382151;
                color: #FFF;
            }
        </style>
    </head>
    
    <body>
        <?php
        foreach ($staff as $type => $info) {
            if ($type == 'heads') $i = 4;
            foreach ($info['names'] as $name) {
                ?>
                <div class="card">
                    <div class="topSec
                    <?php
                    if ($type == 'heads') {
                        echo " grade" . $i;
                    }
                    ?>
                    ">
                        <?=$info['title']?>
                    </div>
                    <br />
                    <img src="chidon.png" width="400" />
                    <div class="personal">
                        <div class="name">
                            <?=$name?>&nbsp;
                        </div>
                        <?php if ($type == 'volunteer' || $type == 'photographer') echo $info['title'] . "<br />"; ?>
                        <?=$info['location']?><br />
                        Brooklyn, NY
                    </div>
                    <div class="bottomSec
                    <?php
                    if ($type == 'heads') {
                        echo " grade" . $i++;
                    }
                    ?>
                    "></div>
                </div>
                <div style="page-break-after: always"></div>
                <?php
            }
        }
        ?>
    </body>
</html>