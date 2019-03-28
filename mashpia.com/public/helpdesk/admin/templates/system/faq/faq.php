<?php if (!defined('PATH')) { exit; }
$categories  = array();
$attachments = array();
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int) $_GET['edit'];
  $EDIT         = mswGetTableData('faq','id', $_GET['edit']);
  checkIsValid($EDIT);
  $categories   = mswFaqCategories($EDIT->id,'get');
  $qAS          = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `itemID` FROM `" . DB_PREFIX . "faqassign` WHERE `question` = '{$EDIT->id}' AND `desc` = 'attachment' GROUP BY `itemID`")
                  or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
  while ($AA = mysqli_fetch_object($qAS)) {
    $attachments[] = $AA->itemID;
  }
}
$qA  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "faqattach` WHERE `enAtt` = 'yes' ORDER BY `name`")
       or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
?>
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
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_kbase13 : $msg_kbase3); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_kbase42; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-reorder fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_import10; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader33; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enFaq" value="yes"<?php echo (isset($EDIT->enFaq) && $EDIT->enFaq=='yes' ? ' checked="checked"' : (!isset($EDIT->enFaq) ? ' checked="checked"' : '')); ?>> <?php echo $msg_kbase28; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="featured" value="yes"<?php echo (isset($EDIT->featured) && $EDIT->featured=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1faq[0]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase; ?></label>
                  <input type="text" class="form-control" name="question" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->question) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase2; ?></label>
                  <?php
                  define('BB_BOX','answer');
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea class="form-control" rows="8" cols="40" name="answer" id="answer" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->id) ? mswSafeDisplay($EDIT->answer) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" value="0" onclick="mswCheckBoxes(this.checked,'#two')"> <?php echo $msg_kbase6; ?></label>
                  </div>
                </div>

		            <?php
                $q1 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `name`, `private` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' AND `enCat` = 'yes' ORDER BY `name`")
                      or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($CAT = mysqli_fetch_object($q1)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="cat[]" value="<?php echo $CAT->id; ?>"<?php echo mswCheckedArrItem($categories,$CAT->id); ?>><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswCleanData($CAT->name); ?></label>
                  </div>
                  <input type="hidden" name="catall[]" value="<?php echo $CAT->id; ?>">
                </div>
                <?php
                $q2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `name` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CAT->id}' AND `enCat` = 'yes' ORDER BY `name`")
                      or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                while ($SUB = mysqli_fetch_object($q2)) {
                ?>
                <div class="form-group">
                  <div class="checkbox indent_10">
                    <label><input type="checkbox" name="cat[]" value="<?php echo $SUB->id; ?>"<?php echo mswCheckedArrItem($categories,$SUB->id); ?>><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswCleanData($SUB->name); ?></label>
                  </div>
                  <input type="hidden" name="catall[]" value="<?php echo $SUB->id; ?>">
                </div>
                <?php
                }
                }
                ?>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th style="width:6%">
                        <input type="checkbox" onclick="mswCheckBoxes(this.checked,'#three')">
                      </th>
                      <th><?php echo $msg_attachments16; ?></th>
                      <th><?php echo $msg_kbase49; ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if (mysqli_num_rows($qA)>0) {
                    while ($ATT = mysqli_fetch_object($qA)) {
                    $ext  = substr(strrchr(strtolower(($ATT->remote ? $ATT->remote : $ATT->path)),'.'),1);
                    $info = '['.strtoupper($ext).'] '.($ATT->size>0 ? mswFileSizeConversion($ATT->size) : 'N/A');
                    ?>
                    <tr>
                      <td><input type="checkbox" name="att[]" value="<?php echo $ATT->id; ?>"<?php echo mswCheckedArrItem($attachments,$ATT->id); ?>></td>
                      <td><?php echo ($ATT->name ? mswSafeDisplay($ATT->name) : ($ATT->remote ? $ATT->remote : $ATT->path)); ?></td>
                      <td><a href="?fattachment=<?php echo $ATT->id; ?>" title="<?php echo mswSafeDisplay($msg_kbase50); ?>"><?php echo $info; ?></a></td>
                    </tr>
                    <?php
                    }
                    } else {
                    ?>
                    <tr class="warning nothing_to_see">
                      <td colspan="3"><?php echo $msg_attachments9; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('faq')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msg_kbase13 : $msg_kbase3)); ?></span></button>
            <?php
            if (in_array('faqman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faqman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>