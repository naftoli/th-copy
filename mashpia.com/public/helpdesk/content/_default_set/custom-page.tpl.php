<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[0]; ?></a></li>
      <li class="active"><?php echo mswCleanData($this->CPAGE->title); ?></li>
    </ol>

    <div class="row">
      <div class="col-lg-8">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            echo $this->CPAGE_TXT;
            ?>
          </div>
        </div>

      </div>
      <div class="col-lg-4">

        <?php
        // Show if FAQ Cats if enabled...
	      if ($this->SETTINGS->kbase == 'yes') {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->TXT[1]; ?>
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
        }

        // CUSTOM PAGES
        // html/custom-pages.htm
        // html/custom-pages-link.htm
        echo $this->OTHER_PAGES;
        ?>

      </div>
    </div>

  </div>