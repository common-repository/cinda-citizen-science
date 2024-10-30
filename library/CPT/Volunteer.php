<?php

namespace cinda\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use cinda\API\Volunteer as API_Volunteer;

class Volunteer{

	public $args = array();
	public static $name = "volunteer";
	private $fields = array(
		'name',
		'surname',
		'email',
		'device_id',
		'wp_user_id'
	);

	/**
	 * Function construct
	*/
	 function __construct(){
		$this->args = array(
			'labels' => array(
				'name' => __( 'Volunteers' ,'Cinda'),
				'singular_name' => __( 'Volunteer' ,'Cinda'),
				'add_new' => __( 'New Volunteer','Cinda'),
				'add_new_item' => __( 'Add new Volunteer','Cinda')
			),
			'description' => "",
			'public' => true,
			'has_archive' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'show_in_menu' => 'edit.php?post_type='. self::$name,
			'menu_position' => 8,
			'menu_icon' => 'dashicons-clipboard',
			'supports' => array(
				'title'
			),
			/*'capabilities' => array(
      	'create_posts' => true, //"edit_".CINDA_PREFIX.self::$name."s",
				'edit_post' => true, // "edit_".CINDA_PREFIX.self::$name,
      ),*/
			'permalink_epmask'	 => 'volunteer',
			'rewrite' => array(
				'slug' => 'volunteer',
			)
		);
		// Actions
		add_action( 'init', array($this,'register') );
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action( 'save_post', array($this,"save_meta"), 10, 3);
	}


	/**
	 * Register the custom post type
	 */
	public function register(){
		// Register post type
		register_post_type(CINDA_PREFIX . self::$name, $this->args );

		// Generate metabox (Formulario)
		add_action( 'add_meta_boxes', array($this,'metaboxes') );

	}

	/**
	 * Create metabox
	 */
	public function metaboxes(){
		add_meta_box(CINDA_PREFIX . self::$name . '_metabox', __('Profile Data','Cinda'), array($this,'create_form'), CINDA_PREFIX . self::$name, 'advanced', 'default');
	}

	/**
	 * Create form
	 */
	public function create_form($post){
		$fields = get_post_meta( $post->ID );
		$volunteer = new API_Volunteer( $post->ID );
		//$volunteer->save(); // ¿?
		include(CINDA_DIR.'assets/views/volunteer/profile_data_table.php');
	}


	/**
	 * Add submenu page
	 */
	function admin_menu() {
		add_submenu_page(
				CINDA_PREFIX."menu", 																// Parent slug //'edit.php?post_type='. self::$name,
				__('Volunteers','Cinda'), 													// Page title
				__('Volunteers','Cinda'),														// Menu title
				'manage_options',																		// Capability
				'edit.php?post_type='.CINDA_PREFIX.self::$name			// Slug
		);
	}

	/**
	 * Save meta
	 * @param int $post_id
	 */
	function save_meta($post_id){

		$post_type = get_post_type( $post_id );

		if(isset($_POST) && !empty($_POST) && CINDA_PREFIX.self::$name == $post_type){

			// NAME
			if(isset($_POST[CINDA_PREFIX."nickname"]) && !empty($_POST[CINDA_PREFIX."nickname"]) ){
				update_post_meta($post_id, CINDA_PREFIX."nickname", sanitize_text_field( $_POST[CINDA_PREFIX."nickname"] ));
			}

			// EMAIL
			if(isset($_POST[CINDA_PREFIX."email"]) && !empty($_POST[CINDA_PREFIX."email"])){
				update_post_meta($post_id, CINDA_PREFIX."email", sanitize_email( $_POST[CINDA_PREFIX."email"] ));
			}

			// DEVICE ID
			if(isset($_POST[CINDA_PREFIX."device-id"]) && !empty($_POST[CINDA_PREFIX."device-id"])){
				update_post_meta($post_id, CINDA_PREFIX."device-id", sanitize_text_field( $_POST[CINDA_PREFIX."device-id"] ));
			}

			// SET WP USER ID
			if(isset($_POST[CINDA_PREFIX."wp_user_id"]) && !empty($_POST[CINDA_PREFIX."wp_user_id"])){
				update_post_meta($post_id, CINDA_PREFIX."wp_user_id", intval($_POST[CINDA_PREFIX."wp_user_id"]));
			}

		}

	}
}
