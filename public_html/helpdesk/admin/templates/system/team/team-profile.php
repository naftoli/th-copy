<?php if (!defined('PATH') || !isset($MSTEAM->id)) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_staffprofile2; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user73; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user76; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-pencil fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user19; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_user; ?></label>
                  <input type="text" class="form-control" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($MSTEAM->name) ? mswSafeDisplay($MSTEAM->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user4; ?></label>
                  <input type="text" class="form-control" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($MSTEAM->email) ? mswSafeDisplay($MSTEAM->email) : ''); ?>">
                </div>

                <div class="form-group">
                  <label id="labelPass"><?php echo $msg_user12; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswPassGenerator('labelPass','accpass');return false" title="<?php echo mswSafeDisplay($msg_accounts20); ?>"><i class="fa fa-refresh fa-fw"></i> </a></span>
                    <input type="password" class="form-control" name="accpass" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user70; ?></label>
                  <select name="timezone" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - - - -</option>
                  <?php
                  // TIMEZONES..
                  foreach ($timezones AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo (isset($MSTEAM->timezone) ? mswSelectedItem($MSTEAM->timezone,$k) : ''); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_user65; ?></label>
                  <input type="text" class="form-control" name="nameFrom" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($MSTEAM->nameFrom) ? mswSafeDisplay($MSTEAM->nameFrom) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user66; ?></label>
                  <input type="text" class="form-control" name="emailFrom" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($MSTEAM->emailFrom) ? mswSafeDisplay($MSTEAM->emailFrom) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user85; ?></label>
                  <input type="text" class="form-control" name="email2" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($MSTEAM->email2) ? mswSafeDisplay($MSTEAM->email2) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label><?php echo $msg_user17; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="signature" class="siggie" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($MSTEAM->signature) ? mswSafeDisplay($MSTEAM->signature) : ''); ?></textarea>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="currID" value="<?php echo $MSTEAM->id; ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('tmprofile')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_staffprofile2; ?></span></button>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>