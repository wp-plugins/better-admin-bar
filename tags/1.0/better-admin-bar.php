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
		var $tab;
		
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
			$opt = get_option( "plugin_settings" );
			if(isset($opt[ 'general' ][ 'show_admin' ])){
				if (!current_user_can('administrator') && !is_admin()) {
					show_admin_bar(false);
				}
			}else{
				if(isset($opt[ 'general' ][ 'hide_admin_bar' ])){
					show_admin_bar(false);
				}
			}
			$inactive = !isset($opt[ 'general' ][ 'inactive_opacity' ]) || $opt[ 'general' ][ 'inactive_opacity' ] == "" ? 30 : $opt[ 'general' ][ 'inactive_opacity' ];
			$active = !isset($opt[ 'general' ][ 'active_opacity' ]) || $opt[ 'general' ][ 'active_opacity' ] == "" ? 100 : $opt[ 'general' ][ 'active_opacity' ]; ?>
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
		
		function plugin_admin_tabs( $current = 'general' ) {
			$tabs = array( 'general' => __('General'), 'info' => __('Help') ); ?>
			<h2 class="nav-tab-wrapper">
			<?php foreach( $tabs as $tab => $name ){ ?>
				<a class="nav-tab <?php echo ( $tab == $current ) ? "nav-tab-active" : "" ?>" href="?page=<?php echo basename(__FILE__) ?>&amp;tab=<?php echo $tab ?>"><?php echo $name ?></a>
			<?php } ?>
			</h2><br><?php
		}

		function admin_options_page() {
			// check if this is plugin admin page
			if ( get_current_screen()->id != $this->plugin_admin_page ) return;
			// get current tab
			$this->tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';
			if(isset($_POST['plugin_sent'])) $this->settings[ $this->tab ] = $_POST;
			update_option( "plugin_settings", $this->settings );
			// display content ?>
			<div class="wrap">
				<h1><?php _e( 'Better Admin Bar', $this->domain ); ?></h1>
				<?php if(isset($_POST['plugin_sent'])) echo '<div id="message" class="below-h2 updated"><p>'.__( 'Settings saved.' ).'</p></div>'; ?>
				<form method="post" action="<?php admin_url( 'options-general.php?page=' . basename(__FILE__) ); ?>">
					<input type="hidden" name="plugin_sent" value="1"><?php
					$this->plugin_admin_tabs( $this->tab );
					switch ( $this->tab ) :
						case 'general' :
							$this->plugin_general_options();
							break;
						case 'info' :
							$this->plugin_info_options();
							break;
					endswitch; ?>
				</form>
			</div><?php
		}
		
		function plugin_general_options(){ ?>
			<table class="form-table">
				<tr>
					<th>
						<label for="hide_admin_bar"><?php _e('Hide admin bar for all users', $this->domain) ?></label> 
					</th>
					<td>
						<input type="checkbox" name="hide_admin_bar" value="checked" id="hide_admin_bar" <?php echo $this->settings[ $this->tab ]["hide_admin_bar"] ?>>
					</td>
				</tr>
				<tr>
					<th>
						<label for="show_admin"><?php _e('Hide admin bar for all users except admin', $this->domain) ?></label> 
					</th>
					<td>
						<input type="checkbox" name="show_admin" value="checked" id="show_admin" <?php echo $this->settings[ $this->tab ]["show_admin"] ?>>
					</td>
				</tr>
				<tr>
					<th>
						<label for="inactive_opacity"><?php _e("Admin bar opacity (inactive):", $this->domain) ?></label> 
					</th>
					<td>
						<input type="text" size="5" name="inactive_opacity" placeholder="30" value="<?php echo $this->settings[ $this->tab ]["inactive_opacity"]; ?>" id="inactive_opacity"> %
					</td>
				</tr>
				<tr>
					<th>
						<label for="active_opacity"><?php _e("Admin bar opacity on hover (active):", $this->domain) ?></label> 
					</th>
					<td>
						<input type="text" size="5" name="active_opacity" placeholder="100" value="<?php echo $this->settings[ $this->tab ]["active_opacity"]; ?>" id="active_opacity"> %
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button button-primary button-large" value="<?php _e( 'Save' ) ?>"></p><?php
		}
		
		function plugin_info_options(){ ?>
			<p><?php _e('Created by KubiQ', $this->domain); ?></p>
			<a href="https://kubiq.sk" target="_blank"><?php _e('Contact us'); ?></a><?php
		}
	}
}

if (class_exists('admin_bar')) { 
	$admin_bar_var = new admin_bar();
} ?>