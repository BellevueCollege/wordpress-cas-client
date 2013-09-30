<?php 
######## 
// New
########




##########################
##########################
##########################
##########################
##########################

	add_action( 'network_admin_menu', 'casclient_network_menu_settings');
	
	function casclient_network_menu_settings(){

		add_submenu_page ('settings.php', 'CAS Client Beta', 'CAS Client Beta', 'manage_network', 'casclient-settings', 'casclient_settings');

	}

	function casclient_settings($active_tab = '' ) {

	    if (is_multisite() && current_user_can('manage_network'))  {

	        ?>
	    <div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2>CAS Client Beta</h2>
	<?php //settings_errors(); ?> 

        <?php if( isset( $_GET[ 'tab' ] ) ) {  
            $active_tab = $_GET[ 'tab' ];  
        } else if( $active_tab == 'role_assignments' ) {  
            $active_tab = 'role_assignments';  
        } else if( $active_tab == 'user_mapping' ) {  
            $active_tab = 'user_mapping';  
        } else {  
            $active_tab = 'server_setup';  
        } // end if/else ?>  
        
       <h2 class="nav-tab-wrapper">  
            <a href="?page=casclient-settings&tab=server_setup" class="nav-tab <?php echo $active_tab == 'server_setup' ? 'nav-tab-active' : ''; ?>">Server Setup</a>  
            <a href="?page=casclient-settings&tab=role_assignments" class="nav-tab <?php echo $active_tab == 'role_assignments' ? 'nav-tab-active' : ''; ?>">Role Assignments</a>  
            <a href="?page=casclient-settings&tab=user_mapping" class="nav-tab <?php echo $active_tab == 'user_mapping' ? 'nav-tab-active' : ''; ?>">User Mapping</a>  
        </h2> 
	
	    <?php 
	
	    if (isset($_POST['action']) && $_POST['action'] == 'update_casclientbeta_settings') {
	
	    check_admin_referer('save_network_casclientbeta_settings', 'casclientbeta-plugin');
	
	    //sample code from Professional WordPress book
	
	    //store option values in a variable
	        $network_casclientbeta_settings = $_POST['network_casclientbeta_settings'];
	
	        //use array map function to sanitize option values
	        $network_casclientbeta_settings = array_map( 'sanitize_text_field', $network_casclientbeta_settings );
	
	        //save option values
	        update_site_option( 'casclient_settings', $network_casclientbeta_settings );
	
	        //just assume it all went according to plan
	        echo '<div id="message" class="updated fade"><p><strong>Globals Settings Updated!</strong></p></div>';
	
	}//if POST
	
	?>
	
	
	<form method="post" action="">
		<input type="hidden" name="action" value="update_casclientbeta_settings" />

		<?php 
		wp_nonce_field('save_network_casclientbeta_settings', 'casclientbeta-plugin');

	$optionarray_def = wpcasldap_getoptions();
				
		
		?>

			<?php 

				if( $active_tab == 'server_setup' ) { ?>
				<h4>CAS Server Setup</h4>
		<?php settings_fields( 'wpcasldap' ); ?>

		<h3><?php _e('Configuration settings for WordPress CAS Client', 'wpcasldap') ?></h3>
		<h4><?php _e('Note', 'wpcasldap') ?></h4>
		<p>
			<?php _e('Now that you’ve activated this plugin, WordPress is attempting to authenticate using CAS, even if it’s not configured or misconfigured.', 'wpcasldap' ) ?><br />
			<?php _e('Save yourself some trouble, open up another browser or use another machine to test logins. That way you can preserve this session to adjust the configuration or deactivate the plugin.', 'wpcasldap') ?>"
		</p>

		<?php if (!isset($wpcasldap_options['include_path'])) : ?>
		<h4><?php _e('phpCAS include path', 'wpcasldap') ?></h4>
		<p>
			<small><em><?php _e('Note: The phpCAS library is required for this plugin to work. We need to know the server path to the CAS.php file.', 'wpcasldap') ?></em></small>
		</p>

		<table class="form-table">

	        <tr valign="top">
				<th scope="row">
					<label>
						<?php _e('CAS.php Path', 'wpcasldap') ?>
					</label>
				</th>

				<td>
					<input type="text" size="80" name="wpcasldap_include_path" id="include_path_inp" value="<?php echo $optionarray_def['include_path']; ?>" />
				</td>
			</tr>

		</table>
	<?php endif; ?>
    
    <?php if (!isset($wpcasldap_options['cas_version']) ||
			!isset($wpcasldap_options['server_hostname']) ||
			!isset($wpcasldap_options['server_port']) ||
			!isset($wpcasldap_options['server_path']) ) : ?>
	<h4><?php _e('phpCAS::client() parameters', 'wpcasldap') ?></h4>
	<table class="form-table">
	    <?php if (!isset($wpcasldap_options['cas_version'])) : ?>

		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('CAS version', 'wpcasldap') ?>
				</lable>
			</th>

			<td>
				<select name="wpcasldap_cas_version" id="cas_version_inp">
                    <option value="2.0" <?php echo ($optionarray_def['cas_version'] == '2.0')?'selected':''; ?>>CAS_VERSION_2_0</option>
                    <option value="1.0" <?php echo ($optionarray_def['cas_version'] == '1.0')?'selected':''; ?>>CAS_VERSION_1_0</option>
                </select>
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_hostname'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Hostname', 'wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_hostname" id="server_hostname_inp" value="<?php echo $optionarray_def['server_hostname']; ?>" />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_port'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Port','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_port" id="server_port_inp" value="<?php echo $optionarray_def['server_port']; ?>" />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['server_path'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('Server Path','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_server_path" id="server_path_inp" value="<?php echo $optionarray_def['server_path']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	</table>
	<?php endif; ?>

    <?php if (!isset($wpcasldap_options['useradd']) ||
			!isset($wpcasldap_options['userrole']) ||
			!isset($wpcasldap_options['useldap']) ||
			!isset($wpcasldap_options['email_suffix']) ) : ?>

	<h4><?php _e('Treatment of Unregistered User','wpcasldap') ?></h4>
		<table class="form-table">
		    <?php if (!isset($wpcasldap_options['useradd'])) : ?>
			<tr valign="top">
				<th scope="row">
					<label>
						<?php _e('Add to Database','wpcasldap') ?>
					</lable>
				</th>

				<td>

					<input type="radio" name="wpcasldap_useradd" id="useradd_yes" value="yes" <?php echo ($optionarray_def['useradd'] == 'yes')?'checked="checked"':''; ?> />
					<label for="useradd_yes">Yes &nbsp;</label>

					<input type="radio" name="wpcasldap_useradd" id="useradd_no" value="no" <?php echo ($optionarray_def['useradd'] != 'yes')?'checked="checked"':''; ?> />
					<label for="useradd_no">No &nbsp;</label>
				</td>
			</tr>
	        <?php endif; ?>
		    <?php if (!isset($wpcasldap_options['userrole'])) : ?>
			<tr valign="top">
				<th scope="row">
					<label>
						<?php _e('Default Role','wpcasldap') ?>
					</label>
				</th>

				<td>
					<select name="wpcasldap_userrole" id="cas_version_inp">
						<option value="subscriber" <?php echo ($optionarray_def['userrole'] == 'subscriber')?'selected':''; ?>>Subscriber</option>
						<option value="contributor" <?php echo ($optionarray_def['userrole'] == 'contributor')?'selected':''; ?>>Contributor</option>
						<option value="author" <?php echo ($optionarray_def['userrole'] == 'author')?'selected':''; ?>>Author</option>
						<option value="editor" <?php echo ($optionarray_def['userrole'] == 'editor')?'selected':''; ?>>Editor</option>
						<option value="administrator" <?php echo ($optionarray_def['userrole'] == 'administrator')?'selected':''; ?>>Administrator</option>
	                </select>
	            </td>
			</tr>
	        <?php endif; ?>
		    <?php if (!isset($wpcasldap_options['useldap'])) : ?>
				<?php if (function_exists('ldap_connect')) :

					//error_log("ldap connect exists");
				?>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('Use LDAP to get user info','wpcasldap') ?>
						</label>
					</th>

					<td>
						<input type="radio" name="wpcasldap_useldap" id="useldap_yes" value="yes" <?php echo ($optionarray_def['useldap'] == 'yes')?'checked="checked"':''; ?> />
						<label for="useldap_yes">Yes &nbsp;</label>

						<input type="radio" name="wpcasldap_useldap" id="useldap_no" value="no" <?php echo ($optionarray_def['useldap'] != 'yes')?'checked="checked"':''; ?> />
						<label for="useldap_no">No &nbsp;</label>
					</td>
				</tr>
				<?php
				else :
				?>
					<input type="hidden" name="wpcasldap_useldap" id="useldap_hidden" value="no" />
				<?php
				endif;
				?>
	        <?php endif; ?>

		   <?php if (!isset($wpcasldap_options['email_suffix'])) : ?>
		   <tr valign="center">
				<th scope="row">
					<label>
						<?php _e('E-mail Suffix','wpcasldap') ?>
					</label>
				</th>

				<td>
					<input type="text" size="50" name="wpcasldap_email_suffix" id="server_port_inp" value="<?php echo $optionarray_def['email_suffix']; ?>" />
				</td>
			</tr>
	        <?php endif; ?>
		</table>
	    <?php endif; ?>
    
    <?php if (function_exists('ldap_connect')) : ?>
    <?php if (!isset($wpcasldap_options['ldapbasedn']) ||
			!isset($wpcasldap_options['ldapport']) ||
			!isset($wpcasldap_options['ldaphost']) ) : ?>
	<h4><?php _e('LDAP parameters','wpcasldap') ?></h4>

	<table class="form-table">
	    <?php if (!isset($wpcasldap_options['ldaphost'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Host','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_ldaphost" id="ldap_host_inp" value="<?php echo $optionarray_def['ldaphost']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	    <?php if (!isset($wpcasldap_options['ldapport'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Port','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_ldapport" id="ldap_port_inp" value="<?php echo $optionarray_def['ldapport']; ?>"  />
			</td>
		</tr>
        <?php endif; ?>

	    <?php if (!isset($wpcasldap_options['ldapbasedn'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Base DN','wpcasldap') ?>
				</label>
			</th>
			<td>
				<input type="text" size="50" name="wpcasldap_ldapbasedn" id="ldap_basedn_inp" value="<?php echo $optionarray_def['ldapbasedn']; ?>" />
			</td>
		</tr>
        <?php endif; ?>
	</table>
    <?php endif; ?>
    <?php endif; ?>


				<p class="submit">
					<input type="submit" class="button-primary" name="wpcasldap_submit" value="Save Settings" />
				</p>

	            <?php 
	            #######################################
	            // User Mapping Tab
	            #######################################
	            ?>
	            
				<?php } elseif  ($active_tab == 'user_mapping' ) { ?>
					<h4>User Mapping Rules</h4>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label></label>
						</th>
					</tr>
				</table>

				<?php				
				###############################
				// Add User Mapping Rule Modal
				###############################
				
				 add_thickbox(); ?>
				<div id="my-content-id" style="display:none;">
				     <p>
				          User Mapping Rules go here.
				     </p>
				</div>
				
				<a href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox button-secondary">Add rule</a>





				<?php } else { ?>
					<h4>Role Assignment Rules</h4>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label></label>
						</th>
					</tr>
				</table>



				<?php				

				###############################
				// Add Role Assignment Rule Modal
				###############################
				
				 add_thickbox(); ?>
				<div id="add_role_assignments" style="display:none;">
					<h3>Add Role Assignment Rule</h3>
						<table class="form-table">
						    <?php if (!isset($wpcasldap_options['casorldap_attribute'])) : ?>
							<tr valign="top">
								<th scope="row">
									<label>
										<?php _e('Choose Attribute','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<select name="wpcasldap_casorldap_attribute" id="casorldap_attribute_sel">
										<option value="CAS" <?php echo ($optionarray_def['casorldap_attribute'] == 'CAS')?'selected':''; ?>>CAS</option>
										<option value="LDAP" <?php echo ($optionarray_def['casorldap_attribute'] == 'LDAP')?'selected':''; ?>>LDAP</option>
					                </select>
					            </td>
							</tr>

					        <?php endif; ?>

						   <?php if (!isset($wpcasldap_options['casatt_name'])) : ?>
						   <tr valign="center">
								<th scope="row">
									<label>
										<?php _e('Name of attribute','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<input type="text" size="50" name="wpcasldap_casatt_name" id="casatt_inp" value="<?php echo $optionarray_def['casatt_name']; ?>" />
								</td>
							</tr>
					        <?php endif; ?>

						    <?php if (!isset($wpcasldap_options['casatt_operator'])) : ?>
							<tr valign="top">
								<th scope="row">
									<label>
										<?php _e('Operator','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<select name="wpcasldap_casatt_operator" id="casatt_operator_sel">
										<option value="is" <?php echo ($optionarray_def['casatt_operator'] == 'is')?'selected':''; ?>>is</option>
										<option value="is not" <?php echo ($optionarray_def['casatt_operator'] == 'is not')?'selected':''; ?>>is not</option>
										<option value="greater than" <?php echo ($optionarray_def['casatt_operator'] == 'greater than')?'selected':''; ?>>greater than</option>
										<option value="less than" <?php echo ($optionarray_def['casatt_operator'] == 'less than')?'selected':''; ?>>less than</option>
										<option value="contains" <?php echo ($optionarray_def['casatt_operator'] == 'contains')?'selected':''; ?>>contains</option>
										<option value="starts with" <?php echo ($optionarray_def['casatt_operator'] == 'starts with')?'selected':''; ?>>starts with</option>
										<option value="ends with" <?php echo ($optionarray_def['casatt_operator'] == 'ends with')?'selected':''; ?>>ends with</option>
					                </select>
					            </td>
							</tr>

					        <?php endif; ?>
							
						   <?php if (!isset($wpcasldap_options['casatt_user_value_to_compare'])) : ?>
						   <tr valign="center">
								<th scope="row">
									<label>
										<?php _e('User value to compare','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<input type="text" size="50" name="wpcasldap_casatt_user_value_to_compare" id="casatt_user_value_to_compare_inp" value="<?php echo $optionarray_def['casatt_user_value_to_compare']; ?>" />
								</td>
							</tr>
					        <?php endif; ?>

						    <?php if (!isset($wpcasldap_options['casatt_operator'])) : ?>
							<tr valign="top">
								<th scope="row">
									<label>
										<?php _e('WP Role','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<select id="casatt_wp_role_sel" name="casatt_wp_role">
										<?php foreach (get_editable_roles() as $role_name => $role_info): ?>
											<option value="<?php echo $role_name; ?>"><?php echo $role_name; ?></option>
										<?php endforeach; ?>
									</select>

					            </td>
							</tr>

					        <?php endif; ?>


						    <?php if (!isset($wpcasldap_options['casatt_wp_site'])) : ?>
							<tr valign="top">
								<th scope="row">
									<label>
										<?php _e('WP Site','wpcasldap') ?>
									</label>
								</th>
				
								<td>
									<?php
									$blog_list = get_blog_list( 0, 'all' );
									 krsort($blog_list); ?>
									<select id="casatt_wp_site_sel" name="casatt_wp_site">
										<option value="select all" <?php echo ($optionarray_def['casatt_wp_site'] == 'select all')?'selected':''; ?>>select all</option>
										<?php  foreach ($blog_list AS $blog): ?>
										 <option value="<?php echo $blog['path']; ?>"><?php echo $blog['path']; ?></option>
										<?php  endforeach; ?>
									</select>
					            </td>
							</tr>

					        <?php endif; ?>



						</table>
		
				<p class="submit">
					<input type="submit" class="button-primary" name="wpcasldap_roleassignment_submit" value="Save Settings" />
				</p>
		
				</div>
				
				<a href="#TB_inline?width=600&height=550&inlineId=add_role_assignments" class="thickbox button-secondary">Add rule</a>				
				<?php } //end if/else
				?>


				
	
	</form>
		
	    <?php
	
	    } else {
	
	     echo '<p>My Network plugin must be used with WP Multisite.  Please configure WP Multisite before using this plugin.  In addition, this page can only be accessed in the by a super admin.</p>';
	     /*Note: if your plugin is meant to be used also by single wordpress installations you would configure the settings page here, perhaps by calling a function.*/
	
	     }
	
	?>
	</div>
	<?php
	
	} //settings page function 


