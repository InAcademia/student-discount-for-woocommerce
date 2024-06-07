<?php
/**
 * InAcademia
 *
 * @package InAcademia
 * @author Martin van Es
 * @copyright 2024 By GÉANT Vereniging
 * @license GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Student Discount for WooCommerce
 * Plugin URI: https://inacademia.org/
 * Description: Adds student validation by InAcademia
 * Version: v1.0
 * Author: Martin van Es
 * Author URI: https://inacademia.org/
 * Text Domain: wc-inacademia-main
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

/*
 * TODO
 *
 *
 */

defined( 'ABSPATH' ) || exit;

@session_start();

// Define INACADEMIA_VERSION.
$plugin_data = get_file_data( __FILE__, array( 'version' => 'version' ) );
define( 'INACADEMIA_VERSION', $plugin_data['version'] );

if (isset($_SESSION['inacademia_validated'])) {
  $validated = $_SESSION['inacademia_validated'];
} else {
  $validated = False;
}
$options = get_option( 'inacademia_options',
                       array(
                         'coupon_name' => 'foobar',
                         'op_url' => 'https://op.inacademia.local/',
                         'client_id' => 'client_id',
                         'client_secret' => 'client_secret',
                         'notification' => 'off',
                         'button' => 'off',
                       )
           );
if (!is_array($options)) $options = array();

$inacademia_coupon = @$options['coupon_name'];
$notification = @$options['notification'] ?? 'off';
$button = @$options['button'] ?? 'off';
$button_allowed = false;

require 'wc-inacademia-admin.php';
require 'wc-inacademia-blocks.php';

add_action('wp_enqueue_scripts','register_my_scripts');
add_action( 'woocommerce_check_cart_items' , 'inacademia_handle_validation' );
add_action( 'woocommerce_applied_coupon', 'inacademia_applied_coupon');
add_action( 'woocommerce_removed_coupon', 'inacademia_removed_coupon');
add_action( 'wp_loaded', 'inacademia_wp_loaded');

add_filter( 'woocommerce_cart_totals_coupon_label', 'inacademia_change_coupon_label', 10, 2);

if ( $button == 'on' ) {
  add_action('woocommerce_proceed_to_checkout', 'inacademia_button', 20);
}

// $_SESSION['inacademia_op_url'] = @$options['op_url'];
// $_SESSION['inacademia_scope'] = @$options['scope'];
$_SESSION['inacademia_client_id'] = @$options['client_id'];
$_SESSION['inacademia_client_secret'] = @$options['client_secret'];

function is_api() {
  return defined('REST_REQUEST') ? REST_REQUEST : false;
}

function register_my_scripts(){
  wp_enqueue_style( 'inacademia', plugins_url( 'assets/inacademia.css' , __FILE__ ) );
}

function inacademia_get_validation_url() {
  return plugins_url( 'start.php', __FILE__ );
}

function inacademia_wp_loaded() {
  if (!WC()->cart or iS_api()) return;

  global $inacademia_coupon, $button_allowed;

  $coupon = new \WC_Coupon( $inacademia_coupon );
  $coupon_id = $coupon->get_id();

  $coupon_product_ids = $coupon->get_product_ids();
  $coupon_excluded_product_ids = $coupon->get_excluded_product_ids();
  // $coupon_product_categories = $coupon->get_product_categories();
  // $coupon_excluded_product_categories = $coupon->get_excluded_product_categories();

  // error_log("Coupon id: " . $coupon_id);
  // error_log("coupon_product_ids: " . print_r($coupon_product_ids, true));
  // error_log("excluded_product_ids: " . print_r($coupon_excluded_product_ids, true));
  // error_log("coupon_product_categories: " . print_r($coupon_product_categories, true));
  // error_log("$coupon_excluded_product_categories: " . print_r($coupon_excluded_product_categories, true));

  $items = WC()->cart->get_cart();

  // Collect all product_ids in cart
  $cart_product_ids = [];
  // $cart_category_ids = [];
  foreach ( $items as $item => $values ) {
    $cart_product_ids[] = $values['data']->get_id();
    // $cart_category_ids = array_merge($cart_category_ids, $values['data']->get_category_ids());
  }
  // error_log("cart_product_ids: " . print_r($cart_product_ids, true));
  // $cart_category_ids = array_unique($cart_category_ids);
  // error_log("cart_category_ids: " . print_r($cart_category_ids, true));

  // We first check required products are present
  if ( sizeof($coupon_product_ids) ) {
    foreach ( $coupon_product_ids as $coupon_product_id ) {
      if ( in_array($coupon_product_id, $cart_product_ids ) ) {
        $button_allowed = true;
        break;
      }
    }
  // We check if products are excluded
  } else {
    foreach ( $cart_product_ids as $cart_product_id ) {
      if (! in_array($cart_product_id, $coupon_excluded_product_ids ) ) {
        $button_allowed = true;
        break;
      }
    }
  }
}

function inacademia_button() {
  // See woocommerce/templates/cart/proceed-to-checkout-button.php for original element
  global $validated, $button_allowed;

  ?>

<?php
  if ( $button_allowed ) {
    if ( !$validated ) {
    ?>
      <a class='inacademia validate' href='<?php echo esc_url( inacademia_get_validation_url() ); ?>' target=_blank><img class='inacademia' src='<?php echo esc_url(plugins_url('assets/mortarboard.svg', __FILE__ ));?>'>&nbsp;<span>I'm a Student</span></a><br>
      <i>Login at your university to apply a student discount</i><br>
    <?php
    } else {
    ?>
      <a class='inacademia validated' href='#' onclick='return false;'><img class='inacademia' src='<?php echo esc_url(plugins_url('assets/mortarboard_white.svg', __FILE__ ));?>'>&nbsp;<span class=''>I'm a student</span></a><br>
    <?php
    }
    ?>
    <br>
    <?php
  }
}

function inacademia_handle_validation() {
    global $validated, $inacademia_coupon, $notification, $button_allowed;

    if (!$button_allowed or is_api()) return;

    if (isset($_SESSION['inacademia_error'])) {
      wc_print_notice( $_SESSION['inacademia_error'], 'error' );
      unset($_SESSION['inacademia_error']);
    }

    // $validated = @$_SESSION['inacademia_validated'] || array();
    $applied = WC()->cart->has_discount($inacademia_coupon);

    if ($validated) {
      if (!$applied) {
          WC()->cart->apply_coupon( $inacademia_coupon );
          wc_clear_notices();
          wc_print_notice( "Student discount applied!", 'notice' );
      }
    } else {
      if ( $notification == 'on' ) {
        wc_print_notice( "Are you a university student? <a href='".inacademia_get_validation_url()."' target=_blank>Login</a> at your university to apply a student discount.", 'notice' );
      }
      if ($applied) {
        WC()->cart->remove_coupon( $inacademia_coupon );
        wc_clear_notices();
      }
    }
}

// Hide 'Coupon: CODE' in cart totals and instead return generic 'Student discount'
// This does not hide the coupon code from the generated cart HTML
function inacademia_change_coupon_label($label, $coupon) {
  global $inacademia_coupon;
  if ($coupon->get_code() == $inacademia_coupon) {
    echo "Student discount";
  } else {
    echo esc_html( $label );
  }
}

function inacademia_applied_coupon($coupon) {
  global $validated, $inacademia_coupon;
  if ($coupon == $inacademia_coupon && !$validated) {
    // Do not allow inacademia coupon to be claimed without inacademia session (validated)
    WC()->cart->remove_coupon( $inacademia_coupon );
    wc_clear_notices();
  }
}

function inacademia_removed_coupon($coupon) {
  global $validated, $inacademia_coupon;
  if ($coupon == $inacademia_coupon && $validated) {
    // Clear the inacademia session (validated)
    $_SESSION['inacademia_validated'] = False;
  }
}

