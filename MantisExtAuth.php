<?php
/**
 * MantisExtAuth - A MantisBT plugin plugin provides an external authentication
 *
 * MantisExtAuth is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisExtAuth is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisExtAuth.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2024-2025 Nikolay Raspopov <raspopov@cherubicsoft.com>
 */

class MantisExtAuthPlugin extends MantisPlugin {

	/**
	 * A method that populates the plugin information and minimum requirements.
	 *
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );

		$this->version = '1.0.0';
		$this->requires = [
			'MantisCore' => '2.0',
		];

		$this->author = 'Nikolay Raspopov';
		$this->contact = 'raspopov@cherubicsoft.com';
		$this->url = 'https://github.com/raspopov/MantisExtAuth';
	}

	/**
	 * Register event hooks for plugin.
	 *
	 * @return array
	 */
	function hooks() {
		return [
			'EVENT_CORE_READY' => 'login',
			'EVENT_AUTH_USER_FLAGS' => 'flags'
		];
	}

	/**
	 * Gets set of flags for authentication for the specified user.
	 *
	 * @param string $p_event The name for the event
	 * @param array  $p_args  The event arguments
	 * @return AuthFlags The auth flags object to use
	 */
	function flags( $p_event, $p_args ) {
		$t_flags = new AuthFlags();

		# Passwords managed externally
		$t_flags->setCanUseStandardLogin( false );

		# Disable re-authentication
		$t_flags->setReauthenticationEnabled( false );

		return $t_flags;
	}

 	/**
	 * Login after the core system is loaded.
	 *
	 * @param string $p_event The name for the event
	 * @param array  $p_args  The event arguments
	 * @return void
	 */
	function login( $p_event, $p_args ) {
		global $g_script_login_cookie;

		if( auth_is_user_authenticated() ) {
			# Already authenticated
			return;
		}

		$t_auth_user = $_SERVER['AUTH_USER'] ?? $_SERVER['REMOTE_USER'] ?? '';

		# Remove the domain name
		$t_full_username = explode( '\\', $t_auth_user );
		$t_username = end( $t_full_username );

		if( !$t_username ) {
			# Access denied
			return;
		}

		$t_user_id = auth_get_user_id_from_login_name( $t_username );
		if( $t_user_id === false ) {
			user_create( $t_username, auth_generate_random_password() );

			$t_user_id = user_get_id_by_name( $t_username );
			if( $t_user_id === false ) {
				# Database error
				return;
			}

			# Sync with LDAP
			if( ON == config_get_global( 'use_ldap_realname' ) ) {
				user_set_realname( $t_user_id, ldap_realname( $t_user_id ) );
			}
			if( ON == config_get_global( 'use_ldap_email' ) ) {
				user_set_email( $t_user_id, ldap_email( $t_user_id ) );
			}
		}

		if( !auth_login_user( $t_user_id ) ) {
			# Disabled account
			return;
		}

		# Set cookie now to bypass login page
		# i.e. auth_is_user_authenticated() later call should return true
		$g_script_login_cookie = user_get_field( $t_user_id, 'cookie_string' );

		current_user_set( $t_user_id );
	}
}
