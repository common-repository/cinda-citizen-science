<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use WP_Query;
use \cinda\API\Volunteer as API_Volunteer;

class VolunteerList{
	
	public $volunteers;
	private $sql;
	
	/**
	 * Constructor
	 * @param unknown $args
	 */
	function __construct( $args = array() ){
		global $wpdb;
		// ARGUMENTS ACTIONS
		$args_defaults = array(
			
		);
		$args = array_merge($args_defaults, $args);
		
		// SQL ACTIONS
		$this->set_sql($args);
	
		$this->set_volunteers();
	}
	
	/**
	 * Get volunteer list
	 * @return array \cinda\API\Volunteer
	 */
	public function get_volunteers(){
		return $this->volunteers;
	}
	
	/**
	 * Set the volunteers
	 */
	private function set_volunteers(){
		global $wpdb;
		$volunteers = $wpdb->get_results($this->sql, ARRAY_A);
		
		foreach($volunteers as $volunteer){
			$tmp_volunteer = new API_Volunteer($volunteer['id']);
			$tmp_volunteer->set_contributions($volunteer['n']);
			$this->volunteers[] = $tmp_volunteer;
		}
	}
	
	/**
	 * Set SQL Query
	 * @param array $args
	 */
	private function set_sql($args){
		global $wpdb;
		$this->sql = "SELECT DISTINCT m.meta_value AS id, COUNT(m.meta_value) AS n FROM ".$wpdb->prefix."postmeta as m WHERE m.meta_key LIKE '".CINDA_PREFIX."author_id' ";
		
		if( isset($args['campaign']) && is_numeric($args['campaign']) ){
			$this->sql .= " AND m.post_id IN (SELECT p.ID FROM ".$wpdb->prefix."posts AS p WHERE p.post_parent = ".$args['campaign']." )";
		}
		
		$this->sql .= " GROUP BY m.meta_value ORDER BY n DESC ";
		
		if(isset($args['number']) && is_numeric($args['number'])){
			$this->sql .= " LIMIT ".$args['number']." ";
		}
	}
	
	/**
	 * Get the SQL Query
	 * @return string
	 */
	private function get_sql(){
		return $this->sql;
	}
	
	/**
	 * Return Array of Volunteers
	 * @param unknown $args
	 * @return multitype:multitype:NULL
	 */
	static function get_volunteerList( $args = array() ){
		
		// POST REQUEST
		// Campaign ID
		if(isset($_POST['campaign']) && !empty($_POST['campaign']) && is_numeric($_POST['campaign']))
			$args['campaign'] = intval( sanitize_text_field( $_POST['campaign'] ) );
		// Number of results
		if(isset($_POST['number']) && !empty($_POST['number']) && is_numeric($_POST['number']))
			$args['number'] = intval( sanitize_text_field( $_POST['number'] ) );
		
		// VOLUNTEERS LIST
		$volunterList = new self($args);
		
		// Get the volunteers array
		$volunterList = $volunterList->get_volunteers();
		
		// Array for JSON Exit
		$volunteers = array();
		
		if(0 < count($volunterList)){
			foreach($volunterList as $volunteer){
				$volunteers[] = array(
					'id' => $volunteer->get_id() ,
					'avatar' => $volunteer->avatar,
					'nickname' => $volunteer->nickname,
					'contributions' => $volunteer->contributions
				);
			}
		}
		
		return $volunteers;
		
	}
	
	/**
	 * Print (JSON) Volunteers List
	 */
	static function volunteers_list(){
		global $wp;
		$args = array();
		
		if(isset($wp->query_vars['cid']) && !empty($wp->query_vars['cid']))
			$args['campaign'] = $wp->query_vars['cid'];
		
		echo json_encode( VolunteerList::get_volunteerList( $args ) );
		die();
	}
	
	/**
	 * Get number of volunteers for a expecific campaign
	 * @param id $campaign
	 */
	static function get_volunteerNumber( $campaign ){
		global $wpdb;
		$volunteerList = new self( array('campaign'=> $campaign) );
		return count( $wpdb->get_results($volunteerList->get_sql(), ARRAY_A) );
	}
}