<?php

define( 'OIDC_MIDDLEWARE_LOGIN_URL', 'http://' . DOMAIN_CURRENT_SITE . '/wp-json/wsu-auth/v1/authorize');
define( 'OIDC_MIDDLEWARE_LOGIN_CALLBACK_URL', 'http://' . DOMAIN_CURRENT_SITE . '/wp-json/wsu-auth/v1/authorize_callback');
define( 'OIDC_MIDDLEWARE_LOGOUT_URL', 'http://' . DOMAIN_CURRENT_SITE . '/wp-json/wsu-auth/v1/logout');
define( 'OIDC_MIDDLEWARE_LOGOUT_CALLBACK_URL', 'http://' . DOMAIN_CURRENT_SITE . '/wp-json/wsu-auth/v1/logout_callback');

define( 'OIDC_LOGIN_TYPE', 'button' );
define( 'OIDC_CLIENT_ID', getenv('OIDC_CLIENT_ID') );
define( 'OIDC_CLIENT_SECRET', getenv('OIDC_CLIENT_SECRET') );
define( 'OIDC_CLIENT_SCOPE', 'openid email profile' );
define( 'OIDC_ENDPOINT_LOGIN_URL', 'https://logintst.wsu.edu/oauth2/default/v1/authorize' );
define( 'OIDC_ENDPOINT_USERINFO_URL', 'https://logintst.wsu.edu/oauth2/default/v1/userinfo' );
define( 'OIDC_ENDPOINT_TOKEN_URL', 'https://logintst.wsu.edu/oauth2/default/v1/token' );
define( 'OIDC_ENDPOINT_LOGOUT_URL', 'https://logintst.wsu.edu/oauth2/default/v1/logout' );
define( 'OIDC_ACR_VALUES', '' );
define( 'OIDC_ENFORCE_PRIVACY', 0 );
define( 'OIDC_LINK_EXISTING_USERS', 1 );
define( 'OIDC_CREATE_IF_DOES_NOT_EXIST', 0 );
define( 'OIDC_REDIRECT_USER_BACK', 1 );
define( 'OIDC_REDIRECT_ON_LOGOUT', 1 );

define( 'OIDC_NO_SSL_VERIFY', 0 );
define( 'OIDC_DISPLAY_NAME_FORMAT', '{given_name} {family_name}' );
define( 'OIDC_HIDE_SETTINGS_PAGE', true );


// Customize plugin functionality via filters
add_filter('openid-connect-generic-login-button-text', function( $text ) {

    $text = 'Login with WSU SSO';

    return $text;

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

?>




