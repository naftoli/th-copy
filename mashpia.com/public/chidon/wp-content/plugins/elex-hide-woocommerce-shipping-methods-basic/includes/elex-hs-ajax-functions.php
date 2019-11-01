<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_elex_hs_create_rule', 'elex_hs_create_rule_callback');
add_action('wp_ajax_elex_hs_edit_rule', 'elex_hs_edit_rule_callback');
add_action('wp_ajax_elex_hs_delete_rule', 'elex_hs_delete_rule_callback');

function elex_hs_create_rule_callback() {
    check_ajax_referer('elex_hs_ajax_nonce', '_elex_hs_ajax_nonce');
    $saved_rules = get_option('elex_hs_rules_to_hide_shipping_methods');
    $rule_to_save = isset($_POST['rule']) ? $_POST['rule'] : array();
    if (!isset($rule_to_save['rule_name']) || trim($rule_to_save['rule_name']) == '') {
        $rule_count = get_option('elex_hs_rule_count');
        if (empty($rule_count)) {
            $rule_to_save['rule_name'] = 'Rule_1';
            update_option('elex_hs_rule_count', 1);
        } else {
            $rule_count++;
            $rule_to_save['rule_name'] = 'Rule_' . $rule_count;
            update_option('elex_hs_rule_count', $rule_count);
        }
    }
    
    $rule_name = sanitize_text_field($rule_to_save['rule_name']);
    $hide_shipping_methods = array_map( 'sanitize_text_field', wp_unslash( $_POST['rule']['hide_shipping_methods'] ));
    if(isset($_POST['rule']['filter_shipping_methods'])) {
        $filter_shipping_methods = array_map( 'sanitize_text_field', wp_unslash( $_POST['rule']['filter_shipping_methods'] ));
        $rule_to_save['filter_shipping_methods'] = $filter_shipping_methods;
    }
    $rule_to_save['rule_name'] = $rule_name;
    $rule_to_save['hide_shipping_methods'] = $hide_shipping_methods;
    
    if (!empty($saved_rules)) {
        if (isset($_POST['is_edit_rule']) && $_POST['is_edit_rule'] == 'true') {
            foreach ($saved_rules as $key => $rules) {
                if($saved_rules[$key]['rule_name'] == sanitize_text_field($_POST['edit_rule_name'])) {
                    $saved_rules[$key] = $rule_to_save;
                    break;
                }
            }
        } else {
            array_push($saved_rules, $rule_to_save);
        }
        update_option('elex_hs_rules_to_hide_shipping_methods', $saved_rules);
    } else {
        $saved_rules = array();
        array_push($saved_rules, $rule_to_save);
        update_option('elex_hs_rules_to_hide_shipping_methods', $saved_rules);
    }
    die();
}

function elex_hs_edit_rule_callback() {
    check_ajax_referer('elex_hs_ajax_nonce', '_elex_hs_ajax_nonce');
    $saved_rules = get_option('elex_hs_rules_to_hide_shipping_methods');
    foreach ($saved_rules as $key => $rules) {
        $rule_name = sanitize_text_field($_POST['rule_name']);
        if ($rule_name == $rules['rule_name']) {
            die(json_encode($saved_rules[$key]));
        }
    }
}

function elex_hs_delete_rule_callback() {
    check_ajax_referer('elex_hs_ajax_nonce', '_elex_hs_ajax_nonce');
    $saved_rules = get_option('elex_hs_rules_to_hide_shipping_methods');
    foreach ($saved_rules as $key => $rules) {
        $rule_name = sanitize_text_field($_POST['rule_name']);
        if ($rule_name == $rules['rule_name']) {
            unset($saved_rules[$key]);
            update_option('elex_hs_rules_to_hide_shipping_methods', $saved_rules);
        }
    }
}

add_filter('woocommerce_package_rates', 'elex_hs_hide_shipping_methods_check', 10, 2);

function elex_hs_hide_shipping_methods_check($available_shipping_methods, $package) {
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $saved_rule = get_option('elex_hs_rules_to_hide_shipping_methods');
    $temp_shipping_methods = $available_shipping_methods;
    if (!empty($saved_rule)) {
        foreach ($saved_rule as $rule) {
            $rule_satisfied = TRUE;
            foreach ($items as $product_details) {
                if ($product_details['variation_id'] != 0) {
                    $pid = $product_details['variation_id'];
                } else {
                    $pid = $product_details['product_id'];
                }
                $product_param = $product_details['data']->get_data();

                //Check for shipping class
                if (isset($rule['shipping_class'])) {
                    $prod_shippping_class = $product_param['shipping_class_id'];
                    if ($prod_shippping_class == 0) {
                        $prod_shippping_class = -1;
                    }
                    if (in_array($prod_shippping_class, $rule['shipping_class'])) {
                        $rule_satisfied = TRUE;
                        break;
                    } else {
                        $rule_satisfied = FALSE;
                    }
                }
            }
            
            //Order Weight check
            if ($rule_satisfied && isset($rule['weight_action'])) {
                $cart_weight = $woocommerce->cart->cart_contents_weight;
                switch ($rule['weight_action']) {
                    case 'lesser':
                        if ($rule['order_weight'] < $cart_weight) {
                            $rule_satisfied = FALSE;
                        }
                        break;
                    case 'greater':
                        if ($rule['order_weight'] > $cart_weight) {
                            $rule_satisfied = FALSE;
                        }
                        break;
                    case 'equal':
                        if ($rule['order_weight'] != $cart_weight) {
                            $rule_satisfied = FALSE;
                        }
                        break;
                    case 'between':
                        if (($rule['order_min_weight'] > $cart_weight) || ($rule['order_max_weight'] < $cart_weight)) {
                            $rule_satisfied = FALSE;
                        }
                        break;
                }
            }

            //Check for shipping methods
            if ($rule_satisfied && isset($rule['filter_shipping_methods'])) {
                foreach ($temp_shipping_methods as $method => $details) {
                    $ship_method = explode(':', $method);
                    if (in_array($ship_method[0], $rule['filter_shipping_methods'])) {
                        $rule_satisfied = TRUE;
                        break;
                    } else {
                        $rule_satisfied = FALSE;
                    }
                }
            }
            

            //Hide shipping methods
            if ($rule_satisfied) {
                if (isset($rule['hide_shipping_methods'])) {
                    foreach ($temp_shipping_methods as $methods => $details) {
                        $ship_method = explode(':', $methods);
                        if (in_array($ship_method[0], $rule['hide_shipping_methods'])) {
                            unset($available_shipping_methods[$methods]);
                        }
                    }
                }
            }
        }
    }

    return $available_shipping_methods;
}
