<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the wp-config.php file. The wp-config.php
 * file will then load the wp-settings.php file, which
 * will then set up the WordPress environment.
 *
 * If the wp-config.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * wp-config.php file.
 *
 * Will also search for wp-config.php in WordPress' parent
 * directory to allow the WordPress directory to remain
 * untouched.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package WordPress
 */

/** Define ABSPATH as this file's directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

if ( file_exists( ABSPATH . 'wp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another install */
	require_once( dirname(ABSPATH) . '/wp-config.php' );

} else {

	// A config file doesn't exist

	define( 'WPINC', 'wp-includes' );
	require_once( ABSPATH . WPINC . '/load.php' );

	// Standardize $_SERVER variables across setups.
	wp_fix_server_vars();

	require_once( ABSPATH . WPINC . '/functions.php' );

	$path = wp_guess_url() . '/wp-admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	require_once( ABSPATH . WPINC . '/version.php' );

	wp_check_php_mysql_versions();
	wp_load_translations_early();

	// Die with an error message
	$die  = __( "There doesn't seem to be a <code>wp-config.php</code> file. I need this before we can get started." ) . '</p>';
	$die .= '<p>' . __( "Need more help? <a href='http://codex.wordpress.org/Editing_wp-config.php'>We got it</a>." ) . '</p>';
	$die .= '<p>' . __( "You can create a <code>wp-config.php</code> file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	wp_die( $die, __( 'WordPress &rsaquo; Error' ) );
}

remove_action('plugins_loaded', 'limit_login_setup', 99999);

/*
Sharing a wordpress session with codeigniter
*/

require_once(dirname(__FILE__)."/SessionHandler.php");
if(isset($_COOKIE['ci_session']))
{
	define('BASEPATH',TRUE);
	require_once(dirname(__FILE__)."/../application/config/database.php");
	$mydb = new wpdb($db['default']['username'],$db['default']['password'],$db['default']['database'],$db['default']['hostname']);
	
	$user = $mydb->get_results("select * from ci_sessions where id = '".$_COOKIE['ci_session']."'",ARRAY_A);
	$data = SessionHandler::unserialize($user[0]['data']);
	
	if(isset($data['email']))
	{		
		$WP_username = $data['email'];
		$WP_password = uniqid();
		$WP_email = $data['email'];
		
		if(username_exists($WP_username))
		{
			// get the user ID from email
			$WP_user = get_user_by('email',$WP_username);
			$WP_user_id = $WP_user->ID;
			
			if(!is_user_logged_in())
				wp_set_auth_cookie($WP_user_id,true);
		}
		else
		{
			wp_create_user($WP_username,$WP_password,$WP_email);
			
			$WP_user = get_user_by('slug',$WP_username);
			$WP_user_id = $WP_user->ID;
			
			if(!is_user_logged_in())
				wp_set_auth_cookie($WP_user_id,true);
		}
	} 
}
