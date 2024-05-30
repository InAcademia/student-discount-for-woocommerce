<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
class InAcademia_Blocks_Integration implements IntegrationInterface {
	private $url = "#";
	private $coupon = "";
	private $button = "off";
	private $img_validate = "";
	private $img_validated = "";

	public function get_name() {
		return 'inacademia';
	}

	public function get_script_handles() {
		return [ 'inacademia-blocks' ];
	}

	public function get_editor_script_handles() {
		return [  ];
	}

	public function get_script_data() {
		$data = [
			'url' => $this->url,
			'img_validate' => $this->img_validate,
			'img_validated' => $this->img_validated,
			'coupon' => $this->coupon,
			'button' => $this->button,
			'coupon_product_ids' => $this->coupon_product_ids,
			'excluded_product_ids' => $this->excluded_product_ids,
		];

		return $data;
	}

	private function excluded_ids($c) {
		if (!WC()->cart or iS_api()) return false;
		$coupon = new \WC_Coupon( $c );
		$coupon_id = $coupon->get_id();
		return $coupon->get_excluded_product_ids();
	}

	private function coupon_ids($c) {
		if (!WC()->cart or iS_api()) return false;
		$coupon = new \WC_Coupon( $c );
		$coupon_id = $coupon->get_id();
		return $coupon->get_product_ids();
	}

	public function initialize() {
		$options = get_option( 'inacademia_options', array());
		$this->coupon = @$options['coupon_name'] ?? "";
		$this->button = @$options['button'] ?? 'off';
		$this->url = esc_url( inacademia_get_validation_url() );
		$this->img_validate = plugins_url('assets/mortarboard.svg', __FILE__ );
		$this->img_validated = plugins_url('assets/mortarboard_white.svg', __FILE__ );
		$this->coupon_product_ids = $this->coupon_ids( $this->coupon );
		$this->excluded_product_ids = $this->excluded_ids( $this->coupon );

		$this->register_inacademia_scripts();
	}

	public function register_inacademia_scripts() {
		$script_path       = '/build/index.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => INACADEMIA_VERSION,
			];

		wp_enqueue_script(
			'inacademia',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_add_inline_script('inacademia', "inacademia_data=" . wp_json_encode($this->get_script_data()) . ";\n", 'before');

	}
}
