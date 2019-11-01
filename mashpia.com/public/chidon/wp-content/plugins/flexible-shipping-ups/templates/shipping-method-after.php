<?php
/**
 * Shipping method after - access point.
 *
 * @package Flexible Shipping Ups.
 */

/**
 * Variables.
 *
 * @var array  $select_options .
 * @var string $selected_access_point .
 */
?>
<tr class="shipping flexible-shipping-ups-shipping">
	<td colspan="2">
		<h4><?php _e( 'UPS Access Point', 'flexible-shipping-ups' ); // wpcs: XSS ok. ?></h4>
		<span class="description" id="ups_access_point-description" ><?php echo esc_html( __( 'List of the closest points based on the billing address or shipping address.', 'flexible-shipping-ups' ) ); ?></span>
		<?php

		$field_args = array(
			'type'    => 'select',
			'options' => $select_options,
		);
		woocommerce_form_field( 'ups_access_point', $field_args, $selected_access_point );
		?>
		<script type="text/javascript">
			let ups_access_point_value;
			jQuery(document).ready(function() {
				if ( jQuery().select2 ) {
					jQuery('#ups_access_point').select2();
				};
				ups_access_point_value = jQuery('#ups_access_point').val();
			});
			jQuery(document).on( 'change', '#ups_access_point', function() {
				if ( ups_access_point_value != jQuery('#ups_access_point').val() ) {
					ups_access_point_value = jQuery('#ups_access_point').val();
					jQuery('#ups_access_point').select2( 'destroy' );
					jQuery(document.body).trigger( 'update_checkout' );
				}
			});
		</script>
	</td>
</tr>
