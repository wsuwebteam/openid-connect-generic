<?php

class User_Profile_Fields {

	public static function insert_user_profile_fields($user) {

		$user_ad_data = get_user_meta( $user->ID, '_wsuwp_ad_data', true );

		if( ! empty( $user_ad_data ) && ! empty( $user_ad_data['memberof'] ) ){
			echo "<h3>Active Directory Groups</h3>";

			echo "<ul>";
			foreach ($user_ad_data['memberof'] as $group) {
				echo "<li>$group</li>";
			}
			echo "</ul>";
		}

	}

	public static function init() {

		add_action( 'edit_user_profile', array( __CLASS__, 'insert_user_profile_fields' ) );
		add_action( 'show_user_profile', array( __CLASS__, 'insert_user_profile_fields' ) );

	}
}

User_Profile_Fields::init();
