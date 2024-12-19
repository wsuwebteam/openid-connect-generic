<?php

class State_Manager {

	private static $splitter = '--';


	public static function get_base( $state ) {

		return current( explode( self::$splitter, $state ) );

	}


	public static function add_param( $state, $key, $value ) {

		return isset( $state ) ? $state . self::$splitter . $key . '=' . $value : $key . '=' . $value;

	}


	public static function get_param( $state, $key ) {

		$params = self::get_params( $state );

		return $params[ $key ] ?? '';

	}


	private static function get_params( $state ) {

		$pairs = explode( self::$splitter, $state );
		$arr   = array();

		foreach ( $pairs as $pair ) {
			if ( str_contains( $pair, '=' ) ) {
				list($key, $value) = explode( '=', $pair, 2 );
				$arr[ $key ]       = $value;
			}
		}

		return $arr;

	}
}


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

		$params          = $request->get_params();
		$current_state   = $params['state'] ?? '';
		$redirect        = State_Manager::get_param( $current_state, 'redirect_uri' );
		$params['state'] = State_Manager::get_base( $current_state );
		$querystrings    = self::array_to_querystrings( $params );

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

		$params   = $request->get_params();
		$redirect = State_Manager::get_param( $params['state'] ?? '', 'post_logout_redirect_uri' );

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

		$params['state']  = State_Manager::add_param( $params['state'] ?? '', $field, $params[ $field ] );
		$params[ $field ] = $value;

		return $params;

	}


	public static function init() {

		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );

	}
}

OpenID_Connect_Generic_Middleware::init();
