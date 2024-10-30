<?php

namespace cinda;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use cinda\API\CampaignsList;

/* IN DEVELOPMENT */
class CindaShortcodes{
	
	private $theme_uri;
	private $plugin_uri;
	
	function __construct(){
		$this->theme_uri = get_template_directory() . "/cinda/shortcodes/";
		$this->plugin_uri = CINDA_DIR . "assets/views/shortcodes/";
		
		// Register all shortcodes
		$this->register();
	}
	
	
	function register(){
		
		add_shortcode( 'cinda_campaigns', array($this,'cinda_campaigns') );
		
	}
	
	
	function cinda_campaigns( $atts = array() ){

		$args = shortcode_atts(array(
			
		), $atts);
		
		$list = new CampaignsList($args);
		$campaigns = json_decode( json_encode( $list->get_campaigns() ) );
		
		if(file_exists( $this->theme_uri . "campaigns.php")){
			require_once $this->theme_uri . "campaigns.php";
		}else{
			require_once $this->plugin_uri . "campaigns.php";
		}
		
	}
}