<?php
/**
 * InAcademia
 *
 * @package InAcademia
 */

$button_text = 'Next';

/**
 * Register our inacademia_settings plugin link
 */
add_filter( 'plugin_action_links_student-discount-for-woocommerce/wc-inacademia.php', 'inacademia_settings_link' );

/**
 * Settings_link
 *
 * @param array $links links.
 */
function inacademia_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url(
		add_query_arg(
			'page',
			'inacademia',
			get_admin_url() . 'admin.php'
		)
	);
	// Create the link.
	$settings_link = "<a href='$url'>Settings</a>";
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
/**
 * Register our inacademia_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'inacademia_settings_init' );

/**
 * Custom option and settings
 */
function inacademia_settings_init() {
	$options = get_option( 'inacademia_options' );
	global $button_text;

	// Register a new setting for "inacademia" page.
	$args = array(
		'sanitize_callback' => 'inacademia_sanitize_options',
	);

	register_setting( 'inacademia', 'inacademia_options', $args );

	// Register a new section in the "inacademia" page.
	add_settings_section(
		'inacademia_section_settings',
		'InAcademia settings.',
		'inacademia_section_settings_callback',
		'inacademia'
	);

	// Register a new field in the "inacademia_section_settings" section, inside the "inacademia" page.
	add_settings_field(
		'coupon_name',
		'Coupon',
		'inacademia_coupon_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'coupon_name',
		)
	);
	if ( ! @$options['coupon_name'] ) {
		return;
	}

	add_settings_field(
		'redirect_uri',
		'Redirect URI',
		'inacademia_redirect_cb',
		'inacademia',
		'inacademia_section_settings',
		array()
	);

	add_settings_field(
		'redirect_uri_done',
		'Redirect URI Done',
		'inacademia_redirect_done_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'redirect_uri_done',
		)
	);
	if ( 'off' == @$options['redirect_uri_done'] ) {
		unset( $options['redirect_uri_done'] );
	}
	if ( ! @$options['redirect_uri_done'] ) {
		return;
	}

	add_settings_field(
		'client_id',
		'ClientID',
		'inacademia_clientid_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'client_id',
		)
	);

	/*
	 * Bikeshed
	// if (!@$options['client_id']) return;
	*/
	add_settings_field(
		'client_secret',
		'ClientSecret',
		'inacademia_clientsecret_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'client_secret',
		)
	);
	if ( ! @$options['client_secret'] ) {
		return;
	}

	/*
	 * Bikeshed
	add_settings_field(
		'scope',
		'User role',
		'inacademia_scope_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'scope',
		)
	);
	if (!@$options['scope']) return;

	add_settings_field(
		'op_url',
		'OP URL',
		'inacademia_opurl_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'op_url',
		)
	);
	*/

	add_settings_field(
		'notification_description',
		'',
		'inacademia_notify_description_cb',
		'inacademia',
		'inacademia_section_settings',
		array()
	);

	add_settings_field(
		'notification',
		'Publish Notice',
		'inacademia_notify_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'notification',
		)
	);

	add_settings_field(
		'button',
		'Publish Button',
		'inacademia_button_cb',
		'inacademia',
		'inacademia_section_settings',
		array(
			'label_for'         => 'button',
		)
	);

	$button_text = 'Save';
}

/**
 * Custom option and settings:
 *  - callback functions
 *
 * @param array $options options.
 */
function inacademia_sanitize_options( $options ) {
	if ( is_array( $options ) && isset( $options['coupon_name'] ) ) {
		$options['coupon_name'] = strtolower( $options['coupon_name'] );
	}
	if ( is_array( $options ) && isset( $options['client_id'] ) ) {
		$wrong_client_id = false;
		$client_id = $options['client_id'];
		if ( strlen( $client_id ) != 11 ) {
			$wrong_client_id = true;
		}
		if ( substr( $client_id, 0, 3 ) != 'wc_' ) {
			$wrong_client_id = true;
		}
		if ( $wrong_client_id ) {
			add_settings_error( 'inacademia', 'inacademia_message', 'ClientID must match the ClientID allocated by your subscription', 'error' );
			unset( $options['client_id'] );
		}
	}
	if ( is_array( $options ) && isset( $options['client_secret'] ) ) {
		$wrong_client_secret = false;
		$client_secret = $options['client_secret'];
		if ( strlen( $client_secret ) != 32 ) {
			$wrong_client_secret = true;
		}
		if ( $wrong_client_secret ) {
			add_settings_error( 'inacademia', 'inacademia_message', 'ClientID must match the Client Secret allocated by your subscription', 'error' );
			unset( $options['client_secret'] );
		}
	}
	return $options;
}

/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function inacademia_section_settings_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php echo esc_html( inacademia_settings_text() ); ?>
	<p>
	<?php
}


/**
 * Coupon field callback function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args args.
 */
function inacademia_coupon_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'Create coupon' ) );
	$coupon = $options[ $label_for ] ?? 'Create coupon';
	?>
	<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	  value="<?php echo esc_attr( $coupon ); ?>">
	<p class="description">
		<?php echo esc_html( 'Set up your discount using the Coupon feature offered by the WooCommerce Marketing feature set and enter it in the box above. If you wish to change the Coupon you will need to enter the new Coupon Code here.' ); ?>
	</p>
	<?php
}

/**
 * Redirect URI field callback function.
 *
 * @param array $args args.
 */
function inacademia_redirect_cb( $args ) {
	 echo esc_url( plugin_dir_url( __FILE__ ) . 'redirect.php' );
	?>
	<p class="description">
		<?php echo 'Please copy the redirect_uri printed above and visit <a href="https://inacademia.org/shop" target=_blank>https://inacademia.org/shop</a> to complete your subscription to the InAcademia Service. During the process of subscribing, you will be asked to \'Enter Redirect URI here\'. This is mandatory, and you must paste the redirect_uri exactly as it appears here paste the redirect_uri into the box.'; ?>
	</p>
	<?php
}

/**
 * Redirect URI Done checkbox callback function.
 *
 * @param array $args args.
 */
function inacademia_redirect_done_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'off' ) );
	$redirect_uri_done = @$options[ $label_for ] ?? 'off';
	?>
	<input type=checkbox
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			<?php echo checked( 'on', $redirect_uri_done ); ?>>
	<p class="description">
		<?php	echo 'I have copied the redirect_uri printed above and visited <a href="https://inacademia.org/shop" target=_blank>https://inacademia.org/shop</a> to complete my subscription.'; ?><br>
	</p>
	<?php
}


/**
 * ClientID field callback function.
 *
 * @param array $args args.
 */
function inacademia_clientid_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'ClientID' ) );
	$client_id = $options[ $label_for ] ?? '';
	?>
	<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	  value="<?php echo esc_attr( $client_id ); ?>">
	<?php
}

/**
 * ClientSecret field callback function.
 *
 * @param array $args args.
 */
function inacademia_clientsecret_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'ClientSecret' ) );
	$client_secret = $options[ $label_for ] ?? '';
	?>
	<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	  value="<?php echo esc_attr( $client_secret ); ?>">
	<p class="description">
		<?php echo 'Your client_id and client_secret will be automatically created during the Subscription order process.'; ?><br>
		<?php echo 'You will find them in the Subscription Summary of <a href="https://inacademia.org/my-account/subscriptions">your account</a>. Follow the link to manage the subscription inside your confirmation email.'; ?><br>
		<?php echo 'Please paste them to the boxes above (if you need to change your redirect_uri please contact us). This is mandatory to link your subscription to your plugin, and the plugin will not function correctly without these values.'; ?>
	</p>
	<?php
}

/**
 * Scope field callback function.
 *
 * @param array $args args.
 */
function inacademia_scope_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'student' ) );
	?>
	<select
			id="<?php echo esc_attr( $label_for ); ?>"
			name="inacademia_options[<?php echo esc_attr( $label_for ); ?>]">
		<option value="student" <?php echo isset( $options[ $label_for ] ) ? ( selected( $options[ $label_for ], 'student', false ) ) : ( '' ); ?>>
			<?php echo esc_html( 'Student' ); ?>
		</option>
		 <option value="member" <?php echo isset( $options[ $label_for ] ) ? ( selected( $options[ $label_for ], 'member', false ) ) : ( '' ); ?>>
			<?php echo esc_html( 'Member' ); ?>
		</option>
	</select>
	<p class="description">
		<?php echo esc_html( 'Please select the required role for validation (cannot be both).' ); ?>
	</p>
	<?php
}

/**
 * OP URL field callback function.
 *
 * @param array $args args.
 */
function inacademia_opurl_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$options = get_option( 'inacademia_options', array( $args['label_for'] => 'https://op.inacademia.local/' ) );
	?>
	<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<option value="https://op.inacademia.local/" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'https://op.inacademia.local/', false ) ) : ( '' ); ?>>
			<?php echo esc_html( 'DEV' ); ?>
		</option>
		 <option value="https://op.srv-test.inacademia.org/" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'https://op.srv-test.inacademia.org/', false ) ) : ( '' ); ?>>
			<?php echo esc_html( 'CIP' ); ?>
		</option>
		 <option value="https://op.srv.inacademia.org/" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'https://op.srv.inacademia.org/', false ) ) : ( '' ); ?>>
			<?php echo esc_html( 'PRD' ); ?>
		</option>
	</select>
	<?php echo esc_html( @$options[ $args['label_for'] ] ); ?>
	<p class="description">
		<?php echo esc_html( 'What environment to use.' ); ?>
	</p>
	<?php
}

/**
 * Notification description field callback function.
 *
 * @param array $args args.
 */
function inacademia_notify_description_cb( $args ) {
	?>
	<p class="description">
		<?php echo 'Please select how you would like to invite users to validate their academic affiliation. This can be achieved either by using a URL inside a <a href="https://woocommerce.com/document/woocommerce-cart-notices/" target=_blank>Notice</a> or by hitting the \'I\'m a Student\' button'; ?><br>
	</p>
	<?php
}


/**
 * Notification field callback function.
 *
 * @param array $args args.
 */
function inacademia_notify_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'on' ) );
	$notifcation = @$options[ $label_for ] ?? 'off';

	/*
	<p class="description">
		<?php echo 'Please select how you would like to invite users to validate their academic affiliation. This can be achieved either by using a URL inside a <a href="https://woocommerce.com/document/woocommerce-cart-notices/" target=_blank>Notice</a> or by hitting the \'I\'m a Student\' button'; ?><br>
	</p>
	*/
	?>
	<input type=checkbox
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="inacademia_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			<?php echo checked( 'on', $notifcation ); ?>>
	<?php
}

/**
 * Button field callback function.
 *
 * @param array $args args.
 */
function inacademia_button_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$label_for = $args['label_for'];
	$options = get_option( 'inacademia_options', array( $label_for => 'on' ) );
	$button = @$options[ $label_for ] ?? 'off';
	?>
	<input type=checkbox
			id="<?php echo esc_attr( $label_for ); ?>"
			name="inacademia_options[<?php echo esc_attr( $label_for ); ?>]"
			<?php echo checked( 'on', $button ); ?>>
	<?php
}

/**
 * Register our inacademia_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'inacademia_options_page' );

/**
 * Add the top level menu page.
 */
function inacademia_options_page() {
	/*
	 * Bikeshed
	// add_menu_page(
	*/
	add_submenu_page(
		// 'options.php',
		'options-general.php',
		'Student Discount',
		'Student Discount',
		'manage_options',
		'inacademia',
		'inacademia_options_page_html'
	);

	/*
	 * Bikeshed
	// remove_menu_page('inacademia');
	*/
}

/**
 * Top level menu callback function
 */
function inacademia_options_page_html() {
	$options = get_option( 'inacademia_options' );
	global $button_text;

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Get the active tab from the $_GET param.
	$default_tab = null;
	$tab = isset( $_GET['tab'] ) ? filter_var( wp_unslash( $_GET['tab'] ), FILTER_SANITIZE_STRING ) : $default_tab;

	// Show error/update messages.
	settings_errors( 'inacademia_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<nav class="nav-tab-wrapper">
	  <a href="?page=inacademia" class="nav-tab
	  <?php
		if ( null === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Important Information</a>
	  <a href="?page=inacademia&tab=settings" class="nav-tab
	  <?php
		if ( 'settings' === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Settings</a>
	  <a href="?page=inacademia&tab=support" class="nav-tab
	  <?php
		if ( 'support' === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Support</a>
	</nav>

	<div class="tab-content">
	<?php
	switch ( $tab ) :
		case 'settings':
			?>
				<form action="options.php" method="post">
					<?php
					// print_r($options);.
					// Output security fields for the registered setting "inacademia".
					settings_fields( 'inacademia' );
					// Output setting sections and their fields.
					// (sections are registered for "inacademia", each field is registered to a specific section).
					do_settings_sections( 'inacademia' );
					// Output save settings button when client_secret has been set.
					if ( @$options['client_secret'] ) {
						echo esc_html( inacademia_submit_text() );
					}
					submit_button( $button_text );
					?>
				</form>
				<?php
			break;
		case 'support':
			echo esc_html( inacademia_support_text() );
			break;
		default:
			echo esc_html( inacademia_welcome_text() );
			break;
			endswitch;
	?>
		</div>
	</div>
	<?php
}

/**
 * Welcome text
 */
function inacademia_welcome_text() {

	?>
	<h2>Important Information</h2>
	<p>InAcademia is a service that's designed to help businesses validate the elgibility of users that claim discounts, offers or services that are only availble to students.</p>
  <p>It uses an interfederation called eduGAIN, which will connect to the user's home institution identity management services using our 'I'm a Student' button (or notice) to confirm whether the user holds the correct role attribute before providing a positive or negative outcome, or validation.</p>
  <p>This page describes the steps that must be followed when completing the settings for this plugin.</p>

  <h2>Step one: configure the discount to be applied</h2>
  <p>Set up your discount using the Coupon feature offered by the <a href="https://woocommerce.com/document/coupon-management/" target=_blank>WooCommerce Marketing feature</a> set and enter it in the box labelled 'Coupon'. If you wish to change the Coupon you will need to overwrite the data with the new Coupon Code in the field labelled 'Coupon'.</p>

  <h2>Step two: set up your subscription and make it unique to the plugin in your shop</h2>
  <p>You will need to visit <a href="https://inacademia.org/shop" target=_blank>https://inacademia.org/shop</a> to complete your subscription to the InAcademia Service in order to receive a unique client_id and client secret, and it is necessary to link your subscription with the plugin in two stages before the 'I'm a Student' button will be available for users to interact with.</p>
  <p>When you installed the plugin, a unique redirect_uri was created on the Setting tab. This value must be entered when promted, when processing your subscription order.</p>

  <h2>Step three: link your subscription to the plugin</h2>
	<p>Your client_id and client_secret will be automatically created during the Subscription order process. You will find them in the Subscription Summary of <a href="https://inacademia.org/my-account/subscriptions">your account</a>; they are both vital terms that are required for the proper-functioning of the service and will be transmitted to the InAcademia service with each user's validation request. You must paste them to the correct boxes in the Settings tab.</p>

  <h2>Step four: activate your service</h2>
  <p>When you have created your discount coupon, linked your redirect_uri to your subscription, and linked the client_id and client_secret to the plugin, you will need to decide how you would like to invite users to validate their academic affiliation, either by using a Notice URL or by hitting the 'I'm a Student' button.</p>
  <p>It's allowable to use either or both, but please be aware that if you check either box, either the 'I'm a Student' button or 'I'm a Student' notice will be enabled on your shopping cart.</p>
  <h2>Ensure that your subscription is complete and active before hitting 'Save Settings'.</h2>
	<?php
}

/**
 * Settings text
 */
function inacademia_settings_text() {

	?>
	<p>Inputting the required data on this page will finalise the configuration of your shop's access to the InAcademia service and will deploy either a button or notice to your checkout, so please follow each step carefully before proceeding to 'Save Settings'. Merchants are strongly advised to test their settings in a WordPress development environment prior to deploying to production.</p>
	<p></p>
	<p><b>WARNING:</b> when you connect this plugin to a live checkout flow, users will be able to interact with the 'I'm a Student' button or Notice, but it will not function without a valid subscription, so please ensure that your subscription details are kept up to date, and if you intend to stop using the extension, please ensure you remove it before the subscription ends (in accordance with the Terms).</p>
	<p>You may not use this plugin without an active subscription, and doing so would breach copyright.</p>
	<p>Detailed instructions accompanied by screenshots can be found at <a href="https://inacademia.org/student-discount-for-woo-commerce-instructions/" target=_blank>https://inacademia.org/student-discount-for-woo-commerce-instructions/</a></p>
	<?php
}

/**
 * Support text
 */
function inacademia_support_text() {

	?>
	<h2>Request Support</h2>
	<p>You can access information and FAQs about the service here: <a href="https://inacademia.org/faqs/" target=_blank>https://inacademia.org/faqs/</a></p>
	<p>If you like InAcademia and would like to use it in other contexts, you can read more about the additional capabilities
	and features it offers here: <a href="https://inacademia.org/inacademia-for-merchants/" target=_blank>https://inacademia.org/inacademia-for-merchants/</a></p>
	<p>Need help? Please <a href="https://inacademia.org/plugin-support" target=_blank>send us a message</a> quoting your client_id so we can help you.</p>
	<?php
}

/**
 * Submit text
 */
function inacademia_submit_text() {

	?>
  <p>It's allowable to use either or both.<p>
  <p>Please be aware that checking either box or both boxes will be enable the feature on your shopping cart immediately when you 'Save Settings'.<p>
	<h2>WARNING:<h2>
  <p><b>If you 'Save Settings' with either of the two 'Publish' options checked will deploy the extension to your checkout,
	so please ensure that you're ready to publish the new feature do so before saving the settings.</b></p>
	<p><b>Ensure that your subscription is complete and active before hitting 'Save Settings'.</b></p>
	<p>Your connection will be enabled within 15 minutes of your subscription being completed.</p>
	<?php
}
