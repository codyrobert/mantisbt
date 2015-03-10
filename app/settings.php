<?php
# Initialize the session
if( PHP_CGI == \Flickerbox\PHP::mode() ) {
	$t_session_id = \Flickerbox\GPC::get_string( 'session_id', '' );

	if( empty( $t_session_id ) ) {
		\Flickerbox\Session::init();
	} else {
		\Flickerbox\Session::init( $t_session_id );
	}
}

# Determines (once-off) whether the client is accessing this script via a
# secure connection. If they are, we want to use the Secure cookie flag to
# prevent the cookie from being transmitted to other domains.
# @global boolean $g_cookie_secure_flag_enabled
$g_cookie_secure_flag_enabled = \Flickerbox\HTTP::is_protocol_https();


if( file_exists( \Flickerbox\Config::get_global( 'config_path' ) . 'custom_relationships_inc.php' ) ) {
	include_once( \Flickerbox\Config::get_global( 'config_path' ) . 'custom_relationships_inc.php' );
}