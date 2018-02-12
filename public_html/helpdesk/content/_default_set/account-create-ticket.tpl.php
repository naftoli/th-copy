<?php if (!defined('PATH')) { exit; }
if ($this->RECAPTCHA) {
  define('LOAD_RECAPTCHA', 1);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <?php
      if ($this->LOGGED_IN == 'yes') {
      ?>
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[21]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $this->TXT[0]; ?></li>
    </ol>

    <form method="post" action="index.php?ajax=create-ticket" enctype="multipart/form-data" id="mswform">
    <div class="row formcontainer">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-ticket fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[24]; ?></span></a></li>
          <?php
          // Is there at least 1 custom field?
          if ($this->CUS_FIELDS_COUNT > 0) {
          ?>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-list fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[23]; ?></span></a></li>
          <?php
          }
          // ENTRY ATTACHMENTS
          if ($this->SETTINGS->attachment == 'yes') {
          ?>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[22]; ?></span></a></li>
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

                <?php
                // If the person is logged in, we already have name and email, so we can hide these fields..
                if ($this->LOGGED_IN == 'no') {
                ?>
                <div class="form-group">
                  <label><?php echo $this->TXT[2]; ?></label>
                  <input type="text" class="form-control" name="name" tabindex="1" maxlength="250" value="" autofocus>
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[3]; ?></label>
                  <input type="text" class="form-control" name="email" tabindex="2" maxlength="250" value="">
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <label><?php echo $this->TXT[5]; ?></label>
                  <select name="dept" tabindex="4" onchange="mswDeptLoader()" class="form-control">
                  <!--<option value="0">- - - -</option>-->
                  <?php
                  // DEPARTMENTS
                  // html/ticket-department.htm
                  echo $this->DEPARTMENTS;
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[4]; ?></label>
                  <input type="text" class="form-control" name="subject" tabindex="3" maxlength="250" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[7]; ?></label>
                  <?php
                  // BBCODE
                  if ($this->SETTINGS->enableBBCode == 'yes') {
                    include(PATH . 'content/' . MS_TEMPLATE_SET . '/bb-code.tpl.php');
                  }
                  ?>
                  <textarea rows="12" cols="40" name="comments" id="comments" tabindex="50" class="form-control"></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[6]; ?></label>
                  <select name="priority" tabindex="5" class="form-control">
                  <option value="0">- - - -</option>
                  <?php
                  foreach ($this->PRIORITY_LEVELS AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <?php
                // SPAM PREVENTION IF RECAPTCHA ENABLED
                // html/recaptcha.htm
                echo $this->RECAPTCHA;
                ?>

              </div>

              <?php
              // Is there at least 1 custom field?
              if ($this->CUS_FIELDS_COUNT > 0) {
              ?>
              <div class="tab-pane fade" id="three">
                <div><i class="fa fa-warning fa-fw"></i> <?php echo $this->TXT[26]; ?></div>
              </div>
              <?php
              }

              // ENTRY ATTACHMENTS
              if ($this->SETTINGS->attachment == 'yes') {
              ?>
              <div class="tab-pane fade" id="two">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo $this->TXT[25]; ?>
                  </div>
                </div>

              </div>
              <?php
              }
              ?>
            </div>

          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="submit" tabindex="51" onclick="mswProcessMultiPart()"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[12]; ?></span></button>
	        </div>
        </div>

      </div>
    </div>
    </form>

  </div>