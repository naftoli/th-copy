<div class="wrap">
	<div id="lbg_logo">
			<h2>Playlist for player: <span style="color:#FF0000; font-weight:bold;"><?php echo $_SESSION['xname']?> - ID #<?php echo $_SESSION['xid']?></span></h2>
 	</div>
  <div id="lbg_audio2_html5_updating_witness"><img src="<?php echo plugins_url('images/ajax-loader.gif', dirname(__FILE__))?>" /> Updating...</div>
<div style="text-align:center; padding:0px 0px 20px 0px;"><img src="<?php echo plugins_url('images/icons/add_icon.gif', dirname(__FILE__))?>" alt="add" align="absmiddle" /> <a href="?page=LBG_AUDIO2_HTML5_Playlist&xmlf=add_playlist_record">Add new</a>  &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; <img src="<?php echo plugins_url('images/icons/magnifier.png', dirname(__FILE__))?>" alt="add" align="absmiddle" /> <a href="javascript: void(0);" onclick="showDialogPreview(<?php echo strip_tags($_SESSION['xid'])?>)">Preview Player</a></div>
<div style="text-align:left; padding:10px 0px 10px 14px;">#Initial Order --- Audio File Title</div>

<div id="previewDialog"><iframe id="previewDialogIframe" src="" width="100%" height="600" style="border:0;"></iframe></div>

<ul id="lbg_audio2_html5_sortable">
	<?php foreach ( $result as $row ) 
	{
		$row=lbg_audio2_html5_unstrip_array($row); ?>
	<li class="ui-state-default cursor_move" id="<?php echo $row['id']?>">#<?php echo $row['ord']?> --- <span id="mov_title_<?php echo $row['id']?>"><?php echo $row['title']?></span> <div class="toogle-btn-closed" id="toogle-btn<?php echo $row['ord']?>" onclick="mytoggle('toggleable<?php echo $row['ord']?>','toogle-btn<?php echo $row['ord']?>');"></div><div class="options"><a href="javascript: void(0);" onclick="lbg_audio2_html5_delete_entire_record(<?php echo $row['id']?>,<?php echo $row['ord']?>);" style="color:#F00;">Delete</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="?page=LBG_AUDIO2_HTML5_Playlist&amp;id=<?php echo strip_tags($_SESSION['xid'])?>&amp;name=<?php echo strip_tags($_SESSION['xname'])?>&amp;duplicate_id=<?php echo $row['id']?>">Duplicate</a></div>
	<div class="toggleable" id="toggleable<?php echo $row['ord']?>">
    <form method="POST" enctype="multipart/form-data" id="form-playlist-html5-audio2-<?php echo $row['ord']?>">
	    <input name="id" type="hidden" value="<?php echo $row['id']?>" />
        <input name="ord" type="hidden" value="<?php echo $row['ord']?>" />
		<table width="100%" cellspacing="0" class="wp-list-table widefat fixed pages" style="background-color:#FFFFFF;">
		  <tr>
		    <td align="left" valign="middle" width="25%"></td>
		    <td align="left" valign="middle" width="77%"></td>
		  </tr>
		  <tr>
		    <td align="right" valign="middle" class="row-title">Title</td>
		    <td align="left" valign="top"><input name="title" type="text" size="80" id="title" value="<?php echo $row['title'];?>"/></td>
		    </tr>
		  <tr>
		    <td align="right" valign="middle" class="row-title">Author</td>
		    <td align="left" valign="top"><input name="author" type="text" size="80" id="author" value="<?php echo $row['author'];?>"/></td>
		  </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Category</td>
		    <td align="left" valign="top"><?php 
				foreach ( $result_categ as $row_categ ) 
				{
					$row_categ=lbg_audio2_html5_unstrip_array($row_categ); 
					$checked_var='';
					if (preg_match_all('/\b'.$row_categ["id"].'\b/', $row['category'], $matches)) { $checked_var='checked="checked"'; }
					?>
				<p><input name="category[]" id="category" type="checkbox" value="<?php echo $row_categ['id'];?>" <?php echo $checked_var; ?> /> <?php echo $row_categ['categ'];?></p>
				<?php }	?></td>
		    </tr>
			<tr>
			  <td align="right" valign="top" class="row-title">Playlist Image </td>
			  <td align="left" valign="top"><input name="imgplaylist" type="text" id="imgplaylist" size="80" value="<?php echo $row['imgplaylist']?>" /> <input name="upload_imgplaylist_button_html5Audio2_<?php echo $row['ord']?>" type="button" id="upload_imgplaylist_button_html5Audio2_<?php echo $row['ord']?>" value="Upload Image" />
			    <br />
			    Enter an URL or upload an image<br />
		      <div id="lbg-html5-audio2_playlistimg_div_<?php echo $row['ord']?>" style="padding:5px 0;"> <img src="<?php echo $row['imgplaylist']?>" alt="" name="imgplaylist_<?php echo $row['ord']?>" id="imgplaylist_<?php echo $row['ord']?>" /> </div></td>
		    </tr>
		    <tr>
		      <td align="right" valign="top" class="row-title">Buy Link</td>
		      <td align="left" valign="top"><input name="buylink" type="text" size="80" id="buylink" value="<?php echo $row['buylink']?>"/></td>
	        </tr>
		    <tr>
		      <td align="right" valign="top" class="row-title">Lyrics Link</td>
		      <td align="left" valign="top"><input name="lyricslink" type="text" size="80" id="lyricslink" value="<?php echo $row['lyricslink']?>"/></td>
	        </tr>
		    <tr>
		      <td align="right" valign="top" class="row-title">MP3 file (Chrome, IE, Safari)*</td>
		      <td align="left" valign="top"><input name="mp3" type="text" id="mp3" size="80" value="<?php echo stripslashes($row['mp3'])?>" />
		        <input name="upload_mp3_button_html5Audio2_<?php echo $row['ord']?>" type="button" id="upload_mp3_button_html5Audio2_<?php echo $row['ord']?>" value="Change File" />
		        <br />
	          Enter an URL or upload the file</td>
	        </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Optional OGG file (Mozzila, Opera)</td>
		    <td align="left" valign="top"><input name="ogg" type="text" id="ogg" size="80" value="<?php echo stripslashes($row['ogg'])?>" />
		      <input name="upload_ogg_button_html5Audio2_<?php echo $row['ord']?>" type="button" id="upload_ogg_button_html5Audio2_<?php echo $row['ord']?>" value="Change File" />
		      <br />
		      Enter an URL or upload the file</td>
	      </tr>

		  <tr>
		    <td align="right" valign="middle" class="row-title">&nbsp;</td>
		    <td align="left" valign="middle">&nbsp;</td>
		    </tr>
		  <tr>
		    <td colspan="2" align="left" valign="middle">&nbsp;</td>
		    </tr>
		  <tr>
		    <td colspan="2" align="left" valign="middle">*Required fields</td>
		  </tr>
		  <tr>
		    <td colspan="2" align="center" valign="middle"><input name="Submit<?php echo $row['ord']?>" id="Submit<?php echo $row['ord']?>" type="submit" class="button-primary" value="Update Playlist Record"></td>
		  </tr>
		</table>    
        </form>
            <div id="ajax-message-<?php echo $row['ord']?>" class="ajax-message"></div>
    </div>
    </li>
	<?php } ?>
</ul>





</div>				