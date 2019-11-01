jQuery( document ).ready(function( $ ) {

	function init_bodymovin() {
		if ( typeof( wpbodymovin ) === 'undefined' ) {
			return false;
		}

		for ( var id in wpbodymovin.animations ) {
			if ( wpbodymovin.animations[id].lazyload || typeof( wpbodymovin.animations[id].animation_data ) === 'undefined' ) {
				async_anim_load( id, wpbodymovin.animations[id] );
			} else {
				// load animation
				anim_load( id, wpbodymovin.animations[id], wpbodymovin.animations[id].animation_data );
			}
		}


	}

	function async_anim_load( id, animation ) {
		var ajax_data = {
			action:      'wpbdmv_get_animation',
			animation_id: wpbodymovin.animations[id].id
		};

		jQuery.post(wpbodymovin.ajaxurl, ajax_data, function(response) {
			jQuery( '#' + animation.container_id ).removeClass('loading');

			if ( response.result === 'ok' ) {
				anim_load( id, animation, response.json_string );
			}
		});
	}

	function anim_load( id, animation, anim_data ) {
		var animData = {
			container: document.getElementById( animation.container_id ),
			renderer: typeof( animation.renderer ) !== 'undefined' ? animation.renderer : 'svg',
			loop: animation.loop,
			assetsPath: typeof( animation.assets_path ) !== 'undefined' ? animation.assets_path : null,
			autoplay: !animation.autoplay_viewport && animation.autoplay_onload,
			rendererSettings: {
				progressiveLoad:false
			},
			animationData: JSON.parse( anim_data )
		};

		var $container = jQuery( '#' + animation.container_id );

		wpbodymovin.animations[id].instance = bodymovin.loadAnimation(animData);

		$(window).on( 'wpbodymovin_anim_load resize scroll' , function () {
			if ( animation.autoplay_viewport === true && typeof jQuery.fn.isOnScreen === 'function' ) {
				if ( $container.isOnScreen(function(deltas){
					return deltas.top >= -100 && deltas.bottom >= -100;
				}) ) {
					if ( $container.hasClass('playing') ) {
						return true; // continue .each loop
					}
					animation.instance.play();
					$container.addClass('playing').removeClass('paused');
				} else {
					if ( animation.autostop_viewport === true ) {
						if ( $container.hasClass('paused') ) {
							return true; // continue .each loop
						}
						animation.instance.pause();
						$container.addClass('paused').removeClass('playing');
					}
				}
			}
		});

		$(window).trigger( 'wpbodymovin_anim_load' );
	}

	init_bodymovin();

});
