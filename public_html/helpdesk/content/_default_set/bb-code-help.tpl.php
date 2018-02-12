<?php if (!defined('PATH')) { exit; }
// For this template, we`ll just pass in the language files instead of assigning via the template..
include(LANG_PATH . 'lang2.php');
include(LANG_PATH . 'lang3.php');
include(LANG_PATH . 'lang4.php');
include(LANG_PATH . 'lang5.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->LANG; ?>" dir="<?php echo $this->DIR; ?>">
	<head>
    <meta charset="<?php echo $this->CHARSET; ?>">

    <title><?php echo $this->TITLE; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bbcode.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/ms.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $this->SYS_BASE_HREF; ?>favicon.ico">

  </head>

	<body>

  <div class="container margin-top-container-nonefixed">

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-info-circle fa-fw"></i> <?php echo $msadminlang3_1[11]; ?>
      </div>
      <div class="panel-body">
       <?php echo $msadminlang3_1[10]; ?>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-text-height fa-fw"></i> <?php echo $msg_bbcode16; ?>
      </div>
      <div class="panel-body text_height_25">

        <div>
         <b>[b]</b> <?php echo $msg_bbcode3; ?> <b>[/b]</b> = <b><?php echo $msg_bbcode3; ?></b>
        </div>

        <div>
         <b>[u]</b> <?php echo $msg_bbcode4; ?> <b>[/u]</b> = <span style="text-decoration:underline"><?php echo $msg_bbcode4; ?></span>
        </div>

        <div>
        <b>[i]</b> <?php echo $msg_bbcode5; ?> <b>[/i]</b> = <span style="font-style:italic"><?php echo $msg_bbcode5; ?></span>
        </div>

        <div>
        <b>[s]</b> <?php echo $msg_bbcode6; ?> <b>[/s]</b> = <span style="text-decoration:line-through"><?php echo $msg_bbcode6; ?></span>
        </div>

        <div>
         <b>[del]</b> <?php echo $msg_bbcode7; ?> <b>[/del]</b> = <span style="text-decoration:line-through;color:red"><?php echo $msg_bbcode7; ?></span>
        </div>

        <div>
         <b>[ins]</b> <?php echo $msg_bbcode8; ?> <b>[/ins]</b> = <span style="background:yellow"><?php echo $msg_bbcode8; ?></span>
        </div>

        <div>
         <b>[em]</b> <?php echo $msg_bbcode9; ?> <b>[/em]</b> = <span style="font-style:italic;font-weight:bold"><?php echo $msg_bbcode9; ?></span>
        </div>

        <div>
         <b>[color=#FF0000]</b> <?php echo $msg_bbcode10; ?><b> [/color]</b> = <span style="color:red"><?php echo $msg_bbcode10; ?></span>
        </div>

        <div>
         <b>[color=blue]</b> <?php echo $msg_bbcode11; ?> <b>[/color]</b> = <span style="color:blue"><?php echo $msg_bbcode11; ?></span>
        </div>

        <div>
         <b>[h1]</b> <?php echo $msg_bbcode12; ?> <b>[/h1]</b> = <span style="font-weight:bold;font-size:22px"><?php echo $msg_bbcode12; ?></span>
        </div>

        <div>
         <b>[h2]</b> <?php echo $msg_bbcode13; ?> <b>[/h2]</b> = <span style="font-weight:bold;font-size:20px"><?php echo $msg_bbcode13; ?></span>
        </div>

        <div>
         <b>[h3]</b> <?php echo $msg_bbcode14; ?> <b>[/h3]</b> = <span style="font-weight:bold;font-size:18px"><?php echo $msg_bbcode14; ?></span>
        </div>

        <div>
         <b>[h4]</b> <?php echo $msg_bbcode15; ?> <b>[/h4]</b> = <span style="font-weight:bold;font-size:16px"><?php echo $msg_bbcode15; ?></span>
        </div>

      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-camera fa-fw"></i> <?php echo $msg_bbcode17; ?>
      </div>
      <div class="panel-body text_height_25">

        <div>
         <b>[url=http://www.example.com]</b> <?php echo $msg_bbcode32; ?> <b>[/url]</b> = <a href="http://www.example.com"><?php echo $msg_bbcode32; ?></a>
        </div>

        <div>
         <b>[url]</b> http://www.example.com <b>[/url]</b> = <a href="http://www.example.com">http://www.example.com</a>
        </div>

        <div>
         <b>[urlnew=http://www.example.com]</b> <?php echo $msg_bbcode32; ?> <b>[/urlnew]</b> = <a href="http://www.example.com"><?php echo $msg_bbcode32; ?></a> (<?php echo $msg_bbcode28; ?>)
        </div>

        <div>
         <b>[urlnew]</b> http://www.example.com <b>[/urlnew]</b> = <a href="http://www.example.com">http://www.example.com</a> (<?php echo $msg_bbcode28; ?>)
        </div>

        <div>
         <b>[email]</b> email@example.com <b>[/email]</b> = <a href="mailto:email@example.com">email@example.com</a>
        </div>

        <div>
         <b>[email=email@example.com]</b> <?php echo $msg_bbcode26; ?> <b>[/email]</b> = <a href="mailto:email@example.com"><?php echo $msg_bbcode26; ?></a>
        </div>

        <div>
         <b>[img]</b> http://www.example.com/images/logo.png <b>[/img]</b> = <?php echo $msg_bbcode31; ?>
        </div>

      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-desktop fa-fw"></i> <?php echo $msg_bbcode28; ?>
      </div>
      <div class="panel-body text_height_25">

        <div>
		     <b>[youtube]</b><?php echo $msg_bbcode30; ?><b>[/youtube]</b> = <?php echo $msg_bbcode29; ?>
		    </div>

        <div>
		     <b>[vimeo]</b><?php echo $msg_bbcode30; ?><b>[/vimeo]</b> = <?php echo $msg_bbcode29; ?>
		    </div>

      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-list-ul fa-fw"></i> <?php echo $msg_bbcode18; ?>
      </div>
      <div class="panel-body text_height_25">

        <div>
          <b>[list]</b><br><b>&nbsp;[*]</b> <?php echo $msg_bbcode20; ?> 1 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode20; ?> 2 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode20; ?> 3 <b>[/*]<br>[/list]</b>
          <div class="alert alert-info margin_top_10">
            <ul style="list-style-type:disc"><li><?php echo $msg_bbcode20; ?> 1</li><li><?php echo $msg_bbcode20; ?> 2</li><li><?php echo $msg_bbcode20; ?> 3</li></ul>
          </div>
        </div>

        <div>
          <b>[list=n]</b><br><b>&nbsp;[*]</b> <?php echo $msg_bbcode21; ?> 1 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode21; ?> 2 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode21; ?> 3 <b>[/*]<br>[/list]</b>
          <div class="alert alert-info margin_top_10">
            <ul style="list-style-type:decimal"><li><?php echo $msg_bbcode21; ?> 1</li><li><?php echo $msg_bbcode21; ?> 2</li><li><?php echo $msg_bbcode21; ?> 3</li></ul>
          </div>
        </div>

        <div>
          <b>[list=a]</b><br><b>&nbsp;[*]</b> <?php echo $msg_bbcode22; ?> 1 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode22; ?> 2 <b>[/*]<br>&nbsp;[*]</b> <?php echo $msg_bbcode22; ?> 3 <b>[/*]<br>[/list]</b>
          <div class="alert alert-info margin_top_10">
            <ul style="list-style-type:lower-alpha"><li><?php echo $msg_bbcode22; ?> 1</li><li><?php echo $msg_bbcode22; ?> 2</li><li><?php echo $msg_bbcode22; ?> 3</li></ul>
          </div>
        </div>

      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading text-uppercase">
        <i class="fa fa-copy fa-fw"></i> <?php echo $msg_bbcode19; ?>
      </div>
      <div class="panel-body text_height_25">

        <div>
         <b>[b][u]</b><?php echo $msg_bbcode23; ?> <b>[/u][/b]</b> = <span style="text-decoration:underline;font-weight:bold"><?php echo $msg_bbcode23; ?></span>
        </div>

        <div>
         <b>[color=blue][b][u]</b> <?php echo $msg_bbcode24; ?> <b>[/u][/b][/color]</b> = <span style="text-decoration:underline;font-weight:bold;color:blue"><?php echo $msg_bbcode24; ?></span>
        </div>

      </div>
    </div>

  </div>

  <script src="<?php echo $this->SYS_BASE_HREF; ?>js/jquery.js"></script>
  <script src="<?php echo $this->SYS_BASE_HREF; ?>js/bootstrap.js"></script>

  </body>
</html>