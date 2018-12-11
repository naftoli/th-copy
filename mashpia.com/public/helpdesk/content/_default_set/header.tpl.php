<?php if (!defined('PATH')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo $this->LANG; ?>" dir="<?php echo $this->DIR; ?>">
	<head>
    <meta charset="<?php echo $this->CHARSET; ?>">

    <title><?php echo $this->TITLE; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap-dialog.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/jquery-ui.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/fam-icons.css" rel="stylesheet">
    <?php
	  // Load Page Specific CSS..
	  echo $this->FILES;
	  ?>
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/ms.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $this->SETTINGS->scriptpath; ?>/favicon.ico">

	</head>

	<body>

  <div class="navbar navbar-default navbar-fixed-top Fixed" id="msnavheader">

    <div class="container msheader">

      <?php
      // Top menu for large screens
      ?>
      <div class="row hidden-xs">
        <div class="<?php echo ($this->LOGGED_IN == 'yes' ? 'msheaderleft' : ''); ?> col-lg-4 col-md-4 col-sm-6">
          <a href="<?php echo $this->SETTINGS->scriptpath; ?>"><i class="fa fa-<?php echo ($this->LOGGED_IN == 'yes' ? 'lock' : 'life-ring'); ?> fa-fw"></i> <?php echo $this->TOP_BAR_TITLE; ?></a>
        </div>
        <div class="msheaderright col-lg-8 col-md-8 col-sm-6 text-right">
          <?php
				  // Is visitor logged in?
          if ($this->LOGGED_IN == 'yes') {
				  ?>
          <a href="<?php echo $this->SETTINGS->scriptpath; ?>"><i class="fa fa-dashboard fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[2]; ?></span></a>
          <div class="btn-group">
            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[6]; ?></span> <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open"><i class="fa fa-pencil fa-fw"></i> <?php echo $this->TXT[1]; ?></a></li>
              <li role="separator" class="divider"></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=profile"><i class="fa fa-user fa-fw"></i> <?php echo $this->TXT[9]; ?></a></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=history"><i class="fa fa-calendar fa-fw"></i> <?php echo $this->TXT[3]; ?></a></li>
              <?php
              // Is the dispute system enabled?
              if ($this->SETTINGS->disputes == 'yes') {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=disputes"><i class="fa fa-bullhorn fa-fw"></i> <?php echo $this->TXT[10]; ?></a></li>
              <?php
              }

              //===================================================================
              // Custom pages
              // Comment out if you want links to appear on the drop down menu..
              //===================================================================

              /*
              if (!empty($this->OTHER_PAGES_MENU)) {
                ?>
                <li role="separator" class="divider"></li>
                <?php
                foreach ($this->OTHER_PAGES_MENU AS $k) {
                ?>
                <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?pg=<?php echo $k['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $k['name']; ?></a></li>
                <?php
                }
              }
              */

              ?>
              <li role="separator" class="divider"></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?lo=1"><i class="fa fa-unlock fa-fw"></i> <?php echo $this->TXT[5]; ?></a></li>
			  <li><a href="/" id="mashpia_link" rel="nofollow"><i class="fa fa-reply fa-fw"></i> Back to Mashpia.com</a></li>
            </ul>
          </div>
          <?php
          } else {
          ?>
          <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open" rel="nofollow"><i class="fa fa-pencil fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[1]; ?></span></a>
          <?php
          // Is account creation enabled?
          if ($this->SETTINGS->createAcc == 'yes') {
				  ?>
				  <a class="margin20" href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=create" rel="nofollow"><i class="fa fa-plus fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[8]; ?></span></a>
				  <?php
				  }
				  ?>
				  <a class="margin20" href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=login" rel="nofollow"><i class="fa fa-lock fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></a>
				  <a href="/" id="mashpia_link" rel="nofollow" class="margin20"><i class="fa fa-reply fa-fw"></i> <span class="hidden-sm hidden-xs">Back to Mashpia.com</span></a>
				  <?php
          }
          ?>
        </div>
      </div>

      <?php
      // Top menu for mobile/small screens
      ?>
      <div class="row hidden-sm hidden-md hidden-lg">
        <div class="mbtopleft col-xs-9">
          <a href="<?php echo $this->SETTINGS->scriptpath; ?>"><i class="fa fa-life-ring fa-fw"></i> <?php echo $this->TOP_BAR_TITLE_MB; ?></a>
        </div>
        <div class="smallscreen col-xs-3 text-right">
          <div class="btn-group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-bars fa-fw"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open"><i class="fa fa-pencil fa-fw"></i> <?php echo $this->TXT[1]; ?></a></li>
			  <li><a href="/" id="mashpia_link"><i class="fa fa-reply fa-fw"></i> Back to App (Mashpia.com)</a></li>
              <?php
              // Is visitor logged in?
              if ($this->LOGGED_IN == 'yes') {
              ?>
              <li role="separator" class="divider"></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=profile"><i class="fa fa-user fa-fw"></i> <?php echo $this->TXT[9]; ?></a></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=history"><i class="fa fa-calendar fa-fw"></i> <?php echo $this->TXT[3]; ?></a></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>"><i class="fa fa-dashboard fa-fw"></i> <?php echo $this->TXT[2]; ?></a></li>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>?lo=1" title="<?php echo mswSafeDisplay($this->TXT[5]); ?>"><i class="fa fa-unlock fa-fw"></i> <?php echo $this->TXT[5]; ?></a></li>
              <?php
              } else {
              ?>
              <li role="separator" class="divider"></li>
              <?php
              // Is account creation enabled?
              if ($this->SETTINGS->createAcc == 'yes') {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=create" rel="nofollow"><i class="fa fa-plus fa-fw"></i> <?php echo $this->TXT[8]; ?></a></li>
              <?php
              }
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=login" rel="nofollow"><i class="fa fa-lock fa-fw"></i> <?php echo $this->TXT[4]; ?></a></li>
              <?php
              }
              ?>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>