<!DOCTYPE html>
<html>
<head>
<title>Chidon</title>
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
</style>
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
		    <script type="text/javascript">
			    $(document).ready(function () {
			        $('#horizontalTab,#horizontalTab1,#horizontalTab2').easyResponsiveTabs({
			            type: 'default', //Types: default, vertical, accordion           
			            width: 'auto', //auto or any width like 600px
			            fit: true   // 100% fit in a container
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
	   <div class="row_8">
   		       <h3  style="font-family: Sanchez, 'Sanchez Slab'; font-size: 30px; color: #22418e;">Quizzes</h3>					
				
				<p class="page-top">The Yahadus curriculum comes with 8-10 quizzes throughout the year and one grand test after all the units of an entire book. If your class does not manage to finish the entire unit in a week, you can instruct students to skip certain questions.
 
These quizzes are to be given using the same procedures as regular school tests, with grades applied to report cards. </p>

			<p>
       			To access there quizzes click <a href="https://www.dropbox.com/sh/n72bq359bp8apou/AACMhH7fXmQ6ppY2HLaFcFwqa/Yahadus Curriculum?dl=0" style="color: red;">here</a> or visit mashpia.com
       		</p>
				<div class="col-md-5 top-magazine">
			<div class="banner"><img src="images/quizz.png"  alt=""/></div></div>
         
           
			<div class="clearfix"></div>
	
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
				  <h3>Yahadus curriculum created in memory of Sara Rohr
</h3>
				</div>
				<div class="col-md-4 top-archives">
				  <h3>A project of: <img src="images/sponsors.png" width="102" height="47" alt=""/></h3>
				</div>
				<div class="col-md-4 top-archives">
               
				  
				  
				  <h3>Chidon Sponsor:
					<? 
					$str = " הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות ";
					$he = iconv('utf8', 'windows-1255', $str);
					echo $he;
					?>
				  </h3>
					
				</div>
                
      <div class="col-md-12 top-archives">
					<h3>Chidon Partners:</h3>
					
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