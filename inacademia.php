<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Autoload OpenOIConnectClient
 *
 * @package InAcademia
 */
require 'vendor/autoload.php';
use Jumbojett\OpenIDConnectClient;

/**
 * Start URL.
 */
function inacademia_create_start_url() {
	$rest_url = get_rest_url();
	$url = $rest_url . INACADEMIA_SLUG . '/start';
	return $url;
}

/**
 * Redirect URL.
 */
function inacademia_create_redirect_url() {
	$rest_url = get_rest_url();
	$url = $rest_url . INACADEMIA_SLUG . '/redirect';
	return $url;
}

/**
 * Dummy validation routine
 */
function inacademia_validate_dummy() {
	session_start( array( 'name' => 'inacademia' ) );
	$validate = true;
	$_SESSION['inacademia_validated'] = $validate;
	if ( ! $validate ) {
		$_SESSION['inacademia_error'] = 'Error';
	}
	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		$http_referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL );
	} else {
		$http_referer = '/';
	}
	header( 'Location: ' . $http_referer, true );
}

/**
 * Validation routing
 */
function inacademia_validate() {
	session_start( array( 'name' => 'inacademia' ) );

	if ( ! isset( $_SESSION['inacademia_referrer'] ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
		$_SESSION['inacademia_referrer'] = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL );
	}

	/*
	 * Bikeshed
	// $op_url = $_SESSION['inacademia_op_url']; // https://op.inacademia.local/
	// $scope = $_SESSION['inacademia_scope']; // student
	*/
	$op_url = INACADEMIA_OP_URL;
	$scope = 'student'; // scope is now fixed.
	$client_id = isset( $_SESSION['inacademia_client_id'] ) ? filter_var( $_SESSION['inacademia_client_id'], FILTER_SANITIZE_STRING ) : '';
	$client_secret = isset( $_SESSION['inacademia_client_secret'] ) ? filter_var( $_SESSION['inacademia_client_secret'], FILTER_SANITIZE_STRING ) : '';

	$oidc = new OpenIDConnectClient( $op_url, $client_id, $client_secret );

	/*
	 * Bikeshed
	 * For debug purposes on local dev.
	// $oidc->setVerifyHost( false );
	// $oidc->setVerifyPeer( false );
	// $oidc->setHttpUpgradeInsecureRequests( false );
	*/

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
	$oidc->setRedirectURL( inacademia_create_redirect_url() );

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
		$_SESSION['inacademia_error'] = tr( $e->getMessage() );
		error_log( json_encode( $e->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	$_SESSION['inacademia_validated'] = $validated;

	if ( isset( $_SESSION['inacademia_referrer'] ) ) {
		$location = filter_var( $_SESSION['inacademia_referrer'], FILTER_SANITIZE_URL );
		unset( $_SESSION['inacademia_referrer'] );
		header( 'Location: ' . $location, true );
		exit();
	}
}

/**
 * Translate known error messages
 *
 * @param string $s string.
 */
function tr( $s ) {
	$translations = array(
		'Error: access_denied Description: no affiliation available for this user' => 'We were unable to process your student validation because the SAML response from your institution does not confirm your student affiliation. As a result, the cart discount cannot be applied.',
		'Error: access_denied Description: affiliation does not match requested validation' => 'We were unable to process your student validation because the SAML response from your institution does not confirm your student affiliation. As a result, the cart discount cannot be applied.',
		'Error: access_denied Description: authentication failed' => 'We were unable to process your student validation because the SAML response from your institution does not confirm your student affiliation. As a result, the cart discount cannot be applied.',
	);

	return $translations[ $s ] ?? $s;
}
