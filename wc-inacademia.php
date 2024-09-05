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
$inacademia_plugin_data = get_file_data( __FILE__, array( 'version' => 'version' ) );
define( 'INACADEMIA_VERSION', $inacademia_plugin_data['version'] );
define( 'INACADEMIA_SLUG', 'student-discount-for-woocommerce' );
define( 'INACADEMIA_OP_URL', 'https://plugin.srv.inacademia.org/' );
// define( 'INACADEMIA_OP_URL', 'https://plugin.srv-test.inacademia.org/' );

$inacademia_validated = false;

$inacademia_options = get_option(
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
if ( ! is_array( $inacademia_options ) ) {
	$inacademia_options = array();
}

$inacademia_coupon = @$inacademia_options['coupon_name'];
$inacademia_notification = @$inacademia_options['notification'] ?? 'off';
$inacademia_button = @$inacademia_options['button'] ?? 'off';
$inacademia_button_allowed = false;

require 'inacademia.php';
require 'wc-inacademia-admin.php';
require 'wc-inacademia-blocks.php';

add_action( 'wp_enqueue_scripts', 'inacademia_register_scripts' );
add_action( 'woocommerce_check_cart_items', 'inacademia_handle_validation' );
add_action( 'woocommerce_applied_coupon', 'inacademia_applied_coupon' );
add_action( 'woocommerce_removed_coupon', 'inacademia_removed_coupon' );
add_action( 'wp_loaded', 'inacademia_wp_loaded' );
add_action( 'rest_api_init', 'inacademia_register_api_routes' );

add_filter( 'woocommerce_cart_totals_coupon_label', 'inacademia_change_coupon_label', 10, 2 );

if ( 'on' == $inacademia_button ) {
	add_action( 'woocommerce_proceed_to_checkout', 'inacademia_button', 20 );
}

/**
 * Register my api routes
 */
function inacademia_register_api_routes() {
	register_rest_route(
		INACADEMIA_SLUG,
		'/(start|redirect)',
		array(
			'methods' => 'GET',
			'callback' => 'inacademia_validate',
			// 'callback' => 'inacademia_validate_dummy',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Register my scripts
 */
function inacademia_register_scripts() {
	wp_enqueue_style( 'inacademia', plugins_url( 'assets/inacademia.css', __FILE__ ) );
}

/**
 * WP_loaded hook
 */
function inacademia_wp_loaded() {
	global $inacademia_coupon, $inacademia_button_allowed, $inacademia_options, $inacademia_validated;

	if ( ! WC()->cart || WC()->is_rest_api_request() ) {
		return;
	}

	session_start( array( 'name' => 'inacademia' ) );
	$_SESSION['inacademia_client_id'] = @$inacademia_options['client_id'];
	$_SESSION['inacademia_client_secret'] = @$inacademia_options['client_secret'];

	$inacademia_validated = isset( $_SESSION['inacademia_validated'] ) ? filter_var( wp_unslash( $_SESSION['inacademia_validated'] ), FILTER_SANITIZE_STRING ) : false;

	$coupon = new \WC_Coupon( $inacademia_coupon );
	$coupon_id = $coupon->get_id();

	$coupon_product_ids = $coupon->get_product_ids();
	$coupon_excluded_product_ids = $coupon->get_excluded_product_ids();

	$items = WC()->cart->get_cart();

	// Collect all product_ids in cart.
	$cart_product_ids = array();
	foreach ( $items as $item => $values ) {
		$cart_product_ids[] = $values['data']->get_id();
	}

	// We first check required products are present.
	if ( count( $coupon_product_ids ) ) {
		foreach ( $coupon_product_ids as $coupon_product_id ) {
			if ( in_array( $coupon_product_id, $cart_product_ids ) ) {
				$inacademia_button_allowed = true;
				break;
			}
		}
		// We check if products are excluded.
	} else {
		foreach ( $cart_product_ids as $cart_product_id ) {
			if ( ! in_array( $cart_product_id, $coupon_excluded_product_ids ) ) {
				$inacademia_button_allowed = true;
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
	global $inacademia_validated, $inacademia_button_allowed;

	?>

	<?php
	if ( $inacademia_button_allowed ) {
		if ( ! $inacademia_validated ) {
			?>
	  <a class='inacademia validate' href='<?php echo esc_url( inacademia_create_start_url() ); ?>' target=_blank><img class='inacademia' src='<?php echo esc_url( plugins_url( 'assets/mortarboard.svg', __FILE__ ) ); ?>'>&nbsp;<span>I'm a Student</span></a><br>
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
	global $inacademia_validated, $inacademia_coupon, $inacademia_notification, $inacademia_button_allowed;

	if ( ! $inacademia_button_allowed || WC()->is_rest_api_request() || ! is_cart() ) {
		return;
	}

	session_start( array( 'name' => 'inacademia' ) );

	$inacademia_error = isset( $_SESSION['inacademia_error'] ) ? filter_var( wp_unslash( $_SESSION['inacademia_error'] ), FILTER_SANITIZE_STRING ) : null;
	if ( $inacademia_error ) {
		wc_print_notice( $inacademia_error, 'error' );
		unset( $_SESSION['inacademia_error'] );
	}

	$applied = WC()->cart->has_discount( $inacademia_coupon );

	if ( $inacademia_validated ) {
		if ( ! $applied ) {
			WC()->cart->apply_coupon( $inacademia_coupon );
			wc_clear_notices();
			wc_print_notice( 'Student discount applied!', 'notice' );
		}
	} else {
		if ( 'on' == $inacademia_notification ) {
			wc_print_notice( "Are you a university student? <a href='" . inacademia_create_start_url() . "' target=_blank>Login</a> at your university to apply a student discount.", 'notice' );
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
	global $inacademia_validated, $inacademia_coupon;

	if ( $coupon == $inacademia_coupon && ! $inacademia_validated ) {
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
	global $inacademia_validated, $inacademia_coupon;

	if ( $coupon == $inacademia_coupon ) {
		// Clear the inacademia session (validated).
		session_start( array( 'name' => 'inacademia' ) );
		unset( $_SESSION['inacademia_validated'] );
	}
}

