<?php if (!defined('PATH')) { exit; } ?>
  <div>

  <label><?php echo $msadminlang3_1[32]; ?></label>
  <input type="text" class="form-control" name="emails" value=""><br>

  <button class="btn btn-primary" type="button" id="testbutton" onclick="jQuery(document).ready(function() {mswMailTest()})"><i class="fa fa-envelope fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msadminlang3_1[33]; ?></span></button>

  </div>