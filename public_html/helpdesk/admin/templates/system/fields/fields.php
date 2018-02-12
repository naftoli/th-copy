<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int)$_GET['edit'];
  $EDIT          = mswGetTableData('cusfields','id',$_GET['edit']);
  checkIsValid($EDIT);
  $deptS         = explode(',',$EDIT->departments);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('fieldsman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=fieldsman"><?php echo $msg_adheader43; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_customfields11 : $msg_customfields2); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-list-alt fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_customfields34; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_customfields35; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-random fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_customfields36; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label class="radio-inline">
                    <input type="radio" name="fieldType" value="input"<?php echo (isset($EDIT->id) && $EDIT->fieldType=='input' ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_customfields6; ?>
                  </label>

                  <label class="radio-inline">
                    <input type="radio" name="fieldType" value="textarea"<?php echo (isset($EDIT->id) && $EDIT->fieldType=='textarea' ? ' checked="checked"' : ''); ?>> <?php echo $msg_customfields5; ?>
                  </label>

                  <label class="radio-inline">
                    <input type="radio" name="fieldType" value="select"<?php echo (isset($EDIT->id) && $EDIT->fieldType=='select' ? ' checked="checked"' : ''); ?>> <?php echo $msg_customfields7; ?>
                  </label>

                  <label class="radio-inline">
                    <input type="radio" name="fieldType" value="checkbox"<?php echo (isset($EDIT->id) && $EDIT->fieldType=='checkbox' ? ' checked="checked"' : ''); ?>> <?php echo $msg_customfields8; ?>
                  </label>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_customfields3; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="fieldInstructions" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->fieldInstructions) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_customfields10; ?></label>
                  <textarea class="form-control" rows="3" cols="40" name="fieldOptions" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->fieldOptions) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="enField" value="yes"<?php echo (isset($EDIT->id) && $EDIT->enField=='yes' ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_customfields27; ?>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="fieldReq" value="yes"<?php echo (isset($EDIT->id) && $EDIT->fieldReq=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_customfields9; ?>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="fieldLoc[]" value="ticket"<?php echo (isset($EDIT->id) && strpos($EDIT->fieldLoc,'ticket')!==false ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_customfields18; ?>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="fieldLoc[]" value="reply"<?php echo (isset($EDIT->id) && strpos($EDIT->fieldLoc,'reply')!==false ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_customfields19; ?>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="fieldLoc[]" value="admin"<?php echo (isset($EDIT->id) && strpos($EDIT->fieldLoc,'admin')!==false ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_customfields20; ?>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="repeatPref" value="yes"<?php echo (isset($EDIT->id) && $EDIT->repeatPref=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_customfields28; ?>
                      </label>
                    </div>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" value="0" onclick="mswCheckBoxes(this.checked,'#three')"> <i class="fa fa-check-square fa-fw"></i>
                    </label>
                  </div>
                </div>

                <div>
                <?php
                $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($DEPT = mysqli_fetch_object($q_dept)) {
                ?>
		            <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="dept[]" value="<?php echo $DEPT->id; ?>"<?php echo (isset($EDIT->id) && in_array($DEPT->id,$deptS) ? ' checked="checked"' : ''); ?>> <?php echo mswSafeDisplay($DEPT->name); ?><br>
                    </label>
                  </div>
		              <input type="hidden" name="deptall[]" value="<?php echo $DEPT->id; ?>">
		            </div>
                <?php
                }
                ?>
		            </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('fields')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msg_levels10 : $msg_levels2)); ?></span></button>
           <?php
           if (in_array('fieldsman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=fieldsman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>