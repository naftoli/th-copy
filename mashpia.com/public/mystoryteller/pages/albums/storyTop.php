<? 
session_start();

if (!isset($_GET['id'])) {
	header("Location: ../index.php");
	exit;
}

$id = $_GET['id'];
require_once '../db.php';
$sql = "select * from cds where id = " . $id;
$result = mysql_query($sql);
$cd = mysql_fetch_assoc($result);

if (isset($_SESSION['cart'])) {
	$numItems = count($_SESSION['cart']);
} else {
	$numItems = 0;
}
?>

<!DOCTYPE html>
<html lang="en-gb">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 
    <title>Story</title>
    <link href="../../images/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
   
    <link rel="stylesheet" href="../../css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="../../css/bootstrap-responsive.css" type="text/css" />
    <link rel="stylesheet" href="../../css/template.css" type="text/css" />
  
    <link rel="stylesheet" href="../../css/jplayer.blue.flag.css" type="text/css" />
    <link rel="stylesheet" href="../../css/idangerous.swiper.css" type="text/css" />
    <link rel="stylesheet" href="../../css/custom/add.css" type="text/css" />

    <style type="text/css">
		body, td, th {
			color: #FFFFFF;
		}
    </style>
   

    <!--[if IE 9]>
    <link rel="stylesheet" href="../../css/ie9.css" type="text/css"/>
    <script type="text/javascript" src="../../js/respond.js"></script>
    <script type="text/javascript" src="../../js/selectivizr.js"></script>
    <script type="text/javascript" src="../../js/html5.js"></script>

    <![endif]-->

    <!--[if IE 8]>
    <link rel="stylesheet" href="../../css/ie8.css" type="text/css"/>
    <script type="text/javascript" src="../../js/respond.js"></script>
    <script type="text/javascript" src="../../js/selectivizr.js"></script>
    <script type="text/javascript" src="../../js/html5.js"></script>
    <script type="text/javascript" src="../../js/PIE.js"></script>
    <![endif]-->

    <!--[if lte IE 7]>
    <link rel="stylesheet" href="../../css/ie7.css" type="text/css"/>
    <script type="text/javascript" src="../../js/respond.js"></script>
    <script type="text/javascript" src="../../js/selectivizr.js"></script>
    <script type="text/javascript" src="../../js/html5.js"></script>

    <![endif]-->

</head>

<body>

<header id="tz-header" class="tz-header tz-border-bottom"><!--start tz-header-->

    <div class="container-fluid"><!--start container-fluid-->

        <div class="tz-inner-logo"><!--start tz-inner-logo-->

            <div class="tz-inner"><!--start tz-inner-->

                <h1 class="tz-logo pull-left">

                    <a href="../index.php" id="tz-logo">
                        <img src="../../images/logo.png" alt="#" />
                    </a>

                </h1>

            </div><!--end tz-inner-->

        </div><!--end tz-inner-logo-->

        <div class="sidebar-search pull-right" ><!--start sidebar-search-->

            <div class="box"><!--start box-->

                <div>

                    <div class="content"><!--start content-->

                        <div class="search form-search"><!--start search-->

                            <form action="../search.php" method="post" class="form-inline">

                                <label class="icon-search">&nbsp;</label>

                                <label for="mod-search-searchword" class="element-invisible">Search...</label> 
                                <input name="searchword" id="mod-search-searchword" maxlength="20"  class="inputbox search-query input-medium" type="text" size="20" value="Search..."  onblur="if (this.value=='') this.value='Search...';" onfocus="if (this.value=='Search...') this.value='';" />    	
                                <input type="hidden" name="task" value="search" />

                            </form>

                        </div><!--end search-->

                    </div><!--end content-->

                </div>

            </div><!--end box-->

        </div><!--end sidebar-search-->

        <div class="tz-inner"><!--start tz-inner-->

            <div class="tz-mainmenu-toggle"><!--start tz-mainmenu-->

                <a href="#" data-toggle="collapse" data-target=".nav-collapse">
                 
              </a>

            </div><!--end tz-mainmenu-toggle-->

            <div class="pull-right nav-collapse collapse tz-main-menu"><!--start pull-left nav-collapse-->

                <ul class="nav menu"><!--start nav menu-->

                  <li>
                  	<a href="../index.php">Home</a>
                  </li>  
                  <li class="item-11">
                        <a href="../about.php" >About</a>
                    </li>
                    <li class="item-112">
                        <a href="../contact.php" >Contact</a>
                    </li>
 <li>
                        <a href="../cart.php" >Cart (<?=$numItems?>)</a>
                    </li>
                </ul><!--end nav menu-->

            </div><!--end pull-left nav-collapse-->

            <div class="clr"></div>

        </div><!--end tz-inner-->

    </div><!--end container-fluid-->

</header><!--end tz-header-->

<section id="tz-slide" class="tz-slide"><!--start tz-slide-->

    <div class="container-fluid"><!--start container-fluid--><!--end tz-inner-->

    </div><!--end container-fluid-->

</section><!--end tz-slide-->