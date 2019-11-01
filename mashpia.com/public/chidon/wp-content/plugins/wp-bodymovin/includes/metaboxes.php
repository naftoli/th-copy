<?php

function wpbdmv_custom_meta_boxes() {

	// Define the custom attachment for animations
	add_meta_box(
		'wpbdmv_json_upload_field',
		'JSON Data file',
		'wpbdmv_json_upload_field',
		'bdm-animations',
		'normal',
		'high',
		null
	);

	add_meta_box(
		'wpbdmv_anim_preview',
		'Preview',
		'wpbdmv_anim_preview_metabox',
		'bdm-animations',
		'normal',
		'high',
		null
	);

	add_meta_box(
		'wpbdmv_anim_media',
		'Media Assets',
		'wpbdmv_anim_media_metabox',
		'bdm-animations',
		'normal',
		'high',
		null
	);

	add_meta_box(
		'wpbdmv_anim_settings',
		'Settings',
		'wpbdmv_anim_settings_metabox',
		'bdm-animations',
		'normal',
		'high',
		null
	);

	add_meta_box(
		'wpbdmv_shortcode_preview',
		'Shortcode generator',
		'wpbdmv_shortcode_gen_metabox',
		'bdm-animations',
		'normal',
		'high',
		null
	);

} // end add_custom_meta_boxes
add_action( 'add_meta_boxes', 'wpbdmv_custom_meta_boxes' );


function wpbdmv_json_upload_field( $post ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'wp_custom_attachment_nonce' );

	$data = get_post_meta( $post->ID, 'wpbdmv_jsondata', true );

	$html = '<p class="description">';

	if ( empty( $data ) ) {
		$html .= esc_html__( 'Upload your Bodymovin generated JSON Data file here.', 'wp-bodymovin' );
	} else {
		$html .= esc_html__( 'Animation loaded. Upload another file will overwrite existing animation.', 'wp-bodymovin' );
	}

	$html .= '</p>';
	$html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25" />';

	echo $html;

} // end wpbdmv_json_upload_field


function wpbdmv_anim_preview_metabox( $post ) {
	wp_enqueue_script( 'wp-bodymovin-backend' );
	$data = get_post_meta( $post->ID, 'wpbdmv_jsondata', true );

	if ( ! empty( $data ) ) {

		?>
	<script type="text/javascript">var wpbdm_preview_data = <?php echo urldecode( $data ); ?>;</script>
	<div id="wpbdmv-anim-preview"></div>
	<div class="wpbdmv-anim-controls">
		<span class="dashicons dashicons-controls-play play"></span>
		<span class="dashicons dashicons-controls-pause pause"></span>
		<span class="dashicons dashicons-controls-repeat repeat"></span>
	</div>
		<?php

	} else {
		echo '<p class="no-animation-loaded">' . esc_html__( 'No animation loaded. Publish your animation to see it\'s preview here.', 'wp-bodymovin' ) . '</p>';
	}
}

function wpbdmv_anim_media_metabox( $post ) {
	$has_assets  = (bool) get_post_meta( $post->ID, 'wpbdmv_has_assets', true );
	$upload_dir  = wp_upload_dir();
	$assets_path = get_post_meta( $post->ID, 'wpbdmv_assets_path', true );
	$assets_path = empty( $assets_path ) ? trailingslashit( $upload_dir['url'] ) : $assets_path;
	$json_assets = get_post_meta( $post->ID, 'wpbdmv_json_assets', true );

	if ( $has_assets ) {
		?>
		<div>
		<?php
		echo sprintf(
			esc_html__( 'This animation requires the following image assets. Make sure that these are uploaded to your media library. %s', 'wp-bodymovin' ),
			'<a class="edit-upload-url" href="#">' . esc_html__( 'Edit upload URL', 'wp-bodymovin' ) . '</a>'
		);
		?>
			</div>

		<div class="wpbdmv_assets_path_wrapper">
			<input name="wpbdmv_assets_path" type="text" id="wpbdmv_assets_path" value="<?php echo esc_url( $assets_path ); ?>" />
			<p><?php esc_html_e( 'Update/save the animation after changing the upload URL to apply changes.', 'wp-bodymovin' ); ?></p>
		</div>
		<input name="wpbdmv_json_assets" type="hidden" id="wpbdmv_json_assets" value="<?php echo esc_attr( implode( ',', $json_assets ) ); ?>" />

		<?php if ( ! empty( $json_assets ) ) { ?>
		<div class="assets-list">
			<ul>
			<?php
			foreach ( $json_assets as $json_asset ) {
				$json_asset_url = esc_url( trailingslashit( $assets_path ) . $json_asset );
				?>
				<li data-asset-url="<?php echo $json_asset_url; ?>">URL: <?php echo $json_asset_url; ?><span class="status"></span></li>
			<?php } ?>
			</ul>
		</div>
		<?php } ?>

		<?php
		wpbdmv_image_uploader( 'custom_image', $width = 115, $height = 115 );

	} else {
		?>
		<div><?php esc_html_e( 'This animation requires no image assets.', 'wp-bodymovin' ); ?></div>
		<?php
	}
}

function wpbdmv_anim_settings_metabox( $post ) {
	$selected = get_post_meta( $post->ID, 'wpbdmv_renderer', true );
	$selected = empty( $selected ) ? 'svg' : $selected;
	?>
	<div class="setting_input">
		<label for="wpbdmv_renderer">Animation renderer</label>
		<select name="wpbdmv_renderer" id="wpbdmv_renderer">
			 <option value="svg" <?php echo $selected == 'svg' ? 'selected="selected"' : ''; ?>>SVG</option>
			 <option value="canvas" <?php echo $selected == 'canvas' ? 'selected="selected"' : ''; ?>>Canvas</option>
			 <option value="html" <?php echo $selected == 'html' ? 'selected="selected"' : ''; ?>>HTML</option>
		</select>
	</div>
	<?php
}

function wpbdmv_shortcode_gen_metabox( $post ) {
	?>
	<div class="wpbdmv_shortcode_gen_fields">
		<label for="wpbdmv_sc_loop"><input name="wpbdmv_sc_loop" type="checkbox" id="wpbdmv_sc_loop" value="wpbdmv_sc_loop" ><?php echo esc_html__( 'Loop animation', 'wp-bodymovin' ); ?></label>
		<label for="wpbdmv_sc_lazyload"><input name="wpbdmv_sc_lazyload" type="checkbox" id="wpbdmv_sc_lazyload" value="wpbdmv_sc_lazyload" ><?php echo esc_html__( 'Lazyload', 'wp-bodymovin' ); ?></label>
		<label for="wpbdmv_sc_autoplay_viewport"><input name="wpbdmv_sc_autoplay_viewport" type="checkbox" id="wpbdmv_sc_autoplay_viewport" value="wpbdmv_sc_autoplay_viewport" ><?php echo esc_html__( 'Autoplay when in viewport', 'wp-bodymovin' ); ?></label>
		<label for="wpbdmv_sc_autostop_viewport"><input name="wpbdmv_sc_autostop_viewport" type="checkbox" id="wpbdmv_sc_autostop_viewport" value="wpbdmv_sc_autostop_viewport" ><?php echo esc_html__( 'Autostop when out of viewport', 'wp-bodymovin' ); ?></label>
		<label for="wpbdmv_sc_width"><?php echo esc_html__( 'Element\'s width', 'wp-bodymovin' ); ?><input type="text" name="wpbdmv_sc_width" id="wpbdmv_sc_width" placeholder="<?php echo esc_html__( 'px or %', 'wp-bodymovin' ); ?>"></label>
		<label for="wpbdmv_sc_height"><?php echo esc_html__( 'Element\'s minimum Height', 'wp-bodymovin' ); ?><input type="text" name="wpbdmv_sc_height" id="wpbdmv_sc_height" placeholder="<?php echo esc_html__( 'px', 'wp-bodymovin' ); ?>"></label>
		<label for="wpbdmv_sc_align">
			<?php echo esc_html__( 'Element\'s Alignment', 'wp-bodymovin' ); ?>
			<select name="wpbdmv_sc_align" id="wpbdmv_sc_align">
				<option value="left" selected><?php echo esc_html__( 'Left', 'wp-bodymovin' ); ?></option>
				<option value="right"><?php echo esc_html__( 'Right', 'wp-bodymovin' ); ?></option>
				<option value="center"><?php echo esc_html__( 'Center', 'wp-bodymovin' ); ?></option>
			</select>
		</label>
	</div>
	<div>
		<input name="wpbdmv_shortcode" type="text" id="wpbdmv_shortcode" readonly="readonly">
	</div>
	<?php
}

function wpbdmv_save_meta_data( $id ) {

	/* --- security verification --- */
	if ( empty( $_POST['wp_custom_attachment_nonce'] ) || ! wp_verify_nonce( $_POST['wp_custom_attachment_nonce'], plugin_basename( __FILE__ ) ) ) {
		return $id;
	} // end if

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $id;
	} // end if

	if ( 'bdm-animations' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $id ) ) {
			return $id;
		} // end if
	} else {
		if ( ! current_user_can( 'edit_page', $id ) ) {
			return $id;
		} // end if
	} // end if
	/* - end security verification - */

	// Make sure the file array isn't empty
	if ( ! empty( $_FILES['wp_custom_attachment']['name'] ) ) {

		// Setup the array of supported file types. In this case, it's just JSON.
		$supported_types = array( 'application/octet-stream', 'application/json' );

		// Check if the type is supported. If not, throw an error.
		if ( in_array( $_FILES['wp_custom_attachment']['type'], $supported_types ) ) {

			$json_data = file_get_contents( $_FILES['wp_custom_attachment']['tmp_name'] );

			// Basic JSON validity check
			$json_decoded = json_decode( $json_data );
			$valid_json   = ( json_last_error() === 0 ) ? true : false;

			if ( ! empty( $json_data ) && $valid_json ) {
				add_post_meta( $id, 'wpbdmv_jsondata', wp_slash( $json_data ) );
				update_post_meta( $id, 'wpbdmv_jsondata', wp_slash( $json_data ) );

				if ( wpbdmv_json_has_assets( $json_decoded ) ) {
					update_post_meta( $id, 'wpbdmv_has_assets', true );
					update_post_meta( $id, 'wpbdmv_json_assets', wpbdmv_json_get_assets( $json_decoded ) );
				} else {
					update_post_meta( $id, 'wpbdmv_has_assets', false );
					update_post_meta( $id, 'wpbdmv_json_assets', array() );
				}
			} else {
				wp_die( esc_html__( 'There was an error uploading your file. Make sure it is a valid JSON file.', 'wp-bodymovin' ) );
			}
		} else {
			wp_die( "The file type that you've uploaded is not a JSON file." );
		} // end if/else
	} // end if

	if ( isset( $_POST['wpbdmv_assets_path'] ) ) {
		update_post_meta( $id, 'wpbdmv_assets_path', esc_url_raw( $_POST['wpbdmv_assets_path'] ) );
	}

	if ( isset( $_POST['wpbdmv_renderer'] ) ) {
		update_post_meta( $id, 'wpbdmv_renderer', esc_attr( $_POST['wpbdmv_renderer'] ) );
	}

} // end save_custom_meta_data
add_action( 'save_post', 'wpbdmv_save_meta_data' );

function wpbdmv_json_get_assets( $json ) {
	$assets = array();

	if ( isset( $json->assets ) ) {
		foreach ( $json->assets as $asset ) {
			if ( isset( $asset->w ) && isset( $asset->h ) && isset( $asset->p ) ) {
				$supported_image = array(
					'gif',
					'jpg',
					'jpeg',
					'png',
				);

				$ext = strtolower( pathinfo( $asset->p, PATHINFO_EXTENSION ) ); // Using strtolower to overcome case sensitive
				if ( in_array( $ext, $supported_image ) ) {
					$assets[] = $asset->p;
				}
			}
		}
	}

	return empty( $assets ) ? false : $assets;
}

function wpbdmv_json_has_assets( $json ) {
	$assets = wpbdmv_json_get_assets( $json );
	return empty( $assets ) ? false : true;
}

function wpbdmv_update_edit_form() {
	echo ' enctype="multipart/form-data"';
} // end wpbdmv_update_edit_form
add_action( 'post_edit_form_tag', 'wpbdmv_update_edit_form' );


/**
 * Image Uploader
 *
 * author: Arthur Gareginyan www.arthurgareginyan.com
 */
function wpbdmv_image_uploader( $name, $width, $height ) {

	// Set variables
	$options       = get_option( 'RssFeedIcon_settings' );
	$default_image = plugins_url( 'img/no-image.png', __FILE__ );

	if ( ! empty( $options[ $name ] ) ) {
		$image_attributes = wp_get_attachment_image_src( $options[ $name ], array( $width, $height ) );
		$src              = $image_attributes[0];
		$value            = $options[ $name ];
	} else {
		$src   = $default_image;
		$value = '';
	}

	$text = __( 'Upload', 'wp-bodymovin' );

	echo '
        <div class="upload">
        	<div class="custom-img-container"></div>
            <div>
                <button type="submit" class="upload_image_button button">' . $text . '</button>
            </div>
        </div>
    ';
}
