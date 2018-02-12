      <div class="form-group searchbox" style="height:130px;display:none">
       <div class="form-group">
         <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
         <input type="text" placeholder="<?php echo mswSafeDisplay($msg_reports2); ?>" class="form-control" id="from" name="from" value="<?php echo (isset($_GET['from']) ? mswSafeDisplay($_GET['from']) : ''); ?>">
       </div>
       <div class="form-group">
         <div class="form-group input-group">
           <input placeholder="<?php echo mswSafeDisplay($msg_reports3); ?>" type="text" class="form-control" id="to" name="to" value="<?php echo (isset($_GET['to']) ? mswSafeDisplay($_GET['to']) : ''); ?>">
           <span class="input-group-addon"><a href="#" onclick="mswDoSearch('<?php echo (isset($searchBoxUrl) ? $searchBoxUrl : $_GET['p']); ?>')"><i class="fa fa-arrow-right fa-fw"></i></a></span>
         </div>
       </div>
      </div>