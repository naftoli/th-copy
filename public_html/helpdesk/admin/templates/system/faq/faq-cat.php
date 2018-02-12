<?php if (!defined('PATH')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswGetTableData('categories','id',$_GET['edit']);
  checkIsValid($EDIT);
}
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('faq-catman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=faq-catman"><?php echo $msg_adheader45; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_kbasecats5 : $msg_kbase16); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-edit fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_kbase59; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enCat" value="yes"<?php echo (isset($EDIT->enCat) && $EDIT->enCat=='yes' ? ' checked="checked"' : (!isset($EDIT->enCat) ? ' checked="checked"' : '')); ?>> <?php echo $msg_kbase24; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="private" value="yes"<?php echo (isset($EDIT->private) && $EDIT->private=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1faq[2]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase17; ?></label>
                  <input class="form-control" type="text" name="name" tabindex="<?php echo (++$tabIndex); ?>" maxlength="100" value="<?php echo (isset($EDIT->name) ? mswSafeDisplay($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase15; ?></label>
                  <input class="form-control" type="text" name="summary" tabindex="<?php echo (++$tabIndex); ?>" maxlength="250" value="<?php echo (isset($EDIT->summary) ? mswSafeDisplay($EDIT->summary) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase38; ?></label>
                  <select name="subcat" class="form-control">
                    <option value="0"><?php echo $msg_kbase36; ?></option>
                    <?php
                    $q_cat = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY `name`")
                             or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
                    if (mysqli_num_rows($q_cat)>0) {
                    ?>
                    <optgroup label="<?php echo mswSafeDisplay($msg_kbase37); ?>">
                    <?php
                    while ($CAT = mysqli_fetch_object($q_cat)) {
                    ?>
                    <option<?php echo (isset($EDIT->id) ? mswSelectedItem($EDIT->subcat,$CAT->id) : ''); ?> value="<?php echo $CAT->id; ?>"><?php echo mswCleanData($CAT->name); ?></option>
                    <?php
                    }
                    }
                    ?>
                    </optgroup>
                  </select>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('faqcat')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData((isset($EDIT->id) ? $msg_kbasecats5 : $msg_kbase16)); ?></span></button>
            <?php
            if (in_array('faq-catman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faq-catman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>