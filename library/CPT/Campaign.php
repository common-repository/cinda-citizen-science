<?php

namespace cinda\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\API\Campaign as API_Campaign;
use \cinda\CindaCSV as Cinda_CSV;

/**
 * Classe relativa al CPT 'Campaña'
 * @author 
 *
 */
class Campaign{
	
	public $args = array();
	public static $name = 'cinda_campaign';
	private $campaign = null;
	
	/**
	 * Function construct
	 */
	public function __construct(){ 
		
		// CPT ARGS
		$this->args = array(
			'labels' => array(
				'name' => __( 'Campaigns' ,'Cinda'),
				'singular_name' => __( 'Campaign' ,'Cinda'),
				'add_new' => __( 'New Campaign','Cinda'),
				'add_new_item' =>  __( 'Add new Campaign','Cinda')
			),
			'description' => "",
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => 'edit.php?post_type='. self::$name,
			'menu_position' => 7,
			'menu_icon' => 'dashicons-clipboard',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt'
			),
			'permalink_epmask'	 => 'campaign',
			'rewrite' => array(
				'slug' => 'campaign',
			)
		);
		
		// Actions
		add_action( 'init', array($this,'register') );
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action( 'save_post', array($this,'save_model') );
		add_action( 'save_post', array($this,'save_attributes') );		
		add_action( 'add_meta_boxes', array($this,'metaboxes') );
		add_filter( 'post_updated_messages', array($this,'update_messages') );
	}
	
	/**
	 * Register the custom post type
	 */
	public function register(){  
		// Register post type
		register_post_type( self::$name, $this->args );

	}
	
	/**
	 * Create metabox
	 */
	public function metaboxes(){
		
		global $pagenow;

		if($pagenow == 'post.php'):
			// Set the campaign object if not exists
			if($this->campaign == null)
				$this->set_campaign( get_the_ID() );
			
			$n = count($this->campaign->get_contributions());
			
			// Add METABOXES
			add_meta_box(self::$name . '_model_table', __('Data-form model','Cinda'), array($this,'metabox_model'), self::$name, 'advanced', 'default');
			add_meta_box(self::$name . '_contributions_table', sprintf(__('%s Contributions','Cinda'),$n), array($this,'metabox_contributions'), self::$name, 'advanced', 'default');
			add_action('admin_footer', array($this,'activate_script'));
		endif;
		
		add_meta_box(self::$name . '_attributes_table', __('Campaign Data'), array($this,'metabox_attributes'), self::$name, 'advanced', 'default');
		
	}
	
	/**
	 * Metabox with model table
	 */
	public function metabox_model(){
		
		// Set the campaign object if not exists
		if($this->campaign == null)
			$this->set_campaign( get_the_ID() );

		$fields = $this->campaign->get_model();

		// View
		include(CINDA_DIR . 'assets/views/campaign/model_table.php');
			
	}
	
	/**
	 * Metabox with posts contributions table
	 */
	public function metabox_contributions(){
		
		// Set the campaign object if not exists
		if($this->campaign == null)
			$this->set_campaign( get_the_ID() );
		
		$fields = $this->campaign->get_model();
		$contributions = $this->campaign->get_contributions();

		// View (Contributions table)
		include(CINDA_DIR . 'assets/views/campaign/contributions_table.php');

	}
	
	/**
	 * Load Metabox view with meta key and meta values
	 */
	public function metabox_attributes(){
		// View (Contributions table)
		include(CINDA_DIR . 'assets/views/campaign/attributes_table.php');
	}
	
	/**
	 * Add Campaings to Global menu
	 */
	function admin_menu() {
		add_submenu_page(
				CINDA_PREFIX."menu", // 'edit.php?post_type='. self::$name, 	// Parent slug
				__('Campaigns','Cinda'), 							// Page title
				__('Campaigns','Cinda'),							// Menu title
				'manage_options',						// Capability
				'edit.php?post_type='. self::$name	// Slug
		);
	}
	
	/**
	 * Set campaign for CPT edit page
	 * @param int $ID
	 */
	function set_campaign($ID){
		$this->campaign = new API_Campaign( $ID );
	}
	
	/**
	 * Initialize jQuery script
	 */
	function activate_script() {
		echo '<script>
				jQuery(function(){
					jQuery("#model").cinda();
				});
			</script>';
	}

	/**
	 * Update Wordpress messages for CPT Campaign
	 * @param array $messages
	 * @return array $messages
	 */
	function update_messages( $messages ) {
		global $post, $post_ID;
	
		$messages[CINDA_PREFIX.'campaign'] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Campaign updated. <a href="%s">View</a>','Cinda'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom data model updated.','Cinda'),
				3 => __('Custom data model deleted.','Cinda'),
				4 => __('Campaign updated.','Cinda'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Campaign restored to revision from %s','Cinda'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Campaign published. <a href="%s">View campaign</a>','Cinda'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Campaign saved.','Cinda'),
				8 => sprintf( __('Campaign submitted. <a target="_blank" href="%s">Preview Campaign</a>','Cinda'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Campaign scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Campaign</a>','Cinda'),
						// translators: Publish box date format, see http://php.net/date
						date_i18n( __( 'M j, Y @ G:i' ,'Cinda'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Campaign draft updated. <a target="_blank" href="%s">Preview Campaign</a>','Cinda'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
	
		return $messages;
	}
	
	
	/**
	 * Save POST atributes
	 */
	function save_attributes(){
	
		if(isset($_POST[CINDA_PREFIX.'start_date']) && !empty($_POST[CINDA_PREFIX.'start_date']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'start_date', sanitize_text_field( $_POST[CINDA_PREFIX.'start_date'] ) );
		
		if(isset($_POST[CINDA_PREFIX.'end_date']) && !empty($_POST[CINDA_PREFIX.'end_date']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'end_date', sanitize_text_field( $_POST[CINDA_PREFIX.'end_date'] ) );
		
		if(isset($_POST[CINDA_PREFIX.'scope']) && !empty($_POST[CINDA_PREFIX.'scope']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'scope', sanitize_text_field( $_POST[CINDA_PREFIX.'scope'] ) );
		
		if(isset($_POST[CINDA_PREFIX.'color']) && !empty($_POST[CINDA_PREFIX.'color']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'color', sanitize_text_field( $_POST[CINDA_PREFIX.'color'] ) );
		
		if(isset($_POST[CINDA_PREFIX.'geoposition']) && !empty($_POST[CINDA_PREFIX.'geoposition']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'geoposition', str_replace( array("(",")"),"", sanitize_text_field( $_POST[CINDA_PREFIX.'geoposition'] ) ) );
		
		if(isset($_POST[CINDA_PREFIX.'radium']) && !empty($_POST[CINDA_PREFIX.'radium']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'radium', intval( sanitize_text_field( $_POST[CINDA_PREFIX.'radium'] ) ) );
		
		if(isset($_POST[CINDA_PREFIX.'logo_image']) && !empty($_POST[CINDA_PREFIX.'logo_image']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'logo_image', intval( sanitize_text_field( $_POST[CINDA_PREFIX.'logo_image'] ) ) );
		
		if(isset($_POST[CINDA_PREFIX.'cover_image']) && !empty($_POST[CINDA_PREFIX.'cover_image']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'cover_image', intval( sanitize_text_field( $_POST[CINDA_PREFIX.'cover_image'] ) ) );
		
		if(isset($_POST[CINDA_PREFIX.'tracking']) && !empty($_POST[CINDA_PREFIX.'tracking']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'tracking', sanitize_text_field(  $_POST[CINDA_PREFIX.'tracking'] ) );
		
		if(isset($_POST[CINDA_PREFIX.'administrator']) && !empty($_POST[CINDA_PREFIX.'administrator']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'administrator', sanitize_text_field(  $_POST[CINDA_PREFIX.'administrator'] ) );
		
		else
			update_post_meta(get_the_ID(), CINDA_PREFIX.'tracking', 0 );
		
	}
	
	/**
	 * Save Data-form Model
	 * @return void|string
	 */
	function save_model(){

		global $post;
		global $wpdb;
		
		// Only in CPT Campaign
		if (empty($post) || CINDA_PREFIX.'campaign' != $post->post_type || !isset($_POST['field'])){
			return;
		}
		
		$updated = 0;
		$inserted = 0;
		$nosave = 0;
		
		$fields = $_POST['field'];
	
		if(0 < count($fields)){
			$i=0;
			foreach ($fields as $field){
				// SI no se ha rellenado correctamente
				if($field['name'] == "" || $field['label'] == ""){
					$nosave++;
				}else{
					
					if($field['type'] == "dictionary"){
						$cinda_options = $field['dictionary'];
					}else{
						$cinda_options = $field['options'];
					}
					
					// Si tiene ID establecido: UPDATE
					if($field['field_id'] != ""){
						
						if( $wpdb->update(
							CINDA_TABLE_MODEL_NAME,
							array(
								'field_position' => $i++,
								'field_label' => sanitize_text_field( $field['label'] ),
								'field_name' => sanitize_text_field( $field['name'] ),
								'field_description' => wp_kses( $field['description'], array('a'=>array('href'=>array(),'title'=>array()),'br'=>array(),'em'=>array(),'strong'=>array(),) ),
								'field_type' => sanitize_text_field( $field['type'] ),
								'field_required' => ( isset($field['required']) && $field['required'] == "on" ) ? 1 : 0,
								'field_options' => sanitize_text_field( $cinda_options ),
							),
							array(
								'id' => $field['field_id'],
								'id_campaign' => intval( sanitize_text_field( $_POST['ID'] ) ),
							),
							array(
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s'
							),
							array(
								'%s',
								'%s'
							)
						) ){
							$updated++;
						}else{
							$nosave++;
						}
							
					}
					// SI no tiene ID: INSERT
					else{
							
						if( $wpdb->insert(
							CINDA_TABLE_MODEL_NAME,
							array(
								'id_campaign' => intval( sanitize_text_field( $_POST['ID'] ) ),
								'field_position' => $i++,
								'field_label' => sanitize_text_field( $field['label'] ),
								'field_name' => sanitize_text_field( $field['name'] ),
								'field_description' => sanitize_text_field( $field['description'] ),
								'field_type' => sanitize_text_field( $field['type'] ),
								'field_required' => (isset($field['required']) && $field['required']) | 0,
								'field_options' => sanitize_text_field( $cinda_options )
							),
							array(
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s'
							)
						) ){
							$inserted++;
						}else{
							$nosave++;
						}
							
					}
				}
			}
		}
	
		return sprintf( __('Fields updated: %b.<br />Fields inserted: %b.<br />Fields not saved: %b','Cinda'), $updated, $inserted, $nosave );
	}
		
}