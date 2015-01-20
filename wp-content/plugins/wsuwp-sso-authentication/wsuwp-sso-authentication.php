<?php
/*
Plugin Name: WSUWP Single Sign On Authentication
Version: 2.0.0
Plugin URI: http://web.wsu.edu
Description: Manages authentication for Washington State University WordPress installations.
Author: washingtonstateuniversity, jeremyfelt, Nate Owen
Author URI: http://web.wsu.edu
*/

class WSUWP_SSO_Authentication {

	/**
	 * @var string Version to use for cache breaking scripts and stylesheets.
	 */
	var $script_version = '2.0.0';

	/**
	 * @var bool Whether a recent update is occurring.
	 */
	var $recent_update = false;

	/**
	 * Track an open ldap link identifier.
	 *
	 * @var resource|bool
	 */
	var $ldap_link_id = false;

	/**
	 * @var string Temporary maintainer of an entered username.
	 */
	private $attempted_user = '';

	/**
	 * @var string Contains the last error for a failed AD attempt.
	 */
	private $last_error = '';

	/**
	 * Add hooks required for this plugin.
	 */
	public function __construct() {
		// Basic login and logout actions.
		add_action( 'login_init',        array( $this, 'login'  ), 11 );

		// Enqueue Javascript and custom stylesheets
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Capture and enforce user additions and changes.
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
		add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_errors' ),  10, 3 );
		add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_role'   ), 999, 3 );

		// Modify the header logo, URL, and link title displayed on wp-login.php
		add_action( 'login_head',        array( $this, 'login_head_css'     ), 10    );
		add_filter( 'login_headerurl',   array( $this, 'login_header_url'   ), 10, 1 );
		add_filter( 'login_headertitle', array( $this, 'login_header_title' ), 10, 1 );

		// Add messaging to direct toward Network ID authentication.
		add_action( 'login_form',     array( $this, 'login_message'      ), 10, 1 );

		add_filter( 'wp_login_errors', array( $this, 'login_errors' ), 10, 1 );

		// Selectively show password fields for users.
		add_filter( 'show_password_fields', array( $this, 'show_password_fields' ), 10, 2 );

		// On non-multisite, we need to do some magic.
		add_action( 'admin_init', array( $this, 'adjust_passwords' ), 10 );

		add_action( 'personal_options', array( $this, 'personal_options' ) );

		// By default, site admins cannot edit network users in a multisite configuration. Returning
		// true on this filter allows this to happen. We continue to restrict this editing with our
		// map_meta_cap filter to only users that are not WSU specific.
		add_filter( 'enable_edit_any_user_configuration', '__return_true', 11 );
		add_filter( 'map_meta_cap', array( $this, 'site_admin_edit_user' ), 10, 4 );
	}

	/**
	 * Enqueue the Javascript required for custom auth maintenance in the admin area.
	 *
	 * This script is loaded in wp-admin/profile.php, wp-admin/user-edit.php, and wp-admin/user-new.php
	 */
	public function admin_enqueue_scripts() {
		if ( in_array( get_current_screen()->base, array( 'user', 'user-edit', 'profile' ) ) ) {
			wp_enqueue_script( 'wsuwp-auth.js', plugins_url( 'js/wsuwp-auth.js', __FILE__ ), array( 'jquery' ), $this->script_version, true );
		}
	}

	/**
	 * Store the type of authentication assigned to a user.
	 *
	 * @param int $user_id ID of the user being edited.
	 */
	public function edit_user_profile_update( $user_id ) {
		if ( isset( $_POST['wsuwp_user_type'] ) && 'standard' === $_POST['wsuwp_user_type'] ) {
			$current_user_type = $this->get_user_type( $user_id );
			update_user_meta( $user_id, '_wsuwp_sso_user_type', $_POST['wsuwp_user_type'] );
			if ( $current_user_type != $_POST['wsuwp_user_type'] ) {
				$this->recent_update = true;
			}
		} else {
			update_user_meta( $user_id, '_wsuwp_sso_user_type', 'nid' );
		}
	}

	/**
	 * Enforce strong passwords when a user profile is edited.
	 *
	 * @param WP_Error $errors Errors generated during the profile update process.
	 * @param bool     $update Not used. Indicates if this is an update procedure.
	 * @param Object   $user   Current user data being processed.
	 */
	public function user_profile_update_errors( $errors, $update, $user ) {
		if ( $this->recent_update || false === $this->show_password_fields( true, $user ) ) {
			return;
		}

		if ( '' === $_POST['pass1'] && '' === $_POST['pass2'] ) {
			return;
		}

		if ( ! isset( $_POST['wsuwp_pass_strength'] ) || 'Strong' !== $_POST['wsuwp_pass_strength'] ) {
			$errors->add( 'pass', '<strong>Error</strong>: Passwords not rated <strong>strong</strong> may not be used.' );
		}
	}

	/**
	 * Detect role changes and enforce a random password if a secure role is being set. By default,
	 * this applies to the administrator role. A filter is available to enforce multiple secure roles.
	 *
	 * @param WP_Error $errors Any current errors that have been compiled.
	 * @param bool     $update Not used. Indicates if this is an update procedure.
	 * @param Object   $user   Current user data being built for update/add.
	 */
	public function user_profile_update_role( $errors, $update, $user ) {
		// If errors have already been generated, we shouldn't do anything.
		if ( ! empty( $errors->errors ) ) {
			return;
		}

		$ad_auth_roles = apply_filters( 'wsuwp_sso_ad_auth_roles', array( 'administrator' ) );

		// Be forceful about the administrator requirement.
		if ( ! is_array( $ad_auth_roles ) ) {
			$ad_auth_roles = array( 'administrator' );
		} elseif ( ! in_array( 'administrator', $ad_auth_roles ) ) {
			$ad_auth_roles[] = 'administrator';
		}

		// If a user's profile is being updated an their role has been marked as secure, reset the password as it passes through.
		if ( isset( $_POST['role'] ) && isset( $user->role ) && in_array( 'administrator', $ad_auth_roles ) && in_array( $user->role, $ad_auth_roles ) ) {
			$user->user_pass = $this->generate_password();
			unset( $_POST['send_password'] ); // Don't send any password to the user.
		}
	}

	/**
	 * Generate a random junk password that will never be used based on a series of allowed characters.
	 *
	 * The generated password will be 32 characters long and contain no less than 24 unique characters.
	 *
	 * @return string Generated password.
	 */
	private function generate_password() {
		$junk_password_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 !"?$%^&).';
		$pass_count = 0;
		$junk_password = '';

		while ( $pass_count < 24 ) {
			$junk_password = '';
			for ( $i = 0; $i < 32; $i++ ) {
				$junk_password .= $junk_password_chars[ rand( 0, strlen( $junk_password_chars ) - 1 ) ];
			}
			$pass_count = strlen( count_chars( $junk_password, 3 ) );
		}

		return $junk_password;
	}

	/**
	 * Provide a link to authenticate through WSU SSO Authentication.
	 */
	public function login_message() {
		echo '<p class="login-message">Please enter your WSU Network ID and password.</p>';
	}

	/**
	 * Add inline CSS to alter the display of the login header image.
	 */
	public function login_head_css() {
		?><style>
		.login h1 a {
				background-image: url('<?php echo esc_url( plugins_url( 'images/wsu-shield-140x140.png', __FILE__ ) ); ?>');
		}
		.login h1 a {
			background-size: 140px 140px;
			width: 140px;
			height: 140px;
			border-radius: 6px;
		}
		.login .login-message {
			color: #777;
			font-size: 14px;
			padding-bottom: 16px;
		}
		#nav {
			display: none;
		}
		</style><?php
	}

	/**
	 * Replace the default URL of wordpress.org with a WSU specific URL.
	 *
	 * @return string URL linked to in the logo.
	 */
	public function login_header_url() {
		return 'http://wsu.edu';
	}

	/**
	 * Replace the default wordpress.org text with Washington State University.
	 *
	 * @return string Text added to the link title.
	 */
	public function login_header_title() {
		return 'Washington State University';
	}

	/**
	 * Establish an initial ldap_connect and anonymous ldap_bind with the AD server.
	 *
	 * @return bool True if successful. False if not.
	 */
	private function _connect() {
		$this->ldap_link_id = ldap_connect( 'ldap://directory.ad.wsu.edu/', 389 );

		if ( false === $this->ldap_link_id ) {
			$this->last_error = 'Initial connection refused.';
			return false;
		}

		$ldap_set_protocol = ldap_set_option( $this->ldap_link_id, LDAP_OPT_PROTOCOL_VERSION, 3 );

		if ( false === $ldap_set_protocol ) {
			$this->last_error = ldap_error( $this->ldap_link_id );
			return false;
		}

		$ldap_set_referrals = ldap_set_option( $this->ldap_link_id, LDAP_OPT_REFERRALS, 0 );

		if ( false === $ldap_set_referrals ) {
			$this->last_error = ldap_error( $this->ldap_link_id );
			return false;
		}

		$ldap_bound = @ldap_bind( $this->ldap_link_id, null, null );

		if ( false === $ldap_bound ) {
			$this->last_error = ldap_error( $this->ldap_link_id );
			return false;
		}

		return true;
	}

	/**
	 * Perform login operations when wp-login.php is loaded.
	 */
	public function login() {
		$ldap = true;

		// Look for actual login attempts and process accordingly.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {
			if ( ! isset( $_POST['wp-submit'] ) || ! isset( $_POST['redirect_to'] ) ) {
				die(); // Intentional silence.
			}

			// Reject any authentication attempts that are made without a username.
			if ( empty( trim( $_POST['log'] ) ) ) {
				$this->last_error = 'A username is required to authenticate.';
				unset( $_POST['log'] );
				unset( $_POST['pwd'] );
				add_filter( 'wp_login_errors', array( $this, 'sso_login_error' ), 15 );
				return;
			}

			// Reject any authentication attempts that are made without a password.
			if ( empty( trim( $_POST['pwd'] ) ) ) {
				$this->last_error = 'A password is required to authenticate.';
				$this->attempted_user = $_POST['log'];
				unset( $_POST['log'] );
				unset( $_POST['pwd'] );
				add_filter( 'wp_login_errors', array( $this, 'sso_login_error' ), 15 );
				return;
			}

			if ( true === apply_filters( 'wsuwp_sso_allow_wp_auth', false ) ) {
				/**
				 * If standard authentication is allowed in general, we should first look
				 * to see if it is allowed for this specific user.
				 */
				$attempt_user = get_user_by( 'login', sanitize_user( $_POST['log'] ) );
				if ( $attempt_user ) {
					if ( 'standard' === $this->get_user_type( $attempt_user->ID ) ) {
						$ldap = false;
					}
				}
			}

			// Always allow local authentication unless explicitly filtered.
			if ( defined( 'WSU_LOCAL_CONFIG' ) && WSU_LOCAL_CONFIG && false === apply_filters( 'wsuwp_sso_force_local_ad', false ) ) {
				$ldap = false;
			}

			if ( $ldap ) {
				// Open an initial LDAP connection.
				if ( false === $this->_connect() ) {
					// Temporarily store the attempted username.
					$this->attempted_user = $_POST['log'];
					unset( $_POST['log'] );
					unset( $_POST['pwd'] );
					add_filter( 'wp_login_errors', array( $this, 'sso_login_error' ), 15 );
					return;
				}

				// Handle friend IDs in addition to standard WSU logins.
				$username_parts = explode( '@', $_POST['log'] );
				if ( isset( $username_parts[1] ) ) {
					$account_suffix = '';
				} else {
					$account_suffix = '@wsu.edu';
				}

				$username = sanitize_user( $_POST['log'] );

				$result = @ldap_bind( $this->ldap_link_id, $username . $account_suffix, $_POST['pwd'] );

				if ( $result ) {
					$this->process_sso_authentication( $username );
				} else {
					// Temporarily store the attempted username.
					$this->attempted_user = $_POST['log'];
					$this->last_error = ldap_error( $this->ldap_link_id );

					unset( $_POST['log'] );
					unset( $_POST['pwd'] );
					add_filter( 'wp_login_errors', array( $this, 'sso_login_error' ), 15 );
				}
			}

			return;
		}

		// Allow a logout request to process properly.
		if ( isset( $_REQUEST['action'] ) && 'logout' === $_REQUEST['action'] ) {
			return;
		}

		// Check to see if a user is already logged in.
		$user = wp_get_current_user();

		// There should be no reason a user visits with a valid user ID, but it *could* be possible.
		if ( 0 != $user->ID ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Provide a custom error if active directory authentication is not successful.
	 *
	 * @param WP_Error $errors Any existing errors attached to this login page load.
	 *
	 * @return WP_Error Modified collection of errors.
	 */
	public function sso_login_error( $errors ) {
		if ( ! is_wp_error( $errors ) ) {
			$errors = new WP_Error();
		}

		if ( 'Invalid credentials' === $this->last_error ) {
			$errors->add( 'incorrect_password', 'Incorrect WSU Network ID or password' );
		} elseif ( empty( $this->last_error ) ) {
			$errors->add( 'incorrect_password', $this->last_error . ' | Please contact University Communications.' );
		} else {
			$errors->add( 'incorrect_password', $this->last_error );
		}

		// Reset the previously attempted WSU Network ID that was removed from the global POST to
		// skip default WordPress processing.
		$_POST['log'] = $this->attempted_user;

		return $errors;
	}

	/**
	 * Filter the default list of login errors to remove the "username exists" messaging.
	 *
	 * @param WP_Error $errors Errors currently attached to this login page load.
	 *
	 * @return WP_Error Modified collection of errors.
	 */
	public function login_errors( $errors ) {
		if ( ! is_wp_error( $errors ) ) {
			return $errors;
		}

		if ( in_array( $errors->get_error_code(), array( 'incorrect_password' ) ) ) {
			$errors = new WP_Error();
			$errors->add( 'incorrect_password', 'Incorrect username or password' );
		}

		return $errors;
	}

	/**
	 * Process the steps required for SSO authentication to be successful.
	 */
	private function process_sso_authentication( $ad_username ) {
		/* @var WP_Roles $wp_roles */
		global $wp_roles;

		// If an empty Network Id is returned from the API request, we're in trouble.
		if ( empty( $ad_username ) ) {
			wp_die( "Authentication was successful, but an empty user name was returned. Please report this error to University Web Communication." );
		}

		// Determine if a user already exists with this Network ID as a username.
		$user = new WP_User( 0, $ad_username );

		if ( 0 < $user->ID ) {
			// A user exists, so cookies can be properly set.
			$this->set_authentication( $user->ID, $ad_username );
		} else {
			// A user does not exist and a decision to add the user as a subscriber should be made. This decision
			// can be made by individual sites through the use of a filter. By default, new users are not created.
			if ( apply_filters( 'wsuwp_sso_create_new_user', false ) ) {

				$junk_password = $this->generate_password();

				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}

				$current_roles = $wp_roles->get_names();
				$new_user_role = apply_filters( 'wsuwp_sso_new_user_role', 'subscriber' );

				// If a filtered role for new users does not exist or is an Administrator, reset to Subscriber.
				if ( ! in_array( $new_user_role, $current_roles ) || 'administrator' === $new_user_role ) {
					$new_user_role = 'subscriber';
				}

				$new_user_data = array(
					'user_pass'  => $junk_password,
					'user_login' => $ad_username,
					'role'       => $new_user_role,
				);
				$new_user_id = wp_insert_user( $new_user_data );

				if ( is_wp_error( $new_user_id ) ) {
					wp_die( "We tried to create a new user for you but the attempt was not successful. Please report this error to University Web Communication." );
				}

				do_action( 'wsuwp_sso_user_created', $new_user_id );

				$this->set_authentication( $new_user_id, $ad_username );
			}
		}

		wp_die( "Please contact your administrator to add you as a user to this WordPress installation." );
	}

	/**
	 * Set the current user and auth cookies before redirecting.
	 *
	 * @param int    $user_id  User ID of the user being authenticated.
	 * @param string $username Username of the user being authenticated.
	 */
	private function set_authentication( $user_id, $username ) {
		wp_set_current_user( $user_id, $username );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $username );

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = admin_url();
		}
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Retrieve the type of authentication associated with a user.
	 *
	 * @param int $user_id ID of the user.
	 *
	 * @return string The type of user authentication for the user.
	 */
	public function get_user_type( $user_id ) {
		$user_type = get_user_meta( $user_id, '_wsuwp_sso_user_type', true );
		if ( 'standard' !== $user_type ) {
			$user_type = 'nid';
		}

		return $user_type;
	}

	/**
	 * Only show password fields for users if standard authentication is allowed.
	 *
	 * This is a blanket check. A more granular check will also be needed for when
	 * mixed users are being added. Only non-NID users should have this option.
	 *
	 * @param bool $show_fields The current setting.
	 * @param bool|WP_User $profileuser The user being edited. False if adding a new user.
	 *
	 * @return bool True if password fields should show. False if not.
	 */
	public function show_password_fields( $show_fields, $profileuser = false ) {
		// When adding a new user, profileuser is not available. We can assume a the filter default.
		if ( true === apply_filters( 'wsuwp_sso_allow_wp_auth', false ) && false === $profileuser ) {
			return true;
		}

		if ( true === apply_filters( 'wsuwp_sso_allow_wp_auth', false ) && 'standard' === $this->get_user_type( $profileuser->ID ) ) {
			return true;
		}

		return false;
	}

	/**
	 * When adding a new user to a single site installation, we need to fake the entry
	 * of a password as it is expected by `user_edit()` when the default process fires.
	 *
	 * Once a new user is added, their network type can be adjusted in the user settings
	 * so that a non network ID password can be assigned if the site supports non NID
	 * users.
	 */
	public function adjust_passwords() {
		if ( is_multisite() ) {
			return;
		}

		$user_pass = $this->generate_password();
		$_POST['pass1'] = $user_pass;
		$_POST['pass2'] = $user_pass;
	}

	/**
	 * Allow the type of user to be selected through a dropdown box.
	 *
	 * The default option will be via WSU Network ID. In some instances, standard
	 * authentication will be available as well.
	 *
	 * @param WP_User $profileuser Object containing user being edited.
	 */
	public function personal_options( $profileuser ) {
		if ( IS_PROFILE_PAGE ) {
			return;
		}

		$user_type = $this->get_user_type( $profileuser->ID );
		?>
		<tr>
			<th><label for="wsuwp_user_type">User Type:</label></th>
			<td>
				<select name="wsuwp_user_type" id="wsuwp-user-type">
					<option value="nid" <?php selected( 'nid', $user_type, true ); ?>>WSU Network ID</option>
					<option value="standard" <?php selected( 'standard', $user_type, true ); ?>>Standard Authentication</option>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Give site administrators the ability to edit individual network users if
	 * they are not WSU Network IDs.
	 *
	 * @param $caps
	 * @param $cap
	 * @param $user_id
	 * @param $args
	 *
	 * @return mixed
	 */
	public function site_admin_edit_user( $caps, $cap, $user_id, $args ) {
		if ( isset( $caps[0] ) && 'do_not_allow' === $caps[0] && 'edit_user' === $cap ) {

			// This logic only applies to site administrators.
			if ( false === current_user_can( 'administrator' ) ) {
				return $caps;
			}

			$user = get_user_by( 'id', $args[0] );

			// Our check relies an a user existing.
			if ( false === $user ) {
				return $caps;
			}

			// Our check relies on an email address attached to the user.
			if ( ! isset( $user->user_email ) || empty( $user->user_email ) ) {
				return $caps;
			}

			$user_email_domain = array_pop( explode( '@', $user->user_email ) );
			$user_email_domain = explode( '.', $user_email_domain );
			$edu = array_pop( $user_email_domain );
			$wsu = array_pop( $user_email_domain );

			// An email address must end with wsu.edu to be considered non-WSU. At this point,
			// we give the site administrator the ability to edit the user.
			if ( 'edu' !== $edu || 'wsu' !== $wsu ) {
				$caps[0] = 'edit_users';
			}
		}

		return $caps;
	}
}
new WSUWP_SSO_Authentication();

/**
 * Determine if a user is authenticated with WordPress using their WSU Network ID.
 *
 * @return bool
 */
function wsuwp_is_user_logged_in() {
	// Check for a valid WordPress authentication first.
	if ( false === is_user_logged_in() ) {
		return false;
	}

	return true;
}

/**
 * Return information about a user who has authenticated with their WSU Network ID.
 *
 * In the future, additional data can be attached to this user object. For now, it is
 * a WP_User object.
 *
 * @return bool|WP_User False if the user is not authenticated. The user's object if authenticated.
 */
function wsuwp_get_current_user() {
	if ( false === wsuwp_is_user_logged_in() ) {
		return false;
	}

	return wp_get_current_user();
}