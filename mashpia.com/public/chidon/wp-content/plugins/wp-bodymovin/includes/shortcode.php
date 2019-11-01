<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpbdmv_shortcode( $atts ) {
	$anim   = $style_atts = $classes = array();
	$output = '';

	extract(
		shortcode_atts(
			array(
				'anim_id'           => '',
				'autoplay_viewport' => 'false',
				'autostop_viewport' => 'false',
				'loop'              => 'false',
				'lazyload'          => 'false',
				'width'             => '100%',
				'height'            => '',
				'align'             => 'left',
				'css'               => '',
			),
			$atts
		)
	);

	wp_enqueue_script( 'wp-bodymovin' );
	wp_enqueue_script( 'jquery.isonscreen' );

	$anim_id       = absint( $anim_id );
	$anim_exists   = get_post_status( $anim_id ) == 'publish' && get_post_type( $anim_id ) == 'bdm-animations' ? true : false;
	$anim_renderer = get_post_meta( $anim_id, 'wpbdmv_renderer', true );
	$container_id  = uniqid( 'anim-' );

	$anim = array(
		'id'                => absint( $anim_id ),
		'container_id'      => $container_id,
		'autoplay_viewport' => $autoplay_viewport == 'true' ? true : false,
		'autostop_viewport' => $autostop_viewport == 'true' ? true : false,
		'loop'              => $loop == 'true' ? true : false,
		'width'             => $width,
		'height'            => $height,
		'lazyload'          => $lazyload == 'true' ? true : false,
	);

	if ( ! empty( $width ) ) {
		$style_atts[] = 'max-width: ' . $width . ';';
	}

	if ( ! empty( $height ) ) {
		$style_atts[] = 'min-height: ' . $height . ';';
	}

	if ( ! empty( $align ) && $align == 'right' ) {
		$style_atts[] = 'margin-left: auto;';
	} elseif ( ! empty( $align ) && $align == 'center' ) {
		$style_atts[] = 'margin-right: auto;';
		$style_atts[] = 'margin-left: auto;';
	}

	if ( $anim['lazyload'] ) {
		$classes[] = 'loading';
	}

	if ( $align ) {
		$classes[] = 'align-' . $align;
	}

	if ( ! empty( $anim_renderer ) ) {
		$classes[] = 'renderer-' . $anim_renderer;
	}

	if ( ! empty( $anim_renderer ) && $anim_renderer == 'html' ) {
		$style_atts[] = 'position: relative;';
	}

	if ( function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$classes[] = ' ' . apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), 'bodymovin', $atts );
	}

	if ( $anim_exists && class_exists( 'wpBodyMovin' ) ) {
		wpBodyMovin::addAnimation( $anim );
		$output .= '<div id="' . esc_attr( $container_id ) . '" class="wpbdmv-animation ' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( implode( ' ', $style_atts ) ) . '"></div>';
	} else {
		$output .= '<p class="anim-error">' . esc_html__( 'Invalid Animation ID', 'wp-bodymovin' ) . '</p>';
	}

	return $output;
}

add_action(
	'init',
	function () {
		add_shortcode( 'bodymovin', 'wpbdmv_shortcode' );
	}
);



