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
   		        <h3 class="page-in">The Chidon</h3>					
				
				<p class="page-top">
					The Chidon will take place in Crown Heights. The Girl's Chidon starts on Thursday, 21 Adar II / March 31st 
					at the Jewish Childrens Museum, and will end on Sunday, 24 Adar II/ April 3rd. 
					The Boy's Chidon starts on Thursday, 28 Adar II/April 7th at the Jewish Children's Museum, 
					and will end on Sunday 2 Nissan/ April 10th.
				</p>
                
                <p class="page-top">
                	The fee per student attending the Chidon Shabbaton is $115. The fee per chaperone attending the chidon Shabbaton is $100.
                </p>
                	
				<p class="page-top">Registration for the Chidon closes two weeks before the chidon starts.  The school will be responsible to register the chayolim before that date. If you need help with arranging accommodation, please make sure to notify the chidon office 3 weeks before the chidon starts. 
During the Chidon Shabbaton the Chayolim are divided into groups (bunks) and compete against each other in a series of competitions. The grand finale is at the grand Chidon game show where the representatives from each school perform on stage, showcasing their knowledge.
The climax of the Chidon the winners are announced.</p>
	<div class="caption">
					
                  <ul>
                      <li>
                        <p> The first place winner from each grade will be awarded the GOLD MEDALS </p>
                      </li><li>
                         <p> The second place winners from each grade will be awarded the SILVER MEDALS  </p>
                       </li>
                       <li>
                         <p>  The third place winner from each grade will be awarded the BRONZE MEDALS.</p>
                       </li>
                        
                       
                </ul>					
			  </div>
                
        <p class="page-top"> All Chayolim who earn a Gold silver or bronze medal will receive a set of Seforim.</p>  
           
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