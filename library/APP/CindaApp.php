<?php
namespace cinda\App;
use cinda\API\API as CindaAPI;
use cinda\API\Volunteer;
use cinda\API\Contribution;
use cinda\API\ContributionList;
use cinda\API\CampaignsList;
use cinda\API\Campaign;

class CindaApp{

	private static $instance = null;

	public static function init(){

		if(is_null(self::$instance) || !self::$instance instanceof CindaApp)
			self::$instance = new self();

		return self::$instance;
	}

	public $prefix;
	public $route;
	private $title;
	private $page_id;
	private $routing;
	private $is_logged_in;
	private $token;
	private $volunteer;
	private $errors = array();
	private $nonce;
	private $result = NULL;

	function __construct(){

		$this->page_id = intval( get_cinda()->get_options('app')['page_id'] );

		$this->set_logged_in();
		$this->set_prefix();

		$this->route = get_site_url() . "/" . $this->prefix;

		add_action( 'init', array($this, 'add_rewrite_rules'), 0 );
		add_action( 'init', array($this,'session_start') );

		add_filter( 'query_vars', array($this, 'add_query_vars'), 0 );
		add_filter( 'template_include', array($this,'load_app_template') );

		add_action( 'wp', array($this, 'api_requests'), 0 );
		add_action( 'get_header', array($this,'header') );

	}

	function getNonce(){
		return $this->nonce;
	}

	function getToken(){
		return $this->token;
	}

	function getResult(){
		return $this->result;
	}

	function getTitle(){
		return $this->title . " :: " . __('CINDA: Volunteers Networks APP');
	}

	function getUrl($path=null, $id=null, $action=null, $args=array()){
		$url = $this->route . "/";

		if( !is_null( $path ) )
			$url .= $path . "/";

		if( is_numeric( $id ) )
			$url .= $id . "/";

		if( in_array($action, array('suscribe','unsuscribe','view','delete','edit','sendData')) )
			$url .= $action . "/";

		if( is_array($args) && 0 < count($args) )
			$url .= "?" . http_build_query($args);

		return  $url;

	}

	function getVolunteer(){
		return $this->volunteer;
	}

	function header( $name ) {

		if('cinda-app' == $name){

		}

	}

	function set_logged_in(){
		if(isset($_COOKIE[ CINDA_COOKIE ]) && !empty( $_COOKIE[ CINDA_COOKIE ])){

			$this->is_logged_in = true;
			$this->token = $_COOKIE[ CINDA_COOKIE ];
			$this->volunteer = Volunteer::get_volunter_by_token( $this->token );

			if(!$this->volunteer){
				$this->is_logged_in = false;
				setcookie(CINDA_COOKIE,'',time(),'/'); // Unset COOKIE
			}

		}else{
			$this->is_logged_in = false;
		}
	}

	function is_logged_in(){
		return ($this->is_logged_in) ? true : false;
	}

	function set_prefix(){
		// Set prefix
		if($this->page_id){
			if($post = get_post($this->page_id))
				$this->prefix = $post->post_name;
		}

		if(empty($this->prefix))
			$this->prefix = 'cinda';

	}

	public function register_assets(){

		if (is_page() && get_the_ID() == $this->page_id ) {

			wp_register_style('select2',plugins_url( 'cinda-citizen-science/assets/css/select2.min.css' ));
			wp_enqueue_style( 'select2' );
			wp_register_script('select2',plugins_url( 'cinda-citizen-science/assets/js/select2.full.min.js' ), array('jquery'));
			wp_enqueue_script('select2');

			wp_register_style( 'cinda-app-css', CINDA_URL . 'assets/css/app.min.css', false, "1.1.3", false );
			wp_enqueue_style( 'cinda-app-css' );

			wp_enqueue_script( 'foundation-js', CINDA_URL . 'vendors/foundation6/js/vendor/foundation.min.js', array('jquery'), "1.1.3", false );
			wp_enqueue_script( 'foundation-app', CINDA_URL . 'vendors/foundation6/js/app.js', array('jquery'), "1.1.3", true );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_register_script("api-google-map-v3", "https://maps.googleapis.com/maps/api/js?key="); // &callback=initMap .$this->get_options('gmaps')['api']
			wp_enqueue_script("api-google-map-v3");

			wp_enqueue_script( 'cinda-app-js', CINDA_URL . 'assets/js/app.js', array('jquery'), "1.2.1", false );

		}

	}

	public function api_requests(){
		global $wp, $CINDA;

		// REST API Controller Comunication
		if(isset($wp->query_vars['__CindaApp'])){

			// Check security
			$this->check_security();

			// Generate APIPrefix
			$APIPrefix = get_site_url() . "/" . $CINDA->get_api()->get_prefix();



			// CAMPAIGN CONTROLLER
			if('campaign' == $wp->query_vars['cpt']){

				// LIST Action
				if('list' == $wp->query_vars['action']){
					$this->title = __('Campaigns List', 'Cinda');

					if(isset($_COOKIE[ CINDA_COOKIE ]))
						$token = $_COOKIE[ CINDA_COOKIE ];
					else
						$token = NULL;

					$this->result = CampaignsList::get_campaignList( array('token' => $token) );
				}

				// VIEW Action
				elseif('view' == $wp->query_vars['action']){
					if(isset($wp->query_vars['id'])){
						$this->result = Campaign::get_campaign( $wp->query_vars['id'], $this->token);
						$this->title = $this->result->title;
					}else{
						$this->result = false;
						$this->title = __('404 Not Found','Cinda');
					}
				}

				// VIEW Action
				elseif(in_array($wp->query_vars['action'], array('suscribe','unsuscribe'))){

					if(isset($_GET['redirect']))
						$redirect = urldecode( $_GET['redirect'] );
					else
						$redirect = cindaApp()->getUrl('campaigns');

					$id_campaign = $wp->query_vars['id'];

					if("suscribe" == $wp->query_vars['action']){
						$result = \cinda\API\Campaign::suscribe($id_campaign, $this->volunteer->get_id());
					}else{
						$result = \cinda\API\Campaign::unsuscribe($id_campaign, $this->volunteer->get_id());
					}

					wp_redirect( $redirect );
				}

			}

			// VOLUNTEER CONTROLLER
			elseif('volunteer' == $wp->query_vars['cpt']){

				// LOGIN ACTION
				if('login' == $wp->query_vars['action']){

					if(isset($_POST) && !empty($_POST)){
						if(Volunteer::login()){
							wp_redirect( $this->get_homeURL() );
						}
					}

					$this->token = rand(1000,9999);
					$this->nonce = CindaAPI::generate_nonce('volunteer_login', $this->token);

				}

				else if('logout' == $wp->query_vars['action']){
					volunteer::logout();
					wp_redirect( $this->get_homeURL() );
				}

				// VIEW PROFILE ACTION
				else if('myprofile' == $wp->query_vars['action']){
					$this->result = array();
					$this->result['contributions'] = ContributionList::get_listData(NULL, $this->volunteer->get_id());

				}

				// VIEW OTHER PROFILE ACTION
				else if('profile' == $wp->query_vars['action']){
					$this->result = array();

					$volunteer = Volunteer::get_volunteer_by_id( $wp->query_vars['id'] );

					if(!$volunteer)
						wp_redirect( $this->get_404URL() );

					$url = $APIPrefix . 'volunteer/'.$volunteer->get_id().'/listData/';
					$this->result['volunteer'] = json_decode( json_encode( $volunteer ));
					$this->result['contributions'] = $this->callAPI('GET', $url, array( 'token' => $this->token ));

				}

			}

			// CONTRIBUTION CONTROLLER
			else if('contribution' == $wp->query_vars['cpt']){

				if(in_array($wp->query_vars['action'], array('view','edit','delete'))){

					$url = $APIPrefix . 'contribution/'.$wp->query_vars['id'].'/';
					$this->result['contribution'] = $this->callAPI('GET', $url, array( 'token' => $this->token ));

					if($this->result['contribution']){

						$url = $APIPrefix . 'campaign/'.$this->result['contribution']->campaign.'/';
						$this->result['campaign'] = $this->callAPI('GET', $url, array( 'token' => $this->token ));

						if($this->result['campaign']){
							$url = $APIPrefix . 'campaign/'.$this->result['campaign']->ID.'/model/';
							$this->result['model'] = $this->callAPI('GET', $url);
						}

					}else{
						wp_redirect( $this->get_404URL() );
					}

				}

				// EDIT CONTRIBUTION ACTION
				if('create' == $wp->query_vars['action']){

					$this->result['campaign'] = new Campaign( $wp->query_vars['id'], $this->token );
					$this->result['model'] = $this->result['campaign']->get_model();
					$this->nonce = CindaAPI::generate_nonce('campaign_sendData', $this->token);

					if(!empty($_POST)){
						$this->errors = Contribution::save();

						if(!is_wp_error($this->errors) && is_numeric($this->errors))
							wp_redirect( $this->route . "/contribution/" .$this->errors."/" );

					}

				}
				// EDIT CONTRIBUTION ACTION
				else if('edit' == $wp->query_vars['action']){

					if($this->result['contribution']->author_id != $this->volunteer->get_id())
						wp_redirect( $this->get_403URL() );

					$this->nonce = CindaAPI::generate_nonce('campaign_sendData', $this->token);

					if(!empty($_POST)){
						$this->errors = Contribution::save();

						if(!is_wp_error($this->errors))
							wp_redirect( $this->route . "/contribution/" .$this->result['contribution']->ID."/" );

					}

				}

			}

		}
	}

	/** Add public query vars
	 *	@param array $query_vars List of current public query vars
	 *	@return array $query_vars
	 */
	public function add_query_vars($query_vars){
		$query_vars[] = '__CindaApp';
		$query_vars[] = 'cpt';
		$query_vars[] = 'id';
		$query_vars[] = 'slug';
		$query_vars[] = 'action';
		return $query_vars;
	}

	private function set_routing(){

		$this->routing = array(

			'logout' => array(
				'pattern' => '^'.$this->prefix.'/logout/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=volunteer&action=logout',
				'pos'=>'top'
			),

			'login' => array(
				'pattern' => '^'.$this->prefix.'/login/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=volunteer&action=login',
				'pos'=>'top'
			),

			'myprofile' => array(
				'pattern' => '^'.$this->prefix.'/profile/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=volunteer&action=myprofile',
				'pos'=>'top'
			),

			'profile' => array(
				'pattern' => '^'.$this->prefix.'/profile/([0-9]+)/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=volunteer&action=profile&id=$matches[1]',
				'pos'=>'top'
			),

			'campaigns' => array(
				'pattern' => '^'.$this->prefix.'/campaigns/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=campaign&action=list',
				'pos'=>'top'
			),

			'campaign' => array(
				'pattern' => '^'.$this->prefix.'/campaign/([0-9]+)/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=campaign&action=view&id=$matches[1]',
				'pos'=>'top'
			),

			'campaign-sendData' => array(
				'pattern' => '^'.$this->prefix.'/campaign/([0-9]+)/sendData/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=contribution&action=create&id=$matches[1]',
				'pos'=>'top'
			),

			'campaign-suscribe' => array(
				'pattern' => '^'.$this->prefix.'/campaign/([0-9]+)/suscribe/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=campaign&action=suscribe&id=$matches[1]',
				'pos'=>'top'
			),

			'campaign-unsuscribe' => array(
				'pattern' => '^'.$this->prefix.'/campaign/([0-9]+)/unsuscribe/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=campaign&action=unsuscribe&id=$matches[1]',
				'pos'=>'top'
			),

			'contribution' => array(
				'pattern' => '^'.$this->prefix.'/contribution/([0-9]+)/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=contribution&action=view&id=$matches[1]',
				'pos'=>'top'
			),

			'contribution-edit' => array(
				'pattern' => '^'.$this->prefix.'/contribution/([0-9]+)/edit/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=contribution&id=$matches[1]&action=edit',
				'pos'=>'top'
			),

			'contribution-delete' => array(
				'pattern' => '^'.$this->prefix.'/contribution/([0-9]+)/delete/?$',
				'redirect' => 'index.php?page_id='.$this->page_id.'&__CindaApp=1&cpt=contribution&id=$matches[1]&action=delete',
				'pos'=>'top'
			),
		);

		return $this->routing;
	}

	/**
	 * Return the routing array;
	 * @return array:
	 */
	function get_routing(){
		return $this->routing;
	}

	function get_errors(){
		return $this->errors;
	}

	function add_rewrite_rules(){

		$this->set_routing();

		if( 0 < count( $this->routing ) ){
			foreach ( $this->routing as $route ){
				add_rewrite_rule( $route['pattern'], $route['redirect'], $route['pos'] );
			}
		}

		flush_rewrite_rules( true );

	}

	function check_security(){
		global $wp;

		if('login' != $wp->query_vars['action'] && !$this->is_logged_in)
			wp_redirect( get_site_url() . "/" . $this->prefix . '/login/' );

		if('login' == $wp->query_vars['action'] && $this->is_logged_in)
			wp_redirect( $this->get_homeURL() );

	}

	function load_app_template( $template_include ) {
		global $wp;

		// Only if we are in Cinda APP Page
		if (is_page() && get_the_ID() == $this->page_id ) {

			if( !isset($wp->query_vars['cpt']) )
				wp_redirect( $this->get_homeURL() );

			if("myprofile" == $wp->query_vars['action'])
				$template = "profile";
			else
				$template = $wp->query_vars['action'];

			// If template exists
			if($template = cindaApp::get_template_dir($wp->query_vars['cpt'], $template))
				$template_include = $template;

		}

		// Return template
		return $template_include;
	}

	function get_header(){

		add_action( 'wp_enqueue_scripts', array($this,'register_assets') );

		if($template = self::get_template_dir('header'))
			include( $template );

	}

	function get_footer(){
		if($template = self::get_template_dir('footer'))
			include( $template );
	}

	function get_homeURL(){
		return $this->getUrl('campaigns');
	}

	function get_404URL(){
		return $this->getUrl('404');
	}

	function get_403URL(){
		return $this->getUrl('403');
	}

	/**
	 * Check if file template exists
	 * @param string $type: campaign|contribution|login
	 * @param string $action list|view|edit|delete|null
	 */
	public static function get_template_dir($type, $action=null){

		if(!$action)
			$filename = $type . ".php";
		else
			$filename = $type ."/". $action .".php";

		// Theme template page in /cinda/templates/
		if(file_exists(get_cinda()->theme_uri() . "cinda/app/" . $filename)){
			return get_cinda()->theme_uri() . "cinda/app/" . $filename;
		}

		// Plugin template
		else if(file_exists(get_cinda()->plugin_uri() . "templates/app/" . $filename)){
			return get_cinda()->plugin_uri() . "templates/app/" . $filename;
		}

		return false;
	}

	/**
	 * Call to API REST
	 * @param string $method: (GET | POST | PUT)
	 * @param string $url
	 * @param string $data: array('param' => 'value)
	 * @return json
	 */
	function callAPI($method, $url, $data = false)
	{
		$curl = curl_init();

		switch ($method){
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);

				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;

			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;

			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);

		return json_decode( $result );
	}

	function session_start(){
		if( !session_id() )
			session_start();
	}

	function get_errors_html(){
		if(!empty($this->errors) ): foreach($this->errors as $key => $error):

			if( is_wp_error($error) ){
				echo "<div class=\"callout alert\">";

					foreach( $error->get_error_messages() as $msg):
						echo "<p>".$msg."</p>";
					endforeach;

				echo "</div>";
			}

		endforeach; endif;
	}
}
