<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><?php echo $this->TXT[0]; ?></a></li>
      <li class="active"><?php echo $this->TXT[15]; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[2]; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-envelope fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[3]; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-lock fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[11]; ?></span></a></li>
          <?php
          if (!empty($this->LANGUAGES)) {
          ?>
          <li><a href="#four" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[9]; ?></span></a></li>
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

                <div class="form-group">
                  <label><?php echo $this->TXT[5]; ?></label>
                  <input type="text" class="form-control" name="name" maxlength="200" value="<?php echo (isset($this->ACCOUNT['name']) ? mswSafeDisplay($this->ACCOUNT['name']) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[6]; ?></label>
                  <select name="timezone" class="form-control">
                   <option value="0">- - - - -</option>
                   <?php
                   // Timezones..
                   // control/timezones.php
                   foreach ($this->TIMEZONES AS $zK => $zV) {
                   ?>
                   <option value="<?php echo $zK; ?>"<?php echo (isset($this->ACCOUNT['timezone']) ? mswSelectedItem($this->ACCOUNT['timezone'],$zK) : ''); ?>><?php echo $zV; ?></option>
                   <?php
                   }
                   ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $this->TXT[7]; ?></label>
                  <input type="text" class="form-control" name="email" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[8]; ?></label>
                  <input type="text" class="form-control" name="email2" value="">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label><?php echo $this->TXT[12]; ?></label>
                  <input type="password" class="form-control" name="curpass" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[13]; ?></label>
                  <input type="password" class="form-control" name="newpass" value="" onkeyup="clearNewPass2()">
                </div>

                <div class="form-group">
                  <label><?php echo $this->TXT[14]; ?></label>
                  <input type="password" class="form-control" name="newpass2" value="">
                </div>

              </div>
              <?php
              if (!empty($this->LANGUAGES)) {
              ?>
              <div class="tab-pane fade" id="four">

                <div class="form-group">
                  <label><?php echo $this->TXT[10]; ?></label>
                  <select name="language" class="form-control">
                   <?php
                   // Languages..
                   foreach ($this->LANGUAGES AS $lK) {
                   ?>
                   <option value="<?php echo $lK; ?>"<?php echo (isset($this->ACCOUNT['language']) ? mswSelectedItem($this->ACCOUNT['language'],$lK) : ''); ?>><?php echo ucfirst($lK); ?></option>
                   <?php
                   }
                   ?>
                  </select>
                </div>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" onclick="mswProcess('profile')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>