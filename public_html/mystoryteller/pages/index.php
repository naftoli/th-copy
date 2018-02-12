<? 
require_once 'db.php';
$cds = array();
$sql = "select * from cds where skip = 0 order by ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$cds[] = $row;
}
?>

<? include 'inc/head.php' ?>

<link href="../css/flat-ui.css" rel="stylesheet">

<style>
	.promo {
		cursor: pointer;
	}
</style>
<!--
<div align="center">
	<a href="addToCart.php?id=100"><img src="../images/mystorytellerbanner.jpg" class="promo" /></a>
</div>
-->
<section id="tz-main"><!-- tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

              <section class="tz-content-wrap row-fluid">

                    <section id="tz-content" class="span8">
                    	
                        <section id="tz-component">
                        	
                            <div class="TzBlog blog">

                                <div class="TzBlogInner">
                                	
                                <? foreach ($cds as $cd) : ?>
                                <? $index = $cd['id'] - 1; ?>	
                                
                                <? if ($cd['id'] == 12) : ?>
                                	<div class="TzItemsRow cols-1 row-1">
                                		<div class="span4">
	                                      <div class="TzItem column-1">
	                                        <div class="tz-info-item">
	                                	   </div>
	                                	</div>
	                                </div>
                                <? endif; ?>
                                
                                  <div class="TzItemsRow cols-1 row-1">                                  	
                                    <div class="span4">
                                      <div class="TzItem column-1">
                                        <div class="TzBlogMedia">
                                          <div class="music-play-blog" data-option-value="<?=$index?>"></div>
                                          <div class="music-pause-blog" data-option-value="<?=$index?>"></div>
                                          <div class="tz_portfolio_image"> <a href="albums/story.php?id=<?=($cd['id'])?>"> 
                                          	<img src="../images/<?=$cd['image']?>" alt="" /> </a> </div>
                                        </div>
                                        <div class="tz-info-item">
                                          <h3 ><?=$cd['title']?></h3>
                                          <div class="TzDescription">
                                           
                                          Story time: <?=$cd['story_time'] . ' ' . $cd['time_type'];?> <br>
<? if ($cd['id'] == 0) : ?>
<span style="color: red; text-decoration: line-through;">Price: <?=$cd['price']?></span><br />
<strong>Current Price: $<?=$cd['discount_price']?></strong> 
<? else : ?>
<strong>Price: $<?=in_array($cd['id'], array(12,13)) ? $cd['discount_price'] : $cd['price']?></strong>
<? endif; ?>
<div class="demo-col">
                                         
                              <a class="btn-small btn-warning  " href="albums/story.php?id=<?=($cd['id'])?>""> More Info </a>
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							  <a class="btn-small btn-primary " href="addToCart.php?id=<?=$cd['id']?>"> Add to cart </a>
                              </div>
                                        
                                          
                                          <div class="item-separator"></div>
                                        </div>
                                        <div class="clr"></div>
                                      </div>
                                    </div>
                                    <span class="row-separator"></span> </div>
                                 </div>
                                 
                                 <? endforeach; ?>
                                    
                                <div class="clearfix"></div>

                                </div>

                            </div><!--end TzBlog-->

                          <div class="blog-music">

                              <div id="jquery_jplayer_blog" class="jp-jplayer"></div>

                              <div id="jp_container_blog" class="jp-audio">

                                  <div class="jp-type-playlist">

                                      <div class="jp-gui jp-interface">

                                          <div class="music-left">

                                              <div class="head-phone"></div>

                                              <div class="tz-jp-title tz-blog-title"></div>

                                              <div class="pull-left">-</div>

                                              <div class="tz-jp-artist tz-blog-artist"></div>

                                          </div>

                                          <div class="controls-inner">

                                              <ul class="jp-controls">
                                                  <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                                                  <li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
                                                  <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                                                  <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                                                  <li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
                                                  <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                                                  <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                                                  <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
                                              </ul>

                                              <div class="jp-progress">

                                                  <div class="jp-seek-bar">

                                                      <div class="jp-play-bar"></div>

                                                  </div>

                                              </div>

                                              <div class="jp-volume-bar">

                                                  <div class="jp-volume-bar-value"></div>

                                              </div>

                                              <div class="jp-current-time"></div>

                                              <div class="pull-left">/</div>

                                              <div class="jp-duration"></div>

                                          </div>

                                          <ul class="jp-toggles">
                                              <li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
                                              <li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
                                              <li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
                                              <li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
                                          </ul>

                                      </div>

                                      <div class="jp-playlist">

                                          <ul>
                                              <li></li>
                                          </ul>

                                      </div>

                                      <div class="jp-no-solution">

                                          <span>Update Required</span>
                                          To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.

                                      </div>

                                  </div>

                              </div>

                            </div><!--end blog-music-->

                        </section><!--end tz-component-->

                    </section><!--end tz-content-->

                    <aside id="right-sidebar" class="span4 right-sidebar">

                        <div class="sidebar-nav"><!--end box-->
<div class="box "><!--start box-->

                               

                                  

                                    <div class="content"><!--start content-->

                                        <div class="custom"  >

                                            <p><img src="../images/thlogo.png" alt=""/></p>
                                        </div>

                                    </div>

                               

                            </div>
                            <div class="box "><!--start box-->

                                <div >

                                    

                                    <div class="content"><!--start content-->

                                      <div id="jquery_jplayer_1" class="jp-jplayer"></div>

                                        <div id="jp_container_1" class="jp-audio">

                                            <div class="jp-type-playlist">

                                                <div class="music-sidebar-title"></div>

                                                <div class="jp-gui jp-interface">

                                                    <div class="jp-progress">

                                                        <div class="jp-seek-bar">

                                                            <div class="jp-play-bar"></div>

                                                        </div>

                                                    </div>

                                                    <div class="jp-volume-bar">

                                                        <div class="jp-volume-bar-value"></div>

                                                    </div>

                                                    <ul class="jp-controls">
                                                        <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                                                        <li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
                                                        <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                                                        <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                                                        <li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
                                                        <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                                                        <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                                                        <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
                                                    </ul>

                                                    <div class="music-time">

                                                        <div class="jp-current-time"></div>

                                                        <div class="pull-left">/</div>

                                                        <div class="jp-duration"></div>

                                                    </div>

                                                    <ul class="jp-toggles">
                                                        <li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
                                                        <li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
                                                        <li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
                                                        <li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
                                                    </ul>

                                                </div>

                                                <div class="jp-playlist">

                                                    <ul>
                                                        <li></li>
                                                    </ul>

                                                </div>

                                                <div class="jp-no-solution">
                                                    <span>Update Required</span>
                                                    To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                          </div><!--end box-->

                          <!--end sidebar_new--><!--end sidebar_new-->

                      </div><!--end sidebar-nav-->

                    </aside><!--end right-sidebar-->

                    <div class="clr"></div>

                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->

      </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'inc/footer.html'; ?>

<script>
	$(function() {
		$(".promo").click( function() {
			window.location.href = "addToCart.php?id=100";
		});
	});
</script>