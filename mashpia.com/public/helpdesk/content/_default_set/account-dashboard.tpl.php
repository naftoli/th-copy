<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <div class="row">
      <div class="col-lg-8">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $this->TXT[7]; ?>
          </div>
        </div>

      </div>
      <div class="col-lg-4">

        <div class="panel panel-default">
          <div class="panel-body">
            <i class="fa fa-caret-right"></i> <?php echo $this->USER_DATA->email; ?><br>
		        <i class="fa fa-caret-right"></i>  <?php echo $this->TXT[5]; ?>: <?php echo ($this->USER_DATA->timezone ? $this->USER_DATA->timezone : $this->SETTINGS->timezone); ?><br>
		        <i class="fa fa-caret-right"></i>  <?php echo $this->TXT[6]; ?>: <?php echo ucfirst($this->USER_DATA->language); ?><br>
		        <i class="fa fa-caret-right"></i>  <?php echo $this->TXT[8]; ?>: <?php echo mswIPAddresses(); ?><br><br>
            <a class="margin_right_20" href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open"><i class="fa fa-pencil fa-fw"></i> <?php echo $this->TXT[9]; ?></a>
            <div class="mobilebreakpoint">
            <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=profile"><i class="fa fa-user fa-fw"></i> <?php echo $this->TXT[10]; ?></a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <?php
	  // Show if FAQ is enabled...
	  if ($this->SETTINGS->kbase == 'yes') {
	  ?>
    <form method="get" action="index.php" id="sform">
    <div class="row">
      <div class="col-lg-8">

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[13]; ?></span></a></li>
              <li><a href="#two" data-toggle="tab"><i class="fa fa-heart-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[11]; ?></span></a></li>
              <li><a href="#three" data-toggle="tab"><i class="fa fa-calendar fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[12]; ?></span></a></li>
              <li><a href="#four" data-toggle="tab"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[15]; ?></span></a></li>
            </ul>
          </div>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="tab-content">
                  <div class="tab-pane active in" id="one">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // FEATURED QUESTIONS
                        // html/faq-question-link.htm
                        // html/nothing-found.htm
                        echo $this->FEATURED;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <div class="tab-pane fade" id="two">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // POPULAR QUESTIONS
                        // html/faq-question-link.htm
                        // html/nothing-found.htm
                        echo $this->POPULAR;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <div class="tab-pane fade" id="three">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // LATEST QUESTIONS
                        // html/faq-question-link.htm
                        // html/nothing-found.htm
                        echo $this->LATEST;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <div class="tab-pane fade" id="four">

                    <div class="form-group">
                      <div class="form-group input-group">
                       <input type="hidden" name="p" value="faq-search">
                       <input type="text" placeholder="<?php echo $this->TXT[16]; ?>" name="q" value="" class="form-control" onkeypress="if(mswKeyCode(event)==13){mswSearchAction()}">
                       <span class="input-group-addon"><i class="fa fa-chevron-right fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="col-lg-4">

        <div class="panel panel-default">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->TXT[14]; ?>
          </div>
          <div class="panel-body text_height_25" id="mswfaqcatarea">

            <?php
            // CATEGORIES
            // html/faq-cat-menu-link.htm
            // html/faq-sub-menu-link.htm
            echo $this->CATEGORIES;
            ?>

          </div>
        </div>

        <?php
        // CUSTOM PAGES
        // html/custom-pages.htm
        // html/custom-pages-link.htm
        echo $this->OTHER_PAGES;
        ?>

      </div>
    </div>
    </form>
    <?php
    } else {

      // CUSTOM PAGES
      // html/custom-pages.htm
      // html/custom-pages-link.htm
      echo $this->OTHER_PAGES;

    }
    ?>

    <div class="row">
      <div class="col-lg-12">

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#otick" data-toggle="tab"><i class="fa fa-ticket fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[3]; ?></span></a></li>
              <?php
              // Only show if dispute system is enabled..
              if ($this->SETTINGS->disputes == 'yes') {
              ?>
              <li><a href="#odisp" data-toggle="tab"><i class="fa fa-bullhorn fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></a></li>
              <?php
              }
              ?>
            </ul>
          </div>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="tab-content">
                  <div class="tab-pane active in" id="otick">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // TICKETS
                        // html/tickets/tickets-dashboard.htm
                        // html/tickets/tickets-no-data.htm
                        echo $this->TICKETS;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <?php
                  // Only show if dispute system is enabled..
                  if ($this->SETTINGS->disputes == 'yes') {
                  ?>
                  <div class="tab-pane fade" id="odisp">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // DISPUTE TICKETS
                        // html/tickets/tickets-dashboard.htm
                        // html/tickets/tickets-no-data.htm
                        echo $this->DISPUTES;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <?php
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>