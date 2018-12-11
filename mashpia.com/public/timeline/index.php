<?php
$info = array(
  'May 2018'   =>  array(
      "Medal Board Visual Update",
      "Visual Progress Reports (All Campaigns) on Mobile Site / App",
      "Leader Board",
  ),
  'June 2018'  =>  array(
      "Mobile Kiosk",
      "Adding Mission Stickers to Medal Board",
      "Visual Progress Reports (Per Campaign) on Mobile Site / App",
      "Visual Progress Reports (Per Task) on Mobile Site / App",
      "Show raffle eligibility on Student Account",
  ),
  'September 2018' =>  array(
      "Speed Optimizations to site",
      "Updating Missing Mission Pictures",
      "Attendance Module for Teachers",
      "Daily Chitas Integration",
      "Points Grid for Teachers",
      "Mission Sheets Barcodes",
      "Personalized Student Reports",
      "Reporting Engine for Base Commanders",
  ),
  'November 2018'  =>  array(
      "Integration with Chabad.org for content / videos",
      "Updated Wordpress to allow audio / video uploads",
      "Comments / Likes on Wordpress ???",
  ),
  'December 2018'  =>  array(
    
  ),
  'January 2019'  =>  array(
    
  ),
  'February 2019' =>  array(
    
  ),
  'March 2019'  =>  array(
    
  )
);
?>
<!DOCTYPE html>
<html >
<head>
  <!-- Site made with Mobirise Website Builder v4.6.6, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v4.6.6, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
  <meta name="description" content="Website Maker Description">
  <title>Page 1</title>
  <link rel="stylesheet" href="assets/web/assets/mobirise-icons/mobirise-icons.css">
  <link rel="stylesheet" href="assets/tether/tether.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="assets/theme/css/style.css">
  <link rel="stylesheet" href="assets/mobirise/css/mbr-additional.css" type="text/css">
  
  
  
</head>
<body>
  <section class="timeline2 cid-qP7jIIOVOn" id="timeline2-q">

    

    <div class="mbr-overlay" style="opacity: 0.9; background-color: rgb(178, 204, 210);">
    </div>

    <div class="container align-center">
        <h2 class="mbr-section-title pb-3 mbr-fonts-style display-2">
            Mashpia Updates Timeline
        </h2>
        <h3 class="mbr-section-subtitle pb-5 mbr-fonts-style display-5">
            A timeline of proposed updates to the current mashpia system
        </h3>
        
        <?php
        // alternate between left aligned and right aligned
        $i = 1;
        // do not show line on last div
        $j = 1;
        $last = count($info);
        foreach ($info as $month => $list) {
          if ($i == 1) $class = "";
          else if ($i == 2) $class = "reverse";
          if ($j < $last) $class2 = "separline";
          else $class2 = "";
          ?>

          <div class="row timeline-element <?=$class?> <?=$class2?>">
             <span class="iconsBackground">
                 <span class="mbri-responsive mbr-iconfont"></span>
             </span>
             <div class="col-xs-12 col-md-6 align-left">
                 <div class="timeline-text-content">
                     <h4 class="mbr-timeline-title pb-3 mbr-fonts-style display-5">
                         <?=$month?>
                     </h4>
                     <p class="mbr-timeline-text mbr-fonts-style display-7">
                         <ul>
                            <?php foreach ($list as $item) : ?>
                            <li><?=$item?></li>
                            <?php endforeach; ?>
                         </ul>
                     </p>
                 </div>
             </div>
         </div>
         
         <?php
         $i++;
         if ($i > 2) $i = 1;
         $j++;
        } ?>
    </div>
</section>


  <section class="engine"><a href="https://mobirise.ws/o">free mobile website builder</a></section><script src="assets/web/assets/jquery/jquery.min.js"></script>
  <script src="assets/popper/popper.min.js"></script>
  <script src="assets/tether/tether.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/smoothscroll/smooth-scroll.js"></script>
  <script src="assets/theme/js/script.js"></script>
  
  
</body>
</html>