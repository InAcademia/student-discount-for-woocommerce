<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
class InAcademia_Blocks_Integration implements IntegrationInterface {

	/**
	 * Url
	 *
	 * @var string url
	 */
	private $url = '#';

	/**
	 * Coupon
	 *
	 * @var string coupon
	 */
	private $coupon = '';

	/**
	 * Button
	 *
	 * @var string button
	 */
	private $button = 'off';

	/**
	 * Img_validate
	 *
	 * @var string validate
	 */
	private $img_validate = '';

	/**
	 * Img_validated
	 *
	 * @var string validated
	 */
	private $img_validated = '';

	/**
	 * Get name
	 */
	public function get_name() {
		return 'inacademia';
	}

	/**
	 * Get script handles
	 */
	public function get_script_handles() {
		return array( 'inacademia-blocks' );
	}

	/**
	 * Get editor script handles
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * Get script data
	 */
	public function get_script_data() {
		$data = array(
			'url' => $this->url,
			'img_validate' => $this->img_validate,
			'img_validated' => $this->img_validated,
			'coupon' => $this->coupon,
			'button' => $this->button,
			'coupon_product_ids' => $this->coupon_product_ids,
			'excluded_product_ids' => $this->excluded_product_ids,
		);

		return $data;
	}

	/**
	 * Excluded ids
	 *
	 * @param int $c coupon id.
	 */
	private function excluded_ids( $c ) {
		if ( ! WC()->cart || inacademia_is_api() ) {
			return false;
		}
		$coupon = new \WC_Coupon( $c );
		$coupon_id = $coupon->get_id();
		return $coupon->get_excluded_product_ids();
	}

	/**
	 * Coupon ids
	 *
	 * @param int $c coupon id.
	 */
	private function coupon_ids( $c ) {
		if ( ! WC()->cart || inacademia_is_api() ) {
			return false;
		}
		$coupon = new \WC_Coupon( $c );
		$coupon_id = $coupon->get_id();
		return $coupon->get_product_ids();
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		$options = get_option( 'inacademia_options', array() );
		$this->coupon = @$options['coupon_name'] ?? '';
		$this->button = @$options['button'] ?? 'off';
		$this->url = esc_url( inacademia_create_start_url() );
		$this->img_validate = plugins_url( 'assets/mortarboard.svg', __FILE__ );
		$this->img_validated = plugins_url( 'assets/mortarboard_white.svg', __FILE__ );
		$this->coupon_product_ids = $this->coupon_ids( $this->coupon );
		$this->excluded_product_ids = $this->excluded_ids( $this->coupon );

		$this->register_inacademia_scripts();
	}

	/**
	 * Register scripts
	 */
	public function register_inacademia_scripts() {
		$script_path       = '/build/index.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = __DIR__ . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => INACADEMIA_VERSION,
			);

		wp_enqueue_script(
			'inacademia',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_add_inline_script( 'inacademia', 'inacademia_data=' . wp_json_encode( $this->get_script_data() ) . ";\n", 'before' );
	}
}
