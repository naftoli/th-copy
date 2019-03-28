<? include 'storyTop.php'; ?>  

<section id="tz-main"><!--start tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

        <div class="tz-inner"><!--start tz-inner-->

        <section class="tz-content-wrap row-fluid">

            <section id="tz-content" class="span9">

                <section id="tz-component">

                    <div class="TzItemPage item-page TzBlog">

                        <div class="TzItemPageInner">

                        <div class="TzArticleMedia">

                            <div class="tz_portfolio_image">

                                <img src="../../images/<?=$cd['image']?>" alt=""
                                     title="">
                                     
                                <? if ($id == 10) echo "<br /><br /><br /><br />"; ?>
                            </div>

                        </div>

<div class="TzArticleTitle">                                           
	<h1><span style="font-size: 22px; color: #4D0C0D;"><?=$cd['title']?></span></h1>                        
</div>
<? if ($id == 10) : ?>
<p>
	<b><i>Voice of Levi:</i></b><br />
	Yaakov HaKohain Friedman of Los Angeles<br />
	<b><i>Niggun sung by:</i></b><br />
	Yaakov HaKohain Friedman of Los Angeles  
</p>
<? endif; ?>

<p>
	<b>Description:</b> <?=$cd['description']?>
</p>

<p>
	<b>Lessons Learned:</b> <?=$cd['lesson']?>
</p>

<p>
	<b>Length of Story:</b> <?=$cd['story_time'] . ' ' . $cd['time_type'];?>
</p>

<p>
	<b>Teachers Resources:</b> <?=$cd['resources']?>
</p>

<div><span style="font-size: 22px; color: red; text-decoration: line-through;">Price: $<?=$cd['price']?> </span></div>
<div><span style="font-size: 30px; color: #4D0C0D;">Current Price: $<?=$cd['discount_price']?> </span></div>
<br />
<div class="TzDescription">
	<div class="demo-col">
		<a class="btn-small btn-warning " href="../addToCart.php?id=<?=$id?>"> Add to cart </a>
	</div>
	<? if ($pdf = $cd['attachment']) : ?>
      <a class="btn-primary btn-small" href="../../pdf/<?=$pdf?>"> Questionnaire</a>
    <? endif; ?>
</div>

<div id="jquery_jplayer_detail" class="jp-jplayer"></div>

                        <div id="jp_container_detail" class="jp-video">

                            <div class="jp-type-single">

                                <div class="jp-gui jp-interface">

                                    <div class="jp-progress">

                                        <div class="jp-seek-bar">

                                            <div class="jp-play-bar"></div>

                                        </div>

                                    </div>

                                    <div class="controler-inner">

                                        <div class="music-left">

                                            <div class="tz-jp-title tz-blog-title">Trailer </div>

                                            <div class="pull-left hidden-phone">-</div>

                                            <div class="tz-jp-artist tz-blog-artist hidden-phone"><?=$cd['title']?></div>

                                        </div>

                                        <div class="jp-controls-holder">

                                            <ul class="jp-toggles">

                                                <li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
                                                <li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>

                                            </ul>

                                            <div class="time-inner hidden-phone">

                                                <div class="jp-current-time"></div>

                                                <div class="pull-left">/</div>

                                                <div class="jp-duration"></div>

                                                <ul class="jp-controls detail-audio">
                                                    <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                                                    <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>

                                                </ul>
                                                <div class="jp-volume-bar">

                                                    <div class="jp-volume-bar-value"></div>

                                                </div>

                                            </div>

                                            <ul class="jp-controls">
                                                <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                                                <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                                                <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                                                <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>

                                            </ul>

                                            <div class="clr"></div>

                                        </div>

                                        <div class="clr"></div>

                                    </div>

                                    <div class="jp-no-solution">

                                        <span>Update Required</span>
                                        To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.

                                    </div>

                                </div>

                            </div>

                        </div>
                        <div class="clr"></div>
                      

                        </div><!--end TzItemPageInner-->

                    </div><!--end TzItemPage-->

                </section><!--end tz-component-->

            </section><!--end tz-content-->

            <aside id="right-sidebar" class="span3 right-sidebar"><!--end sidebar-nav-->
<? if ($id == 11) : ?>
    	<div style="color: red; font-weight: bold; font-size: 16px; padding-top: 80px;">
    		<p>
    			To purchase the physical CD click on the Buy Now Button.
    		</p>
	    	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="7A2SZ875FD6HS">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
    <? endif; ?>
            </aside><!--end right-sidebar-->

            <div class="clr"></div>

        </section><!--end tz-content-wrap-->

        </div><!--end tz-inner-->

        </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'storyBottom.php'; ?>