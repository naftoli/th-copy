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
				
			<div class="magazine">
			<h1  style="font-family: Sanchez, 'Sanchez Slab'; font-size: 30px; color: #22418e;">LIMMUD OVERVIEW</h1>
            <h4><strong>WELCOME TO THE LIMMUD SEFER HAMITZVOS CAMPAIGN!</strong></h4>
           <div class="top-comment-right">
						<img class="img-responsive" src="images/rebbe.png" alt="">
			  </div>
            	<p class="page-top"> There is no need to elaborate on how much it means to the Rebbe that every man, woman, and child study the daily portion of Rambam. 	</p>
                <p class="page-top"> Since the Rebbe first initiated the campaign over thirty years ago, tens of thousands of people have been learning Mishneh Torah every day. Yet many still do not understand the mitzvos properly. </p>
			<p class="page-top"> Our goal is for every soldier to have a clear understanding of all 613 mitzvos of the Torah as codified by the Rambam, so that for the rest of their lives, when they study the daily shiur, they will be able to comprehend it properly.
</p>
	<p class="page-top"> In order to reach this destination, we will be using the acclaimed curriculum created by the Living Lessons Foundation: a series of workbooks explaining each mitzva, its source in the Torah, and the reason behind it. Using colorful images, eye-catching visual aids, and cutting-edge educational technologies, the well-designed Yahadus curriculum is the perfect complement to our new motivational system. 
</p>
<p class="page-top"> The 613 mitzvos are to be completed over a five-year period, from fourth through eighth grade, by learning approximately 125 mitzvos each year. We encourage schools to include this limmud as any other school subject.  
</p>
<p class="page-top"> We are looking forward to getting all the students excited about the 613 mitzvos, every child is capable of participating and we anticipate every chayol's joining!  
</p>
<h4><strong>Tzivos Hashem Headquarters</strong></h4>

			<h6>
			  <!---->		
			  </h6>
			
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