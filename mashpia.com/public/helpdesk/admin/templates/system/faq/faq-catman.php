<?php if (!defined('PATH')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Cat Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `name`';
	    break;
	  // Cat Name (descending)..
    case 'name_desc':
	    $orderBy = 'ORDER BY `name` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
	    break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
	  // Most questions..
    case 'questions_desc':
	    $orderBy = 'ORDER BY `queCount` desc';
	    break;
	  // Least questions..
    case 'questions_asc':
	    $orderBy = 'ORDER BY `queCount`';
	    break;
  }
}

if (isset($_GET['cat'])) {
  define('DISABLED_CATS',1);
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSafeImportString(strtolower($_GET['keys']));
  $SQL           = 'AND (LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`summary`) LIKE \'%' . $_GET['keys'] . '%\')';
}

$q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "faqassign`
      WHERE (`" . DB_PREFIX . "categories`.`id` = `" . DB_PREFIX . "faqassign`.`itemID`)
		  AND `" . DB_PREFIX . "faqassign`.`desc` = 'category'
		 ) AS `queCount`
		 FROM `" . DB_PREFIX . "categories`
     WHERE `subcat` = '0'
		 $SQL
		 $orderBy
		 LIMIT $limitvalue, $limit
		 ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
$c            = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
$countedRows  = (isset($c->rows) ? $c->rows : '0');
$totalCats    = mswRowCount('categories');
?>
  <div class="container margin-top-container min-height-container" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_adheader45; ?> (<?php echo @number_format($totalCats); ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if ($MSTEAM->id == '1' || in_array('faq-cat', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=faq-cat')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            if ($totalCats > 0) {
            $links = array(
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),       'name' => $msg_kbase43),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_kbase44),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_levels23),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels24),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase58),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase57)
            );
            echo $MSBOOTSTRAP->button($msg_search20,$links);
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            }
            ?>
          </div>
          <div class="panel-body">
            <?php
            // Search..
            include(PATH . 'templates/system/bootstrap/search-box.php');
            ?>

            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                 <tr>
                  <?php
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <th style="width:6%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo $msg_customfields; ?></th>
                  <th><?php echo $msg_kbase17; ?></th>
                  <th><?php echo $msg_kbase56; ?></th>
                  <th><?php echo $msg_script43; ?></th>
                 </tr>
                </thead>
              <tbody>
                <?php
                if ($countedRows > 0) {
                while ($CAT = mysqli_fetch_object($q)) {
                ?>
                <tr id="datatr_<?php echo $CAT->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckRange(this.checked,'subcat_<?php echo $CAT->id; ?>');mswCheckCount('panel-body','delButton','mswCVal');" name="del[]" value="<?php echo $CAT->id; ?>"></td>
                <?php
                }
                ?>
                <td>
                 <select name="order[<?php echo $CAT->id; ?>]" class="form-control">
                 <?php
                 for ($i=1; $i<($countedRows+1); $i++) {
                 ?>
                 <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($CAT->orderBy,$i); ?>><?php echo $i; ?></option>
                 <?php
                 }
                 ?>
                 </select>
                </td>
                <td>
                <?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswSafeDisplay($CAT->name); ?>
                <span class="tdCellInfo">
                <?php echo (strlen($CAT->summary)>CATEGORIES_SUMMARY_TEXT_LIMIT ? substr(mswSafeDisplay($CAT->summary),0,CATEGORIES_SUMMARY_TEXT_LIMIT).'..' : mswSafeDisplay($CAT->summary)); ?>
                </span>
                </td>
                <td><a href="?p=faqman&amp;cat=<?php echo $CAT->id; ?>" title="<?php echo @number_format($CAT->queCount); ?>"><?php echo @number_format($CAT->queCount); ?></a></td>
                <td>
                  <i class="fa fa-<?php echo ($CAT->enCat=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($CAT->enCat=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqcatstate','<?php echo $CAT->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                  <a href="?p=faq-cat&amp;edit=<?php echo $CAT->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
                </tr>
                <?php

                //============================
                // SUB CATEGORIES
                //============================

                $q2  = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
                      (SELECT count(*) FROM `" . DB_PREFIX . "faqassign`
                       WHERE (`" . DB_PREFIX . "categories`.`id` = `" . DB_PREFIX . "faqassign`.`itemID`)
                       AND `" . DB_PREFIX . "faqassign`.`desc` = 'category'
                      ) AS `queCount`
                      FROM `" . DB_PREFIX . "categories`
                      WHERE `subcat` = '{$CAT->id}'
                      " . (defined('DISABLED_CATS') ? 'AND `enCat` = \'no\'' : '') . "
                      " . $SQL." " . $orderBy
                      ) or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)),__LINE__,__FILE__));
               $subCount = mysqli_num_rows($q2);
               if ($subCount>0) {
               while ($SUB = mysqli_fetch_object($q2)) {
		           ?>
               <tr id="datatr_<?php echo $SUB->enCat; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td style="padding-left:15px" class="subcat_<?php echo $CAT->id; ?>"><input type="checkbox" onclick="if(!this.checked){mswUncheck('cat_<?php echo $CAT->id; ?>')};mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $SUB->id; ?>"></td>
               <?php
               }
               ?>
               <td style="padding-left:15px">
                <select name="orderSub[<?php echo $SUB->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($subCount+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($SUB->orderBy,$i); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select>
               </td>
               <td style="padding-left:15px">
               <?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSafeDisplay($msadminlang3_1faq[3]) . '"></i> ' : '') . mswSafeDisplay($SUB->name); ?>
               <span class="tdCellInfo">
               <?php echo (strlen($SUB->summary)>CATEGORIES_SUMMARY_TEXT_LIMIT ? substr(mswSafeDisplay($SUB->summary),0,CATEGORIES_SUMMARY_TEXT_LIMIT).'..' : mswSafeDisplay($SUB->summary)); ?>
               </span>
               </td>
               <td><a href="?p=faqman&amp;cat=<?php echo $SUB->id; ?>" title="<?php echo @number_format($SUB->queCount); ?>"><?php echo @number_format($SUB->queCount); ?></a></td>
               <td>
                 <i class="fa fa-<?php echo ($SUB->enCat=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($SUB->enCat=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqcatstate','<?php echo $SUB->id; ?>')" title="<?php echo mswSafeDisplay($msg_response28); ?>"></i>
                 <a href="?p=faq-cat&amp;edit=<?php echo $SUB->id; ?>" title="<?php echo mswSafeDisplay($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
               </td>
               </tr>
               <?php
               }
               }

               //============================
               // END SUB CATEGORIES
               //============================

               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_kbasecats8; ?></td>
               </tr>
               <?php
               }
               ?>
               </tbody>
              </table>
            </div>
          </div>

          <?php
	        if ($countedRows > 0) {
          ?>
          <div class="panel-footer">
          <?php
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswConfirmButtonAction('<?php echo mswSafeDisplay($msg_script_action); ?>','faqcatdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSafeDisplay($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels9); ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('faqcatseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSafeDisplay($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo mswCleanData($msg_levels8); ?></span></button>
          </div>
	        <?php
	        }
          ?>
        </div>

        <?php
        if ($countedRows > 0 && $countedRows > $limit) {
          define('PER_PAGE', $limit);
          $PGS = new pagination(array($countedRows, $msg_script42, $page),'?p=' . $_GET['p'] . '&amp;next=');
          echo $PGS->display();
        }
        ?>

      </div>
    </div>
    </form>

  </div>