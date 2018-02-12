<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int)$_GET['edit'];
  $EDIT          = mswGetTableData('portal','id',$_GET['edit']);
  checkIsValid($EDIT);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('responseman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=accountman"><?php echo $msg_adheader40; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_accounts6 : $msg_adheader39); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts7; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-power-off fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts29; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts17; ?></span></a></li>
          <li><a href="#four" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts18; ?></span></a></li>
          <?php
          if (isset($EDIT->id)) {
          ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts32; ?></span><b class="caret"></b></a>
            <ul class="dropdown-menu dropdown-menu-right">
             <li><a href="#five" data-toggle="tab"><?php echo $msg_systemportal6; ?></a></li>
            </ul>
          </li>
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
                if (!isset($EDIT->id)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="welcome" value="yes" checked="checked"> <?php echo $msg_accounts23; ?>
                    </label>
                  </div>
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <label><?php echo (!isset($EDIT->id) ? '<br>' : '').$msg_user; ?></label>
                  <input type="text" class="form-control" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSafeDisplay($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user4; ?></label>
                  <input type="text" class="form-control" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->email) ? mswSafeDisplay($EDIT->email) : ''); ?>">
                </div>

                <div class="form-group">
                  <label id="labelPass"><?php echo $msg_user12; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswPassGenerator('labelPass','userPass');return false" title="<?php echo mswSafeDisplay($msg_accounts20); ?>"><i class="fa fa-refresh fa-fw"></i></a></span>
                     <input type="password" class="form-control" name="userPass" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enabled" value="yes"<?php echo (isset($EDIT->enabled) && $EDIT->enabled=='yes' ? ' checked="checked"' : (!isset($EDIT->enabled) ? ' checked="checked"' : '')); ?>> <?php echo $msg_accounts19; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_accounts31; ?></label>
                  <textarea class="form-control" rows="5" cols="40" name="reason"><?php echo (isset($EDIT->reason) ? mswSafeDisplay($EDIT->reason) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enableLog" value="yes"<?php echo (isset($EDIT->enableLog) && $EDIT->enableLog=='yes' ? ' checked="checked"' : (!isset($EDIT->enableLog) ? ' checked="checked"' : '')); ?>> <?php echo $msg_accounts40; ?>
                    </label>
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
                  <option value="<?php echo $k; ?>"<?php echo (isset($EDIT->timezone) ? mswSelectedItem($EDIT->timezone,$k) : ''); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_accounts39; ?></label>
                  <select name="language" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  $showlang = opendir(REL_PATH . 'content/language');
                  while (false!==($read=readdir($showlang))) {
                   if (is_dir(REL_PATH . 'content/language/'.$read) && !in_array($read,array('.','..'))) {
                   ?>
                   <option<?php echo (isset($EDIT->language) ? mswSelectedItem($read,$EDIT->language) : ''); ?>><?php echo $read; ?></option>
                   <?php
                   }
                  }
                  closedir($showlang);
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_accounts16; ?></label>
                  <input type="text" class="form-control" name="ip" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->ip) ? mswSafeDisplay($EDIT->ip) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="four">

                 <div class="form-group">
                   <textarea class="form-control" rows="5" cols="40" name="notes"><?php echo (isset($EDIT->notes) ? mswSafeDisplay($EDIT->notes) : ''); ?></textarea>
                 </div>

              </div>
              <?php
              if (isset($EDIT->id)) {
              ?>
              <div class="tab-pane fade" id="five">

                <div class="form-group">
                  <label><?php echo $msg_systemportal8; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswSearchAccounts('dest_email','<?php echo $EDIT->id; ?>');return false" title="<?php echo mswSafeDisplay($msg_add6); ?>"><i class="fa fa-search fa-fw"></i> </a></span>
                    <input type="text" class="form-control" name="dest_email" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group accnte" style="display:none">
                  <select name="accnte" class="form-control" onchange="mswSelectAccount(this.value,'dest_email')"></select>
                </div>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <?php
           if (isset($EDIT->id)) {
           ?>
           <input type="hidden" name="currID" value="<?php echo $EDIT->id; ?>">
           <input type="hidden" name="old_pass" value="<?php echo mswSafeDisplay($EDIT->userPass); ?>">
           <input type="hidden" name="old_email" value="<?php echo mswSafeDisplay($EDIT->email); ?>">
           <?php
           }
           ?>
           <button class="btn btn-primary" type="button" onclick="mswProcess('accounts')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_accounts6 : $msg_accounts4); ?></span></button>
           <?php
           if (in_array('accountman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=accountman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>