<?php

use cinda\APP\CindaApp;
use cinda\API\Dictionary;
use cinda\CPT\Volunteer as Volunteer_CPT;
use cinda\API\Volunteer as Volunteer_API;
require_once 'CindaApp.php';

function cindaApp(){
	return CindaApp::init();
}

/**
	* Login in CindaAPP when user login in Wordpress
	*/
function cinda_app_login_on_wp_login( $user_login, $user ) {

	$cindaAPP = cindaAPP();

	if(!$cindaAPP->is_logged_in()){
		$query = new WP_Query(array(
			'post_type' => CINDA_PREFIX.Volunteer_CPT::$name,
			'meta_query' => array(
				array(
					'key' => CINDA_PREFIX."wp_user_id",
					'value' => $user->ID
				)
			)
		));

		if($query->found_posts){
			$query->the_post();

			$volunteer_ID = get_the_ID();
			$token = Volunteer_API::get_token($volunteer_ID);

			if($token){
				// Initialize session on CINDA APP
				setcookie(CINDA_COOKIE, $token, NULL, '/');
			}

			wp_reset_query();
		}

	}

}
add_action('wp_login', 'cinda_app_login_on_wp_login', 10, 2);

/**
	* Return the HTML for the $field
	*/
function get_field_html($field, $value=null){

	if(!property_exists($field, "field_type"))
		return false;

	if( 'image' == $field->field_type){
		if(!empty($value))
			return "<img src=\"".$value."\" />";
		else
			return false;
	}

	else if( 'file' == $field->field_type ){

		if(!empty($value))
			return "<a href=\"".$value."\">".__('Donwload','Cinda')."</a>";
		else
			return false;

	}

	else if( 'geopos' == $field->field_type){
		$id = rand(0,100) * rand(0,10);
		return '<div class="map not-clickable" id="map'.$id.'"></div>
				<input readonly="readonly" type="text" class="geoposition" value="'. $value .'" />';
	}

	else if( 'select' == $field->field_type ){
		$cinda_options = explode('|',$field->field_options);

		if(in_array($value, $cinda_options))
			return $value;
		else
			return false;
	}

	else{
		return $value;
	}
}


function get_field_input($field, $value=null){
	$html = "";

	if('text' == $field->field_type){
		$html .= '<input type="text" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $value .'" ';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
	}

	else if('textarea' == $field->field_type){
		$html .= '<textarea type="text" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" ';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= ' />'. $value .'</textarea>';
	}

	else if('number' == $field->field_type){
		$html .= '<input type="number" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $value .'"';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
	}

	else if('date' == $field->field_type){
		$html .= '<input type="date" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $value .'"';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
	}

	else if('datetime' == $field->field_type){
		$html .= '<input class="datepicker" type="datetime" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $value .'"';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
	}

	else if('geopos' == $field->field_type){
		$id = rand(0,100) * rand(0,10);
		$html .= '<div class="map" id="map'.$id.'"></div>';
		$html .= '<input type="text" class="geoposition" name="'.$field->field_name.'" placeholder="'.$field->field_label.'" value="'. $value .'" pattern="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$"';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
	}

	else if('image' == $field->field_type){
		if( isset($value) && !empty($value)){
			$html .= '<a href="'. $value .'" target="_blank"><img src="'. $value .'" class="image-table" /></a><br />';
			$html .= '<button type="button" name="replace" class="button button-primary button-large"><i class="fa fa-camera"></i> '.__('Replace','Cinda').'</button>';
		}else{
			$html .= '<button type="button" name="replace" class="button button-primary button-large"><i class="fa fa-camera"></i> '.__('Select Image','Cinda').'</button>';

		}
		$html .= '<input type="file" name="'.$field->field_name.'"';

		if($field->field_required && !$value)
			$html .= 'required="required"';

		$html .= ' />';
	}

	else if('file' == $field->field_type){
		$html .= '<input type="file" name="'.$field->field_name.'" ';

		if($field->field_required && !$value)
			$html .= 'required="required"';

		$html .= '/>';
	}

	else if('select' == $field->field_type){
		$cinda_options = explode("|",$field->field_options);
		$html .= '<select type="file" name="'.$field->field_name.'"';

		if($field->field_required)
			$html .= 'required="required"';

		$html .= '>';
		$html .= "<option value=\"\">".trim( $field->field_label )."</option>";
		if(count($cinda_options)>0){

			if(!empty($value))
				$option_selected = trim($value);
			else
				$option_selected = "";


			foreach($cinda_options as $option){

				$option = trim($option);

				$html .= "<option ";
				if( $option == $option_selected )
					$html .= "selected";
				$html .= ">".$option."</option>";

			}

		}
		$html .= '</select>';
	}

	else if("dictionary" == $field->field_type){
		$dictionary = new Dictionary( intval($field->field_options) );

		$html .= "<select name=\"".$field->field_name."\" class=\"dictionary\">";

		if(0 < count($dictionary->get_terms())){
			foreach($dictionary->get_terms() as $term){
				$html .= "<option ";
					if($term['name'] == $value)
						$html .= "selected=\"selected\"";
				$html .= " title=\"".$term['description']."\" >".$term['name']."[/]".$term['description']."</option>"; //value=\"".$term['code']."\"
			}
		}else{
			$html .= "<option>".__('No terms found', 'Cinda')."</option>";
		}
		$html .= "</select>";
	}

	if($field->field_description != "")
		$html .= '<div class="description help-text">'.$field->field_description."</div>";

	return $html;
}

/*
'date'=> __('Date','Cinda'),
'datetime'=> __('Date and Time','Cinda'),
'description' => __('Description Text', 'Cinda'),
'dictionary' => __('Dictionary','Cinda'),
'file' => __('File','Cinda'),
'geopos'=>__('Geoposition','Cinda'),
'number'=>__('Number','Cinda'),
'image' => __('Image','Cinda'),
'select' => __('Selection','Cinda'),
'text'=>__('Input Text','Cinda'),
'textarea'=>__('Text Area','Cinda'),
*/
