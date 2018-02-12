<?php if (!defined('PATH')) { exit; } ?>
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
      <li class="active"><?php echo $msg_adheader60; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response22; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-random fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response20; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo str_replace('{max}', mswFileSizeConversion($MSUPL->getMaxSize()), $msadminlang3_1uploads[0]); ?>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="clear" value="yes"> <?php echo $msg_import4; ?></label>
                  </div>
                </div>

                <hr>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" value="0" onclick="mswCheckBoxes(this.checked,'#cb')"> <?php echo $msg_response6; ?></label>
                  </div>
                </div>

                <div id="cb">
                  <?php
                  // If global log in no filter necessary..
                  $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQLDepartmentFilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`")
                            or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                  while ($DEPT = mysqli_fetch_object($q_dept)) {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="dept[]" value="<?php echo $DEPT->id; ?>"> <?php echo mswSafeDisplay($DEPT->name); ?></label>
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
            <button class="btn btn-primary" type="button" disabled="disabled" onclick="mswProcess('srimport')" id="upbutton"><i class="fa fa-upload fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_adheader60); ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswDropZoneReload('after')" id="dropzonereload" style="display:none"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msadminlang3_1uploads[2]); ?></span></button>
            <?php
            if (in_array('responseman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=responseman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

        <div class="text-right">
          &#8226; <a href="templates/examples/responses.csv"><?php echo $msg_import15; ?></a> &#8226;
        </div>

      </div>
    </div>
    </form>

  </div>