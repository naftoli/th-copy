
var wpbdmv = wpbdmv || {};


jQuery( document ).ready(function( $ ) {

	wpbdmv.animations = {};

	if ( typeof( wpbdm_preview_data ) !== 'undefined' ) {
		var animData = {
			container: document.getElementById('wpbdmv-anim-preview'),
			renderer: jQuery('#wpbdmv_renderer').val(),
			loop: true,
			assetsPath: jQuery('#wpbdmv_assets_path').val(),
			autoplay: true,
			rendererSettings: {
				progressiveLoad:false
			},
			animationData: wpbdm_preview_data
		};

		wpbdmv.animations.wpbdm_preview = bodymovin.loadAnimation(animData);

		wpbdmv.animations.wpbdm_preview.onLoopComplete = function() {
			if ( !$('.wpbdmv-anim-controls').hasClass('repeat') ) {
				$('.wpbdmv-anim-controls').removeClass('playing');
				wpbdmv.animations.wpbdm_preview.stop();
			}
		};

		$('.wpbdmv-anim-controls').addClass('playing');
	}


	$('.wpbdmv-anim-controls').on('click', '.play', function () {
		$('.wpbdmv-anim-controls').addClass('playing');
		 wpbdmv.animations.wpbdm_preview.play();
	});

	$('.wpbdmv-anim-controls').on('click', '.pause', function () {
		$('.wpbdmv-anim-controls').removeClass('playing');
		wpbdmv.animations.wpbdm_preview.pause();
	});

	$('.wpbdmv-anim-controls').on('click', '.repeat', function () {
		$('.wpbdmv-anim-controls').toggleClass('repeat');
	});


	$('.wpbdmv_shortcode_gen_fields').on('change', 'input, select', function () {
		if ( get_animation_shortcode() ) {
			$('#wpbdmv_shortcode').val( get_animation_shortcode() );
		}
	});

	$('.wpbdmv_shortcode_gen_fields input').eq(0).trigger('change');

	$('#wpbdmv_anim_media').on('click', '.edit-upload-url', function (e) {
		e.preventDefault();
		$('.wpbdmv_assets_path_wrapper').fadeIn();
		return false;
	});

	function get_animation_shortcode() {
		var atts = [];
		var anim_id = $('input#post_ID').val();

		if ( $('input#wpbdmv_sc_loop').is(':checked') ) {
			atts.push('loop="true"');
		}

		if ( $('input#wpbdmv_sc_lazyload').is(':checked') ) {
			atts.push('lazyload="true"');
		}

		if ( $('input#wpbdmv_sc_autoplay_viewport').is(':checked') ) {
			atts.push('autoplay_viewport="true"');
		}

		if ( $('input#wpbdmv_sc_autostop_viewport').is(':checked') ) {
			atts.push('autostop_viewport="true"');
		}

		if ( $('input#wpbdmv_sc_width').val() ) {
			atts.push('width="' + $('input#wpbdmv_sc_width').val() + '"');
		}

		if ( $('input#wpbdmv_sc_height').val() ) {
			atts.push('height="' + $('input#wpbdmv_sc_height').val() + '"');
		}

		if ( $('select#wpbdmv_sc_align').val() ) {
			atts.push('align="' + $('select#wpbdmv_sc_align').val() + '"');
		}

		return anim_id ? '[bodymovin anim_id="' + anim_id + '" ' + atts.join(' ') + ']' : false;
	}

	// Set all variables to be used in scope
	var frame,
	metaBox = $('#wpbdmv_anim_media.postbox'), // Your meta box id here
	addImgLink = metaBox.find('.upload_image_button'),
	delImgLink = metaBox.find( '.remove_image_button'),
	imgContainer = metaBox.find( '.custom-img-container'),
	imgIdInput = metaBox.find( '.wpbdmv_assets_input' );

	// ADD IMAGE LINK
	addImgLink.on( 'click', function( event ){
		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create a new media frame
		frame = wp.media({
			multiple: true  // Set to true to allow multiple files to be selected
		});

		// When an image is selected in the media frame...
		frame.on( 'select', function() {
			checkAssetsExist();
		});

		// Finally, open the modal on click
		frame.open();
	});

	function checkAssetsExist() {
		$('#wpbdmv_anim_media .assets-list ul li').each( function () {
			var $li = $(this);
			$li.removeClass( 'asset-missing asset-exists' );
			$li.find('.status').html( '' );

			if ( $li.data('asset-url') ) {
				$.ajax({
					url: $li.data('asset-url'),
					type:'HEAD',
					error: function(){
						$li.addClass('asset-missing');
						$li.find('.status').html( wpbdmv.strings.asset_not_found );
					},
					success: function(){
						$li.addClass('asset-exists');
						$li.find('.status').html( wpbdmv.strings.asset_ok );
					}
				});
			}
		});
	}

	checkAssetsExist();

});

