<?php

namespace cinda\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\API\Contribution as API_Contribution;
use \cinda\API\Tracking;

use cinda\CindaQuery;
/**
 * Classe relativa al CPT Contribution
 * @author Guille
 *
 */
class Contribution{

	public static $name = "contribution";

	private $args = array();

	/**
	 * Constructor
	 */
	function __construct(){
		// Actions
		add_action( 'init', array($this,'register') );
		add_action( 'save_post',  array($this,'update') );
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action( 'add_meta_boxes', array($this,'metaboxes') );
		add_action( 'admin_enqueue_scripts', array($this,'load_media'));
	}

	/**
	 * Register the custom post type
	 */
	public function register(){
		$this->args = array(
			'labels' => array(
				'name' => __( 'Contributions' ,'Cinda'),
				'singular_name' => __( 'Contribution' ,'Cinda'),
				'add_new' => __( 'New Contribution','Cinda'),
				'add_new_item' =>  __( 'Add new contribution','Cinda')
			),
			'description' => __("Contributions",'Cinda'),
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => 'edit.php?post_type='. CINDA_PREFIX . self::$name,
			'menu_position' => 7,
			'menu_icon' => 'dashicons-clipboard',
			'supports' => array('title','author'),
			'permalink_epmask'	 => 'contribution',
			'rewrite' => array(
				'slug' => 'contribution',
			)
		);

		// Register post type
		register_post_type( CINDA_PREFIX . self::$name, $this->args );

	}

	/**
	 * Add Contributions to Global menu
	 */
	function admin_menu() {
		add_submenu_page(
				CINDA_PREFIX."menu", // 'edit.php?post_type='. self::$name, 	// Parent slug
				__('Contributions','Cinda'), 							// Page title
				__('Contributions','Cinda'),							// Menu title
				'manage_options',						// Capability
				'edit.php?post_type='. CINDA_PREFIX . self::$name	// Slug
		);
	}

	/**
	 * Update Contribution (From WP-Admin only)
	 */
	public function update(){

		if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && sanitize_text_field( $_POST['action'] ) == 'editpost'){
			$contribution = new API_Contribution( get_the_ID() );
			$model = $contribution->get_model();

			foreach($model as $field){
				if(isset($_POST[CINDA_PREFIX.$field->field_name]))
					update_post_meta(get_the_ID(), CINDA_PREFIX.$field->field_name, sanitize_text_field( $_POST[CINDA_PREFIX.$field->field_name] ) );
			}

		}

	}

	/**
	 * Metabox with meta key and meta values
	 */
	public function metabox_attributes(){
		global $wpdb;
		// View (Contributions table)
		$contribution = new API_Contribution( get_the_ID() );
		$campaign_name = $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID = ".$contribution->id_campaign.";");
		$campaign_link =  get_edit_post_link($contribution->id_campaign);
		$volunteer_name = $contribution->data['author_name'];
		$volunteer_link = get_edit_post_link($contribution->data['author_id']);
		$tracking_link = Tracking::get_url( intval($contribution->data['tracking']) );
		$maps = 1;

		include(CINDA_DIR . 'assets/views/contribution/attributes_table.php');
	}

	/**
	 * Add meta box in Add/Edit form
	 */
	public function metaboxes(){
		global $pagenow;
		if($pagenow == 'post.php'){
			// Add METABOXES
			add_meta_box(CINDA_PREFIX . self::$name . '_attributes_table', sprintf(__('Contribution Data','Cinda')), array($this,'metabox_attributes'), CINDA_PREFIX . self::$name, 'advanced', 'default');
		}
	}

	/**
	 * Load media files needed for Uploader
	 */
	function load_media() {
		wp_enqueue_media();
	}

}
