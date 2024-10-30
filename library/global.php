<?php
use cinda\Cinda;
use cinda\API\Campaign;
use cinda\App\CindaApp;

add_theme_support( 'post-thumbnails' );
add_image_size( 'cinda-thumbnail', 1024, 1024 );
add_image_size( 'cinda-image', 2048, 2048 );

// Write in WP Log
if (!function_exists('write_log')) {
	function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

// Add Multilingual Support
add_action( 'plugins_loaded', 'cinda_load_textdomain' );
function cinda_load_textdomain() {
	load_plugin_textdomain( 'Cinda', FALSE, basename( CINDA_DIR ) . '/languages/' );
}


// Add settings link on plugin page
function cinda_setting_link($links) {
	$settings_link = '<a href="admin.php?page='.CINDA_PREFIX.'options">'.__('Configuration','Cinda').'</a>';
	array_unshift($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_".CINDA_SLUG, 'cinda_setting_link' );


// Delete an input for the Model of Campaign
add_action( 'wp_ajax_cinda_field_delete', 'cinda_field_delete' );
function cinda_field_delete(){
	global $wpdb;

	if(isset($_POST['id']) && isset($_POST['id_campaign'])){

		$id = intval(  sanitize_text_field( $_POST['id'] ) );
		$id_campaign = intval(  sanitize_text_field( $_POST['id_campaign'] ) );

		echo $wpdb->delete(CINDA_TABLE_MODEL_NAME, array('id'=>$id,'id_campaign'=>$id_campaign));

	}else{
		echo 0;
	}

	die();

}

add_action('wp_enqueue_scripts', 'cinda_register_public_styles');
function cinda_register_public_styles(){

	wp_enqueue_style( 'cinda-style', CINDA_URL . 'assets/css/style.min.css', false, "1.0", false );

	if(file_exists(get_template_directory_uri() . '/cinda/css/style.css'))
		wp_enqueue_style( 'cinda-style-theme', get_template_directory_uri() . '/cinda/css/style.css', false, "1.0", false );

	else if(file_exists(get_template_directory_uri() . '/cinda/css/style.min.css'))
		wp_enqueue_style( 'cinda-style-theme', get_template_directory_uri() . '/cinda/css/style.min.css', false, "1.0", false );
}

/**
 * AJAX Function
 * Print HTML table for a new field line in model of campaign table
 */
add_action( 'wp_ajax_cinda_new_field', 'cinda_new_field' );
function cinda_new_field(){
	echo include( CINDA_DIR . 'assets/views/campaign/new_field.php');
	die();
}

/**
 * Return a formatted key
 */
add_action( 'wp_ajax_cinda_sanitize_fieldname', 'cinda_sanitize_fieldname' );
function cinda_sanitize_fieldname(){
	echo sanitize_key( $_POST['text'] );
	die();
}

/**
 * Delete a contribution
 */
add_action( 'wp_ajax_cinda_contribution_delete', 'cinda_contribution_delete' );
function cinda_contribution_delete(){
	global $wpdb;
	if( isset($_POST['ID']) ){

		$id = $_POST['ID'];
		$is_contribution = $wpdb->get_var("SELECT COUNT(ID) as num FROM ".$wpdb->prefix."posts WHERE ID=".$id." AND post_type = 'cinda_contribution' ");

		if($is_contribution){
			$wpdb->delete($wpdb->prefix."posts", array('ID'=>$id), array('%d'));
			$wpdb->delete($wpdb->prefix."postmeta", array('post_id'=>$id), array('%d'));

			echo 1;
		}else{
			echo 0;
		}
	}else{
		echo 0;
	}
	die();
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param string $f ('y' | 'n') forze reload
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $name, $s = 150, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {

	$url = 'http://www.gravatar.com/avatar/';
	$url .= $name;
	$url .= "?s=$s&d=$d&r=$r";

	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}

	return $url;
}

/**
 * Create an avatar with initials
 * @param string $char Character that be print in image
 * @param string $name Name of image
 * @param number $s Size in pixels
 * @param string $format Output format
 */
function generate_avatar($char, $name, $s = 150, $format = "png"){

	require_once CINDA_DIR . 'vendors/GDText/Box.php';
	require_once CINDA_DIR . 'vendors/GDText/Color.php';

	$char = strtoupper($char);
	if(2 < strlen($char))
		$char = substr($char,0,2);
	$filename = $name . "." . $format;

	$upload_dir = wp_upload_dir();

	if( !file_exists($upload_dir['basedir'] . "/avatars/") )
		mkdir( $upload_dir['basedir'] . "/avatars/", 0775 );

	$private_uri = $upload_dir['basedir'] . "/avatars/" . $filename;

	// Create image
	$img = @imagecreatetruecolor($s, $s);

	// Background color
	$red = (int)(rand(128,256));
	$green = (int)(rand(128,256));
	$blue = (int)(rand(128,256));
	// Create color
	$color = imagecolorallocate($img, $red, $green, $blue);

	// Asign color to background
	imagefill($img, 0, 0, $color);

	// Text Box (using GDText)
	$font_family = CINDA_DIR . 'assets/fonts/Oswald-Bold.ttf';
	$font_size = (int)( $s/2 );
	$textbox = new GDText\Box( $img );
	$textbox->setFontSize( $font_size );
	$textbox->setFontFace( $font_family );
	$textbox->setFontColor( new GDText\Color(250, 250, 250) ); // black
	$textbox->setBox(
		0,  // distance from left edge
		0,  // distance from top edge
		imagesx($img), // textbox width, equal to image width
		imagesy($img)  // textbox height, equal to image height
	);

	// now we have to align the text horizontally and vertically inside the textbox
	// the texbox covers whole image, so text will be centered relatively to it
	$textbox->setTextAlign('center', 'center');
	// it accepts multiline text
	$textbox->draw( $char );

	// Save image
	if("png" === $format){
		if(imagepng($img, $private_uri)){
			return $filename;
		}
	}else if("jpg" === $format){
		if(imagejpeg($img, $private_uri, 90)){
			return $filename;
		}
	}

	imagedestroy($img);

	return false;

}


/**
 * Replace acents and simbols
 * @param unknown $string
 *
 */
function sanitize_string($string){

	$pattern = array(
		'/"|&|<|>| |¡|¢|£|¤/' => '_',
		'/¥|¦|§|¨|©|«|¬|­|®|¯/' => '_',
		'/±|&sup2;|&sup3;|´|µ|¶|·|÷/' => '_',
		'/°|&sup1;|»|&frac14;|&frac12;|&frac34;|¿/' => '_',
		'/à|á|â|ã|ä|å|æ|ª/' => 'a',
		'/À|Á|Â|Ã|Ä|Å|Æ/' => 'A',
		'/è|é|ê|ë|ð/' => 'e',
		'/È|É|Ê|Ë|Ð/' => 'E',
		'/ì|í|î|ï/' => 'i',
		'/Ì|Í|Î|Ï/' => 'I',
		'/ò|ó|ô|õ|ö|ø|º/' => 'o',
		'/Ò|Ó|Ô|Õ|Ö|Ø/' => 'O',
		'/ù|ú|û|ü/' => 'u',
		'/Ù|Ú|Û|Ü/' => 'U',
		'/ç/' => 'c',
		'/Ç/' => 'C',
		'/ý|ÿ/' => 'y',
		'/Ý|Ÿ/' => 'Y',
		'/ñ/' => 'n',
		'/Ñ/' => 'N',
		'/þ/' => 't',
		'/Þ/' => 'T',
		'/ß/' => 's',
	);

	return preg_replace( array_keys($pattern), array_values($pattern), $string);
}

/**
 * Get Configuration page url
 */
function cinda_options_URL(){
	return get_admin_url(null, 'admin.php?page='.CINDA_PREFIX.'options'); //  "/wp-admin/admin.php?page=".CINDA_PREFIX."options";
}

/**
 * Get Information page url
 */
function cinda_welcome_URL(){
	return get_admin_url(null, 'admin.php?page='.CINDA_PREFIX.'welcome'); // "/wp-admin/admin.php?page=".CINDA_PREFIX."welcome";
}






add_action( 'get_header', 'cinda_header_hook' );
function cinda_header_hook( $name ) {

	if($name == 'cinda'){



		if(is_single() && CINDA_PREFIX . 'campaign' == get_post_type()){

			global $campaign;

			$campaign = new Campaign( get_the_ID() );
			$campaign->set_contributions();
		}

	}

	return;

}

/**
 * Send a push
 * @param array $data: title, text, idCampaign
 * @param array $devices: array with device ids, empty to send to all people who have the app installed
 */
function sendPush($data=array(), $devices=array()){
	global $CINDA;
	$responses = 0;
	$failures = 0;

	$wheres = array();

	$dataDefaults = array(
		'alert' => '',
		'content' => '',
	);

	$push_content = array();
	$push_content['aps'] = array_merge($dataDefaults, $data);

	if(!isset($CINDA->get_options('notification')['parse']))
		die(json_encode(__('Imposible send push, before configure PARSE parameters.','Cinda')));

	// PARSE URL
	$url = $CINDA->get_options('notification')['parse']['url'];
	// APPLICATION ID
	$appId = $CINDA->get_options('notification')['parse']['app_id'];
	// APPLICATION REST API KEY
	$restKey = $CINDA->get_options('notification')['parse']['app_key'];

	// For ALL Devices
	if(0 == count($devices))
		$where = "{}";
	// For Multiple Devices
	else
		$where = array(
			'user' => array(
				'$inQuery'=> array(
					'where' => array(
						'objectId' => array(
							'$in' => array( array_values($devices) ),
						),
					),
					'className' => "_User",
				),
			),
		);

		$push_payload = json_encode(array(
			"where" => $where,
			"data" => $push_content
		));

		$rest = curl_init();
		curl_setopt($rest, CURLOPT_URL, $url);
		curl_setopt($rest, CURLOPT_PORT, 443);
		curl_setopt($rest, CURLOPT_POST, 1);
		curl_setopt($rest, CURLOPT_POSTFIELDS, $push_payload);
		curl_setopt($rest, CURLOPT_HTTPHEADER,
				array(
					"X-Parse-Application-Id: " . $appId,
					"X-Parse-REST-API-Key: " . $restKey,
					"Content-Type: application/json"
				)
		);

		$response = curl_exec($rest);

		curl_close($rest);

		if(json_encode($response))
			$responses++;
		else
			$failures++;

		return array($responses, $failures);

}

/**
 * Add custom template (in plugin or theme folder) for Archive of Custom Post Type
 * @param string $archive_template
 */
function cinda_load_archive_template( $archive_template ) {
	global $post;

	// Campaign
	if ( $post->post_type == CINDA_PREFIX . "campaign" ) {

		if($template = CindaApp::get_template_dir('campaign','list'))
			$archive_template = template;

	}

	// Contribution
	else if ( $post->post_type == CINDA_PREFIX . "contribution" ) {

		if($template = CindaApp::get_template_dir('contribution','list'))
			$archive_template = template;

	}

	return $archive_template;
}
add_filter( 'archive_template', 'cinda_load_archive_template');

function cinda_use_wp_users(){
	$CINDA = get_cinda();
	return (isset($CINDA->get_options('others')['wp_users']) && $CINDA->get_options('others')['wp_users']) ? true : false;
}

function cinda_hide_admin_bar(){
	if(current_user_can(CINDA_PREFIX.'volunteer')){
		show_admin_bar(false);
		add_filter( 'show_admin_bar', '__return_false' );
	}
}
add_action('init','cinda_hide_admin_bar');
