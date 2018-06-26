<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Jn_Html_In_Url_Settings to add admin page setting for plugin.
 */
class Jn_Html_In_Url_Settings {

	/**
	 *  Jn_Html_In_Url_Settings class instance
	 */
	private static $instance;

	/**
	 * __construct Jn_Html_In_Url_Settings class constructor
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'jn_htmlInUrl_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'jn_enque_setting_style' ) );
	}

	
	/**
	 * Add style for setting form
	 */
	function jn_enque_setting_style() {
		wp_enqueue_style( 'jn_setting_style', plugins_url( 'css/style.css', __FILE__ ) );
	}

	/**
	 * Function to create setting page URL for plugin.
	 *
	 * @return String Setting page URL
	 */
	function get_setting_page_url() {
		$query_args = array( 'page' => 'jn-htmlInUrl-settings' );
		$admin_settings_url  = admin_url( 'options-general.php' );
		return add_query_arg( $query_args, $admin_settings_url );
	}

	/**
	 * Add setting page
	 */
	function jn_htmlInUrl_admin_menu() {
		add_options_page( 'Jn Html in Url Settings', 'jn .html In Url', 'manage_options', 'jn-htmlInUrl-settings', array( $this, 'jn_htmlInUrl_settings_callback' ) );
	}

	/**
	 * HTML In Url setting page.
	 */
	function jn_htmlInUrl_settings_callback() {
		if ( isset( $_POST['jn_save_setting'] ) && ! empty( $_POST['jn_save_setting'] ) ) {
			$selected_post_type = array();
			if ( isset( $_POST['jn_htmlinurl_post_types'] )  && ! empty( $_POST['jn_htmlinurl_post_types'] ) ) {
				$selected_post_type = wp_unslash( $_POST['jn_htmlinurl_post_types'] );
			}
			global $wp_rewrite;
			if ( ! empty( $selected_post_type ) && in_array( 'post', $selected_post_type ) ) {
				$permalink_structure = get_option( 'permalink_structure' );
				if ( ! empty( $permalink_structure ) ) {
					if ( ! strpos( $permalink_structure , '.html' ) ) {
						update_option( 'old_permalink_structure', $permalink_structure );
					}
					$permalink_structure = explode( '/', $permalink_structure );
					$total_element = count( $permalink_structure );
					if ( isset( $permalink_structure[ $total_element - 1 ] ) && empty( $permalink_structure[ $total_element - 1 ] ) ) {
						unset( $permalink_structure[ $total_element - 1 ] );
						$permalink_structure = implode( '/', $permalink_structure );
						$permalink_structure .= '.html';
						update_option( 'permalink_structure', $permalink_structure );
					}
				}
			} else {
				$old_permalink_structure = get_option( 'old_permalink_structure' );
				$permalink_structure = get_option( 'permalink_structure' );
				if ( ! empty( $old_permalink_structure ) && strpos( $permalink_structure , '.html' ) ) {
					update_option( 'permalink_structure', $old_permalink_structure );
				}
			}
			$wp_rewrite->flush_rules();

			update_option( 'jn_htmlinurl_post_types', $selected_post_type );
		}
		?>
		<div class='container'>
			<form method='post'>
			<?php
				$args = array('public'   => true, '_builtin' => false, );
				$post_types = get_post_types( $args );
				$restricted_post_types = array( 'post', 'page' );
				$post_types = array_merge( $restricted_post_types, $post_types );
			if ( ! empty( $post_types ) ) {
				$selected_post_type = get_option( 'jn_htmlinurl_post_types' );
				echo '<style>.jn_post_types_lists li{display:inline-block;width:150px; margin-right:10px;}.jn_post_types_lists li input{vertical-align:bottom;}</style>';
				echo "<div><h2>Select post type</h2><ul  class='jn_post_types_lists'>";
				foreach ( $post_types as $post_type ) {
					$checked = '';
					if ( ! empty( $selected_post_type ) && in_array( $post_type, $selected_post_type ) ) {
						$checked = 'checked';
					}
					$post_type_name = strtoupper( $post_type );
					$post_type_name = str_replace( '_', ' ', $post_type_name );
					echo '<li>';
						echo '<input type="checkbox" ' . esc_html( $checked ) . ' name="jn_htmlinurl_post_types[ ]" value="' . esc_html( $post_type ) . '">';
						echo '<label>' . esc_html( $post_type_name ) . '</Label>';
					echo '</li>';
				}
				echo '</ul></div>';
				echo '<div> <input type="submit" name="jn_save_setting" class="button  button-primary" value="Save"> </div>';
			}
			?>
			</form>
		</div>
	<?php }
}
