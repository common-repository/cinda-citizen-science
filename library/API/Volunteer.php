<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use WP_Query;
use Box;
use cinda\CPT\Volunteer as CPT_Volunteer;
use cinda\API\API;

class Volunteer{

	private $suscriptions = array();
	private $id = null;
	private $post;
	private $create = '';
	private $wp_user_id = 0;
	public $email = "";
	public $nickname = "";
	public $device_id = "";
	public $avatar = "";
	public $contributions = 0;

	/**
	 * Construct
	 * @param int $id
	 * @param string $nickname
	 * @param string $email
	 * @param string $device_id
	 */
	function __construct($id, $nickname=null, $email=null, $device_id=null){
		global $wpdb;

		if($id != null && is_numeric($id)){

			$this->id = intval( $id );
			$this->post = get_post( $id );

			if(!$this->post || $this->post->post_type != CINDA_PREFIX . CPT_Volunteer::$name )
				return false;

			$this->create = new \DateTime( $this->post->date );

			$meta_values = get_post_meta( $id );

			// SET EMAIL
			if(isset($meta_values[CINDA_PREFIX.'email']))
				$this->email = $meta_values[CINDA_PREFIX.'email'][0];

			// SET NICKNAME
			if(isset($meta_values[CINDA_PREFIX.'nickname']))
				$this->nickname = $meta_values[CINDA_PREFIX.'nickname'][0];

			// SET DEVICE ID
			if(isset($meta_values[CINDA_PREFIX.'device_id']))
				$this->device_id = $meta_values[CINDA_PREFIX.'device_id'][0];

			// SET WP USER ID
			if(isset($meta_values[CINDA_PREFIX.'wp_user_id']))
				$this->wp_user_id = $meta_values[CINDA_PREFIX.'wp_user_id'][0];

			// SET AVATAR
			if(isset($meta_values[CINDA_PREFIX.'avatar_url']))
				$this->avatar = $meta_values[CINDA_PREFIX.'avatar_url'][0];

			// SET CONTRIBUTIONS
			$this->contributions = $wpdb->get_var("SELECT COUNT(p.ID) FROM ".$wpdb->prefix."posts AS p INNER JOIN ".$wpdb->prefix."postmeta AS m ON p.ID = m.post_id WHERE m.meta_key = '".CINDA_PREFIX."author_id' AND m.meta_value = ".$this->id.";");

		}

		else{
			$this->nickname = $nickname;
			$this->email = $email;
			$this->device_id = $device_id;
		}
	}

	/**
	 * Set suscriptions from DataBase
	 */
	private function set_suscriptions(){
		global $wpdb;
		$this->suscriptions = $wpdb->get_col("SELECT id_campaign FROM ". CINDA_TABLE_SUSCRIPTIONS_NAME ." WHERE id_volunteer = ". $this->id );
	}

	/**
	 * Get suscriptions
	 * @return array: Ids of campaigns where volunteer is subscribed
	 */
	public function get_suscriptions(){
		if(empty($this->suscriptions))
			$this->set_suscriptions();

		return $this->suscriptions;
	}

	public function get_wp_user_id(){
		return $this->wp_user_id;
	}

	/**
	 * Return volunteer id
	 * @return int
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 * Set number of contributions for this volunteer
	 */
	public function set_contributions($num_contributions){
		$this->contributions = intval( $num_contributions );
	}

	public static function generate_token( $email ){
		return sha1( sanitize_text_field( $email ) . date('YmdGis') );
	}

	public static function get_token($volunteer_id){
		global $wpdb;

		$token = $wpdb->get_var( 'SELECT token FROM '.  CINDA_TABLE_TOKENS_NAME .' WHERE id_volunteer = '.$volunteer_id );

		// If only exists one result
		if( $token ){
			return $token;
		}else {
			return false;
		}
		
	}

	/**
	 * Register or login an volunteer
	 * Need $_POST values: 'name', 'surname', 'email', 'device-id'
	 */
	public static function register_volunteer(){
		global $wpdb, $post;

		if(isset($_POST['nonce'])){
			$nonce = $_POST['nonce'];
		}elseif(isset($_POST['_wpnonce'])){
			$nonce = $_POST['_wpnonce'];
			write_log('_wpnonce is deprecated, instead use \'nonce\' ');
		}else
			$nonce = null;

		// Check WPNONCE
		if(!$nonce || !isset($_POST['token']) || !API::verify_nonce($nonce,'volunteer_register', $_POST['token']))
			die( json_encode(0) );

		// If data send by POST
		if(isset($_POST) && !empty($_POST)){

			// If email not is empty
			if(isset($_POST['email']) && !empty($_POST['email'])){

				// Generate Token
				$token = self::generate_token($_POST['email']);

				// if user exists
				if( $user_id = self::volunteer_exists( sanitize_email( $_POST['email'] ) ) ){

					$volunteer = new self($user_id);
					$avatar = $volunteer->set_avatar();
					$result = $wpdb->get_results( 'SELECT token FROM '.  CINDA_TABLE_TOKENS_NAME .' WHERE id_volunteer = '.$user_id, 'ARRAY_A' );

					// If only exists one result
					if( 1 == count($result) ){
						$token = $result[0]['token'];
					}

					// If exists zero or more than one result
					else{
						// Delete duplicated tokens
						if( 1 < count($result) )
							$wpdb->delete( CINDA_TABLE_TOKENS_NAME, array('id_volunteer' => $user_id), array( '%d' ) );

						// Insert new token
						$wpdb->insert(CINDA_TABLE_TOKENS_NAME, array(
							'id_volunteer' => $user_id,
							'token' => $token
						));
					}

				// if user don't exists
				}else{

					// Create a volunteer
					$volunter = new self(null, sanitize_text_field( $_POST['nickname'] ), sanitize_email( $_POST['email'] ), sanitize_text_field($_POST['device_id']));

					// Save volunteer in DB
					if($volunter->save()){

						$user_id = $volunter->id;
						$avatar = $volunter->avatar;

						// insert new token
						$wpdb->insert(CINDA_TABLE_TOKENS_NAME, array(
							'id_volunteer' => $user_id,
							'token' => $token
						));

					}else{
						// Volunter don't create
						die( json_encode(0) );
					}

				}

				// Show response
				die( json_encode( array($user_id, $token, $avatar ) ) );

			// Email not send
			}else{
				// No login (ERROR)
				die( json_encode(0) );
			}

		// NO se han enviado datos por POST
		}else{
			die( json_encode(0) );
		}

	}

	public static function get_volunteer_by_id($ID){

		$ID = intval($ID);

		if(self::volunteer_exists( $ID ))
			return new self( $ID );
		else
			return 0;
	}

	/**
	 * Return information about a volunteer.
	 */
	public static function get_volunteer(){
		global $wp;

		if(isset($wp->query_vars['vid']))
			$volunteer = self::get_volunteer_by_id( $wp->query_vars['vid'] );
		else
			die( json_encode( 0 ) );

		die( json_encode( $volunteer ) );
	}

	/**
	 * Insert or update volunter info into database
	 * @return int 1: succes|0: error
	 */
	function save(){

		// Register user
		$post = array(
			'post_type' 	=> CINDA_PREFIX."volunteer",
			'post_title'    => $this->nickname . " (". $this->email .")",
			'post_status'   => 'publish',
			'post_author'   => 1
		);

		if( $this->id != NULL ){
			$post['ID'] = $this->id;
			if( !wp_update_post( $post ) )
				return 0; // not update post
		}else{
			$this->id = wp_insert_post($post);

			// Generate WP_User
			if(cinda_use_wp_users()):
				$password = wp_generate_password();
				$username = $this->nickname;
				$counter = 0;
				$break = false;
				while(true){
					$user_id = wp_create_user( $username, $password, $this->email );
					if(is_wp_error($user_id)){
						$codes = $user_id->get_error_codes();
						foreach ($codes as $code) {
							if($code == "existing_user_login"){
								$counter++;
								$username = $this->nickname.$counter;
							}elseif($code == "existing_user_email"){
								$break = true;
								$user_id = 0;
							}
						}
						// Break if email exists
						if($break)
							break;

					}else{
						// Set user as Volunteer
						set_user_role($user_id, CINDA_PREFIX.'volunteer' );
						// Break because the user is generated
						break;
					}
				}
				$this->wp_user_id = $user_id;
			endif;
		}

		// Update metas
		if($this->id){
			// Register fields (meta values)
			// NAME
			update_post_meta($this->id, CINDA_PREFIX."nickname", $this->nickname);
			// EMAIL
			update_post_meta($this->id, CINDA_PREFIX."email", $this->email);
			// DEVICE ID
			update_post_meta($this->id, CINDA_PREFIX."device_id", $this->device_id);
			// WP USER ID
			update_post_meta($this->id, CINDA_PREFIX."wp_user_id", $this->wp_user_id);
			// Avatar
			$avatar = $this->set_avatar();

			update_post_meta($this->id, CINDA_PREFIX."avatar_url", $avatar );
			update_post_meta($this->id, CINDA_PREFIX."avatar_date", date('r') );

			return $this->id;

		}

		return 0;
	}

	/**
	 * Generate default profile image and call get_gravatar function
	 * @param int $size Size on pixels
	 */
	function set_avatar($size = 150){

		// Sanitize nickname and select the first character
		$words = explode(" ", $this->nickname);
		$char = "";
		foreach ($words as $word){
			$char .= substr( str_replace("_", "",  sanitize_string( $word ) ), 0, 1);
		}
		$name = md5( strtolower( trim( $this->email ) ) );
		$format = 'png';
		$filename = generate_avatar($char, $name, 150, $format); // GENERATE AVATAR

		// URL Plública a la imagen
		$filename_url = wp_upload_dir()['baseurl'] . "/avatars/" . $filename;
		// URI Privada a la imagen
		$filename_dir = wp_upload_dir()['basedir'] . "/avatars/" . $filename;
		// Gravatar URL
		$gravatar_url = get_gravatar($name, $size, $filename_url);

		if( file_put_contents( $filename_dir, file_get_contents( $gravatar_url ) ) )
			return $filename_url;
		else
			return false;

	}

	/**
	 * Return a volunteer
	 * @param string $token
	 * @return \cinda\API\Volunteer|false
	 */
	public static function get_volunter_by_token($token){
		global $wpdb;

		if($id = self::get_volunter_id($token)){
			return new Volunteer( $id );
		}else{
			return false;
		}

	}

	public static function get_volunter_by_email($email){
		global $wpdb;

		$volunteer_id = $wpdb->get_var('SELECT m.post_id AS id FROM '.$wpdb->prefix.'postmeta AS m WHERE m.meta_key = "' . CINDA_PREFIX . 'email" AND meta_value = "'.$email.'"');
		if($volunteer_id)
			return new self( $volunteer_id );

		return false;

	}

	/**
	 * Search if user exists
	 * @param int|string $value
	 * @return ID of volunteer or false (0)
	 */
	public static function volunteer_exists($value){

		$params = array("post_type"=>CINDA_PREFIX."volunteer");

		if(is_numeric($value)){
			$params['p'] = intval( $value );
		}elseif(is_string($value)){
			$params['meta_key'] = CINDA_PREFIX."email";
			$params['meta_value'] = sanitize_email( $value );
		}else
			return false;

		// WP Query
		$volunteer = new WP_Query( $params );

		if($volunteer->post_count == 1)
			return $volunteer->posts[0]->ID;
		else
			return 0;

	}

	/**
	 * Update meta_value of Endpoint for a volunteer (For notifications)
	 */
	public static function update_endpoint(){

		// Check TOKEN
		if(!isset($_POST['token']) || empty($_POST['token'])){
			die( json_encode(0) );
		}else{
			$token = sanitize_text_field( $_POST['token'] );
		}

		// Check NONCE
		if(isset($_POST['nonce']))
			$nonce = sanitize_text_field( $_POST['nonce'] );
		elseif(isset($_POST['_wpnonce'])){
			write_log('_wpnonce is deprecated, instead use \'nonce\' ');
			$nonce = sanitize_text_field( $_POST['_wpnonce'] );
		}
		else
			$nonce = NULL;

		// Verify NONCE
		if( empty($nonce) || !API::verify_nonce($nonce, 'volunteer_update_endpoint', $token)){
			die( json_encode(0) );
		}

		if(!isset($_POST['token']) || empty($_POST['token']))
			die( json_encode( 0 ) );

		if(!isset($_POST['endpoint']) || empty($_POST['endpoint']))
			die( json_encode( 0 ) );

		$token = sanitize_text_field( $_POST['token'] );
		$endpoint = sanitize_text_field( $_POST['endpoint'] );
		$id_usuario = self::get_volunter_by_token($token);

		if($id_usuario){
			if(update_post_meta($id_usuario->get_id(), CINDA_PREFIX."endpoint", $endpoint))
				die( json_encode( 1 ) );
			else
				die( json_encode( 0 ) );
		}else
			die( json_encode( 0 ) );

	}



	public static function login(){

		// Check WPNONCE
		if(!isset($_POST['nonce']) || !isset($_POST['token']) || !API::verify_nonce($_POST['nonce'], 'volunteer_login', $_POST['token']) )
			die( json_encode(0) );

		// data
		$email = sanitize_email( $_POST['email'] );
		$plane_password = $_POST['password'];

		/*
		 * Check if volunteer exists
		 */
		if($volunteer = self::get_volunter_by_email($email)){

			// Get the password
			$hashed_password = get_post_meta($volunteer->id, CINDA_PREFIX.CPT_Volunteer::$name.'_password',true);

			// Check password (PHP >= 5.5.0)
			if(function_exists('password_verify')){
				$result = password_verify($plane_password, $hashed_password);
			}
			// Traditional encrypting (PHP < 5.5.0)
			else{
				$salt = get_post_meta($volunteer->id, CINDA_PREFIX.CPT_Volunteer::$name.'_salt',true);
				$result = hash_equals($hashed_password, crypt($plane_password, '$2y$12$'.$salt.'$'));
			}

			// If password is correct
			if($result){

				$token = self::generate_token($email);

				// Actualize token
				global $wpdb;
				if( $wpdb->update(
					CINDA_TABLE_TOKENS_NAME,
					array('token' => $token),
					array('id_volunteer' => $volunteer->id),
					array('%s'),
					array('%d')
				)){

					// Initialize session on CINDA
					setcookie(CINDA_COOKIE, $token, NULL, '/');

					// Initialize session on Wordpress
					if(cinda_use_wp_users() && $volunteer->wp_user_id ){
						$user = get_user_by( 'id', $volunteer->wp_user_id );
						if( $user ) {
						    wp_set_current_user( $user->ID, $user->user_login );
						    wp_set_auth_cookie( $user->ID );
						    //do_action( 'wp_login', $user->user_login );
						}
					}

					return $token; // Login successful

				}
			}
		}

		return 0; // Login error

	}

	/**
	 * ENDPOINT
	 */
	public static function login_result(){
		die( json_encode( self::login() ) );
	}

	/**
		* Logout the user sessions
		*/
	public static function logout(){
		setcookie(CINDA_COOKIE,'',time(),'/');
		wp_logout();
	}

	/**
		* Change the password for Volunteer.
		* If the Cinda uses "wp_users" also changes the password of the user linked.
		*/
	public function change_password($new_password){

		if(function_exists('password_hash'))
			$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
		else{
			$salt = uniqid(mt_rand(), true);
			update_post_meta($this->id, CINDA_PREFIX.CPT_Volunteer::$name.'_salt', $salt);
			$hashed_password = crypt($new_password, '$2y$12$'.$salt.'$');
		}

		// Save password
		update_post_meta($this->id, CINDA_PREFIX.CPT_Volunteer::$name.'_password', $hashed_password);

		// Change the wordpress user password
		if(cinda_use_wp_users() && $this->wp_user_id){
			wp_set_password( $new_password, intval($this->wp_user_id) );
		}

	}

	/**
	 * Generate password and activate web login
	 */
	public static function activate_login(){
		global $CINDA;

		if(!isset($_GET['token']) || ! $volunteer = self::get_volunter_by_token( sanitize_text_field( $_GET['token'] ) ))
			die( json_encode(0) );

		$plane_password = wp_generate_password( 20, false, false );

		$volunteer->change_password( $plane_password );



		$subject = sprintf(__('Your credential to access at CINDA in the server %s', 'Cinda'),  get_bloginfo());

		$url = cindaApp()->getUrl('login');
		$message = sprintf(__("Here you have your credentials to access at CINDA in the server %s",'Cinda'), get_bloginfo()) . "\n\n";
		$message .= sprintf(__("URL: %s",'Cinda'), $url) . "\n";
		$message .= sprintf(__("Email: %s",'Cinda'), $volunteer->email ) . "\n";
		$message .= sprintf(__("Password: %s",'Cinda'), $plane_password ) . "\n";

		if( wp_mail(
			array($volunteer->email),		// to
			$subject,						// subject
			$message, 						// Message
			'From: '.get_bloginfo().' <'.get_option('admin_email').'>' . "\r\n"					// headers
		) ) die( json_encode(1) );

		die( json_encode(0) );

	}

	/**
	 * Return an ID of volunteer if exists
	 * @param string $token
	 * @return number|0
	 */
	static function get_volunter_id($token){
		global $wpdb;

		$id = $wpdb->get_var("SELECT id_volunteer AS id FROM ". CINDA_TABLE_TOKENS_NAME ." WHERE token = '". $token ."';");

		if($id)
			return $id;
		else
			return 0;

	}

}
