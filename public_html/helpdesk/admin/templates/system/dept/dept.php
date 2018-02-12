<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswGetTableData('departments','id',$_GET['edit']);
  checkIsValid($EDIT);
  $dayEn        = ($EDIT->days ? explode(',', $EDIT->days) : array());
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('deptman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=deptman"><?php echo $msg_dept9; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_dept5 : $msg_dept2); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_dept24; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-sign-in fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_dept25; ?></span></a></li>
	        <li><a href="#three" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1dept[1]; ?></span></a></li>
	      </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_dept19; ?></label>
                  <input type="text" class="form-control" maxlength="100" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSafeDisplay($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input<?php echo (isset($EDIT->id) && $EDIT->manual_assign=='yes' ? ' onclick="if(!this.checked){alert(\'' . mswSafeDisplay($msg_script_action5) . '\')}" ' : ' '); ?>type="checkbox" name="manual_assign" value="yes"<?php echo (isset($EDIT->id) && $EDIT->manual_assign=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_dept22; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group"><label><?php echo $msg_dept17; ?></label>
                  <input type="text" class="form-control" name="dept_subject" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->dept_subject) ? mswSafeDisplay($EDIT->dept_subject) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_dept18; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="dept_comments" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->dept_comments) ? mswSafeDisplay($EDIT->dept_comments) : ''); ?></textarea><br><br>
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="showDept" value="yes"<?php echo (isset($EDIT->id) && $EDIT->showDept=='yes' ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_dept15; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1dept[0]; ?></label>
                  <div style="margin-top:10px">
                  <?php
                  foreach (($SETTINGS->weekStart == 'sun' ? $msg_script29 : $msg_script28) AS $days) {
                  ?>
                  <input type="checkbox" name="days[]" value="<?php echo $days; ?>"<?php echo (isset($dayEn) && in_array($days, $dayEn) ? ' checked="checked"' : (!isset($dayEn) ? ' checked="checked"' : '')); ?>> <?php echo $days; ?>&nbsp;&nbsp;
                  <?php
                  }
                  ?>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('dept')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_dept5 : $msg_dept2); ?></span></button>
            <?php
            if (in_array('deptman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=deptman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>