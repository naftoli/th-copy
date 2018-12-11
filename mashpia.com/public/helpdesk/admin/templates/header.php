<?php if (!defined('PATH')) { exit; }
include(PATH . 'templates/menu.php');
include(PATH . 'templates/menu-panel.php');
$unread = mswUnreadMailbox($MSTEAM->id);
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($html_lang) ? $html_lang : 'en'); ?>" dir="<?php echo (isset($lang_dir) ? $lang_dir : 'ltr'); ?>">
	<head>
    <meta charset="<?php echo $msg_charset; ?>">

    <title><?php echo ($title ? $title.': ' : '').$msg_script.' - '.$msg_adheader.(LICENCE_VER!='unlocked' ? ' (Free Version)' : '').(defined('DEV_BETA') && DEV_BETA!='no' ? ' - BETA VERSION' : ''); ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="templates/css/bootstrap.css" rel="stylesheet">
    <link href="templates/css/bootstrap-dialog.css" rel="stylesheet">
    <link href="templates/css/theme.css" rel="stylesheet">
    <link href="templates/css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="templates/css/jquery-ui.css" rel="stylesheet">
    <link href="templates/css/fam-icons.css" rel="stylesheet">
    <link href="templates/css/jquery.mmenu.css" rel="stylesheet">
    <?php
    if (isset($mswUploadDropzone['ajax']) || defined('DROPZONE_LOADER')) {
    ?>
    <link href="templates/css/jquery.uploader.css" rel="stylesheet">
    <?php
    }
    if (isset($loadiBox)) {
    ?>
    <link href="templates/css/jquery.ibox.css" rel="stylesheet">
    <?php
    }
    if (isset($loadJQPlot)) {
    ?>
    <link href="templates/css/jquery.jqplot.css" rel="stylesheet">
    <?php
    }
    if (isset($loadBBCSS)) {
    ?>
    <link href="templates/css/bbcode.css" rel="stylesheet">
    <?php
    }
    ?>
    <link href="templates/css/ms.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico">

    <?php
	  // For meta reloads, do NOT remove..
	  if (isset($metaReload)) {
	    echo $metaReload;
	  }
	  ?>
	</head>

	<body>

  <div id="mshtmlwrapper">

  <div class="navbar navbar-default navbar-fixed-top Fixed" id="msnavheader">

    <?php
    $beta = '';
    if (defined('DEV_BETA') && DEV_BETA != 'no') {
      $beta = '&nbsp;&nbsp;&nbsp;(v' . $SETTINGS->softwareVersion . ', b' . DEV_BETA . ')';
    }
    ?>
    <div class="container msheader">

      <div class="row visible-sm visible-md visible-lg">
        <div class="msheaderleft col-lg-5 col-md-6 col-sm-6">
          <button class="btn btn-info slidepanelbuttonleft" id="leftpanelbutton"><i class="fa fa-navicon fa-fw"></i></button> <a href="index.php"> <i class="fa fa-lock fa-fw"></i><?php echo mswCleanData($msg_adheader) . $beta; ?></a>
        </div>
        <div class="msheaderright msheaderrighta col-lg-7 col-md-6 col-sm-6 text-right">
          <?php
          foreach ($msTopMenu AS $ntm) {
          if ($ntm['url'] == 'index.php?p=mailbox') {
          ?>
          <a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i><span class="<?php echo $ntm['class']; ?>"><?php echo $ntm['text']; ?> (<span class="mailboxcount"><?php echo ($unread > 0 ? '<span class="unread">' . $unread . '</span>' : '0'); ?></span>)</span></a>
          <?php
          } else {
          ?>
          <a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i> <span class="<?php echo $ntm['class']; ?>"><?php echo $ntm['text']; ?></span></a>
          <?php
          }
          }
          ?>
        </div>
      </div>

      <div class="row visible-xs">
        <div class="msheaderleft col-xs-3">
          <button class="btn btn-info slidepanelbuttonleft" id="leftpanelbuttonxs"><i class="fa fa-navicon fa-fw"></i></button>
        </div>
        <div class="msheadermiddle col-xs-6 text-center">
          <a href="index.php"><?php echo mswCleanData($msg_adheader) . $beta; ?></a>
        </div>
        <div class="msheaderright col-xs-3 text-right">
          <div class="btn-group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-chevron-down fa-fw"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
              <?php
              foreach ($msTopMenu AS $ntm) {
              if ($ntm['url'] == 'index.php?p=mailbox') {
              ?>
              <li><a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i> <?php echo $ntm['text']; ?> (<span class="mailboxcount"><?php echo ($unread > 0 ? '<span class="unread">' . $unread . '</span>' : '0'); ?></span>)</a></li>
              <?php
              } else {
              ?>
              <li><a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i> <?php echo $ntm['text']; ?></a></li>
              <?php
              }
              }
              ?>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>