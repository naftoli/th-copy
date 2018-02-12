<?php if (!defined('PATH')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo $this->LANG; ?>" dir="<?php echo $this->DIR; ?>">
	<head>
    <meta charset="<?php echo $this->CHARSET; ?>">

    <title>404</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/ms.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $this->SETTINGS->scriptpath; ?>/favicon.ico">

	</head>

	<body>

  <div class="navbar navbar-default navbar-fixed-top Fixed" id="msnavheader">

    <div class="container msheader">
      <span class="pull-right"><i class="fa fa-warning fa-fw"></i> 404</span>
      <i class="fa fa-home fa-fw"></i> <a href="<?php echo $this->SETTINGS->scriptpath; ?>/index.php"><?php echo $this->SETTINGS->website; ?></a>
    </div>

  </div>

  <div class="container margin-top-container min-height-container" id="mscontainer">

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $this->TXT[1]; ?><br><br>
            <i class="fa fa-reply fa-fw"></i> <a href="<?php echo $this->SETTINGS->scriptpath; ?>/index.php"><?php echo $this->TXT[2]; ?></a>
          </div>
        </div>

      </div>
    </div>

  </div>

  <footer><?php echo $this->SETTINGS->website; ?><br><?php echo date('Y'); ?></footer>

  </body>
</html>