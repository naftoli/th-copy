<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    header("Location: /reports/");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Shterna Custom Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="/mobile/css/lib/animate.css" />
    <style>
    /* spinner */
    .spinner { margin: 100px auto; width: 40px; height: 40px; position: relative; }
    .cube1, .cube2 { background-color: #333; width: 15px; height: 15px;position: absolute; top: 0; left: 0; -webkit-animation: sk-cubemove 1.8s infinite ease-in-out; animation: sk-cubemove 1.8s infinite ease-in-out; }
    .cube2 { -webkit-animation-delay: -0.9s; animation-delay: -0.9s; }
    @-webkit-keyframes sk-cubemove {
        25% { -webkit-transform: translateX(42px) rotate(-90deg) scale(0.5) }
        50% { -webkit-transform: translateX(42px) translateY(42px) rotate(-180deg) }
        75% { -webkit-transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5) }
        100% { -webkit-transform: rotate(-360deg) }
    }
    @keyframes sk-cubemove {
        25% { transform: translateX(42px) rotate(-90deg) scale(0.5); -webkit-transform: translateX(42px) rotate(-90deg) scale(0.5); }
        50% { transform: translateX(42px) translateY(42px) rotate(-179deg); -webkit-transform: translateX(42px) translateY(42px) rotate(-179deg);} 
        50.1% { transform: translateX(42px) translateY(42px) rotate(-180deg); -webkit-transform: translateX(42px) translateY(42px) rotate(-180deg);} 
        75% { transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5); -webkit-transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5);} 
        100% { transform: rotate(-360deg); -webkit-transform: rotate(-360deg); }
    }
    /* other */
    .row { margin-top: 2.5%; }
    #content{ display: none; }
    p.total {font-size: 2.5em;text-align: center;}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="/">Mashpia.com</a>
    </nav>
    <div class="spinner">
        <div class="cube1"></div>
        <div class="cube2"></div>
    </div>
    <div class="container animated fadeIn" id="content">
        <div class="row">
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Kapitalach</h5>
                        <p class="card-text total" id="total_kapitalach"></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Minutes</h5>
                        <p class="card-text total" id="total_minutes"></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Chayolim</h5>
                        <p class="card-text total" id="total_chayolim"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">By Month</h5>
                        <div id="minutes">
                            <canvas id="myChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
    <script src="/world/countUp.js"></script>
    <script>
        $.get("ajax/shterna_year_long_report.php", function( response ){
            var options = { useEasing: true, useGrouping: true, separator: ',', decimal: '.' };
            new CountUp("total_kapitalach", 0, response.total_kapitalach, 0, 2.5, options).start();
            new CountUp("total_minutes", 0, response.total_minutes, 0, 2.5, options).start();
            new CountUp("total_chayolim", 0, response.total_chayolim, 0, 2.5, options).start();

            new Chart( $("#myChart"), {
                type: 'bar',
                data: {
                    labels: Object.keys(response.kapitalach),
                    datasets: [{
                        label: "Kapitalach",
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, .5)',
                        data: Object.values(response.kapitalach),
                        borderWidth: 1
                    }, {
                        label: "Minutes",
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, .5)',
                        data: Object.values(response.minutes),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                }
            });

            $(".spinner").hide();
            $("#content").show();
            console.log(response);
        });
    </script>
</body>
</html>