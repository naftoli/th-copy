<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('faqman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=faqman"><?php echo $msg_adheader47; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader55; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response22; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-reorder fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_import10; ?></span></a></li>
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
                    <label><input type="checkbox" value="0" onclick="mswCheckBoxes(this.checked,'#cb')"> <?php echo $msg_kbase6; ?></label>
                  </div>
                </div>

                <div id="cb">
                <?php
                $q_cat = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `name`, `private` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY `name`")
                         or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($CAT = mysqli_fetch_object($q_cat)) {
                ?>
		            <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="cat[]" value="<?php echo $CAT->id; ?>"><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswSafeDisplay($CAT->name); ?></label>
                  </div>
		              <input type="hidden" name="catall[]" value="<?php echo $CAT->id; ?>">
                </div>
                <?php
                $q_cat2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `name`, `private` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CAT->id}' ORDER BY `name`")
                          or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($SUB = mysqli_fetch_object($q_cat2)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="cat[]" value="<?php echo $SUB->id; ?>">- <?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswCleanData($SUB->name); ?></label>
                  </div>
                  <input type="hidden" name="catall[]" value="<?php echo $SUB->id; ?>">
                </div>
                <?php
                }
                }
                ?>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="button" disabled="disabled" onclick="mswProcess('faqimport')" id="upbutton"><i class="fa fa-upload fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_adheader55); ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswDropZoneReload('after')" id="dropzonereload" style="display:none"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msadminlang3_1uploads[2]); ?></span></button>
            <?php
            if (in_array('faqman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faqman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

        <div class="text-right">
          &#8226; <a href="templates/examples/faqs.csv"><?php echo $msg_import15; ?></a> &#8226;
        </div>

      </div>
    </div>
    </form>

  </div>