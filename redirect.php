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
inacademia_authenticate();

if ( isset( $_SESSION['inacademia_referrer'] ) ) {
	header( 'Location: ' . filter_var( $_SESSION['inacademia_referrer'], FILTER_SANITIZE_URL ), true );
}
