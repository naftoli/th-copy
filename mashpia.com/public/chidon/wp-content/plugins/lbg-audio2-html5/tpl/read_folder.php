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



});
</script>

<div class="wrap">
	<div id="lbg_logo">
			<h2 style="padding:20px;">From this section, you can generate the player playlist by reading folder content</span></h2>
 	</div>


  <form method="POST" enctype="multipart/form-data">




	  <table width="100%" cellspacing="0" class="wp-list-table widefat fixed pages">

		  <tr>
		    <td align="right" valign="top" class="row-title" width="30%">Select Player*</td>
		    <td align="left" valign="top" width="70%"><select name="playerid" id="playerid">
            	<option value="">Select a player...</option>
            <?php foreach ( $result_player as $row )
			{ ?>
              <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
			<?php } ?>
            </select></td>
	      </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Default Category*</td>
		    <td align="left" valign="middle"><select name="category" id="category">
            <?php foreach ( $result_categ as $row_categ )
			{ ?>
              <option value="<?php echo $row_categ['id']; ?>"><?php echo $row_categ['categ']; ?></option>
			<?php } ?>
            </select></td>
	    </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">Choose default image </td>
		    <td align="left" valign="middle"><input name="imgplaylist" type="text" id="imgplaylist" size="80" value="<?php echo (array_key_exists('imgplaylist', $_POST))?strip_tags($_POST['imgplaylist']):''?>" /> <input name="upload_imgplaylist_button" type="button" id="upload_imgplaylist_button" value="Upload Image" /></td>
	    </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">The path to the folder which contains the .MP3 files*</td>
		    <td align="left" valign="middle"><input name="folder_path" type="text" size="35" id="folder_path" />
		      use the relative path to that folder: 'some_optional_path/folder_name'</td>
	    </tr>
		  <tr>
		    <td align="right" valign="top" class="row-title">*mandatory fields</td>
		    <td align="left" valign="middle">&nbsp;</td>
	    </tr>


      </table>


<div style="text-align:center; padding:20px 0px 20px 0px;"><input name="Submit" type="submit" id="Submit" class="button-primary" value="Generate Playlist"></div>

</form>
</div>
