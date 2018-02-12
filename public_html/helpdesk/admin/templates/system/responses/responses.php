<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int)$_GET['edit'];
  $EDIT          = mswGetTableData('responses','id',$_GET['edit']);
  checkIsValid($EDIT);
  $deptArr       = ($EDIT->departments!='0' ? explode(',',$EDIT->departments) : array());
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('responseman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=responseman"><?php echo $msg_adheader54; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($_GET['edit']) ? $msg_response13 : $msg_adheader53); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response19; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-random fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response20; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enResponse" value="yes"<?php echo (isset($EDIT->enResponse) && $EDIT->enResponse=='yes' ? ' checked="checked"' : (!isset($EDIT->enResponse) ? ' checked="checked"' : '')); ?>> <?php echo $msg_response21; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_response; ?></label>
                  <input type="text" class="form-control" name="title" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->title) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_response2; ?></label>
                  <?php
                  define('BB_BOX','answer');
                  include(PATH . 'templates/system/bbcode-buttons.php');?>
                  <textarea class="form-control" rows="8" cols="40" name="answer" id="answer" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->answer) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" value="0" onclick="mswCheckBoxes(this.checked,'#two')"> <?php echo $msg_response6; ?>
                    </label>
                  </div>
                </div>

                <?php
                // If global log in no filter necessary..
                $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($DEPT = mysqli_fetch_object($q_dept)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="dept[]"<?php echo (isset($deptArr) && in_array($DEPT->id,$deptArr) ? ' checked="checked" ' : ' '); ?>value="<?php echo $DEPT->id; ?>"> <?php echo mswSafeDisplay($DEPT->name); ?>
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
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('response')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msg_response13 : $msg_response3)); ?></span></button>
           <?php
           if (in_array('responseman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=responseman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>