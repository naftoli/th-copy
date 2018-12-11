<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="?p=open"><?php echo $this->TXT[1]; ?></a></li>
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            // Confirmation message..
            echo $this->TXT[2];
            // Additional text only shown when an account is also created..
            if ($this->ADD_TXT) {
              echo '<hr>' . $this->ADD_TXT;
            }
            ?>
          </div>
        </div>

      </div>
    </div>

  </div>