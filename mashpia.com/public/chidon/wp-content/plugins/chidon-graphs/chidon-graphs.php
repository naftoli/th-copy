<?php
/*
Plugin Name: Chidon Graphs 
Description: Custom post type graphs for Chidon Website
Author: Esty Rosenberg
Author URI: http://www.estyrosenberg.com
*/

add_action( 'init', 'chidon_graphs' );



function chidon_init() {
    wp_enqueue_script( 'chart-js', plugins_url( '/scripts/Chart.min.js', __FILE__ ));
    wp_enqueue_script( 'scritp-js', plugins_url( '/scripts/script.js', __FILE__ ), array('jquery'));
}
add_action('wp_enqueue_scripts','chidon_init');

function chidon_graphs() {
    register_post_type( 'graph', array(
      'labels' => array(
        'name' => 'Graphs',
        'singular_name' => 'Graph',
       ),
      'description' => 'Graphs to display Chidon\'s growth.',
      'public' => true,
      'menu_position' => 20,
      'supports' => array( 'title', 'thumbnail', 'custom-fields' )
    ));
}

function chidon_graphs_shortcode( $atts ) { 
    $a = shortcode_atts( array(
        'id' => null
    ), $atts );

    $graph_load = new WP_Query( array (
        'post_type' => 'graph',
        'p' => $a['id']
    ) );

    $data = '';

    if( $graph_load->have_posts() ) {
        while( $graph_load->have_posts() ) {
            $graph_load->the_post();
            $data .= '<div class="graph" id="graph-'.get_the_ID().'">'; 
            $years = get_post_meta( get_the_ID() );
            $data .= '<canvas class="graph-canvas"></canvas>';
            foreach ($years as $year => $value) {
                if(!($year == "_edit_last" || $year == "_edit_lock" || $year == "_thumbnails_id") && intval($year) > 2000 && intval($year) < 3000 ) {
                    $data .= '<p class="hidden graph-value">'.'<span class="x">'.$year.'</span><span class="y">'.$value[0].'</span></p>';
                }
            }
            $data .= '<div class="graph-title">'.get_the_post_thumbnail().'<h3>'.get_the_title().'</h3></div>'; 
            $data .= '</div>'; 
        }
    } else {
        return 'Graph not found';
    }
    wp_reset_postdata(); 
    return $data; 
 } 
add_shortcode('graph','chidon_graphs_shortcode');
