<?php
$middleware_protocol = isHttps() ? 'https://' : 'http://';
$middleware_domain = defined('DOMAIN_CURRENT_SITE') ? DOMAIN_CURRENT_SITE : $_SERVER['HTTP_HOST'];
define( 'OIDC_MIDDLEWARE_LOGIN_URL', $middleware_protocol . $middleware_domain . '/wp-json/wsu-auth/v1/authorize');
define( 'OIDC_MIDDLEWARE_LOGIN_CALLBACK_URL', $middleware_protocol . $middleware_domain . '/wp-json/wsu-auth/v1/authorize_callback');
define( 'OIDC_MIDDLEWARE_LOGOUT_URL', $middleware_protocol . $middleware_domain . '/wp-json/wsu-auth/v1/logout');
define( 'OIDC_MIDDLEWARE_LOGOUT_CALLBACK_URL', $middleware_protocol . $middleware_domain . '/wp-json/wsu-auth/v1/logout_callback');

define( 'OIDC_LOGIN_TYPE', 'button' );
define( 'OIDC_CLIENT_SCOPE', 'offline_access openid email profile umc_wp' );
define( 'OIDC_ENDPOINT_LOGIN_URL', 'https://login.wsu.edu/oauth2/ausnkfx56r2cZppkA2p7/v1/authorize' );
define( 'OIDC_ENDPOINT_USERINFO_URL', 'https://login.wsu.edu/oauth2/ausnkfx56r2cZppkA2p7/v1/userinfo' );
define( 'OIDC_ENDPOINT_TOKEN_URL', 'https://login.wsu.edu/oauth2/ausnkfx56r2cZppkA2p7/v1/token' );
define( 'OIDC_ENDPOINT_LOGOUT_URL', 'https://login.wsu.edu/oauth2/ausnkfx56r2cZppkA2p7/v1/logout' );
define( 'OIDC_ACR_VALUES', '' );
define( 'OIDC_ENFORCE_PRIVACY', 0 );
define( 'OIDC_LINK_EXISTING_USERS', 1 );
define( 'OIDC_CREATE_IF_DOES_NOT_EXIST', 0 );
define( 'OIDC_REDIRECT_USER_BACK', 1 );
define( 'OIDC_REDIRECT_ON_LOGOUT', 1 );

define( 'OIDC_NO_SSL_VERIFY', 0 );
define( 'OIDC_HTTP_REQUEST_TIMEOUT', 120 );
define( 'OIDC_STATE_TIME_LIMIT', 300 );
define( 'OIDC_DISPLAY_NAME_FORMAT', '{given_name} {family_name}' );
define( 'OIDC_ENABLE_LOGGING', true );
define( 'OIDC_HIDE_SETTINGS_PAGE', true );

function isHttps()
{

    if (array_key_exists("HTTPS", $_SERVER) && 'on' === $_SERVER["HTTPS"]) {
        return true;
    }
    if (array_key_exists("SERVER_PORT", $_SERVER) && 443 === (int)$_SERVER["SERVER_PORT"]) {
        return true;
    }
    if (array_key_exists("HTTP_X_FORWARDED_SSL", $_SERVER) && 'on' === $_SERVER["HTTP_X_FORWARDED_SSL"]) {
        return true;
    }
    if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && 'https' === $_SERVER["HTTP_X_FORWARDED_PROTO"]) {
        return true;
    }

    return false;

}

// Customize plugin functionality via filters & actions
add_filter('openid-connect-generic-login-button-text', function( $text ) {

    return 'WSU Login';

});


add_filter('openid-connect-generic-auth-url', function( $url ) {

	$url_parts = explode('?', $url);
	$new_url = OIDC_MIDDLEWARE_LOGIN_URL . '?' . $url_parts[1];

    return $new_url;

});


add_filter('openid-connect-logout-redirect-url', function( $url ) {

	$url_parts = explode('?', $url);
	$new_url = OIDC_MIDDLEWARE_LOGOUT_URL . '?' . $url_parts[1];

    return $new_url;

});


add_filter('openid-connect-generic-alter-request', function( $request, $operation ) {

	if ( $operation == 'get-authentication-token' ) {
        $request['body']['redirect_uri'] = OIDC_MIDDLEWARE_LOGIN_CALLBACK_URL;
    }

    return $request;

}, 10, 2);

add_action('openid-connect-generic-update-user-using-current-claim', function( $user, $user_claim) {

	$user_data_version = 'r2701';
	$user_ad_data = get_user_meta( $user->ID, '_wsuwp_ad_data', true );
	$refresh = false;

	if ( empty( $user_ad_data ) || ! isset( $user_ad_data['last_refresh'] ) || ! isset( $user_ad_data['version'] ) ) {
		$refresh = true;
	}

	if ( false === $refresh && ( time() > ( absint( $user_ad_data['last_refresh'] ) + 86400 ) ) ) {
		$refresh = true;
	}

	if ( false === $refresh && $user_data_version !== $user_ad_data['version'] ) {
		$refresh = true;
	}

	if ( true === $refresh ) {
		$user_ad_data = array(
			'wsuaffiliation' => 'NA', // TODO: Can we get this from Okta?
			'memberof' => $user_claim['umc_wp.groups'] ?? array(),
			'user_type' => 'nid',
		);

		$user_ad_data['last_refresh'] = time();
		$user_ad_data['version'] = $user_data_version;

		update_user_meta( $user->ID, '_wsuwp_ad_data', $user_ad_data );
	}

}, 10, 2);
