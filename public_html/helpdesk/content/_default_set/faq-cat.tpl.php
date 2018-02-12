<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[0]; ?></a></li>
      <?php
      // Is this a sub category?
      if (isset($this->SUB['id'])) {
      ?>
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->SUB['id']; ?>"><?php echo mswCleanData($this->SUB['name']); ?></a></li>
      <li class="active"><?php echo mswCleanData($this->PARENT['name']); ?></li>
      <?php
      } else {
      ?>
      <li class="active"><?php echo mswCleanData($this->PARENT['name']); ?></li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Show summary..
    if ($this->PARENT['summary']) {
    ?>
    <div class="well well-sm">
      <?php
      echo mswSafeDisplay($this->PARENT['summary']);
      ?>
    </div>
    <?php
    }
    ?>

    <form method="get" action="index.php" id="sform">
    <div class="row">
      <div class="col-lg-8">

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></a></li>
              <?php
              // No point showing search box if nothing exists..
              if ($this->COUNT > 0) {
              ?>
              <li><a href="#two" data-toggle="tab"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[2]; ?></span></a></li>
              <?php
              }
              ?>
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
                        // QUESTIONS FOR THIS CATEGORY
                        // html/faq-question-link.htm
                        // html/nothing-found.htm
                        echo $this->FAQ;
                        ?>
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <?php
                  if ($this->COUNT > 0) {
                  ?>
                  <div class="tab-pane fade" id="two">

                    <div class="form-group">
                      <div class="form-group input-group">
                       <input type="hidden" name="p" value="faq-search">
                       <input type="text" placeholder="<?php echo $this->TXT[3]; ?>" name="q" value="" class="form-control" onkeypress="if(mswKeyCode(event)==13){mswSearchAction()}">
                       <span class="input-group-addon"><i class="fa fa-chevron-right fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
                      </div>
                    </div>

                    <div class="form-group">

                      <div class="radio">
                        <label><input type="radio" name="c" value="<?php echo $this->PARENT['id']; ?>" checked="checked"> <?php echo $this->TXT[7]; ?></label>
                      </div>

                      <div class="radio">
                        <label><input type="radio" name="c" value="0"> <?php echo $this->TXT[6]; ?></label>
                      </div>

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
      <div class="col-lg-4">

        <div class="panel panel-default">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->TXT[5]; ?>
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
	  // PAGE NUMBERS
	  if ($this->PAGES) {
      ?>
      <div class="faqpages">
      <?php
	    // control/classes/class.page.php
	    echo $this->PAGES;
      ?>
      </div>
      <?php
	  }
    ?>

  </div>