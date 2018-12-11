<?php if (!defined('PATH')) { exit; }
$tempSets = ($SETTINGS->langSets ? unserialize($SETTINGS->langSets) : array());
$defLogs  = ($SETTINGS->defKeepLogs ? unserialize($SETTINGS->defKeepLogs) : array());
$apiHndls = ($SETTINGS->apiHandlers ? explode(',',$SETTINGS->apiHandlers) : array());
include_once(PATH . 'control/recaptcha.php');
define('LOAD_DATE_PICKERS', 1);
define('JS_LOADER', 'settings.php');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader2; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="dropdown active">
					  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-cog fa-fw" title="<?php echo mswSafeDisplay($msg_settings22); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings22; ?></span> <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="#one_a" data-toggle="tab"><?php echo $msg_settings86; ?></a></li>
              <li><a href="#one_g" data-toggle="tab"><?php echo $msg_settings105; ?></a></li>
              <li><a href="#one_f" data-toggle="tab"><?php echo $msg_settings92; ?></a></li>
              <li><a href="#one_b" data-toggle="tab"><?php echo $msg_settings87; ?></a></li>
              <li><a href="#one_c" data-toggle="tab"><?php echo $msg_settings91; ?></a></li>
              <li><a href="#one_d" data-toggle="tab"><?php echo $msg_settings88; ?></a></li>
              <li><a href="#one_h" data-toggle="tab"><?php echo $msg_settings119; ?></a></li>
              <li><a href="#one_e" data-toggle="tab"><?php echo $msg_settings89; ?></a></li>
            </ul>
		      </li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-clock-o fa-fw" title="<?php echo mswSafeDisplay($msg_settings10); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings10; ?></span></a></li>
          <li><a href="#five" data-toggle="tab"><i class="fa fa-paperclip fa-fw" title="<?php echo mswSafeDisplay($msg_settings23); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings23; ?></span></a></li>
	        <li><a href="#six" data-toggle="tab"><i class="fa fa-info-circle fa-fw" title="<?php echo mswSafeDisplay($msg_settings29); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings29; ?></span></a></li>
          <li class="dropdown">
					  <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-fw" title="<?php echo mswSafeDisplay($msg_settings85); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings85; ?></span> <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right verysmalladjustment" role="menu">
              <li><a href="#seven" data-toggle="tab"><?php echo $msg_settings24; ?></a></li>
              <li><a href="#three" data-toggle="tab"><?php echo $msg_settings83; ?></a></li>
              <li><a href="#four" data-toggle="tab"><?php echo $msg_settings62; ?></a></li>
              <li><a href="#nine" data-toggle="tab"><?php echo $msg_settings111; ?></a></li>
              <?php
              if (LICENCE_VER == 'unlocked') {
              ?>
              <li><a href="#eight" data-toggle="tab"><?php echo $msg_settings56; ?></a></li>
              <?php
              }
              ?>
            </ul>
		      </li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one_a">

                <div class="form-group">
                  <label><?php echo $msg_settings9; ?></label>
                  <input type="text" class="form-control" maxlength="150" name="website" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->website); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings20; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="scriptpath" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData($SETTINGS->scriptpath); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings84; ?></label>
                  <select name="language" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  $showlang = opendir(REL_PATH . 'content/language');
                  while (false!==($read=readdir($showlang))) {
                    if (is_dir(REL_PATH . 'content/language/' . $read) && !in_array($read, array('.', '..'))) {
                    ?>
                    <option<?php echo mswSelectedItem($read,$SETTINGS->language); ?>><?php echo $read; ?></option>
                    <?php
                    }
                  }
                  closedir($showlang);
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings30; ?></label>
                  <input type="text" class="form-control" maxlength="50" tabindex="<?php echo (++$tabIndex); ?>" name="afolder" value="<?php echo $SETTINGS->afolder; ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="one_g">

                <?php
                $showlang = opendir(REL_PATH . 'content/language');
                while (false!==($read=readdir($showlang))) {
                  if (is_dir(REL_PATH . 'content/language/' . $read) && !in_array($read,array('.','..'))) {
                  ?>
                  <div class="form-group">
                  <label>
                  <?php
                  echo ucfirst(strtolower($read));
                  ?>
                  </label>
                  <select name="templateSet[<?php echo $read; ?>]" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  $showsets = opendir(REL_PATH . 'content');
                  while (false!==($rd=readdir($showsets))) {
                    if (is_dir(REL_PATH . 'content/'.$rd) && !in_array($rd,array('.','..')) && substr($rd,0,1)=='_') {
                    ?>
                    <option<?php echo (isset($tempSets[$read]) ? mswSelectedItem($tempSets[$read],$rd) : ''); ?> value="<?php echo $rd; ?>"><?php echo $rd; ?></option>
                    <?php
                    }
                  }
                  closedir($showsets);
                  ?>
                  </select>
                  </div>
                  <?php
                  }
                }
                closedir($showlang);
                ?>

              </div>
              <div class="tab-pane fade" id="one_b">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="autoCloseMail" value="yes"<?php echo ($SETTINGS->autoCloseMail=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings75; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings13; ?> (<?php echo $msg_settings14; ?>)</label>
                  <input type="text" class="form-control" name="autoClose" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->autoClose; ?>">
                </div>

                <p>
                 <a href="../close-tickets.php" onclick="window.open(this);return false"><i class="fa fa-play fa-fw"></i> <?php echo $msg_user105; ?></a>
                </p>

              </div>
              <div class="tab-pane fade" id="one_c">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="createPref" value="yes"<?php echo ($SETTINGS->createPref=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings90; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="ticketHistory" value="yes"<?php echo (isset($SETTINGS->ticketHistory) && $SETTINGS->ticketHistory=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings101; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="closenotify" value="yes"<?php echo (isset($SETTINGS->closenotify) && $SETTINGS->closenotify=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings103; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings21; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->email); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings104; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="replyto" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->replyto); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings114; ?></label>
                  <input type="text" class="form-control" maxlength="2" name="minTickDigits" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->minTickDigits); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="one_d">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="disputes" value="yes"<?php echo ($SETTINGS->disputes == 'yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings81; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="disputeAdminStop" value="yes"<?php echo ($SETTINGS->disputeAdminStop=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings129; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="one_e">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="apiLog" value="yes"<?php echo ($SETTINGS->apiLog=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings124; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="apiHandlers[]" value="json"<?php echo (in_array('json',$apiHndls) ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings125; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="apiHandlers[]" value="xml"<?php echo (in_array('xml',$apiHndls) ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings126; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings59; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswGenerateAPIKey();return false"><i class="fa fa-key fa-fw"></i></a></span>
                    <input type="text" class="form-control" maxlength="100" name="apiKey" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->apiKey; ?>">
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="one_f">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="createAcc" value="yes"<?php echo ($SETTINGS->createAcc=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings93; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enableBBCode" value="yes"<?php echo ($SETTINGS->enableBBCode=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings58; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="accProfNotify" value="yes"<?php echo ($SETTINGS->accProfNotify=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings106; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="newAccNotify" value="yes"<?php echo ($SETTINGS->newAccNotify=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings108; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enableLog" value="yes"<?php echo ($SETTINGS->enableLog=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings110; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings99; ?></label>
                  <input type="text" class="form-control" name="loginLimit" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->loginLimit; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings100; ?></label>
                  <input type="text" class="form-control" name="banTime" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->banTime; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings107; ?></label>
                  <input type="text" class="form-control" name="minPassValue" maxlength="3" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->minPassValue; ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_settings2; ?></label>
                  <input type="text" class="form-control" name="dateformat" maxlength="20" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->dateformat; ?>">
                  <input type="text" class="form-control form_box_margin_5" name="timeformat" maxlength="15" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->timeformat; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings12; ?></label>
                  <select name="timezone" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  // TIMEZONES..
                  foreach ($timezones AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo mswSelectedItem($SETTINGS->timezone,$k); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings64; ?></label>
                  <select name="weekStart" tabindex="<?php echo ++$tabIndex; ?>" class="form-control">
                  <option value="sun"<?php echo mswSelectedItem($SETTINGS->weekStart,'sun'); ?>><?php echo $msg_settings65; ?></option>
                  <option value="mon"<?php echo mswSelectedItem($SETTINGS->weekStart,'mon'); ?>><?php echo $msg_settings66; ?></option>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings69; ?></label>
                  <select name="jsDateFormat" tabindex="<?php echo ++$tabIndex; ?>" class="form-control">
                  <?php
                  foreach (array('DD-MM-YYYY','DD/MM/YYYY','YYYY-MM-DD','YYYY/MM/DD','MM-DD-YYYY','MM/DD/YYYY') AS $jsf) {
                  ?>
                  <option value="<?php echo $jsf; ?>"<?php echo mswSelectedItem($SETTINGS->jsDateFormat,$jsf); ?>><?php echo $jsf; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="checkbox">
                  <label><input type="checkbox" name="sysstatus" value="yes"<?php echo ($SETTINGS->sysstatus=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings70; ?></label>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings82; ?></label>
                  <textarea name="offlineReason" rows="5" cols="40" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSafeDisplay($SETTINGS->offlineReason); ?></textarea>
                </div>

                <div class="form-group">
		              <label><?php echo $msg_settings73; ?></label>
                  <input type="text" class="form-control" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" id="from" name="autoenable" value="<?php echo (!in_array($SETTINGS->autoenable, array('0000-00-00','1970-01-01')) ? $MSDT->mswConvertMySQLDate($SETTINGS->autoenable) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="four">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enCapLogin" value="yes"<?php echo ($SETTINGS->enCapLogin=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings67; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings60; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="recaptchaPublicKey" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->recaptchaPublicKey); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings61; ?></label>
                  <input type="text" class="form-control" maxlength="250" name="recaptchaPrivateKey" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->recaptchaPrivateKey); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings109; ?></label>
                  <select name="recaptchaLang" class="form-control">
                  <?php
                  foreach ($gRC_Lang AS $rclv => $rclk) {
                  ?>
                  <option value="<?php echo $rclk; ?>"<?php echo mswSelectedItem($SETTINGS->recaptchaLang,$rclk); ?>><?php echo $rclv; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                  <select name="recaptchaTheme" class="form-control form_box_margin_5">
                  <?php
                  foreach ($gRC_Themes AS $rctk => $rctv) {
                  ?>
                  <option value="<?php echo $rctk; ?>"<?php echo mswSelectedItem($SETTINGS->recaptchaTheme,$rctk); ?>><?php echo $rctv; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="five">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="attachment" value="yes"<?php echo ($SETTINGS->attachment=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings3; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="rename" value="yes"<?php echo ($SETTINGS->rename=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings76; ?></label>
                  </div>
                </div>

		            <div class="form-group">
                  <label><?php echo $msg_settings4; ?></label>
                  <input type="text" class="form-control" name="filetypes" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->filetypes; ?>">
                </div>

                <div class="form-group" id="maxsizeinput">
                  <label><?php echo $msg_settings5; ?> (<?php echo $msg_script19; ?>)</label>
                  <div class="form-group input-group">
                    <div class="input-group-addon"><i class="fa fa-calculator fa-fw cursor_pointer" onclick="mswShowMaxSizeOptions()"></i></div>
                    <input type="text" class="form-control" tabindex="<?php echo (++$tabIndex); ?>" maxlength="15" name="maxsize" value="<?php echo $SETTINGS->maxsize; ?>">
                  </div>
                </div>

                <div class="form-group" id="maxsizeoptions" style="display:none">
                  <label><?php echo $msg_settings5; ?> (<?php echo $msg_script19; ?>)</label>
                  <select name="maxsizesel" onchange="mswMaxSize(this.value)" class="form-control">
                    <option value="<?php echo $SETTINGS->maxsize; ?>"><?php echo $msg_settings95; ?></option>
                    <?php
                    $mb = (1024 * 1024);
                    $gb = ((1024 * 1024) * 1024);
                    $l  = 0;
                    $sizes    =  array(
                      (1 * $mb) . '|1MB',
                      (2 * $mb) . '|2MB',
                      (3 * $mb) . '|3MB',
                      (4 * $mb) . '|4MB',
                      (5 * $mb) . '|5MB',
                      (10 * $mb) . '|10MB',
                      (15 * $mb) . '|15MB',
                      (20 * $mb) . '|20MB',
                      (50 * $mb) . '|50MB',
                      (100 * $mb) . '|100MB',
                      (1 * $gb) . '|1GB',
                      (2 * $gb) . '|2GB',
                      (5 * $gb) . '|5GB',
                      'x|' . $msg_settings96
                      );
                      foreach ($sizes AS $sk) {
                      $chop = explode('|',$sk);
                      ?>
                      <option value="<?php echo ($chop[0]=='x' ? '' : $chop[0]); ?>"><?php echo $chop[1]; ?></option>
                      <?php
                      }
                      ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings25; ?></label>
                  <?php
                  if (LICENCE_VER == 'locked') {
                  ?>
                  <input type="hidden" name="attachboxes" value="<?php echo RESTR_ATTACH; ?>">
                  <input type="text" disabled="disabled" class="form-control" maxlength="3" name="attachboxes_free" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->attachboxes; ?>">
                  <?php
                  } else {
                  ?>
                  <input type="text" class="form-control" maxlength="3" name="attachboxes" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->attachboxes; ?>">
                  <?php
                  }
                  ?>
                </div>

		            <div class="form-group">
                  <label><?php echo $msg_settings27; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="autoPath('server','attachpath');return false"><i class="fa fa-refresh fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="attachpath" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData($SETTINGS->attachpath); ?>">
		              </div>
                </div>

		            <div class="form-group">
                  <label><?php echo $msg_settings94; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="autoPath('http','attachhref');return false"><i class="fa fa-refresh fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="attachhref" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData($SETTINGS->attachhref); ?>">
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="six">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="kbase" value="yes"<?php echo ($SETTINGS->kbase=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enableVotes" value="yes"<?php echo ($SETTINGS->enableVotes=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings57; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="multiplevotes" value="yes"<?php echo ($SETTINGS->multiplevotes=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings32; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="faqcounts" value="yes"<?php echo ($SETTINGS->faqcounts=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1faq[1]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="renamefaq" value="yes"<?php echo ($SETTINGS->renamefaq=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings76; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings33; ?></label>
                  <input type="text" class="form-control" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" name="popquestions" value="<?php echo $SETTINGS->popquestions; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings34; ?></label>
                  <input type="text" class="form-control" maxlength="5" tabindex="<?php echo (++$tabIndex); ?>" name="cookiedays" value="<?php echo $SETTINGS->cookiedays; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings68; ?></label>
                  <input type="text" class="form-control" maxlength="3" tabindex="<?php echo (++$tabIndex); ?>" name="quePerPage" value="<?php echo $SETTINGS->quePerPage; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings98; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="autoPath('server','attachpathfaq');return false"><i class="fa fa-refresh fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="attachpathfaq" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData($SETTINGS->attachpathfaq); ?>">
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings97; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="autoPath('http','attachhreffaq');return false"><i class="fa fa-refresh fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="attachhreffaq" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswCleanData($SETTINGS->attachhreffaq); ?>">
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="seven">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enableMail" value="yes"<?php echo ($SETTINGS->enableMail=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings115; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="smtp_debug" value="yes"<?php echo ($SETTINGS->smtp_debug=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings15; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings16; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="?p=settings&amp;mailTest=yes" rel="ibox"><i class="fa fa-envelope fa-fw"></i></a></span>
                    <input type="text" class="form-control" name="smtp_host" maxlength="100" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->smtp_host; ?>">
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings17; ?></label>
                  <input type="text" class="form-control" name="smtp_user" maxlength="100" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->smtp_user; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings18; ?></label>
                  <input type="password" class="form-control" name="smtp_pass" maxlength="100" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSafeDisplay($SETTINGS->smtp_pass); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings19; ?></label>
                  <input type="text" class="form-control" name="smtp_port" maxlength="4" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->smtp_port; ?>">
                  <select name="smtp_security" tabindex="<?php echo ++$tabIndex; ?>" class="form-control margin_top_10">
                    <option value=""<?php echo mswSelectedItem($SETTINGS->smtp_security,''); ?>><?php echo $msg_settings78; ?></option>
                    <option value="tls"<?php echo mswSelectedItem($SETTINGS->smtp_security,'tls'); ?>><?php echo $msg_settings79; ?></option>
                    <option value="ssl"<?php echo mswSelectedItem($SETTINGS->smtp_security,'ssl'); ?>><?php echo $msg_settings80; ?></option>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="nine">

                <div class="form-group">
                  <label><?php echo $msg_settings112; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="defKeepLogs[user]" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($defLogs['user']) ? $defLogs['user'] : '0'); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings113; ?></label>
                  <input type="text" class="form-control" maxlength="5" name="defKeepLogs[acc]" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($defLogs['acc']) ? $defLogs['acc'] : '0'); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="one_h">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="imap_debug" value="yes"<?php echo ($SETTINGS->imap_debug=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_settings120; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="imap_attach" value="yes"<?php echo ($SETTINGS->imap_attach=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1[27]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="imap_notify" value="yes"<?php echo ($SETTINGS->imap_notify=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1[28]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings121; ?></label>
                  <input type="text" class="form-control" maxlength="10" name="imap_param" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->imap_param; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings122; ?> (M)</label>
                  <input type="text" class="form-control" maxlength="3" name="imap_memory" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->imap_memory; ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings123; ?></label>
                  <input type="text" class="form-control" maxlength="3" name="imap_timeout" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo $SETTINGS->imap_timeout; ?>">
                </div>

              </div>
              <?php
              if (LICENCE_VER == 'unlocked') {
              ?>
              <div class="tab-pane fade" id="eight">

                <div class="form-group">
                  <label><?php echo $msg_settings54; ?></label>
                  <textarea name="adminFooter" rows="5" cols="40" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSafeDisplay($SETTINGS->adminFooter); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_settings55; ?></label>
                  <textarea name="publicFooter" rows="5" cols="40" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSafeDisplay($SETTINGS->publicFooter); ?></textarea>
                </div>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="button" onclick="mswProcess('tlsettings')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_settings7); ?></span></button>
          </div>
        </div>
      </div>
    </div>
    </form>

  </div>