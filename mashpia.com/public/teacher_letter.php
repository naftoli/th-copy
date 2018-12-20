<?php
// $admin_auth = array('school');
// require 'header.php';
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/api/header/header.php" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" integrity="sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP" crossorigin="anonymous">
    <title>Teacher Account Letter Printouts</title>
    <style>
        @media screen {
            body {
                width: 8.5in;
                margin: 0 auto;
            }
            .letter {
                margin: .5in 0px;
                border: 1px solid #ccc;
            }

            .print-btn {
                text-align: center;
            }
        }

        ul.checkboxes,
        ul.dashed {
            list-style: none;
            padding-left: 1em;
        }

        ul.checkboxes > li,
        ul.dashed > li {
            padding-top: 0.25rem;
            position: relative;
        }

        ul.dashed > li:before,
        ul.checkboxes > li:before {
            font: normal normal normal 14px/1 Font Awesome\ 5 Free;
            font-weight: 600;   font-size: 1em;
            
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            
            margin-top: 0.1rem; margin-right: 5px;
            
            position: absolute; left: -1.3em;
        }

        ul.checkboxes > li:before { content: "\F00C"; }
        ul.dashed > li:before { content: "\F061"; }
        
        h1 { 
            text-align: center;
        }
        .letter ul {
            margin-left: 0.5in;
        }
    @media print {
        .letter {
            page-break-after: always;
            height: 10in;
        }
        .no-print {
            display: none;
        }
    }
    </style>
</head>
<body>
    <h1 class="no-print">Teacher Account Letters Printout</h1>
    <div class="no-print print-btn">
        <button onclick="window.print()">
            <i class='fas fa-print'></i> Print
        </button>
    </div>
    <?php
        $teachers = [];

        if ( $current_user->login->code == 'HQ' ) {
            $schools = \School::all([ 'conditions' => 'test_school = 0' ]);
        } else if ( $current_user->login->code == 'INST' ) {
            $schools = \School::all([ 'conditions' => 'inst_id = '.$current_user->login->id ]);
        } else if ( $current_user->login->code == 'BC' ) {
            $schools = [ $current_user->login->model ];
        }
        // print for each base
        foreach ($schools as $school) {
            // and each bases platoons
            foreach( $school->platoons as $platoon ) {
                // for each staff member that we have
                foreach( $platoon->staff( true ) as $teacher ) {
                    $teachers[ $teacher['admin_id'] ]['teacher'] = $teacher;
                    $teachers[ $teacher['admin_id'] ]['classes'][] = [
                        'base' => $school->name,
                        'platoon' => $platoon->name()
                    ];
                }
            }
        }

        foreach( $teachers as $info ) { 
            $teacher = $info['teacher'];
            $classes = $info['classes'];
            ?>
            <div class="letter">
                Dear <?= $teacher['title']; ?> <?= $teacher['first']; ?> <?= $teacher['last']; ?>, <br />
                <br />
                Do you use <strong>chinuch.org</strong>? Do you love it?<br />
                <br />
                Do you wish there was a resource website with ready-made <strong>Chassidishe</strong> Resources?<br />
                <br />
                BARUCH HASHEM! YOUR WISH HAS COME TRUE !<br />
                <br />
                <strong>Tzivos Hashem</strong> has created a special Account for YOU with incredible Resources and NEW Features!<br />
                To access the resources, simply go to <strong><a href='https://mashpia.com/new'>TzivosHashem.com</a></strong> and sign in with your username and password. The resources are updated weekly, so be sure to check back often.<br />
                <br />
                <strong>Username: <?=$teacher['username']?></strong><br />
                <strong>E-Mail: <?=$teacher['email']?></strong><br />
                <strong>Passwrod: <?=$teacher['password']?></strong><br />
                <br />
                We would love to hear your feedback. Please contact us at <a href='mailto:cth@tzivoshashem.org'>cth@tzivoshashem.org</a>.<br />
                <br />
                <strong>Your Platoons (Classes):</strong>
                <ul class='dashed'>
                <?php foreach( $classes as $platoon ) { ?>
                    <li><?= $platoon['base'] ?> - <?= $platoon['platoon'] ?></li>
                <?php } ?>
                </ul>

                <strong>Your Account Features:</strong>
                <ul class='checkboxes'>
                    <li><strong>Weekly Resources</strong> - Spanning from the weekly Parsha, Niggunim, YomimTovim& more!</li>
                    <li><strong>All Resources for <?= \GlobalSettings::getCurrentYear() ?></strong> - Includes teacher’s guides and student worksheets with:
                        <ul class='dashed'>
                            <li>Chassidishe Yomim Tovim Resources</li>
                            <li>Yom Tov Resources</li>
                            <li>Niggunim Resources</li>
                            <li>Parsha Resources</li>
                            <li>Tefillah Resources</li>
                            <li>Rebbe Resources</li>
                            <li>Sefer Hazichronos “Roots” Resources</li>
                            <li>Chitas Resources</li>
                            <li>Tanya Baal Peh Resources</li>
                        </ul>
                    </li>
                    <li><strong>Teacher’s Achievement Card Incentive</strong> - Students can earn “Achievement Cards” in YOUR classroom to earn points and buy prizes on their Tzivos Hashem Store!</li>
                    <li><strong>Prize Store</strong> - Teachers can upload prizes to their Base that students in their Platoon can purchase from their Tzivos Hashem Store!</li>
                    <li><strong>Tzivos Hashem Calendar</strong> - Calendar with all upcoming Tzivos Hashem happenings!</li>
                    <li><strong>Chidon Resources</strong></li>
                    <li><strong>Tzivos Hashem Platoon Missions</strong> - Mark off your student’s in-class missions using a personalizable dashboard for each platoon.</li>
                </ul>
                <br />
                <br />
                Sincerely,<br/>
                Your Base Commander, <?=$current_user->first ?> <?=$current_user->last ?>.
                <span style="float: right">Tzivos Hashem HQ</span>
            </div>
    <?php } ?>
</body>
</html>

