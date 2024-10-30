<?php

namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\API\Campaign;
use \cinda\API\CampaignsList;
use \cinda\API\Dictionary;
use \cinda\API\Volunteer;
use \cinda\API\RealTime;
use \cinda\API\Tracking;

/**
 * Class CindaAPI
 * Description: Manage all endpoint of plugin.
 * Based in Pugs_API_Endopoint of Brian Fegter
 * @link http://coderrr.com/create-an-api-endpoint-in-wordpress/
 */
class API{
	
	private $prefix = 'cindaAPI/'; //'API/';
	
	protected $routing = array();

	/**
	 * Construct
	 */
	function __construct(){
		$this->set_routing();	
		add_filter('query_vars', array($this, 'add_query_vars'), 0);
		add_action('parse_request', array($this, 'sniff_request'), 0);
		add_action('init', array($this, 'add_endpoints'), 0);
	}
	
	/** Add public query vars
	 *	@param array $vars List of current public query vars
	 *	@return array $vars
	 */
	public function add_query_vars($vars){
		$vars[] = '__cindaAPI';
		$vars[] = 'format';
		$vars[] = 'action';
		$vars[] = 'class';
		$vars[] = 'cid'; // Campaign ID
		$vars[] = 'vid'; // Volunteer ID
		$vars[] = 'uid'; // User ID (Volunteer): Generally an email acount
		$vars[] = 'did'; // Dictionary ID
		$vars[] = 'tid'; // Tracking ID
		$vars[] = 'nonce_action';
		return $vars;
	}
	
	/** 
	 * Add API Endpoints
	 * Foreach hover all routes of $routing for create all rewrite rules.
	 */
	function add_endpoints(){
		if( 0 < count( $this->routing ) ){
			foreach ( $this->routing as $route ){
				add_rewrite_rule( $route['pattern'], $route['redirect'], $route['pos'] );
			}
		}
	}
	
	/**
	* Sniff
	* This is where we hijack all API requests
	* If $_GET['__cindaAPI'] is set, we kill WP and serve up pug bomb awesomeness
	* @return die if API request
	*/
	function sniff_request(){
		global $wp;
		if( isset($wp->query_vars['__cindaAPI']) && $wp->query_vars['__cindaAPI'] != ""){
			$this->handle_request();
			exit;
		}
	}
	
	/**
	 * Get Prefix of API REST
	 * @return string
	 */
	function get_prefix(){
		return $this->prefix;
	}
	
	/**
	 * Handle_request
	 * Check if class & action exists and execute it
	 */
	function handle_request(){
		global $wp;
		$class = '\cinda\API\\' . $wp->query_vars['class'];
		$action = $wp->query_vars['action'];
		$format = $wp->query_vars['format'];
		if( class_exists( $class, false ) ){
			if( is_callable( $class, $action ) ){
				
				if($format === 'json'){
					header('Content-type:application/json;charset=utf-8');
					$class::$action();
				}elseif($format === 'csv'){
					header("Content-type: text/csv");
					header("Content-Disposition: attachment; filename=export".date('Y-m-d_H-i').".csv");
					header("Pragma: no-cache");
					header("Expires: 0");
					$class::$action();
				}
				
				
			}else{
				die(json_encode(0));
			}
		}else{
			die(json_encode(0));
		}
	}
	
	/** 
	 * Return the routing array;
	 * @return array:
	 */
	function get_routing(){
		return $this->routing;
	}
	
	
	/**
	 * Set array of accepted rewrite rules (URLs)
	 */
	private function set_routing(){
		$this->routing = array(
			// Server data
			array(
				'pattern' => '^' . $this->prefix . 'server/info/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=API&action=get_server_data',
				'pos'=>'top'
			),
			// Get NONCE
			array(
				'pattern' => '^' . $this->prefix . 'nonce/([a-zA-Z0-9_]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=API&action=get_nonce&nonce_action=$matches[1]',
				'pos'=>'top'
			),
			// Listado de Camapañas
			array(
				'pattern' => '^' . $this->prefix . 'campaigns/list/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=CampaignsList&action=campaigns_list',
				'pos'=>'top'
			),
			// Campaign info
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Campaign&action=campaign_info&cid=$matches[1]',
				'pos'=>'top'
			),
			// Campaign model
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/model/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Campaign&action=campaign_model&cid=$matches[1]',
				'pos'=>'top'
			),
			// Campaign contributions
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/listData/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=ContributionList&action=listData&cid=$matches[1]',
				'pos'=>'top'
			),
			// Send contribution
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/sendData/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Campaign&action=sendData&cid=$matches[1]',
				'pos'=>'top'
			),
			// Volunteers suscribed to a campaign
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/listVolunteers/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=VolunteerList&action=volunteers_list&cid=$matches[1]',
				'pos'=>'top'
			),
			// Top volunteer suscribed to a campaign
			array(
				'pattern' => '^' . $this->prefix . 'topVolunteers/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=VolunteerList&action=volunteers_list',
				'pos'=>'top'
			),
			// Volunteer suscription
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/suscribe/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Campaign&action=campaign_suscribe&cid=$matches[1]',
				'pos'=>'top'
			),
			// Volunteer unsuscription
			array(
				'pattern' => '^' . $this->prefix . 'campaign/([0-9]+)/unsuscribe/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Campaign&action=campaign_unsuscribe&cid=$matches[1]',
				'pos'=>'top'
			),
			// Volunteer register
			array(
				'pattern' => '^' . $this->prefix . 'volunteer/register/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Volunteer&action=register_volunteer',
				'pos'=>'top'
			),
			// Volunteer activate login
			array(
				'pattern' => '^' . $this->prefix . 'volunteer/activate-login/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Volunteer&action=activate_login',
				'pos'=>'top'
			),
			// Volunteer login
			array(
				'pattern' => '^' . $this->prefix . 'login/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Volunteer&action=login_result',
				'pos'=>'top'
			),
			// Actualize volunteer endpoint
			array(
				'pattern' => '^' . $this->prefix . 'volunteer/update-endpoint/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Volunteer&action=update_endpoint',
				'pos'=>'top'
			),
			// Volunteer profile
			array(
				'pattern' => '^' . $this->prefix . 'volunteer/([0-9]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Volunteer&action=get_volunteer&vid=$matches[1]',
				'pos'=>'top'
			),
			// Volunteer contributions
			array(
				'pattern' => '^' . $this->prefix . 'volunteer/([0-9]+)/listData/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=ContributionList&action=listData&vid=$matches[1]',
				'pos'=>'top'
			),
			// Contribution data
			array(
				'pattern' => '^' . $this->prefix . 'contribution/([0-9]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Contribution&action=get_contribution&cid=$matches[1]',
				'pos'=>'top'
			),
			// Contributions
			array(
				'pattern' => '^' . $this->prefix . 'realtime/contributions/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=RealTime&action=get_contributions',
				'pos'=>'top'
			),
			// Nearby activity
			array(
				'pattern' => '^' . $this->prefix . 'realtime/nearby-activity/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=RealTime&action=get_nearbyActivity',
				'pos'=>'top'
			),
			// Watchface
			array(
				'pattern' => '^' . $this->prefix . 'realtime/watchface/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=RealTime&action=get_watchfaceData&vid=$matches[1]',
				'pos'=>'top'
			),
			// Get Dictionary by ID
			array(
				'pattern' => '^' . $this->prefix . 'dictionary/([0-9]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Dictionary&action=get_dictionary&did=$matches[1]',
				'pos'=>'top'
			),
			// Get Tracking by ID
			array(
				'pattern' => '^' . $this->prefix . 'trackings/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Tracking&action=get_all_trackings',
				'pos'=>'top'
			),
			// Get Tracking by ID
			array(
				'pattern' => '^' . $this->prefix . 'tracking/([0-9]+\-[0-9]+)/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Tracking&action=get&tid=$matches[1]',
				'pos'=>'top'
			),
			// Send Tracking
			array(
				'pattern' => '^' . $this->prefix . 'tracking/send/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Tracking&action=save',
				'pos'=>'top'
			),
			// OpenData Campaign
			array(
				'pattern' => '^' . $this->prefix . 'opendata/campaigns/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Opendata&action=get_campaigns',
				'pos'=>'top'
			),
			// OpenData Campaign
			array(
				'pattern' => '^' . $this->prefix . 'opendata/contributions/?$',
				'redirect' => 'index.php?__cindaAPI=1&format=json&class=Opendata&action=get_contributions',
				'pos'=>'top'
			),
		);
	}
	
	/**
	 * Return info about this API Service
	 */
	function get_server_data(){
		global $CINDA;
		$data = array();
		$data['name'] = $CINDA->get_options('server')['name'];
		$data['description'] = $CINDA->get_options('server')['description'];
		$data['url'] = $CINDA->get_options('server')['url'];
		$data['gmaps']['api'] = $CINDA->get_options('gmaps')['api'];
		$data['parse']['api'] = $CINDA->get_options('notification')['parse']['app_id'];
		$data['parse']['key'] = $CINDA->get_options('notification')['parse']['client_key'];
		
		die( json_encode($data) );
	}
	
	/**
	 * Return a nonce
	 */
	function get_nonce(){
		global $wp;
		
		if(!isset($_GET['token']))
			die(json_encode(0));
	
		if(!isset($wp->query_vars['nonce_action']) || empty($wp->query_vars['nonce_action']))
			die(json_encode(0));
	
		die( json_encode( self::generate_nonce( $wp->query_vars['nonce_action'], $_GET['token'] )  ) );
	}
	
	static function generate_nonce( $action, $salt ){
		$salt = sanitize_text_field( $salt );
		return wp_create_nonce( $action . "_" . $salt );
	}
	
	static function verify_nonce($nonce, $action, $salt){
		$salt = sanitize_text_field( $salt );
		return wp_verify_nonce($nonce, $action . "_" . $salt);
	}
}