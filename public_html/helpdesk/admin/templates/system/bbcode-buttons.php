<?php if (!defined('PARENT')) { exit; }
$box = (defined('BB_BOX') ? BB_BOX : 'comments');
?>
<div class="bbButtons">
  <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('bold','<?php echo $box; ?>')"><i class="fa fa-bold fa-fw"></i></button>
  <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('italic','<?php echo $box; ?>')"><i class="fa fa-italic fa-fw"></i></button>
  <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('underline','<?php echo $box; ?>')"><i class="fa fa-underline fa-fw"></i></button>
  <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('url','<?php echo $box; ?>')"><i class="fa fa-link fa-fw"></i></button>
  <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('email','<?php echo $box; ?>')"><i class="fa fa-envelope-o fa-fw"></i></button>
  <div class="mobilebreakpoint">
   <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('img','<?php echo $box; ?>')"><i class="fa fa-picture-o fa-fw"></i></button>
   <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('youtube','<?php echo $box; ?>')"><i class="fa fa-youtube fa-fw"></i></button>
   <button class="btn btn-info btn-sm" type="button" onclick="mswBBTags('vimeo','<?php echo $box; ?>')"><i class="fa fa-play fa-fw"></i></button>
   <button class="btn btn-success btn-sm" type="button" onclick="window.open('index.php?p=bbCode','_blank')"><i class="fa fa-question fa-fw"></i></button>
  </div>
</div>