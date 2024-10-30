<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \cinda\API\Contribution as API_Contribution;
use \cinda\API\Volunteer as API_Volunteer;

class ContributionList{

	public $contribution = null;
	private $sql;
	
	/**
	 * Constructor
	 * @param array $args
	 */
	function __construct($args = array()){
		global $wpdb;
		
		// ARGUMENTS ACTIONS
		$args_defaults = array();
		$args = array_merge($args_defaults, $args);
		
		// SQL ACTIONS
		$this->set_sql($args);
		
		// Get ids
		$ids = $wpdb->get_col($this->sql);
		
		if( 0 < count($ids) ){
			foreach($ids as $id){
				$this->contribution[] = new API_Contribution( $id );
			}
		}

	}

	/**
	 * Set sql query
	 * @param unknown $args
	 */
	function set_sql($args){
		global $wpdb;
		$where_sql = "";
		$inner_sql = "";
		$select_sql = "";
		
		if(isset($args['campaign']) && !empty($args['campaign']))
			$where_sql .= "AND p.post_parent = ".$args['campaign']." ";
		
		if(isset($args['volunteer']) && !empty($args['volunteer'])){
			$inner_sql .= "INNER JOIN ".$wpdb->prefix."postmeta AS m ON p.ID = m.post_id ";
			$where_sql .= "AND ( m.meta_key LIKE '".CINDA_PREFIX."author_id' AND m.meta_value = ".$args['volunteer']." ) ";
		}
		
		$this->sql = "SELECT p.ID FROM ".$wpdb->prefix."posts AS p ";
		
		$this->sql .= $inner_sql;
		
		$this->sql .= "WHERE p.post_type = '".CINDA_PREFIX."contribution' ";
	
		$this->sql .= $where_sql;
		
		$this->sql .= " ORDER BY p.post_date DESC";
	
	}
	
	/**
	 * Get contributions of this list
	 * @return \cinda\API\Contribution
	 */
	function get_contributions(){
		return $this->contribution;
	}

	static function get_listData($campaign_id=NULL, $volunteer_id=NULL){
		global $wp;
		$args = array();
		
		if($campaign_id)
			$args['campaign'] = $campaign_id;
			
		if($volunteer_id)
			$args['volunteer'] = $volunteer_id;
		
		$contributionList = new self($args);

		return $contributionList->get_contributions();
	}
	
	/**
	 * List contributions based on Campaign ID and/or Volunteer ID
	 */
	static function listData(){
		global $wp;
		
		if(isset($wp->query_vars['cid']) && !empty($wp->query_vars['cid']))
			$campaign_id = $wp->query_vars['cid'];
		else
			$campaign_id = NULL;
		
		// Volunteer by ID
		if(isset($wp->query_vars['vid']) && !empty($wp->query_vars['vid']))
			$volunteer_id = intval( $wp->query_vars['vid'] );
		// Volunteer by token
		else if(isset($_GET['token']) && !empty($_GET['token']))
			if($volunteer = API_Volunteer::get_volunter_by_token( sanitize_text_field( $_GET['token'] ) ))
				$volunteer_id = $volunteer->get_id();
			else 
				$volunteer_id = -1;
		else
			$volunteer_id = NULL;
		
		
		// GET CONTRIBUTIONS
		$array_contributions = self::get_listData($campaign_id, $volunteer_id);
		$contributions = array();
		
		if(0 < count( $array_contributions )){
			foreach($array_contributions as $contribution){
				$contributions[] = $contribution->serialize( false );
			}
		}
		
		die( json_encode( $contributions ) );
		
	}

}