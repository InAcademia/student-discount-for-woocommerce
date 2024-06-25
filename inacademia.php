<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

session_start();

/**
 * Autoload OpenOIConnectClient
 *
 * @package InAcademia
 */
require 'vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

/**
 * Redirect URL.
 */
function redirect_url() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? filter_input( INPUT_SERVER, 'HTTP_HOST' ) : '';
	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? filter_input( INPUT_SERVER, 'SCRIPT_NAME' ) : '/start.php';
	$url = 'http';
	$url .= isset( $_SERVER['HTTPS'] ) ? 's' : '';
	$url .= '://' . $host . str_replace( 'start.php', 'redirect.php', $script );
	return $url;
}

/**
 * Authenticate
 */
function inacademia_authenticate() {
	/*
	 * Bikeshed
	// $op_url = $_SESSION['inacademia_op_url']; // https://op.inacademia.local/
	// $scope = $_SESSION['inacademia_scope']; // student
	*/
	$op_url = 'https://plugin.srv.inacademia.org/';
	$scope = 'student'; // scope is now fixed.
	$client_id = isset( $_SESSION['inacademia_client_id'] ) ? filter_var( $_SESSION['inacademia_client_id'], FILTER_SANITIZE_STRING ) : '';
	$client_secret = isset( $_SESSION['inacademia_client_secret'] ) ? filter_var( $_SESSION['inacademia_client_secret'], FILTER_SANITIZE_STRING ) : '';

	$oidc = new OpenIDConnectClient( $op_url, $client_id, $client_secret );

	// For debug purposes on local dev.
	$oidc->setVerifyHost( false );
	$oidc->setVerifyPeer( false );
	$oidc->setHttpUpgradeInsecureRequests( false );

	$oidc->addScope( explode( ' ', 'transient ' . $scope ) );

	/*
	 * Bikeshed
	// $oidc->addAuthParam(array('aarc_idp_hint' => $aarc_idp_hint));
	// $oidc->addAuthParam(array('claims' => 'student'));
	// $oidc->addAuthParam(array('response_mode' => 'form_post'));
	*/
	$oidc->setResponseTypes( array( 'code' ) );

	/*
	 * Bikeshed
	// $oidc->setAllowImplicitFlow(true);
	*/
	$oidc->setRedirectURL( redirect_url() );

	$claims = isset( $_SESSION['inacademia_claims'] ) ? filter_var( $_SESSION['inacademia_claims'], FILTER_SANITIZE_STRING ) : null;
	$validated = false;

	try {
		if ( ! $claims ) {
			$oidc->authenticate();
			$claims = $oidc->getVerifiedClaims();
			if ( in_array( $scope, $claims->returned_scopes->values ) ) {
				$validated = true;
			}
		}
	} catch ( Exception $e ) {
		$_SESSION['inacademia_error'] = $e->getMessage();
		error_log( json_encode( $e->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	$_SESSION['inacademia_validated'] = $validated;
}
