<?php
/**
 * Shipping method after - single access point.
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
		<h4><?php _e( 'UPS Access Point', 'flexible-shipping-ups' ); // WPCS: XSS ok. ?></h4>
		<?php if ( count( $select_options ) ) : ?>
			<p><?php _e( 'The closest point based on the billing address or shipping address.', 'flexible-shipping-ups' ); // WPCS: XSS ok. ?></p>
			<input type="hidden" name="ups_access_point" value="<?php echo esc_attr( $selected_access_point ); ?>" />
			<p><?php echo $select_options[ $selected_access_point ]; // WPCS: XSS ok. ?></p>
		<?php else : ?>
			<strong class="no-access-points"><?php _e( 'Access point unavailable for selected shipping address!', 'flexible-shipping-ups' ); // WPCS: XSS ok. ?></strong>
		<?php endif; ?>
	</td>
</tr>
