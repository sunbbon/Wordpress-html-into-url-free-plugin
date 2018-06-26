<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

class JN_Html_In_Url {
	/**
	 *  JN_Html_In_Url class instance
	 *
	 *  @var protected static $instance
	 */
	public $instance;

	/**
	 * Array of selected post type for url change
	 *
	 * @var $selected_post_type
	 */
	private $selected_post_type;
	/**
	 * __construct JN_Html_In_Url class constructor
	 */
	function __construct() {
		add_action( 'init', array( $this, 'jn_htmlInUrl_page_permalink' ), -1 );
		register_activation_hook( __FILE__, array( $this, 'jn_htmlInUrl_active' ) );
		register_deactivation_hook( __FILE__, array( $this, 'jn_htmlInUrl_deactive' ) );
		add_filter( 'user_trailingslashit', array( $this, 'jn_htmlInUrl_page_slash' ),66,2 );
		$this->selected_post_type = get_option( 'jn_htmlinurl_post_types' );
		if ( ! is_array( $this->selected_post_type ) ) {
			$this->selected_post_type = array();
		}
		add_filter( 'redirect_canonical', '__return_false' );
		add_filter( 'rewrite_rules_array', array( $this, 'jn_htmlinurl_rewrite_rules' ) );
		add_filter( 'post_type_link',array( $this, 'jn_htmlinurl_cpt_url' ), 10, 1 );
		add_filter('user_trailingslashit', array($this,'jn_htmlinurl_trailingslashit'),66,2);
		add_filter( 'post_link', array($this, 'append_query_string'), 10, 3 );
		add_filter('register_post_type_args', array($this,'post_to_blog'), 10, 2);
	}



	/**
	 * Function to add .html at the end of file.
	 */
	function jn_htmlInUrl_page_permalink() {
		global $wp_rewrite;
		if ( in_array( 'page', $this->selected_post_type ) ) {
			if ( ! strpos( $wp_rewrite->get_page_permastruct(), '.html' ) ) {
				$wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
			}
		}
		$wp_rewrite->flush_rules();
	}

	/**
	 * Conditionally adds a trailing slash if the permalink structure has a trailing slash, strips the 
	 */
	function jn_htmlInUrl_page_slash( $string, $type ) {
		global $wp_rewrite;
		if ( in_array( $type, $this->selected_post_type ) ) {
			if ( $wp_rewrite->using_permalinks() && true === $wp_rewrite->use_trailing_slashes && 'page' === $type ) {
				$string = untrailingslashit( $string );
			}
		}
		return $string;
	}

	/**
	 * Function to get call when Plugin get activated
	 */
	function jn_htmlInUrl_active() {
		global $wp_rewrite;
		if ( in_array( 'page', $this->selected_post_type ) ) {
			if ( ! strpos( $wp_rewrite->get_page_permastruct(), '.html' ) ) {
				$wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
			}
		}
		$wp_rewrite->flush_rules();
	}

	/**
	 * Function to get call when Plug in get deactivated
	 */
	function jn_htmlInUrl_deactive() {
		global $wp_rewrite;
		if ( in_array( 'page', $this->selected_post_type ) ) {
			$wp_rewrite->page_structure = str_replace( '.html','',$wp_rewrite->page_structure );
			$wp_rewrite->flush_rules();
		}
	}

	/**
	 * Add rewrite rules for all post, Custom Post Type
	 */
	function jn_htmlinurl_rewrite_rules( $rules ) {
		$new_rules = array();
		if ( isset( $this->selected_post_type ) && ! empty( $this->selected_post_type ) ) {
			$post_type_array = $this->selected_post_type;
			$exclude = array( 'page' );
			$post_types = array_diff( $post_type_array, $exclude );
			foreach ( $post_types as $key => $value ) {
				$new_rules[ $value . '/([^/]+)\.html$' ] = 'index.php?post_type=' . $value . '&name=$matches[1]';
			}
		}
		$new_rules = $new_rules + $rules;
		return $new_rules;
	}
	function post_to_blog($args, $post_type){
	    if ($post_type == 'post'){
	        $args['rewrite']['slug'] = 'blog';
	        $args['rewrite']['with_front'] = false;
	    }
	    return $args;
	}

	/**
	 * Add .html in custom post URL.
	 */
	function jn_htmlinurl_cpt_url( $post_link ) {
		global $post;
		if ( isset( $post->ID ) && ! empty( $post->ID ) ) {
			if ( isset( $this->selected_post_type ) && ! empty( $this->selected_post_type ) ) {
				$post_type_array = $this->selected_post_type;
				$type = get_post_type( $post->ID );
				$exclude = array( 'page' );
				$post_types = array_diff( $post_type_array, $exclude );
				if ( in_array( $type, $post_types ) ) {
					$post_link = home_url( $type . '/' . $post->post_name . '.html' );
				}
			}
		}
		return $post_link;
	}
	function append_query_string( $url, $post, $leavename ) {
		if ( isset( $this->selected_post_type ) && ! empty( $this->selected_post_type ) ) {
			$post_type_array = $this->selected_post_type;
			$type = get_post_type( $post->ID );
			$exclude = array( 'page' );
			$post_types = array_diff( $post_type_array, $exclude );
			if ( in_array( $type, $post_types ) ) {
				$url = home_url( user_trailingslashit( "$post->post_name".".html" ) );
			}
		}
		return rtrim($url ,'/');
	}
	function jn_htmlinurl_trailingslashit($string, $type){
		global $wp_rewrite;
		if ($wp_rewrite->using_permalinks() && $wp_rewrite->use_trailing_slashes==true && $type == 'page'){
			return untrailingslashit($string);
		}else{
			return $string;
		}
	}

}
