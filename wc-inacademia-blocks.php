<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'woocommerce_blocks_loaded',
	function () {
		require_once __DIR__ . '/class-inacademia-blocks-integration.php';
		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function ( $integration_registry ) {
				$integration_registry->register( new InAcademia_Blocks_Integration() );
			}
		);
	}
);
