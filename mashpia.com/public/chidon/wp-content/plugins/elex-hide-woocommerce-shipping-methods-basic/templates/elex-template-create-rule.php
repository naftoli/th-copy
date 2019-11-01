<?php
if (!defined('ABSPATH')) {
    exit;
}
global $woocommerce;
?>
<div class="elex-hs-loader"></div>
<div class='wrap postbox' style='padding:2px 20px;' id="elex_hs_filter_div">
    <h2>
        <?php _e('Filter', 'elex-hide-shipping-methods'); ?>
    </h2>
    <h4><?php _e('Define how you want to apply filters to hide shipping methods and options. Based on the options you choose here, the shipping methods and options will be hidden on the Cart and Checkout pages.', 'elex-hide-shipping-methods'); ?></h4>
    <hr>
    <table class='elex-hs-content-table'>
        <tr>
            <td class="elex-hs-content-table-left">
                <?php _e('Shipping Class', 'elex-hide-shipping-methods'); ?>
            </td>
            <td class='elex-hs-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose the shipping classes for which you want to hide particular shipping methods. To hide shipping methods for products that are not associated with any shipping classes, choose the option "No Shipping Class".', 'elex-hide-shipping-methods'); ?>'></span>
            </td>
            <td class="elex-hs-content-table-right">
                <span><select data-placeholder='<?php _e('Select Shipping Classes', 'elex-hide-shipping-methods'); ?>' multiple class="elex-hs-chosen" id="elex_hs_filter_shipping_class" style="width: 26%;">
                        <?php
                        $ship = $woocommerce->shipping->get_shipping_classes();
                        ?>
                        <option value='-1'><?php _e('No Shipping Class', 'elex-hide-shipping-methods'); ?></option>
                        <?php
                        if (count($ship) > 0) {
                            foreach ($ship as $key => $value) {
                                echo "<option value='" . $value->term_id . "'>" . $value->name . "</option>";
                            }
                        }
                        ?>
                    </select></span>
            </td>
        </tr>
        <tr>
            <td class="elex-hs-content-table-left">
                <?php _e('Order Weight', 'elex-hide-shipping-methods') ?>
                <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_weight_unit')) . ')'; ?></span>
            </td>
            <td class='elex-hs-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Specify a range of total product weight in the cart to hide particular shipping methods.', 'elex-hide-shipping-methods'); ?>'></span>
            </td>
            <td class="elex-hs-content-table-right">
                <select id="elex_hs_filter_order_weight_action">
                    <option value="all"><?php _e('All', 'elex-hide-shipping-methods') ?></option>
                    <option value="greater">>=</option>
                    <option value="lesser"><=</option>
                    <option value="equal">==</option>
                    <option value="between">|| <?php _e(' (Between)', 'elex-hide-shipping-methods') ?></option>
                </select>
                <span id='elex_hs_filter_weight_range_text'></span>
            </td>
        </tr>
        <tr>
            <td class="elex-hs-content-table-left">
                <?php _e('Shipping Methods', 'elex-hide-shipping-methods') ?>
            </td>
            <td class='elex-hs-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Based on the available shipping methods, you can hide certain other shipping methods. For example, you may need to hide Flat Rate Shipping when Free Shipping is available. To achieve this, you can choose Free Shipping as a filter and select Flat Rate shipping on STEP 2.', 'elex-hide-shipping-methods'); ?>'></span>
            </td>
            <td class="elex-hs-content-table-right">
                <?php
                $shipping_methods = $woocommerce->shipping()->get_shipping_methods();
                ?>
                <span><select data-placeholder='<?php _e('Select Shipping Methods', 'elex-hide-shipping-methods'); ?>' multiple class="elex-hs-chosen" id="elex_hs_filter_shipping_methods">
                        <?php
                        if (count($shipping_methods) > 0) {
                            foreach ($shipping_methods as $key => $val) {
                                echo "<option value='" . $key . "'>" . $key . "</option>";
                            }
                        }
                        ?>
                    </select></span>
            </td>
        </tr>
    </table>
    <button style="float: right; margin: -3% 0px 0px 0px;" class='button button-primary button-large' id="elex_hs_filter_rule_btn"><?php _e('Save & Continue', 'elex-hide-shipping-methods'); ?></button>
</div>
<?php
include_once ELEX_HIDE_SHIPPING_METHODS_TEMPLATE_PATH . "/elex-template-hide-shipping.php";

