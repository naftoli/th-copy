<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('accountman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=accountman"><?php echo $msg_adheader40; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader59; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response22; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-globe fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts33; ?></span></a></li>
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
                    <label><input type="checkbox" name="welcome" value="yes" checked="checked"> <?php echo $msg_accounts23; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1acc[0]; ?></label>
                  <textarea class="form-control" rows="5" cols="20" name="notes"></textarea>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" disabled="disabled" onclick="mswProcess('accimp')" id="upbutton"><i class="fa fa-upload fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_adheader59; ?></span></button>
           <button class="btn btn-link" type="button" onclick="mswDropZoneReload('after')" id="dropzonereload" style="display:none"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msadminlang3_1uploads[2]); ?></span></button>
           <?php
           if (in_array('accountman', $userAccess)  || $MSTEAM->id == '1') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=accountman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

        <div class="text-right">
          &#8226; <a href="templates/examples/accounts.csv"><?php echo $msg_import15; ?></a> &#8226;
        </div>

      </div>
    </div>
    </form>

  </div>