<?php if (!defined('PATH')) { exit; }
if ($this->RECAPTCHA) {
  define('LOAD_RECAPTCHA', 1);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">

            <div class="form-group">
              <label><?php echo $this->TXT[5]; ?></label>
              <input type="text" class="form-control" name="name" tabindex="1" maxlength="200" value="" autofocus>
            </div>

            <div class="form-group">
              <label><?php echo $this->TXT[2]; ?></label>
              <input type="text" class="form-control" name="email" tabindex="2" value="">
            </div>

            <div class="form-group">
              <label><?php echo $this->TXT[3]; ?></label>
              <input type="text" class="form-control" name="email2" tabindex="3" value="">
		        </div>

            <?php
            // SPAM PREVENTION IF RECAPTCHA ENABLED
            // html/recaptcha.htm
            echo $this->RECAPTCHA;
            ?>

          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" onclick="mswProcess('create')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>