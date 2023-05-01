<?php

class OpenID_Connect_Generic_Middleware {

	public static function register_routes() {

		register_rest_route(
			'wsu-auth/v1',
			'authorize',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'authorize' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wsu-auth/v1',
			'authorize_callback',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'authorize_callback' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wsu-auth/v1',
			'logout',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'logout' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'wsu-auth/v1',
			'logout_callback',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'logout_callback' ),
				'permission_callback' => '__return_true',
			)
		);

	}


	public static function authorize( \WP_REST_Request $request ) {

		$params       = $request->get_params();
		$params       = self::swap_param( $params, 'redirect_uri', OIDC_MIDDLEWARE_LOGIN_CALLBACK_URL );
		$querystrings = self::array_to_querystrings( $params );

		header( 'Location: ' . OIDC_ENDPOINT_LOGIN_URL . '?' . $querystrings );

		exit();

	}

	public static function authorize_callback( \WP_REST_Request $request ) {

		$redirect     = self::retrieve_session('redirect_uri');
		$params       = $request->get_params();
		$querystrings = self::array_to_querystrings( $params );

		header( 'Location: ' . $redirect . '&' . $querystrings );

		exit();

	}

	public static function logout( \WP_REST_Request $request ) {

		$params       = $request->get_params();
		$params       = self::swap_param( $params, 'post_logout_redirect_uri', OIDC_MIDDLEWARE_LOGOUT_CALLBACK_URL );
		$querystrings = self::array_to_querystrings( $params );

		header( 'Location: ' . OIDC_ENDPOINT_LOGOUT_URL . '?' . $querystrings );

		exit();

	}

	public static function logout_callback( \WP_REST_Request $request ) {

		$redirect = self::retrieve_session('post_logout_redirect_uri');

		header( 'Location: ' . $redirect );

		exit();

	}


	private static function array_to_querystrings( $array ) {

		$querystrings = array();

		foreach ( $array as $key => $value ) {
			$querystrings[] = $key . '=' . $value;
		}

		return implode( '&', $querystrings );

	}


	private static function swap_param( $params, $field, $value ) {

		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['tmp_' . $field] = $params[$field];

		$params[$field] = $value;

		return $params;

	}


	private static function retrieve_session($field){

		if ( ! session_id() ) {
			session_start();
		}

		$value = $_SESSION['tmp_' . $field];
		unset($_SESSION['tmp_' . $field]);

		return $value;

	}


	public static function init() {

		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );

	}
}

OpenID_Connect_Generic_Middleware::init();
