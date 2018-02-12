<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=create"><?php echo $this->TXT[1]; ?></a></li>
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">

            <?php
            // Show message..
            echo $this->TXT[2];
            ?>

          </div>
          <?php
          // If the account has already been verified, show resend message
          if (in_array($this->FLAG, array('ok','exists'))) {
          ?>
          <div class="panel-footer">
           <input type="hidden" name="code" value="<?php echo mswSafeDisplay($_GET['va']); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('resend')"><i class="fa fa-envelope fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[3]; ?></span></button>
          </div>
          <?php
          }
          ?>
        </div>

      </div>
    </div>
    </form>

  </div>