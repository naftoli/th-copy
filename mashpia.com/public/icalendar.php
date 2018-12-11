<? $no_login = true; ?>
<? require('header.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Calendar Date Picker'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="styles.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
		.cal table {
		  margin: auto;
		}

		.cal .picked_day {
		  border: none;
		  font-weight: normal;
		}
		</STYLE>

		<SCRIPT type="text/javascript">
			function sendDate(el) {
				window.frameElement.callBack(
					el.href.match(/[?&]date=(\d*)/)[1],
					el.textContent + ' ' + document.getElementsByTagName('caption')[0].getElementsByTagName('span')[0].textContent
				);
			}
		</SCRIPT>

	</HEAD>
	
<BODY>
<DIV style="text-align: center;">
  <? if(!isset($_GET['required'])): ?>
    <A HREF="#" onClick="window.frameElement.callBack('', '')"><?=T_('Clear')?></A>
    &nbsp;
  <? endif; ?>
  <A HREF="#" onClick="window.frameElement.callBack('close', '')"><?=T_('Close')?></A>
</DIV>
<?
$cal_url = 'icalendar.php?'
           . (isset($_GET['required']) ? 'required&amp;' : '')
           . (isset($_GET['dates']) ? 'dates=' . urlencode(gr('dates')) . '&amp;' : '')
;
$cal_onclick = 'sendDate(this); return false;';
include('cal.php');
?>
</BODY>
</HTML>
