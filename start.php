<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

/**
 * InAcademia
 *
 * @package InAcademia
 */
require 'inacademia.php';
if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
	$_SESSION['inacademia_referrer'] = filter_input( INPUT_SERVER, 'HTTP_REFERER' );
}

/*
 * Bikeshed
$_SESSION['inacademia_validated'] = True;
 */
inacademia_authenticate();

if ( isset( $_SESSION['inacademia_referrer'] ) ) {
	header( 'Location: ' . filter_var( $_SESSION['inacademia_referrer'], FILTER_SANITIZE_URL ), true );
}
