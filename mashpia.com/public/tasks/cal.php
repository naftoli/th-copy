<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>

<?
echo mb_convert_encoding( jdtojewish( gregoriantojd(10, 8, 2010), true,
       CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH), "UTF-8", "ISO-8859-8");
?>

	</BODY>
</HTML>
