<?php

namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\CPT\Campaign as CPT_Campaign;
use \cinda\API\Campaign as API_Campaign;
use \cinda\API\Volunteer as API_Volunteer;
use WP_Query;

class CampaignsList{
	
	public $campaigns = array();
	private $sql;
	
	/**
	 * Constructor
	 * @param unknown $args
	 */
	function __construct( $args = array() ){
		global $wpdb;
		
		$args_defaults = array(
			'token' => null,
			'extended' => false,
			'date_start' => array("CURDATE()", "<="),
			'date_end' => array("CURDATE()", ">="),
			'all' => false,
		);
		
		if(!empty( $args )){
			
			if( !isset($args['all']) || $args['all'] != true ){
				if( isset($args['date_start']) && !empty( $args['date_start'] ) )
					$args['date_start'] = array("STR_TO_DATE('".$args['date_start']."','%Y-%m-%d')",">=");
	
				if( isset($args['date_end']) && !empty( $args['date_end'] ) )
					$args['date_end'] = array("STR_TO_DATE('".$args['date_end']."','%Y-%m-%d')","<=");
			}

		}
		
		$args = array_merge($args_defaults, $args);
		
		// Check if Volunteer is sucribed
		if( isset($args['token']) && $args['token'] ){
			if($volunteer = API_Volunteer::get_volunter_by_token( $args['token'] ) ){
				$suscriptions = $volunteer->get_suscriptions();
			}
		}
		
		$select_sql = "";
		$inner_sql = "";
		$where_sql = "";
		$order_sql = "";
		$group_sql = "";
		$limit_sql = "";
		
		if( !isset($args['all']) || $args['all'] != true){
						
			if( (isset($args['date_start']) && is_array($args['date_start']) && !empty($args['date_start']) ) || ( isset($args['date_end']) && is_array($args['date_end']) && !empty($args['date_end']) ) ){
				
				if ( isset($args['date_start']) && !empty($args['date_start']) ){
					$inner_sql .= " INNER JOIN ".$wpdb->prefix."postmeta AS m1 ON ( p.ID = m1.post_id ) ";
					$where_sql .= " AND (m1.meta_key LIKE '".CINDA_PREFIX."start_date' AND STR_TO_DATE(m1.meta_value,'%Y-%m-%d') ".$args['date_start'][1]." ".$args['date_start'][0]." ) ";
				}
				
				if ( isset($args['date_end']) && !empty($args['date_end']) ){
					$inner_sql .= " INNER JOIN ".$wpdb->prefix."postmeta AS m2 ON ( p.ID = m2.post_id ) ";
					$where_sql .= " AND (m2.meta_key LIKE '".CINDA_PREFIX."end_date' AND STR_TO_DATE(m2.meta_value,'%Y-%m-%d') ".$args['date_end'][1]." ".$args['date_end'][0]." ) ";
				}
			}
	
		}
		
		if( isset($args['orderby'])){
			
			if($args['orderby'] == "suscriptions"){
				$inner_sql .= " INNER JOIN ".CINDA_TABLE_SUSCRIPTIONS_NAME." AS s ON ( p.ID = s.id_campaign ) ";
				$order_sql .= " ORDER BY COUNT( DISTINCT s.id_volunteer )";
				$group_sql .= " GROUP BY s.id_campaign";
			}
			
		}
		
		if( isset($args['number']) && is_numeric($args['number'])){
			$limit_sql .= " LIMIT ".$args['number'];
		}
		
		$this->sql = "
				SELECT 
					p.ID 
				FROM 
					".$wpdb->prefix."posts AS p
				".$inner_sql."
				WHERE
					p.post_type LIKE '".CINDA_PREFIX."campaign' 
				AND 
					p.post_status LIKE 'publish' 
				".$where_sql."
				".$group_sql." 
				".$order_sql." 
				".$limit_sql."
				;";
		
		$campaigns = $wpdb->get_results($this->sql);
		
		foreach($campaigns as $campaign){

			$obj = new API_Campaign( $campaign->ID );
			
			if( isset($suscriptions) && in_array( $campaign->ID, $suscriptions )  )
				$obj->set_subscribed('true');
			
			$this->campaigns[] = $obj;
		}
	}
	
	/**
	 * Return the campaign of this list
	 * @return Array of \cinda\API\Campaign
	 */
	function get_campaigns(){
		return $this->campaigns;
	}
	
	/**
	 * Get the SQL query of this campaigns list
	 * @return string
	 */
	function get_sql(){
		return $this->sql;
	}
	
	
	public static function get_campaignList($args = array()){
		
		if(isset($_GET['token']))
			$args['token'] = sanitize_text_field( $_GET['token'] );
		
		return new self( $args );
		
	}
	
	/**
	 * 
	 */
	public static function campaigns_list(){
		
		$args = array();
		$campaignList = self::get_campaignList($args);
		
		echo json_encode( $campaignList->get_campaigns() );
		
	}
	
}