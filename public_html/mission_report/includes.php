<?
$days_of_week = array("F", "ש", "S", "M", "T", "W", "T");
 
$campaignLogos = array(
	1	=>	'Tehillim.gif',
	4	=>	'Tefilla.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'hiskashrus.gif',
	21	=>	'sefer-hamitzvos.gif',
	27	=>	'tanya.gif',
	40	=>	'Yom-Dipagra.gif',
	41	=>	'Father-Son.gif',
	42	=>	'Footsteps.gif',
	45	=>	'Cheshbon-Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Brias-Haguf.gif'
);

$stickerOutlines = array(
	1	=>	'Shabbos Mevorchim Tehillim.gif', 
	4	=>	'Tefillah.gif',
	12	=>	'Mivtzoim.gif',
	13	=>	'Niggunim.gif',
	16	=>	'Sticker - Hiskashrus outline.png', 
	21	=>	'sefer hamitzvos bw.png',
	27	=>	'Tanya.gif',
	40	=>	'Yomei Dipagra.gif',
	41	=>	'Avos Ubonim.gif',
	42	=>	'Vihalachta Bidrachov.gif',
	45	=>	'Cheshbon Hanefesh.gif',
	90	=>	'Chitas.gif',
	100	=>	'Sticker - Brias Haguf_outline bw.png'
);

$dailyStickers = array(
	1	=>	'tehillim 5 of 7.png', 
	4	=>	'tefilah 5 of 7.png',
	12	=>	'mivtzoyim 5 of 7.png',
	13	=>	'niggunim 5 of 7.png',
	16	=>	'hiskashrus 5 of 7.png', 
	21	=>	'sefer hamitzvos 5 of 7.png',
	27	=>	'tanya 5 of 7.png',
	40	=>	'yoma dipagra 5 of 7.png',
	41	=>	'avos ubanim 5 of 7.png',
	42	=>	'halachta bdrachav5 of 7.png',
	45	=>	'cheshbon hanefesh 5 of 7.png',
	90	=>	'chitas 5 of 7.png',
	100	=>	'brias haguf 5 of 7.png'
);

function pager($page, $totalRendered, $totalRows, $addLabel = 0, $mediumPictures = false) {
	/**
	 * figure out when to make second column and when to make new page
	 * if we are on first or last page, columnize after 10 and make new page after 20
	 * if we are on any other page columnize after 12 and make new page after 24
	 *  
	 * returns 1 to columnize and 2 to pagify (0 to do nothing)
	 **/
	
	if (!$mediumPictures) {
		
		$columnizeFirst = 11;
		$newPageFirst = 22;
		$columnizeReg = 14;
		$newPageReg = 28;
		$columnizeLast = 12;
		$newPageLast = 24;
		
		$lastPage = 1;
		if ($totalRows > $newPageFirst) {
			$lastPage++;
			if ($totalRows > ($newPageFirst + $newPageReg)) {
				$lastPage++;
				if ($totalRows > ($newPageFirst + ($newPageReg * 2))) {
					$lastPage++;
					if ($totalRows > ($newPageFirst + ($newPageReg * 3))) {
						$lastPage++;
						if ($totalRows > ($newPageFirst + ($newPageReg * 4))) {
							$lastPage++;
							if ($totalRows > ($newPageFirst + ($newPageReg * 5))) {
								$lastPage++;
								if ($totalRows > ($newPageFirst + ($newPageReg * 6))) {
									$lastPage++;
								}
							}
						}
					}
				}
			}
		}
		
		if ($page == 1) {
			if (
				($totalRendered == $columnizeFirst) || 
				($totalRendered < $columnizeFirst && (($totalRendered + $addLabel) >= $columnizeFirst))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageFirst) {
				return 2;
			} else {
				return 0;
			}
		} else if ($page == $lastPage) {
			if (
				($totalRendered == $columnizeLast) || 
				($totalRendered < $columnizeLast && (($totalRendered + $addLabel) >= $columnizeLast))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageLast) {
				return 2;
			} else {
				return 0;
			}
		} else {
			if (
				($totalRendered == $columnizeReg) || 
				($totalRendered < $columnizeReg && (($totalRendered + $addLabel) >= $columnizeReg))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageReg) {
				return 2;
			} else {
				return 0;
			}
		}
		
	} else {
		
		$columnizeFirst = 8;
		$newPageFirst = 16;
		$columnizeSecond = 11;
		$newPageSecond = 26;
		$columnizeReg = 14;
		$newPageReg = 28;
		$columnizeLast = 13;
		$newPageLast = 26;
		
		$lastPage = 1;
		if ($totalRows > $newPageFirst) {
			$lastPage++;
			if ($totalRows > ($newPageFirst + $newPageSecond)) {
				$lastPage++;
				if ($totalRows > ($newPageFirst + $newPageSecond + $newPageReg)) {
					$lastPage++;
					if ($totalRows > ($newPageFirst + $newPageSecond + ($newPageReg * 2))) {
						$lastPage++;
						if ($totalRows > ($newPageFirst + $newPageSecond + ($newPageReg * 3))) {
							$lastPage++;
							if ($totalRows > ($newPageFirst + $newPageSecond + ($newPageReg * 4))) {
								$lastPage++;
								if ($totalRows > ($newPageFirst + $newPageSecond + ($newPageReg * 5))) {
									$lastPage++;
									if ($totalRows > ($newPageFirst + $newPageSecond + ($newPageReg * 6))) {
										$lastPage++;
									}
								}
							}
						}
					}
				}
			}
		}
		
		if ($page == 1) {
			if (
				($totalRendered == $columnizeFirst) || 
				($totalRendered < $columnizeFirst && (($totalRendered + $addLabel) >= $columnizeFirst))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageFirst) {
				return 2;
			} else {
				return 0;
			}
		} else if ($page == 2) {
			if (
				($totalRendered == $columnizeSecond) || 
				($totalRendered < $columnizeSecond && (($totalRendered + $addLabel) >= $columnizeSecond))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageSecond) {
				return 2;
			} else {
				return 0;
			}
		} else if ($page == $lastPage) {
			if (
				($totalRendered == $columnizeLast) || 
				($totalRendered < $columnizeLast && (($totalRendered + $addLabel) >= $columnizeLast))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageLast) {
				return 2;
			} else {
				return 0;
			}
		} else {
			if (
				($totalRendered == $columnizeReg) || 
				($totalRendered < $columnizeReg && (($totalRendered + $addLabel) >= $columnizeReg))
				) {
				return 1;
			} else if (($totalRendered + $addLabel) >= $newPageReg) {
				return 2;
			} else {
				return 0;
			}
		}
	}
} 
?>