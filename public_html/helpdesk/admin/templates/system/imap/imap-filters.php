<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('imapman', $userAccess) || $MSTEAM->id == '1') {
      ?>
      <li><a href="index.php?p=imapman"><?php echo $msadminlang3_1[4]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader62; ?></li>
    </ol>

    <form method="post" action="#">
    <script>
    //<![CDATA[
    function showResetDays(check) {
      jQuery('input[name="reset_days"]').prop('disabled',(!check ? true : false));
    }
    //]]>
    </script>
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-cutlery fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap43; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-bullseye fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap44; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-bolt fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap45; ?></span></a></li>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-flask fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_imap64; ?></span><b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="#five" data-toggle="tab"><?php echo $msg_imap65; ?></a></li>
              <li><a href="#four" data-toggle="tab"><?php echo $msg_imap61; ?></a></li>
              <li><a href="#six" data-toggle="tab"><?php echo $msg_spam6; ?></a></li>
            </ul>
          </li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="learning" value="yes"<?php echo (isset($B8_CFG->learning) && $B8_CFG->learning=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap50; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap46; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="tokens" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->tokens) ? mswSafeDisplay($B8_CFG->tokens) : '15'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap47; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="min_dev" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->min_dev) ? mswSafeDisplay($B8_CFG->min_dev) : '0.5'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap48; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="x_constant" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->x_constant) ? mswSafeDisplay($B8_CFG->x_constant) : '0.5'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap49; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="s_constant" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->s_constant) ? mswSafeDisplay($B8_CFG->s_constant) : '0.3'); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="num_parse" value="yes"<?php echo (isset($B8_CFG->num_parse) && $B8_CFG->num_parse=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap53; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="uri_parse" value="yes"<?php echo (isset($B8_CFG->uri_parse) && $B8_CFG->uri_parse=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap54; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="html_parse" value="yes"<?php echo (isset($B8_CFG->html_parse) && $B8_CFG->html_parse=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap55; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap51; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="min_size" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->min_size) ? mswSafeDisplay($B8_CFG->min_size) : '3'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap52; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="max_size" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($B8_CFG->max_size) ? mswSafeDisplay($B8_CFG->max_size) : '30'); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label class="checkbox">
                    <label><input<?php echo (!function_exists('mb_substr') ? ' disabled="disabled" ' : ' '); ?>type="checkbox" name="multibyte" value="yes"<?php echo (isset($B8_CFG->multibyte) && $B8_CFG->multibyte=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_imap56.(!function_exists('mb_substr') ? $msg_imap58 : ''); ?></label>
                  </label>
                </div>

                <?php
                // Show encoding sets..
                if (function_exists('mb_list_encodings')) {
                ?>
                <div class="form-group">
                  <label><?php echo $msg_imap57; ?></label>
                  <select name="encoder" class="form-control">
                   <?php
                   foreach (mb_list_encodings() AS $enc) {
                   ?>
                   <option value="<?php echo $enc; ?>"<?php echo (isset($B8_CFG->encoder) && $B8_CFG->encoder==$enc ? ' selected="selected"' : ''); ?>><?php echo $enc; ?></option>
                   <?php
                   }
                   ?>
                  </select>
                </div>
                <?php
                }
                ?>

              </div>
              <div class="tab-pane fade" id="four">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="reset" value="yes" onclick="showResetDays(this.checked)"> <?php echo $msg_imap62; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_imap69; ?></label>
                  <input type="text" class="form-control" maxlength="3" name="reset_days" tabindex="<?php echo (++$tabIndex); ?>" value="" disabled="disabled">
                </div>

              </div>
              <div class="tab-pane fade" id="five">

                <div class="form-group">
                  <label><?php echo $msg_imap66; ?></label>
                  <textarea class="form-control" name="add-to" rows="5" cols="20" tabindex="<?php echo (++$tabIndex); ?>"></textarea>
                </div>

                <div class="form-group">
                  <select name="classify" class="form-control">
                    <option value="spam" selected="selected"><?php echo $msg_imap67; ?></option>
                    <option value="ham"><?php echo $msg_imap68; ?></option>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="six">

                <div class="form-group">
                  <label><?php echo $msg_spam7; ?></label>
                  <textarea class="form-control" name="skipFilters" rows="5" cols="20" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($B8_CFG->skipFilters) ? mswSafeDisplay($B8_CFG->skipFilters) : ''); ?></textarea>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="button" onclick="mswProcess('imspam')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_imap59; ?></span></button>
            <?php
            if (in_array('imapman', $userAccess)  || $MSTEAM->id == '1') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=imapman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels11); ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>