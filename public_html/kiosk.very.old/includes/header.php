<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<?
$title = (isset($title)?$title:"");
?>
<title><?=T_($title).' - '.T_('Tzivos Hashem Management System')?></title>
<link rel="alternate" media="print" href="../withdraw_print.php">
<link rel="stylesheet" type="text/css" href="scripts/shadowbox/shadowbox.css">
<link href="styles/reset.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css" rel="stylesheet" type="text/css" />
<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />
<LINK href="../card_printer.css" rel="stylesheet" type="text/css">
<!--[if IE]>
<link href="styles/style_ie.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--
<script>
function EvalSound(soundobj) {
  var thissound = eval ( "document." + soundobj);
 thissound.Play();
}
</script>

-->
<script src="scripts/jquery.core.js" type="text/javascript"></script>
<script src="scripts/jquery.ui.js" type="text/javascript"></script>
<script type="text/javascript" src="scripts/easySlider1.7.js"></script>
<script type="text/javascript">
    $(document).ready(function(){    
        $("#slider").easySlider({
            numeric: true, 
            controlsBefore:    '<div class="page_dots">',
            controlsAfter:    '</div>'
            });
    });    
</script>
</head>

