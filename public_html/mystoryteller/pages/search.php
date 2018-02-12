<? include 'inc/head.php'; ?>

<link rel="stylesheet" href="../fonts/SourceSansProRegular/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="../fonts/SourceSansProLight/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="../fonts/SourceSansProItalic/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="../fonts/SourceSansProSemibold/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="../fonts/SourceSansProExtraLight/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="../css/mod_tz_portfolio_categories.css" type="text/css" />

<section id="tz-main">

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

                <section class="tz-content-wrap row-fluid">

                <section id="tz-content" class="span9">

                    <section id="tz-component">

                        <div class="search">
                            <h1 class="page-title">
                                Search	</h1>

                            <form id="searchForm" action="" method="post">

                                <div class="btn-toolbar">

                                    <div class="btn-group pull-left">

                                        <input type="text" name="searchword" placeholder="Search Keyword:" id="search-searchword" size="30" maxlength="20" value="" class="inputbox" />

                                    </div>

                                    <div class="btn-group pull-left">
                                        <button name="Search" onclick="this.form.submit()" class="btn hasTooltip" title="Search"><span class="icon-search"></span></button>
                                    </div>

                                    <input type="hidden" name="task" value="search" />

                                    <div class="clearfix"></div>

                                </div>

                                <div class="searchintro">
                                    <p><strong>Total: <span class="badge badge-info">0</span> results found.</strong></p>
                                </div>

                                <fieldset class="phrases">
                                    <legend>Search for:		</legend>

                                    <div class="phrases-box">

                                        <div class="controls">

                                            <label for="searchphraseall" id="searchphraseall-lbl" class="radio">

                                                <input type="radio" name="searchphrase" id="searchphraseall" value="all"  checked="checked" >All words
                                            </label>
                                            <label for="searchphraseany" id="searchphraseany-lbl" class="radio">

                                                <input type="radio" name="searchphrase" id="searchphraseany" value="any"  >Any words
                                            </label>
                                            <label for="searchphraseexact" id="searchphraseexact-lbl" class="radio">

                                                <input type="radio" name="searchphrase" id="searchphraseexact" value="exact"  >Exact Phrase
                                            </label>

                                        </div>

                                    </div>

                                    <div class="ordering-box">

                                        <label for="ordering" class="ordering">
                                            Ordering:			</label>
                                        <select id="ordering" name="ordering" class="inputbox">
                                            <option value="newest" selected="selected">Newest First</option>
                                            <option value="oldest">Oldest First</option>
                                            <option value="popular">Most Popular</option>
                                            <option value="alpha">Alphabetical</option>
                                            <option value="category">Category</option>
                                        </select>

                                    </div>

                                </fieldset>

                                <fieldset class="only">

                                    <legend>Search Only:</legend>
                                    <label for="area-tz_portfolio_content" class="checkbox">
                                        <input type="checkbox" name="areas[]" value="tz_portfolio_content" id="area-tz_portfolio_content"  >
                                        Articles		</label>
                                    <label for="area-tz_portfolio_categories" class="checkbox">
                                        <input type="checkbox" name="areas[]" value="tz_portfolio_categories" id="area-tz_portfolio_categories"  >
                                        Categories		</label>

                                </fieldset>

                            </form>

                        </div>

                    </section>

                </section>

                <aside id="right-sidebar" class="span3 right-sidebar">

                    <div class="sidebar-nav"><!--end box-->

                    <div class="box "><!--start box-->

                        <div>

                           

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

                    </div><!--end box--><!--end sidebar_new--><!--end sidebar_new-->

                    </div><!--end sidebar-nav-->

                </aside><!--end right-sidebar-->

                <div class="clr"></div>

                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->

        </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'inc/footer.html'; ?>