<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[10]; ?></a></li>
      <?php
      // Is this a sub category?
      if (isset($this->PARENT['name'])) {
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
      } else {
      ?>
      <li class="active"><?php echo $this->TXT[12]; ?></li>
      <?php
      }
      ?>
    </ol>

    <div class="row">
      <div class="col-lg-8">

        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[11]; ?></span></a></li>
              <?php
              // Only show tab if there are attachments
              if ($this->ATTACHMENTS) {
              ?>
              <li><a href="#two" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[9]; ?></span></a></li>
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

                    <b>
                    <?php
                    // Question..
                    echo mswCleanData($this->ANSWER['question']);
                    ?>
                    </b>

                    <hr>

                    <?php
                    // Answer
                    echo $this->ANSWER_TXT;
                    ?>

                    <hr>

                    <div class="row">

                      <div class="col-lg-6">

                        <i class="fa fa-clock-o fa-fw"></i> <?php echo $this->TXT[13]; ?>

                      </div>

                      <div class="col-lg-6 text-right printarticle">

                        <a href="#" onclick="window.print();return false"><i class="fa fa-print fa-fw"></i> <?php echo $this->TXT[2]; ?></a>

                      </div>

                    </div>

                  </div>
                  <?php
                  // Only show if there are attachments
                  if ($this->ATTACHMENTS) {
                  ?>
                  <div class="tab-pane fade" id="two">

                    <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <?php
                        // ATTACHMENTS
                        // html/faq-attachment-link.htm
                        echo $this->ATTACHMENTS;
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
      <div class="col-lg-4">

        <?php
        if ($this->SETTINGS->enableVotes == 'yes') {
        ?>
        <div class="panel panel-default votingarea">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-question-circle fa-fw"></i> <?php echo $this->TXT[1]; ?>
          </div>
          <div class="panel-body text_height_25">

            <div class="row votefont">

              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">

                <i class="fa fa-thumbs-up fa-fw cursor_pointer" onclick="mswVote(this, '<?php echo $this->ANSWER['id']; ?>')"></i> <span><?php echo $this->STATS[0]; ?></span>

              </div>

              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">

                <i class="fa fa-thumbs-down fa-fw cursor_pointer" onclick="mswVote(this, '<?php echo $this->ANSWER['id']; ?>')"></i> <span><?php echo $this->STATS[1]; ?></span>

              </div>

            </div>

            <div class="totalvotes">
              <?php echo $this->TXT[14] . '<span class="votetotalarea">' . $this->STATS[2]; ?></span>
            </div>

          </div>
        </div>
        <?php
        }
        ?>

        <div class="panel panel-default">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->TXT[6]; ?>
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

  </div>