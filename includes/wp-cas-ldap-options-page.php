<?php
/*
 * Copyright (C) 2014 Bellevue College
 * Copyright (C) 2009 Ioannis C. Yessios
 *
 * This file is part of the WordPress CAS Client
 *
 * The WordPress CAS Client is free software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Bellevue College
 * Address: 3000 Landerholm Circle SE
 *          Room N215F
 *          Bellevue WA 98007-6484
 * Phone:   +1 425.564.4201
 */

require_once constant( 'CAS_CLIENT_ROOT' ) . '/includes/class-wp-cas-ldap-settings.php';

/**
 * wp_cas_ldap_options_page function hook for WordPress.
 */
function wp_cas_ldap_options_page( ) {
	global $wp_cas_ldap_options, $form_action;

	// Get Options
	$option_array_def = wp_cas_ldap_settings :: get_options( );
?>
	<div class="wrap">
	<h2><?php _e('CAS Client', 'wpcasldap'); ?></h2>
<?php
	echo '<form method="post" action="' . $form_action . '">';
	settings_fields( 'wpcasldap' );
	echo '<h3>';
	_e( 'Configuration settings for WordPress CAS Client', 'wpcasldap' );
	echo '</h3>';

	echo '<h4>';
	_e( 'Note', 'wpcasldap' );
	echo '</h4>';
?>
<p>
<?php
	_e( "Now that you've activated this plugin, WordPress is attempting to authenticate using CAS, even if it's not configured or misconfigured.", 'wpcasldap' );
	echo '<br />';
	_e( 'Save yourself some trouble, open up another browser or use another machine to test logins. That way you can preserve this session to adjust the configuration or deactivate the plugin.', 'wpcasldap' );
	echo '"';
?>
</p>
<?php
	if ( ! isset( $wp_cas_ldap_options['include_path'] ) ) {
		echo '<h4>';
		_e( 'phpCAS include path', 'wpcasldap' );
		echo '</h4>';
?>
<p>
<?php
		echo '<small><em>';
		_e( 'Note: The phpCAS library is required for this plugin to work. We need to know the server absolute path to the CAS.php file.', 'wpcasldap' );
		echo '</em></small>';
?>
</p>

		<table class="form-table">

	        <tr valign="top">
				<th scope="row">
					<label>
<?php
		_e( 'CAS.php path', 'wpcasldap' );
?>
					</label>
				</th>

				<td>
<?php
		echo '<input type="text" size="80" name="wpcasldap_include_path" id="include_path_inp" value="' . $option_array_def['include_path'] . '" />';
		if ( isset( $option_array_def['include_path'] ) && !empty( $option_array_def['include_path'] ) && !file_exists( $option_array_def['include_path'] ) ) {
			echo "<p><strong style='color: red'>";
			_e( 'WARNING : The path to CAS.php file currently defined is incorrect!', 'wpcasldap' );
			echo "</strong></p>";
		}
?>
				</td>
			</tr>

		</table>
<?php
	}

	if ( ! isset( $wp_cas_ldap_options['cas_version'] ) ||
      ! isset( $wp_cas_ldap_options['server_hostname'] ) ||
			! isset( $wp_cas_ldap_options['server_port'] ) ||
			! isset( $wp_cas_ldap_options['server_path'] ) ) {
		echo '<h4>';
		_e( 'phpCAS::client() parameters', 'wpcasldap' );
		echo '</h4>';
?>
	<table class="form-table">
<?php
		if ( ! isset( $wp_cas_ldap_options['cas_version'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
		_e( 'CAS version', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
				<select name="wpcasldap_cas_version" id="cas_version_inp">
<?php
		echo '<option value="2.0" ';
		echo ( '2.0' === $option_array_def['cas_version'] ) ? 'selected' : '';
		echo '>CAS_VERSION_2_0</option>';
		echo '<option value="1.0" ';
		echo ( '1.0' === $option_array_def['cas_version'] ) ? 'selected' : '';
		echo '>CAS_VERSION_1_0</option>';
?>
				</select>
			</td>
		</tr>
<?php
	}

		if ( ! isset( $wp_cas_ldap_options['server_hostname'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Hostname', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_hostname" id="server_hostname_inp" value="' . $option_array_def['server_hostname'] . '" />'
?>
			</td>
		</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['server_port'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Port', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_port" id="server_port_inp" value="' . $option_array_def['server_port'] . '" />';
?>
			</td>
		</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['server_path'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Server Path', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_server_path" id="server_path_inp" value="' . $option_array_def['server_path'] . '" />';
?>
			</td>
		</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['disable_cas_logout'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Disable CAS logout', 'wpcasldap' );
?>
					</label>
				</th>

				<td>

<?php
			echo '<input type="radio" name="wpcasldap_disable_cas_logout" id="disable_cas_logout_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['disable_cas_logout'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="disable_cas_logout_yes"><?php _e( 'Yes', 'wpcasldap' ); ?> &nbsp;</label>
<?php
			echo '<input type="radio" name="wpcasldap_disable_cas_logout" id="disable_cas_logout_no" value="no" ';
			echo ( 'yes' !== $option_array_def['disable_cas_logout'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="disable_cas_logout_no"><?php _e( 'No', 'wpcasldap' ); ?> &nbsp;</label>
<?php
			echo '<p><small><em>';
			_e( "Note: If you disable CAS logout, when a user click on the logout link, he will only be logged off from Wordpress, not from the CAS server (and potential other CAS authenticated services).", 'wpcasldap' );
			echo '</em></small></p>';
?>
				</td>
			</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['cas_redirect_using_js'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Redirect to CAS login page using Javascript', 'wpcasldap' );
?>
					</label>
				</th>

				<td>

<?php
			echo '<input type="radio" name="wpcasldap_cas_redirect_using_js" id="cas_redirect_using_js_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['cas_redirect_using_js'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="cas_redirect_using_js"><?php _e( 'Yes', 'wpcasldap' ); ?> &nbsp;</label>
<?php
			echo '<input type="radio" name="wpcasldap_cas_redirect_using_js" id="cas_redirect_using_js_no" value="no" ';
			echo ( 'yes' !== $option_array_def['cas_redirect_using_js'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="cas_redirect_using_js_no"><?php _e( 'No', 'wpcasldap' ); ?> &nbsp;</label>
<?php
			echo '<p><small><em>';
			_e( "Note: Using Javascript to redirect user to CAS login page enables to keep hashtag in URL (if present).", 'wpcasldap' );
			echo '</em></small></p>';
?>
				</td>
			</tr>
<?php
		}

?>

	</table>
<?php
	}

	if ( ! isset($wp_cas_ldap_options['useradd'] ) ||
		! isset( $wp_cas_ldap_options['userrole'] ) ||
		! isset( $wp_cas_ldap_options['useldap'] ) ||
		! isset( $wp_cas_ldap_options['email_suffix'] ) ) {

		echo '<h4>';
		_e( 'Treatment of unregistered users', 'wpcasldap' );
		echo '</h4>';
?>
		<table class="form-table">
<?php
		if ( ! isset( $wp_cas_ldap_options['useradd'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Add to database', 'wpcasldap' );
?>
					</label>
				</th>

				<td>

<?php
			echo '<input type="radio" name="wpcasldap_useradd" id="useradd_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['useradd'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="useradd_yes"><?php _e( 'Yes', 'wpcasldap' ); ?> &nbsp;</label>
<?php
			echo '<input type="radio" name="wpcasldap_useradd" id="useradd_no" value="no" ';
			echo ( 'yes' !== $option_array_def['useradd'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
					<label for="useradd_no"><?php _e( 'No', 'wpcasldap' ); ?> &nbsp;</label>
				</td>
			</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['userrole'] ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label>
<?php
			_e( 'Default role', 'wpcasldap' );
?>
					</label>
				</th>

				<td>
					<select name="wpcasldap_userrole" id="cas_version_inp">
<?php
						echo '<option value="subscriber" ';
						echo ( 'subscriber' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Subscriber</option>';

						echo '<option value="contributor" ';
						echo ( 'contributor' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Contributor</option>';

						echo '<option value="author" ';
						echo ('author' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Author</option>';

						echo '<option value="editor" ';
						echo ( 'editor' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Editor</option>';

						echo '<option value="administrator" ';
						echo ( 'administrator' === $option_array_def['userrole'] ) ? 'selected' : '';
						echo '>Administrator</option>';
?>
	                </select>
<p>
<?php
		echo '<small><em>';
		_e( "Note: This default role is only used to create the user on its first connection. Afterwards, the user role could be configured in Wordpress and will not be overwritten from LDAP.", 'wpcasldap' );
		echo '</em></small>';
?>
</p>

	            </td>
			</tr>
<?php
		}

		if ( ! isset( $wp_cas_ldap_options['useldap'] ) ) {
?>
				<tr valign="top">
					<th scope="row">
						<label>
<?php
			_e( 'Use LDAP to get user info', 'wpcasldap' );
?>
						</label>
					</th>

					<td>
<?php
			echo '<input type="radio" name="wpcasldap_useldap" id="useldap_yes" value="yes" ';
			echo ( 'yes' === $option_array_def['useldap'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
						<label for="useldap_yes"><?php _e( 'Yes', 'wpcasldap' ); ?> &nbsp;</label>

<?php
			echo '<input type="radio" name="wpcasldap_useldap" id="useldap_no" value="no" ';
			echo ( 'yes' !== $option_array_def['useldap'] ) ? 'checked="checked"' : '';
			echo ' />';
?>
						<label for="useldap_no"><?php _e( 'No', 'wpcasldap' ); ?> &nbsp;</label>
					</td>
				</tr>
<?php
		} else {
?>
					<input type="hidden" name="wpcasldap_useldap" id="useldap_hidden" value="no" />
<?php
		}
	}

	if ( ! isset( $wp_cas_ldap_options['email_suffix'] ) ) {
?>
		   <tr valign="center">
				<th scope="row">
					<label>
<?php
		_e('E-mail Suffix', 'wpcasldap')
?>
					</label>
				</th>

				<td>
<?php
		echo '<input type="text" size="50" name="wpcasldap_email_suffix" id="server_port_inp" value="';
		echo $option_array_def['email_suffix'];
		echo '" />';
		echo '<p><small><em>';
		_e( "Note: This suffix is used to constitute user email if it couldn't be retreived from LDAP. You must only enter the email domain name (without the '@').", 'wpcasldap' );
		echo '</em></small></p>';
?>
				</td>
			</tr>
<?php
	}
?>
		</table>

<?php

	if ( function_exists( 'ldap_connect' ) ) {
		if ( ! isset( $wp_cas_ldap_options['ldapbasedn'] ) ||
				! isset( $wp_cas_ldap_options['ldapbinddn'] ) ||
				! isset( $wp_cas_ldap_options['ldapbindpwd'] ) ||
				! isset( $wp_cas_ldap_options['ldapport'] ) ||
				! isset( $wp_cas_ldap_options['ldaphost'] ) ) {
			echo '<h4>';
			_e( 'LDAP parameters', 'wpcasldap' );
			echo '</h4>';
?>

	<table class="form-table">
<?php
			if ( ! isset( $wp_cas_ldap_options['ldaphost'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Host', 'wpcasldap' )
?>
				</label>
			</th>

			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldaphost" id="ldap_host_inp" value="';
				echo $option_array_def['ldaphost'];
				echo '" />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldapport'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Port', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldapport" id="ldap_port_inp" value="';
				echo $option_array_def['ldapport'];
				echo '"  />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldapbasedn'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Base DN', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldapbasedn" id="ldap_basedn_inp" value="';
				echo $option_array_def['ldapbasedn'];
				echo '" />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldapbinddn'] ) || ! isset( $wp_cas_ldap_options['ldapbindpwd'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Bind DN', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldapbinddn" id="ldap_binddn_inp" value="';
				echo $option_array_def['ldapbinddn'];
				echo '" />';
?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP Bind password', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="password" size="50" name="wpcasldap_ldapbindpwd" id="ldap_binddn_inp" value="';
				if (strlen($option_array_def['ldapbindpwd']) > 0)
					echo wp_cas_ldap_settings :: decrypt($option_array_def['ldapbindpwd']);
				echo '" />';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldap_users_basedn'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP users Base DN', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldap_users_basedn" id="ldap_users_basedn_inp" value="';
				echo $option_array_def['ldap_users_basedn'];
				echo '" />';
				echo '<p><small><em>';
				_e( "Note: This parameter is optional. The base DN of the LDAP server is used otherwise.", 'wpcasldap' );
				echo '</em></small></p>';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldap_users_filter'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP users filter', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldap_users_filter" id="ldap_users_filter_inp" value="';
				echo $option_array_def['ldap_users_filter'];
				echo '" />';
				echo '<p><small><em>';
				_e( "Note: This parameter is optional. If it's provided, this filter will be combined with the LDAP filter generating from the user's login.", 'wpcasldap' );
				echo '</em></small></p>';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldap_groups_basedn'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP groups Base DN', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldap_groups_basedn" id="ldap_groups_basedn_inp" value="';
				echo $option_array_def['ldap_groups_basedn'];
				echo '" />';
				echo '<p><small><em>';
				_e( "Note: This parameter is optional. The base DN of the LDAP server is used otherwise.", 'wpcasldap' );
				echo '</em></small></p>';
?>
			</td>
		</tr>
<?php
			}

			if ( ! isset( $wp_cas_ldap_options['ldap_groups_filter'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
				_e( 'LDAP groups filter', 'wpcasldap' );
?>
				</label>
			</th>
			<td>
<?php
				echo '<input type="text" size="50" name="wpcasldap_ldap_groups_filter" id="ldap_groups_filter_inp" value="';
				echo $option_array_def['ldap_groups_filter'];
				echo '" />';
				echo '<p><small><em>';
				_e( "Note: This parameter is required to retreive the user's groups. In this filter, the keywords enclosed by brace will be replace by user's corresponding information. For instance, {user_login} will be replace by the user's login or {user_email} by the user's email. You also can use the keyword {user_dn} that will be replaced by the LDAP user object's DN.", 'wpcasldap' );
				echo '</em></small></p>';
?>
			</td>
		</tr>
<?php
			}
?>
	</table>
<?php
			echo '<h4>';
			_e( 'LDAP attributes mapping', 'wpcasldap' );
			echo '</h4>';
			echo '<p>';
			_e( "You have to configure here which LDAP attributes could be mapped with Wordpress user profil information.", 'wpcasldap' );
			echo '</p>';
?>
	<table class="form-table">
<?php
	$map_attrs = array(
			'login' => __('the login', 'wpcasldap'),
			'first_name' => __('the first name', 'wpcasldap'),
			'last_name' => __('the last name', 'wpcasldap'),
			'nickname' => __('the nickname', 'wpcasldap'),
			'nicename' => __('the nice name', 'wpcasldap'),
			'role' => __('the role', 'wpcasldap'),
			'affiliations' => __('the affiliations', 'wpcasldap'),
			'email' => __('the email', 'wpcasldap'),
			'alt_email' => __('the alternative email', 'wpcasldap'),
	);
	foreach($map_attrs as $key => $label) {
		if (! isset( $wp_cas_ldap_options['ldap_map_'.$key.'_attr'] )) {
?>
                <tr valign="top">
                        <th scope="row">
                                <label>
<?php
                                printf(__( 'LDAP attribut for %s', 'wpcasldap' ), $label);
?>
                                </label>
                        </th>
                        <td>
<?php
                                echo '<input type="text" size="50" name="wpcasldap_ldap_map_'.$key.'_attr" id="ldap_map_'.$key.'_attr" value="';
                                echo $option_array_def['ldap_map_'.$key.'_attr'];
                                echo '" />';
?>
                        </td>
                </tr>
<?php
		}
	}
?>
	</table>
<?php
		}
	}

	if ( ! isset( $wp_cas_ldap_options['who_can_view'] ) ) {

		echo '<h4>';
		_e( 'Site access restriction', 'wpcasldap' );
		echo '</h4>';
?>
	<p>
	<?php _e( "You can restrict access to the public website here:", 'wpcasldap' ); ?>
	<ul style="list-style-type: disc; padding-left: 1em;">
		<li><?php _e( "If you choose to allow access only to <em>CAS authenticated users</em>, the user will be authenticated using CAS and authenticated in Wordpress only if he already has an account.", 'wpcasldap' ); ?></li>
		<li><?php _e( "If you choose to allow access only to <em>Wordpress authenticated users</em>, the user will be authenticated using CAS and authenticated in Wordpress if he already has an account or if you choose to allow adding user in database. Otherwise, the access will be denied.", 'wpcasldap' ); ?></li>
		<li><?php _e( "If you choose to allow access to <em>everyone</em>, no restriction will be applied.", 'wpcasldap'); ?></li>
	</ul>
	</p>

	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label>
<?php
		_e( 'Restrict site access to', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
				<select name="wpcasldap_who_can_view" id="who_can_view">
<?php
		echo '<option value="everyone" '.( 'everyone' === $option_array_def['who_can_view'] ? 'selected' : '').'>';
		_e( 'Everyone', 'wpcasldap' );
		echo '</option>';

		echo '<option value="cas_authenticated_users" '.( 'cas_authenticated_users' === $option_array_def['who_can_view'] ? 'selected' : '').'>';
		_e( 'CAS authenticated users', 'wpcasldap' );
		echo '</option>';

		echo '<option value="wordpress_authenticated_users" '.( 'wordpress_authenticated_users' === $option_array_def['who_can_view'] ? 'selected' : '').'>';
		_e( 'Wordpress authenticated users', 'wpcasldap' );
		echo '</option>';

?>
				</select>
			</td>
		</tr>
<?php
	}

		if ( ! isset( $wp_cas_ldap_options['access_denied_redirect_url'] ) ) {
?>
		<tr valign="top">
			<th scope="row">
				<label>
<?php
			_e( 'Access denied redirect URL', 'wpcasldap' );
?>
				</label>
			</th>

			<td>
<?php
			echo '<input type="text" size="50" name="wpcasldap_access_denied_redirect_url" id="access_denied_redirect_url" value="' . $option_array_def['access_denied_redirect_url'] . '" />';
?>
			</td>
		</tr>
<?php
		}
?>
	</table>
	<div class="submit">
		<input type="submit" name="wpcasldap_submit" value="<?php _e('Save', 'wpcasldap'); ?>" />
	</div>
	</form>
<?php
}
