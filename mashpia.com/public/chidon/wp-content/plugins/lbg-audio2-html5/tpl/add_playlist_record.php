<script>
jQuery(document).ready(function() {

	// Uploading files

	jQuery('#upload_imgplaylist_button').click(function(event) {
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			//alert (attachment.url);
			jQuery('#imgplaylist').val(attachment.url);
		});
		// Finally, open the modal
		file_frame.open();
	});




jQuery('#upload_mp3_button').live('click', function( event ){
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			//alert (attachment.url);
			jQuery('#mp3').val(attachment.url);
		});
		// Finally, open the modal
		file_frame.open();
	});

jQuery('#upload_ogg_button').live('click', function( event ){
		var file_frame;
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			//alert (attachment.url);
			jQuery('#ogg').val(attachment.url);
		});
		// Finally, open the modal
		file_frame.open();
	});

});
</script>

<div class="wrap">
	<div id="lbg_logo">
			<h2>Playlist for player: <span style="color:#FF0000; font-weight:bold;"><?php echo $_SESSION['xname']?> - ID #<?php echo $_SESSION['xid']?></span> - Add New</h2>
 	</div>

    <form method="POST" enctype="multipart/form-data" id="form-add-playlist-record">
	    <input name="playerid" type="hidden" value="<?php echo $_SESSION['xid']?>" />
		<table class="wp-list-table widefat fixed pages" cellspacing="0">
		  <tr>
		    <td align="left" valign="middle" width="25%">&nbsp;</td>
		    <td align="left" valign="middle" width="77%"><a href="?page=LBG_AUDIO2_HTML5_Playlist" style="padding-left:25%;">Back to Playlist</a></td>
		  </tr>
		  <tr>
		    <td colspan="2" align="center" valign="middle">&nbsp;</td>
		  </tr>
		  <tr>
		    <td align="right" valign="middle" class="row-title">Set It First</td>
		    <td align="left" valign="top"><input name="setitfirst" type="checkbox" id="setitfirst" value="1" checked="checked" />
		      <label for="setitfirst"></label></td>
	      </tr>
		  <tr>
		    <td align="right" valign="middle" class="row-title">Title</td>
		    <td align="left" valign="top"><input name="title" type="text" size="80" id="title" value="<?php echo (array_key_exists('title', $_POST))?strip_tags($_POST['title']):''?>"/></td>
		  </tr>
		  <tr>
		    <td align="right" valign="middle" class="row-title">Author</td>
		    <td align="left" valign="top">
		    <input name="author" type="text" size="80" id="author" value="<?php echo (array_key_exists('author', $_POST))?strip_tags($_POST['author']):''?>"/></td>
		  </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Category</td>
		    <td align="left" valign="top"><?php foreach ( $result as $row )
				{
					//if ( !is_array($_POST['category']) ) {
          if ( array_key_exists('category', $_POST) && !is_array($_POST['category']) ) {
						$_POST['category']=array();
					}
					$row=lbg_audio2_html5_unstrip_array($row); ?>
				<p><input name="category[]" id="category" type="checkbox" value="<?php echo $row['id'];?>" <?php echo (array_key_exists('category', $_POST) && in_array($row['id'],$_POST['category']) )?'checked="checked"':''?> /> <?php echo $row['categ'];?></p>
				<?php }	?></td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Playlist Image </td>
		    <td align="left" valign="top"><input name="imgplaylist" type="text" id="imgplaylist" size="80" value="<?php echo (array_key_exists('imgplaylist', $_POST))?strip_tags($_POST['imgplaylist']):''?>" /> <input name="upload_imgplaylist_button" type="button" id="upload_imgplaylist_button" value="Upload Image" />
		      <br />
		      Enter an URL or upload an image</td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Buy Link</td>
		    <td align="left" valign="top"><input name="buylink" type="text" size="80" id="buylink" value="<?php echo (array_key_exists('buylink', $_POST))?strip_tags($_POST['buylink']):''?>"/></td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Lyrics Link</td>
		    <td align="left" valign="top"><input name="lyricslink" type="text" size="80" id="lyricslink" value="<?php echo (array_key_exists('lyricslink', $_POST))?strip_tags($_POST['lyricslink']):''?>"/></td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">MP3 file (Chrome, IE, Safari)*</td>
		    <td align="left" valign="top"><input name="mp3" type="text" id="mp3" size="80" value="<?php echo (array_key_exists('mp3', $_POST))?strip_tags($_POST['mp3']):''?>" />
		      <input name="upload_mp3_button" type="button" id="upload_mp3_button" value="Upload File" />
		      <br />
		      Enter an URL or upload the file</td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Optional OGG file (Mozzila, Opera)</td>
		    <td align="left" valign="top"><input name="ogg" type="text" id="ogg" size="80" value="<?php echo (array_key_exists('ogg', $_POST))?strip_tags($_POST['ogg']):''?>" />
		      <input name="upload_ogg_button" type="button" id="upload_ogg_button" value="Upload File" />
		      <br />
		      Enter an URL or upload the file</td>
	      </tr>
		  <tr>
            <td align="right" valign="top" class="row-title">&nbsp;</td>
		    <td align="left" valign="top">&nbsp;</td>
	      </tr>
		  <tr>
		    <td colspan="2" align="left" valign="middle">*Required fields</td>
		  </tr>
		  <tr>
		    <td colspan="2" align="center" valign="middle"><input name="Submit" id="Submit" type="submit" class="button-primary" value="Add Record"></td>
		  </tr>
		</table>
  </form>






</div>
