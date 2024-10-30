<?php

namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Dictionary{
	
	protected $name;
	protected $description;
	protected $image;
	protected $terms;
	
	/**
	 * Construct
	 * @param int $ID
	 */
	function __construct($ID){
		global $wpdb;
		// Check if ID is a valid Dictionary ID
		$res = $wpdb->get_var("SELECT COUNT(ID) FROM ".$wpdb->prefix."posts WHERE ID = ".$ID." AND post_type = '".CINDA_PREFIX."dictionary';");
		
		if(1 == $res && $post = get_post($ID)){
			$this->name = $post->post_title;
			$this->description = $post->post_content;
			$this->image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
			$this->terms = get_post_meta($post->ID, CINDA_PREFIX."terms",true);
		}
	}
	
	function get_terms(){
		return $this->terms;
	}
	
	/**
	 * Serialize object
	 * @return multitype:NULL
	 */
	function serialize(){
		return array(
			'name' => $this->name,
			'description' => $this->description,
			'image' => $this->image,
			'terms' => $this->terms
		);
	}
	
	/**
	 * Print a dictionary based on an ID
	 */
	static function get_dictionary(){
		global $wp;
		$dictionary = new self( $wp->query_vars['did'] );
		
		echo json_encode( $dictionary->serialize() );
	}
	
	/**
	 * Return a list of al published dictionaries
	 */
	public static function get_list(){
		global $wpdb;
		$sql = "SELECT ID AS id, post_title AS name FROM ".$wpdb->prefix."posts WHERE post_type = '".CINDA_PREFIX."dictionary' AND post_status = 'publish';";
		return $wpdb->get_results($sql, ARRAY_A);
	}
}