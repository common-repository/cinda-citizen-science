<?php
namespace cinda;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\CindaQuery;
use \cinda\API\API;
use \cinda\CPT\Campaign as CPT_Campaign;
use \cinda\CPT\Contribution as CPT_Contribution;
use \cinda\CPT\Volunteer as CPT_Volunteer;
use \cinda\CPT\Dictionary as CPT_Dictionary;
use \cinda\API\Tracking;
use \cinda\API\Campaign as API_Campaign;
use \cinda\API\Volunteer as API_Volunteer;
use \cinda\CindaShortcodes;

class Cinda{

	private static $instance = null;

	private $theme_uri;
	private $plugin_uri;
	private $options = array();

	private $CindaAPI;
	private $CindaAPP;
	private $CindaExport;
	private $CindaShortcodes;

	public static function init(){

		if(is_null(self::$instance) || !(self::$instance instanceof Cinda))
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Constuctor
	 */
	function __construct(){

		$this->load_classes();

		$this->theme_uri = get_template_directory();
		$this->plugin_uri = CINDA_DIR;

		$this->options = get_option(CINDA_PREFIX . 'options');

		// Create CPTs
		$CampaignCPT = new CPT_Campaign();
		$VolunteerCPT = new CPT_Volunteer();
		$ContributionCPT = new CPT_Contribution();
		$DictionaryCPT = new CPT_Dictionary();


		// Initialize API
		$this->CindaAPI = new API();
		// Initialize CSV-Export
		$this->CindaExport = new CindaCSV();
		// Initialize Shortcodes
		$this->CindaShortcodes = new CindaShortcodes();

		$this->actualize();

		add_action( 'admin_enqueue_scripts', array($this,'admin_resources') );
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action('admin_init', array($this,'send_push') );
	}

	function set_option($key, $value, $group=null){
		if( is_null( $group ) || empty( $group ) ){
			$this->options[$key] = $value;
		}else{
			$group = sanitize_text_field($group);

			if(!isset($this->options[$group]))
				$this->options[$group] = array();
			elseif(!is_array($this->options[$group])){
				$tmp = $this->options[$group];
				$this->options[$group] = array($tmp);
			}

			$this->options[$group][$key] = $value;
		}
	}

	function theme_uri(){
		return $this->theme_uri;
	}

	function plugin_uri(){
		return $this->plugin_uri;
	}

	function get_api(){
		return $this->CindaAPI;
	}

	function actualize(){

		$actualize = false;

		/* ---------  VERSION 1.1.3 --------- */
		if(get_option('CINDA_DATABASE_VERSION')){
			delete_option('CINDA_DATABASE_VERSION');
		}

		if(get_option('cinda_permalinks')){
			delete_option('cinda_permalinks');
		}

		if(isset($this->options['database_version'])){
			$this->options['database']['version'] = $this->options['database_version'];
			unset($this->options['database_version']);

			$actualize = true;
		}

		if(isset($this->options['server_name'])){
			if(!isset($this->options['server']['name']))
				$this->options['server']['name'] = $this->options['server_name'];
			unset($this->options['server_name']);

			$actualize = true;
		}

		if(isset($this->options['server_description'])){
			if(!isset($this->options['server']['description']))
				$this->options['server']['description'] = $this->options['server_description'];
			unset($this->options['server_description']);

			$actualize = true;
		}

		if(isset($this->options['server_url'])){
			if(!isset($this->options['server']['url']))
				$this->options['server']['url'] = $this->options['server_url'];
			unset($this->options['server_url']);

			$actualize = true;
		}

		if(isset($this->options['google_map_API'])){
			if(!isset($this->options['gmaps']['api']))
				$this->options['gmaps']['api'] = $this->options['google_map_API'];
			unset($this->options['google_map_API']);

			$actualize = true;
		}

		if(isset($this->options['gmap'])){
			if(!isset($this->options['gmaps']))
				$this->options['gmaps'] = $this->options['gmap'];
			unset($this->options['gmap']);

			$actualize = true;
		}

		if(get_option(CINDA_PREFIX.'server_name')){

			if(!isset($this->options['server']['name']))
				$this->options['server']['name'] = get_option(CINDA_PREFIX.'server_name');

			delete_option(CINDA_PREFIX.'server_name');

			$actualize = true;
		}


		if(get_option(CINDA_PREFIX.'server_description')){
			if(!isset($this->options['server']['description']))
				$this->options['server']['description'] = get_option(CINDA_PREFIX.'server_description');

			delete_option(CINDA_PREFIX.'server_description');

			$actualize = true;
		}

		if(get_option(CINDA_PREFIX.'server_url')){
			if(!isset($this->options['server']['url']))
				$this->options['server']['url'] = get_option(CINDA_PREFIX.'server_url');

			delete_option(CINDA_PREFIX.'server_url');

			$actualize = true;
		}

		if(get_option(CINDA_PREFIX.'gmap_API')){
			if(!isset($this->options['gmaps']['api']))
				$this->options['gmaps']['api'] = get_option(CINDA_PREFIX.'gmap_API');

			delete_option(CINDA_PREFIX.'gmap_API');

			$actualize = true;
		}

		if(get_option(CINDA_PREFIX.'notification_parse_url')){
			if(!isset($this->options['notification']['parse']['url']))
				$this->options['notification']['parse']['url'] = get_option(CINDA_PREFIX.'notification_parse_url');
			delete_option(CINDA_PREFIX.'notification_parse_url');

			$actualize = true;
		}

		if("" != get_option(CINDA_PREFIX.'notification_parse_app_id')){

			if(!isset($this->options['notification']['parse']['app_id']))
				$this->options['notification']['parse']['app_id'] = get_option(CINDA_PREFIX.'notification_parse_app_id');

			delete_option(CINDA_PREFIX.'notification_parse_app_id');

			$actualize = true;
		}

		if("" != get_option(CINDA_PREFIX.'notification_parse_app_key')){

			if(!isset($this->options['notification']['parse']['app_key']))
				$this->options['notification']['parse']['app_key'] = get_option(CINDA_PREFIX.'notification_parse_app_key');

			delete_option(CINDA_PREFIX.'notification_parse_app_key');

			$actualize = true;
		}

		if("" != get_option(CINDA_PREFIX.'notification_parse_client_key')){

			if(!isset($this->options['notification']['parse']['client_key']))
				$this->options['notification']['parse']['client_key'] = get_option(CINDA_PREFIX.'notification_parse_client_key');

			delete_option(CINDA_PREFIX.'notification_parse_client_key');

			$actualize = true;
		}


		if($actualize)
			$this->save_options();
	}

	function get_options($option=null){

		if(!is_null($option)){
			if(isset($this->options[$option]))
				return($this->options[$option]);
			else
				return false;
		}

		return $this->options;
	}

	/**
	 * Save options to database
	 */
	function save_options(){
		return update_option(CINDA_PREFIX . 'options', $this->options);
	}


	/**
	 * Instalation of plugin
	 */
	static function install(){
		$Cinda = get_cinda();
		$cinda_options = $Cinda->get_options();

		if(!isset($cinda_options['database']['version']) || intval( str_replace('.','', $cinda_options['database']['version'])) < intval( str_replace('.','', CINDA_DATABASE_VERSION))){
			// Create SQL Tables
			CindaQuery::create_tables();
			// Set Database Version
			$Cinda->set_option('version', CINDA_DATABASE_VERSION, 'database');
		}

		if(!isset($cinda_options['app']['page_id']) || !is_numeric($cinda_options['app']['page_id']) || !get_page($cinda_options['app']['page_id']) ){
			$app_page = wp_insert_post(
				array(
					'post_title' => 'Cinda App',
					'post_name' => 'cinda',
					'post_type' => 'page',
					'post_status' => 'publish',
				)
			);
			if($app_page)
				$Cinda->set_option('page_id', $app_page, 'app');
		}

		// Flush Rewrite Rules
		flush_rewrite_rules( true );

		// Save Options
		$Cinda->save_options();

		// Add Role
		if(! get_role(CINDA_PREFIX.'volunteer')){
			add_role(
		    CINDA_PREFIX.'volunteer',
		    __('Volunteer','Cinda'),
		    array(
		      'read'         => true,  // true allows this capability

					'read_'.CINDA_PREFIX.CPT_Contribution::$name => true,					// Read own contributions
		      'edit_'.CINDA_PREFIX.CPT_Contribution::$name   => true,				// Edit own contributions
		      'delete_'.CINDA_PREFIX.CPT_Contribution::$name => false, 			// Delete own contributions (false)
					'edit_'.CINDA_PREFIX.CPT_Contribution::$name.'s' => true,			// List contributions
					'publish_'.CINDA_PREFIX.CPT_Contribution::$name.'s' => true, 	// Publicar contribuciones

					'read_'.CINDA_PREFIX.CPT_Volunteer::$name => true,							// Read own volunteer card
		      'edit_'.CINDA_PREFIX.CPT_Volunteer::$name   => true,						// Edit own volunteer card
		      'delete_'.CINDA_PREFIX.CPT_Volunteer::$name => false, 					// Delete own volunteer card (false)
					'edit_'.CINDA_PREFIX.CPT_Volunteer::$name.'s' => false,					// Create volunteers (false)
					'publish_'.CINDA_PREFIX.CPT_Contribution::$name.'s' => false, 	// Publish volunteer card (false)
		    )
			);
		}

		// Redirect to Option page.
		wp_redirect( cinda_options_URL() );

	}

	/**
	 * Generate Admin menu
	 */
	function admin_menu() {
		// @source: https://codex.wordpress.org/Function_Reference/add_menu_page
		add_menu_page(
			__('CINDA: Volunteers Networks','Cinda'),
			__('CINDA: Volunteers Networks','Cinda'),
			'manage_options',
			CINDA_PREFIX."menu",
			array($this,'welcome_page'),
			CINDA_URL.'/assets/images/icon.png',
			3
		);
		// @source: https://codex.wordpress.org/Function_Reference/add_submenu_page
		add_submenu_page(
			CINDA_PREFIX."menu",
			__('About CINDA','Cinda'),
			__('About CINDA','Cinda'),
			'manage_options',
			CINDA_PREFIX."about",
			array($this,'welcome_page')
		);
		// @source: https://codex.wordpress.org/Function_Reference/add_submenu_page
		add_submenu_page(
			CINDA_PREFIX."menu",
			__('Configuration','Cinda'),
			__('Configuration','Cinda'),
			'manage_options',
			CINDA_PREFIX."options",
			array($this,'options_page')
		);
		// @source: https://codex.wordpress.org/Function_Reference/add_submenu_page
		add_submenu_page(
			CINDA_PREFIX."menu",
			__('CSV Export','Cinda'),
			__('CSV Export','Cinda'),
			'manage_options',
			'export',
			array($this, 'export_page')
		);
		// @source: https://codex.wordpress.org/Function_Reference/add_submenu_page
		add_submenu_page(
			CINDA_PREFIX."menu",
			__('Notifications','Cinda'),
			__('Notifications','Cinda'),
			'manage_options',
			'push',
			array($this, 'push_page')
		);
		// @source: https://codex.wordpress.org/Function_Reference/add_submenu_page
		add_submenu_page(
			null,
			__('Tracking','Cinda'),
			__('Tracking','Cinda'),
			'manage_options',
			CINDA_PREFIX . 'tracking',
			array($this, 'tracking_page')
		);
	}

	/**
	 * Load CSV Export view
	 */
	function export_page(){
		include( CINDA_DIR . 'assets/views/cinda/page_export.php' );
	}

	/**
	 * Load options view
	 */
	function options_page(){
		include( CINDA_DIR . 'assets/views/cinda/page_options.php' );
	}

	/**
	 * Load welcome view
	 */
	function welcome_page(){
		include( CINDA_DIR . 'assets/views/cinda/page_welcome.php' );
	}

	/**
	 * Load CSV Export view
	 */
	function push_page(){
		global $wpdb;
		$campaigns = $wpdb->get_results("SELECT p.ID AS id, p.post_title AS title FROM ".$wpdb->prefix."posts AS p WHERE p.post_type = '".CINDA_PREFIX."campaign' AND p.post_status = \"publish\";", ARRAY_A);
		include( CINDA_DIR . 'assets/views/cinda/page_push.php' );
	}

	/**
	 * Load welcome view
	 */
	function tracking_page(){

		if(Tracking::exists($_GET['id']))
			$tracking = new Tracking($_GET['id']);
		else
			return;

		$campaign_name = (new API_Campaign( $tracking->get_idCampaign() ))->title;
		$author_name = (new API_Volunteer( $tracking->get_idVolunteer() ))->nickname;

		include( CINDA_DIR . 'assets/views/cinda/page_tracking.php' );
	}

	/**
	 * Send the Push
	 */
	function send_push(){

		if( !is_user_logged_in() || !current_user_can('manage_options') || !isset( $_POST[CINDA_PREFIX.'push_action'] ) )
			return 0;

		if( "parse" == sanitize_text_field( $_POST[CINDA_PREFIX.'push_action'] ) ){

			$data = array();
			$where = array();
			$devices = array();

			if(isset($_POST[CINDA_PREFIX."push_send_to_cid"]) && !empty($_POST[CINDA_PREFIX."push_send_to_cid"])){
				global $wpdb;
				$devices = $wpdb->get_col( $wpdb->prepare("SELECT m.meta_value FROM ".$wpdb->prefix."post_meta WHERE m.meta_key = '".CINDA_PREFIX."endpoint' AND m.post_id IN (SELECT id_volunteer FROM ".CINDA_TABLE_SUSCRIPTIONS_NAME." WHERE id_campaign = ". intval( sanitize_text_field( $_POST[CINDA_PREFIX."push_send_to_cid"] ) ).")"));

				if(!is_array($devices))
					$devices = array();
			}

			if(isset($_POST[CINDA_PREFIX."push_title"]))
				$data['alert'] = sanitize_text_field( $_POST[CINDA_PREFIX."push_title"] );

			if(isset($_POST["pushmessage"]))
				$data["content"] = sanitize_text_field( $_POST["pushmessage"] );

			if(isset($_POST[CINDA_PREFIX."push_active_campaign"]) && isset($_POST[CINDA_PREFIX."push_cid"]))
				$data['cid'] = intval( sanitize_text_field( $_POST[CINDA_PREFIX."push_cid"] ) );

			$result = sendPush($data, $devices);

		}

	}

	/**
	 * Register styles and javascript for the plugin
	 */
	function admin_resources() {

		/* -- JavaScript -- */
		// jQuery Ui
		wp_enqueue_script('	jquery-ui-core');
		// WP Color Picker
		wp_enqueue_script( 'wp-color-picker');
		// Select2
		wp_register_script('select2-js',plugins_url( 'cinda-citizen-science/assets/js/select2.full.min.js' ), array('jquery'), '4.0.1-rc.1');
		wp_enqueue_script('select2-js');
		// Api Google Maps
		if(isset($this->get_options('gmaps')['api'])){
			wp_register_script("api-google-map-v3", "https://maps.googleapis.com/maps/api/js?key="); // &callback=initMap .$this->get_options('gmaps')['api']
			wp_enqueue_script("api-google-map-v3");
		}
		// Custom javascript
		wp_register_script(CINDA_PREFIX . 'scripts',plugins_url( 'cinda-citizen-science/assets/js/custom.js' ), array('jquery'), '1.0.0', true);
		wp_enqueue_script(CINDA_PREFIX . 'scripts');


		/* -- Cascade Style Sheet -- */
		// WP Color Picker
		wp_enqueue_style( 'wp-color-picker');
		// Font Awesome
		wp_register_style( 'font-awesome',plugins_url( 'cinda-citizen-science/assets/css/font-awesome.min.css' ), false, '4.5.0' );
		wp_enqueue_style( 'font-awesome' );
		// Select2
		wp_register_style('select2-css',plugins_url( 'cinda-citizen-science/assets/css/select2.min.css' ), false, '1.0.0' );
		wp_enqueue_style( 'select2-css' );
		// Custom CSS
		wp_register_style( CINDA_PREFIX . 'admin_css',plugins_url( 'cinda-citizen-science/assets/css/admin-style.css' ), false, '1.0.0' );
		wp_enqueue_style( CINDA_PREFIX . 'admin_css' );
	}

	/**
	 * Load all classes required for the functioning of the plugin
	 */
	function load_classes(){
		// Load Cinda Class
		require_once( CINDA_DIR . 'library/CindaQuery.php' );
		require_once( CINDA_DIR . 'library/CindaCSV.php' );
		require_once( CINDA_DIR . 'library/CindaShortcodes.php' );
		// Load CPT Classes
		require_once( CINDA_DIR . 'library/CPT/Campaign.php' );
		require_once( CINDA_DIR . 'library/CPT/Dictionary.php' );
		require_once( CINDA_DIR . 'library/CPT/Contribution.php' );
		require_once( CINDA_DIR . 'library/CPT/Volunteer.php' );
		// Load API Classes
		require_once( CINDA_DIR . 'library/API/API.php' );
		require_once( CINDA_DIR . 'library/API/Campaign.php' );
		require_once( CINDA_DIR . 'library/API/CampaignsList.php' );
		require_once( CINDA_DIR . 'library/API/Contribution.php' );
		require_once( CINDA_DIR . 'library/API/ContributionList.php' );
		require_once( CINDA_DIR . 'library/API/Dictionary.php' );
		require_once( CINDA_DIR . 'library/API/Opendata.php' );
		require_once( CINDA_DIR . 'library/API/RealTime.php' );
		require_once( CINDA_DIR . 'library/API/Tracking.php' );
		require_once( CINDA_DIR . 'library/API/Volunteer.php' );
		require_once( CINDA_DIR . 'library/API/VolunteerList.php' );
	}

	/**
	 * Save global options
	 * @return 1 true | 0 false
	 */
	function save_options_old(){

		if( !empty($_POST) ){

			// SERVER NAME
			if( isset($_POST[CINDA_PREFIX.'server_name']) && !empty($_POST[CINDA_PREFIX.'server_name']))
				$this->options['server']['name'] = sanitize_text_field( $_POST[CINDA_PREFIX.'server_name'] );

			// SERVER DESCRIPTION
			if( isset($_POST[CINDA_PREFIX.'server_description']) && !empty($_POST[CINDA_PREFIX.'server_description']))
				$this->options['server']['description'] = sanitize_text_field( $_POST[CINDA_PREFIX.'server_description'] );

			// SERVER URL
			if( isset($_POST[CINDA_PREFIX.'server_url']) && !empty($_POST[CINDA_PREFIX.'server_url']))
				$this->options['server']['url'] = esc_url( $_POST[CINDA_PREFIX.'server_url'] );

			// GOOGLE MAPS API
			if( isset($_POST[CINDA_PREFIX.'gmap_API']) && !empty($_POST[CINDA_PREFIX.'gmap_API']))
				$this->options['gmaps']['api'] = sanitize_text_field( $_POST[CINDA_PREFIX.'gmap_API'] );


			// Options of notifications
			// PARSE URL
			if( isset($_POST[CINDA_PREFIX.'notification_parse_url']) && !empty($_POST[CINDA_PREFIX.'notification_parse_url']))
				$this->options['notification']['parse']['url'] = esc_url( $_POST[CINDA_PREFIX.'notification_parse_url']);
			// PARSE APP ID
			if( isset($_POST[CINDA_PREFIX.'notification_parse_app_id']) && !empty($_POST[CINDA_PREFIX.'notification_parse_app_id']))
				$this->options['notification']['parse']['app_id'] = sanitize_text_field($_POST[CINDA_PREFIX.'notification_parse_app_id']);
			// PARSE APP KEY
			if( isset($_POST[CINDA_PREFIX.'notification_parse_app_key']) && !empty($_POST[CINDA_PREFIX.'notification_parse_app_key']))
				$this->options['notification']['parse']['app_key'] = sanitize_text_field($_POST[CINDA_PREFIX.'notification_parse_app_key']);
			// PARSE CLIENT KEY
			if( isset($_POST[CINDA_PREFIX.'notification_parse_client_key']) && !empty($_POST[CINDA_PREFIX.'notification_parse_client_key']))
				$this->options['notification']['parse']['client_key'] = sanitize_text_field($_POST[CINDA_PREFIX.'notification_parse_client_key']);


			// USE WORDPRESS OPTIONS
			if( isset($_POST[CINDA_PREFIX.'wp_users']) && $_POST[CINDA_PREFIX.'wp_users'])
				$this->options['others']['wp_users'] = 1;
			else
				$this->options['others']['wp_users'] = 0;

			$this->save_options();

			return 1;
		}

		return 0;

	}

}
