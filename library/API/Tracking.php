<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\API\Campaign as API_Campaign;
use \cinda\API\API;

class Tracking{
	
	private $id;
	private $id_campaign;
	private $id_volunteer;
	private $tracking;
	private $create_date;
	private $campaign_name;
	private $campaign_image;
	
	/**
	 * Constructor
	 * @param int $id
	 */
	function __construct($id){
		if(self::exists($id)){
			global $wpdb;
			$res = $wpdb->get_row("SELECT * FROM ".CINDA_TABLE_TRACKINGS_NAME." WHERE id = '".$id."'", ARRAY_A);
			$this->id = $res['id'];
			$this->id_campaign = $res['id_campaign'];
			$this->tracking = wp_upload_dir()['baseurl']."/trackings/" . $res['tracking'];
			$this->id_volunteer = $res['id_volunteer'];
			$this->create_date = $res['create_date'];

			$campaign = new API_Campaign( $this->id_campaign );
			$this->campaign_name = $campaign->title;
			$this->campaign_image = $campaign->image;
		}
	}
	
	/**
	 * Serialize object
	 */
	public function serialize(){
		return array(
			'id' => $this->id,
			'id_campaign' => intval( $this->id_campaign ),
			'id_volunteer' => intval( $this->id_volunteer ),
			'campaign_name' => $this->campaign_name,
			'campaign_image' => $this->campaign_image,
			'tracking' => $this->tracking,
			'create_date' => $this->create_date,
		);
	}
	
	function get_idCampaign(){
		return $this->id_campaign;
	}
	
	function get_idVolunteer(){
		return $this->id_volunteer;
	}
	
	function get_createDate(){
		return $this->create_date;
	}
	
	function get_gpx_url(){
		return $this->tracking;
	}
	
	/**
	 * Get a tracking
	 */
	static function get(){
		global $wp;
		
		if(!isset($wp->query_vars['tid']))
			die(json_encode(0));
	
		die(json_encode((new self( $wp->query_vars['tid'] ))->serialize()));
	}
	
	/**
	 * Check if trackings exists
	 * @param unknown $id
	 */
	static function exists($id){
		global $wpdb;
		$exists = $wpdb->get_var("SELECT id FROM ".CINDA_TABLE_TRACKINGS_NAME." WHERE id = '".$id."';");
		if($exists == 0)
			return false;
		else 
			return true;
	}
	
	/**
	 * Get tracking URL
	 * @param int $id
	 */
	public static function get_url($id){
		global $wpdb;
		if($id && 1 === intval($wpdb->get_var("SELECT COUNT(id) FROM ".CINDA_TABLE_TRACKINGS_NAME." WHERE id = ".$id."; ")))
			return admin_url( 'index.php?page='.CINDA_PREFIX.'tracking&id='.$id);
		else
			return false;
	}
	
	public static function get_all_trackings(){
		
		$trackings = array();
		
		$id_volunteer = 0;
		global $wpdb;
		
		if( isset($_GET['token']) ){
			$id_volunteer = Volunteer::get_volunter_id( sanitize_text_field($_GET['token']) );
		}
		
		if($id_volunteer){
			$id_trackings = $wpdb->get_col('SELECT id FROM '. CINDA_TABLE_TRACKINGS_NAME . ' where id_volunteer = ' . $id_volunteer . ' ORDER BY create_date, id ASC');
			
			if(0 < count($id_trackings)){
				foreach($id_trackings as $id){
					$tracking = new self($id);
					$trackings[] = $tracking->serialize();
				}
			}
			
		}
		
		die( json_encode($trackings) );
	}
	
	/**
	 * Save Tracking
	 * Insert if not exists or Update if exists
	 */
	static function save(){
		global $wpdb;
		
		
		// Check NONCE
		if(isset($_POST['nonce']))
			$nonce = sanitize_text_field( $_POST['nonce'] );
		else if(isset($_POST['_wpnonce']))
			$nonce = sanitize_text_field( $_POST['_wpnonce'] );
		else
			$nonce = '';
		
		if(!isset($_POST['token']))
			die( json_encode(0) );
			
		// Verify NONCE
		if( empty($nonce) || !API::verify_nonce($nonce, 'tracking_send', sanitize_text_field( $_POST['token'] ) ))
			die( json_encode(0) );
		
		// Proceed
		if(isset($_POST['id']) && !empty($_POST['id'])){
			
			$id = sanitize_text_field( $_POST['id'] );
			
			$create_date = date('Y-m-d H:i:s');
			
			// Campaign ID must exists and this can not be empty
			if(!isset($_POST['id_campaign']) || empty($_POST['id_campaign']))
				die( json_encode(0) );
			else
				$id_campaign = intval( sanitize_text_field( $_POST['id_campaign'] ) );
			
			if(!isset($_POST['token']) || empty($_POST['token']))
				die( json_encode(0) );
			else
				$id_volunteer = Volunteer::get_volunter_id( sanitize_text_field( $_POST['token'] ) );
			
			if(!$id_volunteer)
				die( json_encode(0) );
			
			// Tracking must exists and this can not be empty
			if( isset($_FILES['tracking']) && $_FILES['tracking']['error'] == 0){
				
				$tracking = $_FILES['tracking'];
				
				$file_name = '';
				if('gpx' == pathinfo($tracking['name'], PATHINFO_EXTENSION)){
					$upload_dir = wp_upload_dir()['basedir']."/trackings";
					$file_name = sanitize_file_name( $tracking['name'] );

					if (!file_exists( $upload_dir )) {
						mkdir($upload_dir, 0775, true);
					}
					
					if(!move_uploaded_file($tracking['tmp_name'], $upload_dir . "/" . $file_name)){
						die( json_encode(0) );
					}
					
				}else{
					die( json_encode(0) );
				}
			}
			
			// INsert new TRACKING
			if(!self::exists($id)){
				if( $wpdb->insert(CINDA_TABLE_TRACKINGS_NAME, array(
						'id' => $id,
						'id_campaign' => $id_campaign,
						'id_volunteer' => $id_volunteer,
						'tracking' => $file_name,
						'create_date' => $create_date,
					), array(
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
					)) 
				){
					die(json_encode(1));
				}else
					die(json_encode(0));
			}
			// Update TRACKING
			else{
				if(!isset($tracking)){
					$tracking = $wpdb->get_var( 'SELECT tracking FROM '.CINDA_TABLE_TRACKINGS_NAME.' WHERE id = '.$id.';' );
				}
				
				if($wpdb->update(
					CINDA_TABLE_TRACKINGS_NAME,
					array(
						'id_campaign' => $id_campaign,
						'id_volunteer' => $id_volunteer,
						'tracking' => $tracking,
					),
					array(
						'id' => $id
					),
					array(
						'%d',
						'%d',
						'%s',
					),
					array(
						'%s'
					))
				){
					die(json_encode(1));
				}else
					die(json_encode(0));
			}
		}else{
			die(json_encode(0));
		}
	}
}