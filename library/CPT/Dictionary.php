<?php
namespace cinda\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Dictionary{
	
	public static $name = "dictionary";
	private $args = array();
	
	/**
	 * Constructor
	 */
	public function __construct(){
		// Actions
		add_action( 'init', array($this,'register') );
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action( 'add_meta_boxes', array($this,'metaboxes') );
		add_action( 'save_post', array($this,'save') );
	}
	
	/**
	 * Register Custom Post Type
	 */
	public function register(){

		$this->args = array(
				'labels' => array(
					'name' => __( 'Dictionaries' ,'Cinda'),
					'singular_name' => __( 'Dictionary' ,'Cinda'),
					'add_new' => __( 'New Dictionary','Cinda'),
					'add_new_item' =>  __( 'Add new Dictionary','Cinda')
				),
				'description' => __("Dictionary",'Cinda'),
				'public' => true,
				'has_archive' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => 'edit.php?post_type='. CINDA_PREFIX . self::$name,
				'menu_position' => 9,
				'menu_icon' => 'dashicons-clipboard',
				'supports' => array(
					'title',
					'editor',
					'thumbnail',
					'revisions',
				)
		);
		
		// Register post type
		register_post_type( CINDA_PREFIX . self::$name, $this->args );
		
	}
	
	/**
	 * Add metaboxes
	 */
	public function metaboxes(){
		global $pagenow;
		if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
			// Add METABOXES
			add_meta_box(CINDA_PREFIX . self::$name . '_csv_table', sprintf(__('Terms imported','Cinda')), array($this,'metabox_terms'), CINDA_PREFIX . self::$name, 'advanced', 'default');
			// Add METABOXES
			add_meta_box(CINDA_PREFIX . self::$name . '_terms_table', sprintf(__('Import terms from CSV','Cinda')), array($this,'metabox_csv'), CINDA_PREFIX . self::$name, 'advanced', 'default');
		}
	}

	/**
	 * Metabox Import from CSV
	 */
	public function metabox_csv(){
		include(CINDA_DIR . 'assets/views/dictionary/csv_table.php');
	}
	
	/**
	 * Metabox Terms
	 */
	public function metabox_terms(){
		include(CINDA_DIR . 'assets/views/dictionary/terms_table.php');
	}
	
	/**
	 * Add to Global menu
	 */
	function admin_menu() {
		add_submenu_page(
			CINDA_PREFIX."menu", 
			__('Dictionaries','Cinda'),
			__('Dictionaries','Cinda'),
			'manage_options',
			'edit.php?post_type='.CINDA_PREFIX . self::$name
		);
	}
	
	/**
	 * Save Dictionary
	 */
	function save(){
		// Save CODE
		if(isset($_POST[CINDA_PREFIX.'code']) && !empty($_POST[CINDA_PREFIX.'code']) )
			update_post_meta(get_the_ID(), CINDA_PREFIX.'code', sanitize_text_field( $_POST[CINDA_PREFIX.'code'] ) );
		
		// Save CSV
		if(isset($_POST[CINDA_PREFIX.'csv_file']) && !empty( $_POST[CINDA_PREFIX.'csv_file'] ) && $_POST[CINDA_PREFIX.'csv_file'] != get_post_meta(get_the_ID(), CINDA_PREFIX."csv_file",true) ){
			
			$url = wp_get_attachment_url( intval( sanitize_text_field( $_POST[CINDA_PREFIX.'csv_file'] ) ) );

			$curl = curl_init();
			
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_VERBOSE, true);
			curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
			$output = curl_exec($curl);
			
			
			if(!curl_errno($curl)){
				$info = curl_getinfo($curl);
				
				if("text/csv" == $info['content_type']){
										
					$lines = explode("\n", $output );
					array_pop($lines);
					
					$i = 0;
					
					$terms = array();
					
					foreach($lines as $line){
						
						$line = explode(",", trim($line));
						
						if($i === 0){
							if($line[0] != 'code' || $line[1] != 'name'|| $line[2] != 'description')
								break;
						}else{
							$terms[] = array(
								'code' => trim($line[0]),
								'name' => trim($line[1]),
								'description' => trim($line[2]),
							);
						}
						
						$i++;
					}
					
					if(!empty($terms)){
						update_post_meta(get_the_ID(), CINDA_PREFIX.'terms', $terms );
						update_post_meta(get_the_ID(), CINDA_PREFIX.'csv_file', sanitize_text_field( $_POST[CINDA_PREFIX.'csv_file'] ) );
						update_post_meta(get_the_ID(), CINDA_PREFIX.'csv_update', date('Y-m-d H:i:s') );
					}
				}
			}
			
			curl_close($curl);
		}
	}

}