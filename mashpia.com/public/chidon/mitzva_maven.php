<!DOCTYPE html>
<html>
<head>
<title>Chidon</title>
<meta charset="utf8" />
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<!-- Custom Theme files -->
<!--theme-style-->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />	
<!--//theme-style-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1255" />
<meta name="keywords" content="" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!--fonts-->

<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Sanchez" />
<!--//fonts-->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(".scroll").click(function(event){		
							event.preventDefault();
							$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
						});
					});
				</script>
<link href="css/nav.css" rel="stylesheet" type="text/css" media="all"/>
<style type="text/css">
body,td,th {
	font-family: Sanchez, "Sanchez Slab";
}
.indent {
    margin-left: 5%;
}
</style>
<link href="css/easyResponsiveTabs.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#horizontalTab').easyResponsiveTabs({
			type: 'default', //Types: default, vertical, accordion           
			width: 'auto', //auto or any width like 600px
			fit: true,   // 100% fit in a container
			tabidentify: 'sub_1', // The tab groups identifier
			activate: function(event) { // Callback function if tab is switched
				var $tab = $(this);
				var $info = $('#nested-tabInfo');
				var $name = $('span', $info);
				$name.text($tab.text());
				$info.show();
			}
		});
	});
   </script>


<script src="js/main.js"></script> <!-- Resource jQuery -->
	   
</head>
<body>
<!--header-->

<div class="container">
<div class="main-top">
	<div class="main">
		<div class="header">
			<div class="header-top">
				<div class="header-in">
					<div class="logo"> <img src="images/topheader.png" alt="" >
					</div>
					
				  <div class="clearfix"> </div>
				</div>
				
				<div class="clearfix"> </div>
			</div>
			<!---->
			
	</div>
		
	<?php require 'menu.php' ?>
	
<div class="content">
	<div class="col-md-9 content-top">
		<div class="number">
				
			<div class="magazine">
                
                <h1 style="font-family: Sanchez, 'Sanchez Slab'; font-size: 30px; color: #22418e;">Mitzvah Maven</h1>
				<br />
                
            <div id="horizontalTab">
                <ul class="resp-tabs-list sub_1">
                    <li>Study Requirements</li>
                    <li>Videos</li>
                    <li>Study Guides</li>
                    <li>Eligibility</li>
                </ul>
            
                <div class="resp-tabs-container sub_1">
                    <div>
                        Always wanted to be part of Chidon but find the standard system too challenging? Now you can participate in an easier method of studying and testing. 
                        Here’s what you’ll need to learn:<br /><br />
                        <div class="indent">
                            - Mitzvah boxes of all the units in your textbook<br />
                            - English title of the mitzvah<br />
                            - Hebrew mitzvah title<br />
                            <div class="indent">
                                - This will not be translated on the test, so although you will not be asked to say the name by heart, you must recognize the Hebrew title and be able to match it to the English<br />
                            </div>
                            - Mitzvah synopsis (the text between the passuk and icons)<br />
                            - Grades 7-8: Bolded Hebrew text of the passuk<br />
                            <div class="indent">
                                - You are not responsible for the English translation, yet you will be asked to identify the mitzvah when the passuk is presented<br />
                            </div>
                            - Extra credit: details of the mitzvah<br />
                        </div>
                        <br />
                        You will not be tested on the introductory text (text between the blue header “The Mitzvah” and the mitzvah box) and you will not be tested on the mitzvah number. 
                    </div>
                </div>
                
                <div class="resp-tabs-container sub_1">
                    <div>
                        Students are required to watch or listen to these video shiurim on each mitzvah. They are vital to understanding the mitzvos properly and clarify information that you need to know.
                        <br /><br />
                        <div class="indent">
                            <a href="https://vimeo.com/295069370">Book 1</a><br />
                            <a href="https://vimeo.com/295555511">Book 2</a><br />
                            <a href="https://vimeo.com/295512849">Book 3</a><br />
                            <a href="https://vimeo.com/296783824">Book 4</a><br />
                            <a href="https://vimeo.com/295520212">Book 5</a><br />
                        </div>
                    </div>
                </div>
                
                <div class="resp-tabs-container sub_1">
                    <div>
                        Here are links to modified study guides with mitzvah spreadsheets:
                        <br />
                        <div class="indent">
							Coming Soon...
                        </div>
                    </div>
				</div>
                
                <div class="resp-tabs-container sub_1">
                    <div>
                        Mitzvah Maven tests will be provided by Chidon HQ and given at your school on the same day and at the same time as regular Chidon tests. 
                        The tests will be in spreadsheet format, requiring you to fill in select icons and mitzvah translations from the mitzvos on that test. 
                        There will also be an extra credit section with basic questions about the mitzvah details.
                        <br /><br />

                        Each school determines the passing grade for their students and keeps track of the marks. After passing all three tests, students are 
                        eligible to join a trip arranged by their school and receive a certificate of achievement from HQ. 
                        <br /><br />

                        Any Chidon student can choose to take a Mitzvah Maven test. If a student doesn’t receive a high enough score to join the Chidon Shabbaton, 
                        but passed the Mitzvah Maven track, they can still go on their school’s trip. Students are only eligible to join the Chidon Shabbaton if 
                        they meet the regular eligibility requirements (by taking the regular Chidon Shabbaton tests). 
                    </div>
                </div>
            </div>

            </div>
		</div>
			<!---->
			
			<!---->
			
			<!---->
	</div>
	<!---->
		<!---->
        
		<div class="clearfix"></div>
		</div>
	</div>
	
	<div class="archives-top">
				
				<div class="col-md-4 top-archives">
				  <h3>The Yahadus curriculum was created in memory of Mrs. Sara (Charlotte) Rohr
</h3>
				</div>
				<div class="col-md-4 top-archives">
				  <h3>A project of: <img src="images/sponsors.png" width="102" height="47" alt=""/></h3>
				</div>
				<div class="col-md-4 top-archives">
               
				  
				  
				  <h3>Chidon Sponsor:
                    הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות
					<? 
					//$str = " הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות ";
					//$he = iconv('utf8', 'windows-1255', $str);
					//echo $he;
					?>
				  </h3>
					
				</div>
                
        <div class="col-md-12 top-archives" align="center" style="background: #fff">
			לע"נ הרב אליעזר בן הרב מרדכי ע"ה וונגר 
			לע"נ הרב יצחק בן הרב אליעזר צבי זאב ע"ה צירקינד
		</div>
                
      <div class="col-md-12 top-archives">
					<h3>Chidon Partners:
					<div id="sponsors">
							<div>
							<?
							$partners = array();
							if ($dh = opendir(getcwd() . '/sponsors')) {
							    while (($file = readdir($dh)) !== false) {
							    	if ($file != '.' && $file != '..') {
							      		$partners[] = $file;
									}
							    }
							    closedir($dh);
							}
							sort($partners);
							foreach ($partners as $key => $file) {
								if ($key && ($key % 5 == 0)) {
									echo "</div><div style='clear: both'></div><div>";
								}
								echo "<img src='sponsors/" . $file . "' />";
							}
							?>
							</div>
						</div>
					</h3>
					
				</div>
				<div class="clearfix"></div>
                
  </div>
	
	
	</div>
	
<script type="text/javascript">
						$(document).ready(function() {
							/*
							var defaults = {
					  			containerID: 'toTop', // fading element id
								containerHoverID: 'toTopHover', // fading element hover id
								scrollSpeed: 1200,
								easingType: 'linear' 
					 		};
							*/
							
							$().UItoTop({ easingType: 'easeOutQuart' });
							
						});
					</script>
				<a href="#" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>


</div>
</body>
</html>