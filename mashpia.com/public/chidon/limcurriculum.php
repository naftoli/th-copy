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
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700' rel='stylesheet' type='text/css'>
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
				
	
			
			<!---->		
    		<div class="span_8">
    		
   		      <div class="row_8">
   		        <h3 class="page-in">The Curriculum </h3>					
				<p class="page-top">Student textbooks � $45 The student textbook includes a vast amount of information on each of the 613 Mitzvos. They are grouped by themes, called �units,� and contain sources, halachos, stories, and interesting facts.
The student textbook is purchased by the school and used year after year. The first four volumes can be purchased directly from <a href="http://livinglessons.com">livinglessons.com</a> </p><div class="banner"><img src="images/books.png"  alt=""/></div>
            </div>
                <div class="row_8">
                <h3 class="page-in">Workbooks � $15 </h3>					
				<p class="page-top">The students� workbook has activities and games to help make sure the information is understood. There are also review questions, which can be assigned as homework, to ensure that the students master the material in preparation for the tests.
                
                </p><div class="banner"><img src="images/workbook.png" alt=""/></div>
              </div>
              <div class="row_8">
                <h3 class="page-in">Teachers� guides � $130 </h3>					
				<p class="page-top">The helpful teachers� guide elaborates on each mitzva, giving teachers additional background along with a focus and goal of each section. It also includes pointers for various ways to introduce the material.<div class="banner"><img src="images/teacherbook.png"  alt=""/></p></div>
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