<?php
/**
 * InAcademia
 *
 * @package InAcademia
 *
 * @wordpress-plugin
 * Plugin Name: Student Discount for WooCommerce
 * Plugin URI: https://inacademia.org/
 * Description: Adds student validation by InAcademia
 * Version: 1.0
 * Author: Martin van Es for GEANT Association
 * Author URI: https://geant.org/
 * Text Domain: wc-inacademia-main
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) || exit;

// Define INACADEMIA_VERSION.
$plugin_data = get_file_data( __FILE__, array( 'version' => 'version' ) );
define( 'INACADEMIA_VERSION', $plugin_data['version'] );

$validated = false;

$options = get_option(
	'inacademia_options',
	array(
		'coupon_name' => 'foobar',
		'op_url' => 'https://op.inacademia.local/',
		'client_id' => 'client_id',
		'client_secret' => 'client_secret',
		'notification' => 'off',
		'button' => 'off',
	)
);
if ( ! is_array( $options ) ) {
	$options = array();
}

$inacademia_coupon = @$options['coupon_name'];
$notification = @$options['notification'] ?? 'off';
$button = @$options['button'] ?? 'off';
$button_allowed = false;

require 'wc-inacademia-admin.php';
require 'wc-inacademia-blocks.php';

add_action( 'wp_enqueue_scripts', 'inacademia_register_scripts' );
add_action( 'woocommerce_check_cart_items', 'inacademia_handle_validation' );
add_action( 'woocommerce_applied_coupon', 'inacademia_applied_coupon' );
add_action( 'woocommerce_removed_coupon', 'inacademia_removed_coupon' );
add_action( 'wp_loaded', 'inacademia_wp_loaded' );

add_filter( 'woocommerce_cart_totals_coupon_label', 'inacademia_change_coupon_label', 10, 2 );

if ( 'on' == $button ) {
	add_action( 'woocommerce_proceed_to_checkout', 'inacademia_button', 20 );
}

// @session_start();
// $_SESSION['inacademia_op_url'] = @$options['op_url'];
// $_SESSION['inacademia_scope'] = @$options['scope'];
// $_SESSION['inacademia_client_id'] = @$options['client_id'];
// $_SESSION['inacademia_client_secret'] = @$options['client_secret'];

/**
 * Check if this is an api call
 */
function inacademia_is_api() {
	return defined( 'REST_REQUEST' ) ? REST_REQUEST : false;
}

/**
 * Register my scripts
 */
function inacademia_register_scripts() {
	wp_enqueue_style( 'inacademia', plugins_url( 'assets/inacademia.css', __FILE__ ) );
}

/**
 * Get validation URL
 */
function inacademia_get_validation_url() {
	return plugins_url( 'start.php', __FILE__ );
}

/**
 * WP_loaded hook
 */
function inacademia_wp_loaded() {
	global $inacademia_coupon, $button_allowed, $options, $validated;

	if ( ! WC()->cart || inacademia_is_api() ) {
		return;
	}

	session_start( array( 'name' => 'inacademia' ) );
	// $_SESSION['inacademia_op_url'] = @$options['op_url'];
	// $_SESSION['inacademia_scope'] = @$options['scope'];
	$_SESSION['inacademia_client_id'] = @$options['client_id'];
	$_SESSION['inacademia_client_secret'] = @$options['client_secret'];
	$validated = isset( $_SESSION['inacademia_validated'] ) ? filter_var( wp_unslash( $_SESSION['inacademia_validated'] ), FILTER_SANITIZE_STRING ) : false;

	$coupon = new \WC_Coupon( $inacademia_coupon );
	$coupon_id = $coupon->get_id();

	$coupon_product_ids = $coupon->get_product_ids();
	$coupon_excluded_product_ids = $coupon->get_excluded_product_ids();

	/*
	 * Bikeshed
	$coupon_product_categories = $coupon->get_product_categories();
	$coupon_excluded_product_categories = $coupon->get_excluded_product_categories();

	error_log("Coupon id: " . $coupon_id);.
	error_log("coupon_product_ids: " . print_r($coupon_product_ids, true));
	error_log("excluded_product_ids: " . print_r($coupon_excluded_product_ids, true));
	error_log("coupon_product_categories: " . print_r($coupon_product_categories, true));
	error_log("$coupon_excluded_product_categories: " . print_r($coupon_excluded_product_categories, true));
	*/

	$items = WC()->cart->get_cart();

	// Collect all product_ids in cart.
	$cart_product_ids = array();
	/* $cart_category_ids = []; */
	foreach ( $items as $item => $values ) {
		$cart_product_ids[] = $values['data']->get_id();
		// $cart_category_ids = array_merge($cart_category_ids, $values['data']->get_category_ids());
	}

	/*
	 * Bikeshed
	error_log("cart_product_ids: " . print_r($cart_product_ids, true));
	$cart_category_ids = array_unique($cart_category_ids);
	error_log("cart_category_ids: " . print_r($cart_category_ids, true));
	*/

	// We first check required products are present.
	if ( count( $coupon_product_ids ) ) {
		foreach ( $coupon_product_ids as $coupon_product_id ) {
			if ( in_array( $coupon_product_id, $cart_product_ids ) ) {
				$button_allowed = true;
				break;
			}
		}
		// We check if products are excluded.
	} else {
		foreach ( $cart_product_ids as $cart_product_id ) {
			if ( ! in_array( $cart_product_id, $coupon_excluded_product_ids ) ) {
				$button_allowed = true;
				break;
			}
		}
	}
}

/**
 * Show InAcademia Button
 */
function inacademia_button() {
	// See woocommerce/templates/cart/proceed-to-checkout-button.php for original element.
	global $validated, $button_allowed;

	?>

	<?php
	if ( $button_allowed ) {
		if ( ! $validated ) {
			?>
	  <a class='inacademia validate' href='<?php echo esc_url( inacademia_get_validation_url() ); ?>' target=_blank><img class='inacademia' src='<?php echo esc_url( plugins_url( 'assets/mortarboard.svg', __FILE__ ) ); ?>'>&nbsp;<span>I'm a Student</span></a><br>
	  <i>Login at your university to apply a student discount</i><br>
			<?php
		} else {
			?>
	  <a class='inacademia validated' href='#' onclick='return false;'><img class='inacademia' src='<?php echo esc_url( plugins_url( 'assets/mortarboard_white.svg', __FILE__ ) ); ?>'>&nbsp;<span class=''>I'm a student</span></a><br>
			<?php
		}
		?>
	<br>
		<?php
	}
}

/**
 * Handle InAcademia Validation
 */
function inacademia_handle_validation() {
	global $validated, $inacademia_coupon, $notification, $button_allowed;

	if ( ! $button_allowed || inacademia_is_api() ) {
		return;
	}

	session_start( array( 'name' => 'inacademia' ) );

	$inacademia_error = isset( $_SESSION['inacademia_error'] ) ? filter_var( wp_unslash( $_SESSION['inacademia_error'] ), FILTER_SANITIZE_STRING ) : null;
	if ( $inacademia_error ) {
		wc_print_notice( $inacademia_error, 'error' );
		unset( $_SESSION['inacademia_error'] );
	}

	/* $validated = @$_SESSION['inacademia_validated'] || array(); */
	$applied = WC()->cart->has_discount( $inacademia_coupon );

	if ( $validated ) {
		if ( ! $applied ) {
			WC()->cart->apply_coupon( $inacademia_coupon );
			wc_clear_notices();
			wc_print_notice( 'Student discount applied!', 'notice' );
		}
	} else {
		if ( 'on' == $notification ) {
			wc_print_notice( "Are you a university student? <a href='" . inacademia_get_validation_url() . "' target=_blank>Login</a> at your university to apply a student discount.", 'notice' );
		}
		if ( $applied ) {
			WC()->cart->remove_coupon( $inacademia_coupon );
			wc_clear_notices();
		}
	}
}

/**
 * Change InAcademia Coupon label
 * Hide 'Coupon: CODE' in cart totals and instead return generic 'Student discount'
 * This does not hide the coupon code from the generated cart HTML
 *
 * @param string $label label.
 * @param coupon $coupon coupon.
 */
function inacademia_change_coupon_label( $label, $coupon ) {
	global $inacademia_coupon;

	if ( $coupon->get_code() == $inacademia_coupon ) {
		echo 'Student discount';
	} else {
		echo esc_html( $label );
	}
}

/**
 * Applied coupon hook
 *
 * @param coupon $coupon coupon.
 */
function inacademia_applied_coupon( $coupon ) {
	global $validated, $inacademia_coupon;

	if ( $coupon == $inacademia_coupon && ! $validated ) {
		// Do not allow inacademia coupon to be claimed without inacademia session (validated).
		WC()->cart->remove_coupon( $inacademia_coupon );
		wc_clear_notices();
	}
}

/**
 * Removed coupon hook
 *
 * @param coupon $coupon coupon.
 */
function inacademia_removed_coupon( $coupon ) {
	global $validated, $inacademia_coupon;

	if ( $coupon == $inacademia_coupon ) {
		// Clear the inacademia session (validated).
		session_start( array( 'name' => 'inacademia' ) );
		unset( $_SESSION['inacademia_validated'] );
	}
}

