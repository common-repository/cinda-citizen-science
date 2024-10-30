<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Opendata{
	
	
	static function get_campaigns(){
		$list = new CampaignsList(array('all'=>true));
		die( json_encode( $list->get_campaigns() ) );
	}
	
	static function get_contributions(){
	
		die();
	}
}