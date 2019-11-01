<?php
/*
Plugin Name: WP Bodymovin
Plugin URI: iodsgn.com/wp-bodymovin/
Description: Use bodymovin exported JSON files in WordPress
Version: 2.1.0
Author: ioDSGN
Author URI: iodsgn.com
Text Domain: wp-bodymovin
// Requires PHP 5.3+
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'includes/shortcode.php';
require_once 'includes/metaboxes.php';

/**
 * Load plugin textdomain.
 */
function wpbdmv_load_textdomain() {
	load_plugin_textdomain( 'wp-bodymovin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wpbdmv_load_textdomain' );


if ( ! class_exists( 'wpBodyMovin' ) ) {
	class wpBodyMovin {
		public static $animations = array();

		function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'init', array( $this, 'register_posttype' ) );
			add_action( 'wp_footer', array( $this, 'add_animation_data' ), 5 );
			add_action( 'wp_ajax_wpbdmv_get_animation', array( $this, 'ajax_get_animation_data' ) );
			add_action( 'wp_ajax_nopriv_wpbdmv_get_animation', array( $this, 'ajax_get_animation_data' ) );
		}

		function scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_register_script( 'bodymovin', plugin_dir_url( __FILE__ ) . 'assets/js/lottie' . $suffix . '.js', array(), '5.5.3' );
			wp_register_script( 'wp-bodymovin', plugin_dir_url( __FILE__ ) . 'assets/js/wpbodymovin' . $suffix . '.js', array( 'jquery', 'bodymovin' ), '2.1.0', true );
			wp_register_script( 'jquery.isonscreen', plugin_dir_url( __FILE__ ) . 'assets/js/isonscreen' . $suffix . '.js', array( 'jquery' ), '0.0.1', true );
		}

		function admin_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$screen = get_current_screen();
			wp_register_script( 'bodymovin', plugin_dir_url( __FILE__ ) . 'assets/js/lottie' . $suffix . '.js', array(), '5.5.3' );
			wp_register_script( 'wp-bodymovin-backend', plugin_dir_url( __FILE__ ) . 'assets/js/wpbodymovin-backend' . $suffix . '.js', array( 'bodymovin' ), '2.1.0' );

			wp_localize_script(
				'wp-bodymovin-backend',
				'wpbdmv',
				array(
					'strings' => array(
						'asset_not_found' => esc_html__( 'Asset not found', 'wp-bodymovin' ),
						'asset_ok'        => esc_html__( 'Ok.', 'wp-bodymovin' ),
					),
				)
			);

			if ( $screen->post_type == 'bdm-animations' ) {
				wp_enqueue_style( 'wp-bodymovin-admin', plugin_dir_url( __FILE__ ) . 'assets/css/wpbodymovin-admin' . $suffix . '.css', array(), '2.1.0' );
				wp_enqueue_media();
			}
		}

		function register_posttype() {
			$labels = array(
				'name'               => esc_html__( 'Animations', 'wp-bodymovin' ),
				'singular_name'      => esc_html__( 'Animation', 'wp-bodymovin' ),
				'add_new'            => esc_html__( 'Add New', 'wp-bodymovin' ),
				'add_new_item'       => esc_html__( 'Add New Animation', 'wp-bodymovin' ),
				'edit_item'          => esc_html__( 'Edit Animation', 'wp-bodymovin' ),
				'new_item'           => esc_html__( 'New Animation', 'wp-bodymovin' ),
				'view_item'          => esc_html__( 'View Animation', 'wp-bodymovin' ),
				'search_items'       => esc_html__( 'Search Animations', 'wp-bodymovin' ),
				'not_found'          => esc_html__( 'Nothing found', 'wp-bodymovin' ),
				'not_found_in_trash' => esc_html__( 'Nothing found in Trash', 'wp-bodymovin' ),
				'parent_item_colon'  => '',
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'capability_type'     => 'page',
				'hierarchical'        => false,
				'rewrite'             => true,
				'supports'            => array( 'title' ),
				'menu_icon'           => 'dashicons-video-alt',
				'menu_position'       => 20,
			);
			register_post_type( 'bdm-animations', $args );
		}

		public static function addAnimation( $animation = array() ) {
			if ( empty( $animation ) || empty( $animation['id'] ) ) {
				return false;
			}

			if ( ( ! isset( $animation['lazyload'] ) || $animation['lazyload'] != true ) && $data = self::getAnimationData( $animation['id'] ) ) {
				$animation['animation_data'] = $data;
			}

			if ( ! isset( $animation['autoplay_onload'] ) ) {
				$animation['autoplay_onload'] = true;
			}

			if ( (bool) get_post_meta( $animation['id'], 'wpbdmv_has_assets', true ) ) {
				if ( get_post_meta( $animation['id'], 'wpbdmv_assets_path', true ) ) {
					$animation['assets_path'] = esc_url( trailingslashit( get_post_meta( $animation['id'], 'wpbdmv_assets_path', true ) ) );
				}
			}

			if ( get_post_meta( $animation['id'], 'wpbdmv_renderer', true ) ) {
				$animation['renderer'] = esc_attr( get_post_meta( $animation['id'], 'wpbdmv_renderer', true ) );
			}

			self::$animations[] = $animation;
		}

		public static function getAnimationData( $id ) {
			if ( ! empty( $id ) ) {

				$data = get_post_meta( $id, 'wpbdmv_jsondata', true );

				if ( ! empty( $data ) ) {
					return $data;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public static function getAnimations() {
			return apply_filters( 'wpbdmv-animations', self::$animations );
		}

		public static function hasAnimations() {
			$animations = self::getAnimations();
			return empty( $animations ) ? false : true;
		}

		function add_animation_data() {
			if ( ! self::hasAnimations() ) {
				return;
			}

			wp_localize_script(
				'wp-bodymovin',
				'wpbodymovin',
				array(
					'animations' => self::getAnimations(),
					'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				)
			);
		}


		function ajax_get_animation_data() {

			$id = isset( $_POST['animation_id'] ) ? intval( $_POST['animation_id'] ) : 0;

			if ( get_post_status( $id ) == 'publish' ) {
				$json_string = self::getAnimationData( $id );
			}

			if ( ! empty( $json_string ) ) {
				$response = array(
					'result'      => 'ok',
					'json_string' => $json_string,
				);
			} else {
				$response = array(
					'result'        => 'error',
					'error_message' => esc_html__( 'Animation data not found for id: ', 'wp-bodymovin' ) . $id,
				);
			}

			wp_send_json( $response );
			wp_die();
		}
	}

	$wpBodyMovin = new wpBodyMovin();
}

function wpbdmv_get_animation_posts() {
	$args       = array(
		'posts_per_page' => -1,
		'post_type'      => 'bdm-animations',
	);
	$animations = get_posts( $args );
	$array_out  = array();

	foreach ( $animations as $animation ) {
		$array_out[ $animation->post_title ] = $animation->ID;
	}

	return $array_out;
}


