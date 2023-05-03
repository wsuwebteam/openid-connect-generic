<?php

class OpenID_Connect_Generic_Login {


    public static function init() {

        add_filter( 'login_message', array( __CLASS__, 'edit_login_form' ) );
        add_action( 'login_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
        
    }


    public static function edit_login_form( $message ) {

        $message = '<a href="https://wsu.edu" class="wsu-logo">Visit WSU</a>' . $message;

        return $message;
       
    }


    public static function add_scripts() {

        $source = plugin_dir_url( dirname( __FILE__ ) ) . 'css/style-login.css';

        wp_enqueue_style( 'wsu-okta-login-style', $source, false ); 

    }
}

OpenID_Connect_Generic_Login::init();