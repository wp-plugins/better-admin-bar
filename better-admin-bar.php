<?php
/*
	Plugin Name: Better Admin Bar
	Plugin URI: http://kubiq.sk
	Description: Better Admin Bar
	Version: 1.0
	Author: Jakub Novák
	Author URI: http://kubiq.sk
*/

if (!class_exists('admin_bar')) {
	class admin_bar {
		var $domain = 'admin_bar';
		var $plugin_admin_page;
		var $settings;
		
		function admin_bar(){ $this->__construct(); }	
		
		function __construct(){
			// translating strings
			$mo = plugin_dir_path(__FILE__) . 'languages/' . $this->domain . '-' . get_locale() . '.mo';
			load_textdomain($this->domain, $mo);
			// add plugin to menu
			add_action( 'admin_menu', array( &$this, 'plugin_menu_link' ) );
			// action on plugin initializing
			add_action( 'init', array(&$this, "plugin_init"));
		}
		
		function plugin_init(){
			add_action( 'wp_head', array(&$this, 'advanced_admin_bar'), 11 );
		}

		function advanced_admin_bar() {
			$opt = get_option( "admin_bar_settings" );
			if(isset($opt[ 'show_admin' ])){
				if (!current_user_can('administrator') && !is_admin()) {
					show_admin_bar(false);
				}
			}else{
				if(isset($opt[ 'hide_admin_bar' ])){
					show_admin_bar(false);
				}
			}
			$inactive = $opt[ 'inactive_opacity' ] == "" ? 30 : $opt[ 'inactive_opacity' ];
			$active = $opt[ 'active_opacity' ] == "" ? 100 : $opt[ 'active_opacity' ]; ?>
			<style type="text/css" media="screen">
				html { margin-top: 0px !important; }
				* html body { margin-top: 0px !important; }
				#wpadminbar{
					zoom: 1;
					-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=<?php echo $inactive; ?>)";
					filter: alpha(opacity=<?php echo $inactive; ?>);
					-moz-opacity:<?php echo (int)$inactive / 100; ?>;
					-khtml-opacity: <?php echo (int)$inactive / 100; ?>;
					opacity: <?php echo (int)$inactive / 100; ?>;
					-ms-filter:”alpha(opacity=<?php echo $inactive; ?>)”;
					filter: progid:DXImageTransform.Microsoft.Alpha(opacity=<?php echo (int)$inactive / 100; ?>);
					-webkit-transition: all .3s ease;
					-moz-transition: all .3s ease;
					-o-transition: all .3s ease;
					transition: all .3s ease;
				}
				#wpadminbar:hover{
					zoom: 1;
					-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=<?php echo $active; ?>)";
					filter: alpha(opacity=<?php echo $active; ?>);
					-moz-opacity:<?php echo (int)$active / 100; ?>;
					-khtml-opacity: <?php echo (int)$active / 100; ?>;
					opacity: <?php echo (int)$active / 100; ?>;
					-ms-filter:”alpha(opacity=<?php echo $active; ?>)”;
					filter: progid:DXImageTransform.Microsoft.Alpha(opacity=<?php echo (int)$active / 100; ?>);
				}
				<?php if(isset($opt[ 'autohide' ])){ ?>
					#wpadminbar{
						top: -<?php echo 32 - ((float)$opt['hover_area'] < 5 ? 5 : (float)$opt['hover_area']); ?>px;
					}
					#wpadminbar:hover{
						top: 0px;
					}
				<?php } ?>
			</style><?php
		}

		function filter_plugin_actions($links, $file) {
		   $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
		   array_unshift( $links, $settings_link );
		   return $links;
		}
		
		function plugin_menu_link() {
			$this->plugin_admin_page = add_submenu_page(
				'options-general.php',
				__( 'Better Admin Bar', $this->domain ),
				__( 'Better Admin Bar', $this->domain ),
				'manage_options',
				basename(__FILE__),
				array( $this, 'admin_options_page' )
			);
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'filter_plugin_actions'), 10, 2 );
		}

		function admin_options_page() {
			// check if this is plugin admin page
			if ( get_current_screen()->id != $this->plugin_admin_page ) return;
			$this->settings = get_option( "admin_bar_settings" );
			if(isset($_POST['plugin_sent'])){
				$this->settings = $_POST;
				update_option( "admin_bar_settings", $this->settings );
			}
			// display content ?>
			<div class="wrap">
				<br>
				<h1><?php _e( 'Better Admin Bar', $this->domain ); ?></h1>
				<?php if(isset($_POST['plugin_sent'])) echo '<div id="message" class="below-h2 updated"><p>'.__( 'Settings saved.' ).'</p></div>'; ?>
				<form method="post" action="<?php admin_url( 'options-general.php?page=' . basename(__FILE__) ); ?>">
					<input type="hidden" name="plugin_sent" value="1">
					<table class="form-table">
						<tr>
							<th>
								<label for="hide_admin_bar"><?php _e('Hide admin bar for all users', $this->domain) ?></label> 
							</th>
							<td>
								<input type="checkbox" name="hide_admin_bar" value="checked" id="hide_admin_bar" <?php echo $this->settings["hide_admin_bar"] ?>>
							</td>
						</tr>
						<tr>
							<th>
								<label for="show_admin"><?php _e('Hide admin bar for all users except admin', $this->domain) ?></label> 
							</th>
							<td>
								<input type="checkbox" name="show_admin" value="checked" id="show_admin" <?php echo $this->settings["show_admin"] ?>>
							</td>
						</tr>
						<tr>
							<th>
								<label for="inactive_opacity"><?php _e("Admin bar opacity (inactive):", $this->domain) ?></label> 
							</th>
							<td>
								<input type="text" size="5" name="inactive_opacity" placeholder="30" value="<?php echo $this->settings["inactive_opacity"]; ?>" id="inactive_opacity"> %
							</td>
						</tr>
						<tr>
							<th>
								<label for="active_opacity"><?php _e("Admin bar opacity on hover (active):", $this->domain) ?></label> 
							</th>
							<td>
								<input type="text" size="5" name="active_opacity" placeholder="100" value="<?php echo $this->settings["active_opacity"]; ?>" id="active_opacity"> %
							</td>
						</tr>
						<tr>
							<th>
								<label for="autohide"><?php _e('Auto-hide admin bar (show on hover)', $this->domain) ?></label> 
							</th>
							<td>
								<input type="checkbox" name="autohide" value="checked" id="autohide" <?php echo $this->settings["autohide"] ?>>
							</td>
						</tr>
						<tr>
							<th>
								<label for="hover_area"><?php _e("Top hover area height (if autohide):", $this->domain) ?></label> 
							</th>
							<td>
								<input type="text" size="5" name="hover_area" placeholder="5" value="<?php echo $this->settings["hover_area"]; ?>" id="hover_area"> px
							</td>
						</tr>
					</table>
					<p class="submit"><input type="submit" class="button button-primary button-large" value="<?php _e( 'Save' ) ?>"></p>
				</form>
			</div><?php
		}
	}
}

if (class_exists('admin_bar')) { 
	$admin_bar_var = new admin_bar();
} ?>