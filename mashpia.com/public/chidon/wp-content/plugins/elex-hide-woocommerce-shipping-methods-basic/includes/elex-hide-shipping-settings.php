<?php
if (!defined('ABSPATH')) {
    exit;
}

class ELEX_Hide_Shipping_Setting {

    function __construct() {
        $this->elex_hs_load_assets();
        $this->elex_hs_display_tabs();
    }

    function elex_hs_load_assets() {
        wp_nonce_field('elex_hs_ajax_nonce', '_elex_hs_ajax_nonce');
        global $woocommerce;
        $woocommerce_version = function_exists('WC') ? WC()->version : $woocommerce->version;
        wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css', array(), $woocommerce_version);
        wp_register_style('elex-hs-plugin-bootstrap', plugins_url('/assets/css/bootstrap.css', dirname(__FILE__)));
        wp_enqueue_style('elex-hs-plugin-bootstrap');
        wp_register_style('elex-hs-plugin-styles', plugins_url('/assets/css/elex-hs-styles.css', dirname(__FILE__)));
        wp_enqueue_style('elex-hs-plugin-styles');
        wp_register_script('elex-hs-tooltip-jquery', plugins_url('/assets/js/tooltip.js', dirname(__FILE__)));
        wp_enqueue_script('elex-hs-tooltip-jquery');
        wp_register_script('elex-chosen-jquery', plugins_url('/assets/js/chosen.jquery.js', dirname(__FILE__)));
        wp_enqueue_script('elex-chosen-jquery');
        wp_register_script('elex-hs-jquery', plugins_url('/assets/js/elex-hs-script.js', dirname(__FILE__)));
        wp_enqueue_style('elex-gpf-bootstrap', plugins_url('resources/css/bootstrap.css',dirname(__FILE__)));
       
        $js_var = array(
            'elex_order_weight_min_range' => __('Min Value', 'elex-hide-shipping-methods'),
            'elex_order_weight_max_range' => __('Max Value', 'elex-hide-shipping-methods'),
            'elex_order_weight_value' => __('Enter Value', 'elex-hide-shipping-methods'),
            'elex_states' => __('States', 'elex-hide-shipping-methods'),
            'elex_states_placeholder' => __('Select States', 'elex-hide-shipping-methods'),
            'elex_filter_state' => __('Choose the shipping destination states which you want to hide the shipping methods.', 'elex-hide-shipping-methods')
        );
        wp_localize_script('elex-hs-jquery', 'elex_hs_js_texts', $js_var);
        wp_enqueue_script('elex-hs-jquery');
    }

    function elex_hs_display_tabs() {
        $current_tab = 'elex_hs_create_rule';
        echo '
                    <script>
                    jQuery(function($){
                    show_selected_tab($(".tab_elex_hs_create_rule"),"elex_hs_create_rule");
                    $(".tab_elex_hs_create_rule").on("click",function() {
                        return show_selected_tab($(this),"elex_hs_create_rule");
                    });
                    
                    $(".tab_elex_hs_manage_rule").on("click",function() {
                        return show_selected_tab($(this),"elex_hs_manage_rule");
                    });
                    $(".tab_elex_hs_go_premium").on("click",function() {
                        return show_selected_tab($(this),"elex_hs_go_premium");
                    });
                   
                    function show_selected_tab($element,$tab) {
                        $(".nav-tab").removeClass("nav-tab-active");
                        $element.addClass("nav-tab-active");
                        $(".elex_hs_create_rule_tab_field").closest("tr,h3").hide();
                        $(".elex_hs_create_rule_tab_field").next("p").hide();
                                         
                        $(".elex_hs_manage_rule_tab_field").closest("tr,h3").hide();
                        $(".elex_hs_manage_rule_tab_field").next("p").hide();

                        
                        $("."+$tab+"_tab_field").closest("tr,h3").show();
                        $("."+$tab+"_tab_field").next("p").show();
                        
                        
                        if($tab=="elex_hs_create_rule") {
                                $(".elex-hs-all-step").show();
                                $("#elex_hs_step2").removeClass("active");
                                $("#elex_hs_step1").addClass("active");
                        	$("#elex_hs_filter_div").show();
                                $("#elex_hs_hide_shipping_div").hide();
                        }
                        else {
                                $(".elex-hs-all-step").hide();
                        	$("#elex_hs_filter_div").hide();
                                $("#elex_hs_hide_shipping_div").hide();
                        }
                        if($tab=="elex_hs_manage_rule") {
                        	$("#elex_hs_manage_rule_div").show();
                        }
                        else {
                        	$("#elex_hs_manage_rule_div").hide();
                        }
                        if($tab=="elex_hs_go_premium") {
                        	$("#elex_hs_market_content").show();
                        }
                        else {
                        	$("#elex_hs_market_content").hide();
                        }
                        
                        return false;
                    }   
                    });
                    </script>
                    <style>
                   
                    a.nav-tab{
                                cursor: default;
                    }
                    </style>
                    <hr class = "wp-header-end">';
        $tabs = array(
            'elex_hs_create_rule' => __("Create Rule ", 'elex-hide-shipping-methods'),
            'elex_hs_manage_rule' => __("Manage Rules", 'elex-hide-shipping-methods'),
            'elex_hs_go_premium' => __("<span style='color:red;'>Go Premium</span>", 'elex-hide-shipping-methods'),
        );
        $html = '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $stab => $name) {
            $class = ($stab == $current_tab) ? 'nav-tab-active' : '';
            $html .= '<a style="text-decoration:none !important;" class="nav-tab ' . $class . " tab_" . $stab . '" >' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
        ?>
        <div class="elex-hs-all-step">
            <div id ="elex_hs_step1" class="elex-hs-steps active">
                <?php echo _e('STEP 1: Set the Filter', 'elex-hide-shipping-methods'); ?>
            </div>
            <div id ="elex_hs_step2" class="elex-hs-steps">
                <?php echo _e('STEP 2: Shipping Methods to Hide', 'elex-hide-shipping-methods'); ?>
            </div>
        </div>
        <?php
        
    }

}

new ELEX_Hide_Shipping_Setting();
include_once ELEX_HIDE_SHIPPING_METHODS_TEMPLATE_PATH . "/elex-template-create-rule.php";
 include_once("market.php");
