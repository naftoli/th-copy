<?php if (!defined('PATH')) { exit; }
define('LOGGED_OUT_SCR', 1);
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[2]; ?></a></li>
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="logoutdivarea">
              <?php
              // Logout message..
              echo $this->TXT[1];
              ?>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>