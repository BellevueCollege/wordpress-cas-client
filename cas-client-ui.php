<?php 
######## 
// New
########




##########################
##########################
##########################
##########################
##########################
/*
	add_action( 'network_admin_menu', 'casclient_network_menu_settings');
	
	function casclient_network_menu_settings(){

		add_submenu_page ('settings.php', 'CAS Client Beta', 'CAS Client Beta', 'manage_network', 'casclient-settings', 'casclient_settings');

	}

	function casclient_settings($active_tab = '' ) { */
		function wpcasldap_options_page($active_tab = '' ) {

	   // if (is_multisite() && current_user_can('manage_network'))  {

	        ?>
	    <div class="wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2>CAS Client</h2>
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
            <a href="?page=casclient&tab=server_setup" class="nav-tab <?php echo $active_tab == 'server_setup' ? 'nav-tab-active' : ''; ?>">Server Setup</a>  
            <a href="?page=casclient&tab=role_assignments" class="nav-tab <?php echo $active_tab == 'role_assignments' ? 'nav-tab-active' : ''; ?>">Role Assignments</a>  
            <a href="?page=casclient&tab=user_mapping" class="nav-tab <?php echo $active_tab == 'user_mapping' ? 'nav-tab-active' : ''; ?>">User Mapping</a>  
        </h2> 
	
	    <?php 
	/*
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
	*/
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
					<?php
						$casPath = $optionarray_def['include_path'];
						debug_log("cas path :".$casPath);
						if(!isset($optionarray_def['include_path']) || empty($optionarray_def['include_path']))
						{
							if(file_exists( DEFAULT_CASFILE_PATH ))
							{
								$casPath = DEFAULT_CASFILE_PATH ;
								if(is_multisite())
								{
									 update_site_option('wpcasldap_include_path',$casPath);
							    }
								else
								{
									update_option('wpcasldap_include_path',$casPath);
						        }
							}
						}
					?>
					<input type="text" size="80" name="wpcasldap_include_path" id="include_path_inp" value="<?php echo $casPath; ?>" />
				</td>
			</tr>

		</table>
	<?php endif; ?>
    
    <?php if (!isset($wpcasldap_options['cas_version']) ||
			//!isset($wpcasldap_options['server_hostname']) ||
			//!isset($wpcasldap_options['server_port']) ||
			//!isset($wpcasldap_options['server_path'])
			 !isset($wpcasldap_options['casserver'])) : ?>
	<h4><?php _e('CAS Server', 'wpcasldap') ?></h4>
	<table class="form-table">

	    
	<?php if (!isset($wpcasldap_options['casserver'])) : ?>
        <tr valign="top">
			<th scope="row">
				<label>
					<?php _e('CAS Server URI', 'wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text"  name="wpcasldap_casserver" size="50" id="casserver_inp" value="<?php echo $optionarray_def['casserver']; ?>" />
			</td>
		</tr>
	<?php endif; ?>

    <?php if (!isset($wpcasldap_options['cas_version'])) : ?>

		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('CAS version', 'wpcasldap') ?>
				</label>
			</th>

			<td>
				<select name="wpcasldap_cas_version" id="cas_version_inp">
                    <option value="2.0" <?php echo ($optionarray_def['cas_version'] == '2.0')?'selected':''; ?>>CAS_VERSION_2_0</option>
                    <option value="1.0" <?php echo ($optionarray_def['cas_version'] == '1.0')?'selected':''; ?>>CAS_VERSION_1_0</option>
                </select>
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
					</label>
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
					<input type="text" size="50" name="wpcasldap_email_suffix" id="email_suffix_inp" value="<?php echo $optionarray_def['email_suffix']; ?>" />
				</td>
			</tr>
	        <?php endif; ?>
		</table>
	    <?php endif; ?>
    
    <?php if (function_exists('ldap_connect')) : ?>
    <?php if (!isset($wpcasldap_options['ldapbasedn']) ||
			//!isset($wpcasldap_options['ldapport']) ||
			!isset($wpcasldap_options['ldapuri']) ) : ?>

	<h4><?php _e('LDAP parameters','wpcasldap') ?></h4>

	<table class="form-table">
		<?php if (!isset($wpcasldap_options['ldapuri'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP URI','wpcasldap') ?>
				</label>
			</th>

			<td>
				<input type="text" size="50" name="wpcasldap_ldapuri" id="ldap_uri_inp" value="<?php echo $optionarray_def['ldapuri']; ?>" />
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

         <?php if (!isset($wpcasldap_options['ldapuser'])) : ?>
        <tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP User','wpcasldap') ?>
				</label>
			</th>
			<td>
				<input type="text"  name="wpcasldap_ldapuser" id="ldap_user_inp" value="<?php echo $optionarray_def['ldapuser']; ?>" />
			</td>
		</tr>
		 <?php endif; ?>

		 <?php if (!isset($wpcasldap_options['ldappassword'])) : ?>
		<tr valign="top">
			<th scope="row">
				<label>
					<?php _e('LDAP Password','wpcasldap') ?>
				</label>
			</th>
			<td>
				<input type="button" name="reset" id="reset" onclick="showPasswordField()" size="20" value="Reset">
				<input type="password"  name="wpcasldap_ldappassword" id="ldap_password_inp" style="display:none;" disabled/> 
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
					
				</div>
				
				<a href="#TB_inline?width=600&height=550&inlineId=add_role_assignments" class="thickbox button-secondary">Add rule</a>						
	
	</form>
		
	</div>
	<?php
		}
	
	wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . '/js/cas-client.js' );
	
	} //settings page function 
	?>

	
