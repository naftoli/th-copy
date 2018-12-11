      <div class="form-group searchbox" style="height:180px;display:none">
       <div class="form-group">
         <div class="form-group input-group">
           <input class="form-control" type="text" placeholder="<?php echo mswSafeDisplay($msg_log10); ?>" name="keys" value="<?php echo (isset($_GET['keys']) ? urlencode(mswSafeDisplay($_GET['keys'])) : ''); ?>">
           <span class="input-group-addon"><a href="#" onclick="mswDoSearch('<?php echo (isset($searchBoxUrl) ? $searchBoxUrl : $_GET['p']); ?>')"><i class="fa fa-arrow-right fa-fw"></i></a></span>
         </div>
       </div>
       <div class="form-group">
         <?php
          foreach ($_GET AS $k => $v) {
          if (!in_array($k, array('p','next'))) {
          ?>
          <input type="hidden" name="<?php echo mswSafeDisplay($k); ?>" value="<?php echo mswSafeDisplay($v); ?>">
          <?php
          }
          }
          ?>
         <input type="text" placeholder="<?php echo mswSafeDisplay($msg_reports2); ?>" class="form-control" id="from" name="from" value="<?php echo (isset($_GET['from']) ? mswSafeDisplay($_GET['from']) : ''); ?>">
       </div>
       <div class="form-group">
         <input placeholder="<?php echo mswSafeDisplay($msg_reports3); ?>" type="text" class="form-control" id="to" name="to" value="<?php echo (isset($_GET['to']) ? mswSafeDisplay($_GET['to']) : ''); ?>">
       </div>
       <div class="form-group">
         <div class="checkbox">
           <label><input type="checkbox" name="replies" value="yes"> <?php echo $msg_search23; ?></label>
         </div>
       </div>
      </div>