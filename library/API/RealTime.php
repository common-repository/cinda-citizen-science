<?php
namespace cinda\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use WP_Query;
use \cinda\API\Volunteer as API_Volunteer;

class RealTime{
	
	/**
	 * Get a list of contributions that are around a position (LatLng)
	 * @return: idContribution, geoposition LatLng, Campaign Name | 0
	 */
	public static function get_contributions(){
		global $wpdb;
		
		// Set the radius
		if(isset($_POST['radius']) && $_POST['radius'])
			$radius = intval( sanitize_text_field( $_POST['radius'] ) );
		else
			$radius = 500;
		
		// Set the latitude and longitude position
		if(isset($_POST['geopos']))
			$latlng = explode(",", str_replace(array("(",")"," "), '', sanitize_text_field( $_POST['geopos']) ) );
		else
			$latlng = null;
		$lat = ( $latlng[0] ) ? $latlng[0] : 0;
		$lng = ( $latlng[1] ) ? $latlng[1] : 0;
		
		
		$fields = $wpdb->get_col("SELECT f.field_name FROM ".CINDA_TABLE_MODEL_NAME." AS f WHERE f.field_type = 'geopos'");
		$fields = "'".CINDA_PREFIX.join("','".CINDA_PREFIX,$fields)."'";
		
		$sql = "
			SELECT 
				m.post_id as ID,
				@lat:=SUBSTRING(m.meta_value, 1, LOCATE(',', m.meta_value) - 2) as lat,
				@lng:=SUBSTRING(m.meta_value, LOCATE(',', m.meta_value) + 2) as lng,
				@parent:=p.post_parent as parent,
				@name := (SELECT p.post_title FROM ".$wpdb->prefix."posts AS p WHERE p.ID = @parent) as name,
				@distance := (6371 * ACOS(
					COS( RADIANS(".$lat.") )
					* COS(RADIANS( @lat ) )
					* COS(RADIANS( @lng ) - RADIANS(".$lng.") )
					+ SIN( RADIANS(". $lat.") )
					* SIN( RADIANS( @lat ) )
				)) AS distance
			FROM
				".$wpdb->prefix."postmeta as m,
				".$wpdb->prefix."posts as p
			WHERE
				m.meta_key IN (".$fields.")
			AND
				p.ID = m.post_id
			AND
				p.post_type = '".CINDA_PREFIX."contribution'
			GROUP BY distance
			HAVING distance <= ". $radius ."
			;";

		if($ids = $wpdb->get_results($sql, ARRAY_A) ){
			
			foreach($ids as $id){
				$results[] = array(
					'ID' => $id['ID'],
					'name' => $id['name'],
					'geopos' => $id['lat'].",".$id['lng']
				);
			}
			
			echo json_encode($results);
			
		}else{
			echo json_encode(0);
		}
		
		die();
		
	}
	
	/**
	 * Get a list of campaigns that have one or more contributions around a position (LatLng)
	 * @return: array of Campaigns (idCampaign, color, title, contributions (Array: idContribution, LatLng, Date&Time) | 0
	 */
	public static function get_nearbyActivity(){
		global $wpdb;
		
		// Set the radius
		if(isset($_POST['radius']) && $_POST['radius'])
			$radius = intval( sanitize_text_field( $_POST['radius'] ) );
		else
			$radius = 500;
		
		// Set the latitude and longitude position
		if(isset($_POST['geopos']))
			$latlng = explode(",", str_replace(array("(",")"," "), '', sanitize_text_field( $_POST['geopos']) ) );
		else
			$latlng = null;
		$lat = ( $latlng[0] ) ? $latlng[0] : 0;
		$lng = ( $latlng[1] ) ? $latlng[1] : 0;
		
		$fields = $wpdb->get_col("SELECT f.field_name FROM ".CINDA_TABLE_MODEL_NAME." AS f WHERE f.field_type = 'geopos'");
		$fields = "'".CINDA_PREFIX.join("','".CINDA_PREFIX,$fields)."'";
		
		$sql = "
			SELECT 
				m.post_id as ID,
				p.post_parent as parent,
				p.post_date as date,
				@lat:=SUBSTRING(m.meta_value, 1, LOCATE(',', m.meta_value) - 2) as lat,
				@lng:=SUBSTRING(m.meta_value, LOCATE(',', m.meta_value) + 2) as lng,
				@distance := (6371 * ACOS(
					COS( RADIANS(".$lat.") )
					* COS(RADIANS( @lat ) )
					* COS(RADIANS( @lng ) - RADIANS(".$lng.") )
					+ SIN( RADIANS(". $lat.") )
					* SIN( RADIANS( @lat ) )
				)) AS distance
			FROM
				".$wpdb->prefix."postmeta as m,
				".$wpdb->prefix."posts as p
			WHERE
				m.meta_key IN (".$fields.")
			AND
				p.ID = m.post_id
			AND
				p.post_type = '".CINDA_PREFIX."contribution'
			GROUP BY distance
			HAVING distance <= ". $radius ."
			ORDER BY parent ASC
			;";
		
		if($contributions = $wpdb->get_results($sql,ARRAY_A)){
			
			$parents = array_unique( $wpdb->get_col($sql, 1) );
			
			$i = 0;
			foreach ($parents as $id){
				$results[$i] = array(
					'ID' => $id,
					'name' =>get_the_title($id),
					'color'=>get_post_meta($id,CINDA_PREFIX.'color',true)
				);
				
				foreach($contributions as $contribution){
					if($contribution['parent'] == $id)
						$results[$i]['contributions'][] = array(
							'ID' => $contribution['ID'],
							'date' => $contribution['date'],
							'geopos' => $contribution['lat'].",".$contribution['lng']
						);
				}
				
				$i++;
			}
			
			echo json_encode($results);
			
		}else{
			echo json_encode(0);
		}
		
		die();
	
	}
	
	/**
	 * Print json array with all information for the watchface application
	 */
	public static function get_watchfaceData(){
		global $wp;
		global $wpdb;
		
		$token = (isset($_GET['token'])) ? sanitize_text_field($_GET['token']) : "";

		if($id_volunteer = API_Volunteer::get_volunter_id($token)){
			$campaigns = new CampaignsList(array('orderby'=>'suscriptions', 'number' => 10, 'token'=>$token));
			$volunteers = new VolunteerList(array('number' => 10));
			$contributions = new ContributionList(array('volunteer' => $id_volunteer));
			
			$response = array();
			
			// CAMPAIGNS
			$response['campaigns'] = array();
			if(0 < count($campaigns = $campaigns->get_campaigns())){
				// Calc Angle
				$total_suscriptors = 0;
				foreach ($campaigns as $campaign){
					$total_suscriptors += $campaign->suscriptions;
				}
				
				$total_angle = 0;
				foreach ($campaigns as $campaign){
					$angle = round( ( ( ($campaign->suscriptions * 100) / $total_suscriptors ) * 360 ) / 100 );
					$total_angle += $angle;
					$response['campaigns'][] = array(
						'id' => $campaign->ID,
						'color' => $campaign->color,
						'name' => $campaign->title,
						'suscriptions' => $campaign->suscriptions,
						'angle' => $angle,
					);
				}

				if($total_angle < 360){
					$dif = 360 - $total_angle;
					$response['campaigns'][ count( $response['campaigns'] ) - 1 ]['angle'] += $dif;
				}
			}
			
			// VOLUNTEERS
			$response['volunteers'] = array();
			if(0 < count($volunteers = $volunteers->get_volunteers())){
				
				$total_contributions = 0;
				foreach ($volunteers as $volunteer){
					$total_contributions += $volunteer->contributions;
				}
				
				foreach($volunteers as $volunteer){
					$response['volunteers'][] = array(
						'id' => $volunteer->get_ID(),
						'name' => $volunteer->nickname,
						'avatar' => $volunteer->avatar,
						'contributions' => $volunteer->contributions,
						'angle' => round( ( ( ($volunteer->contributions * 100) / $total_contributions ) * 360 ) / 100 ),
					);
				}

			}
			
			// VOLUNTEERS
			$total_contributions = count($contributions = $contributions->get_contributions());
			if(0 < $total_contributions){
				$response['contributions'] = array();
				foreach($contributions as $contribution){
					$data = $contribution->serialize(false);
					unset( $data['author_id'] );
					unset( $data['author_name'] );
					$data['angle'] = round( ( ( (1 * 100) / $total_contributions ) * 360 ) / 100 );
					$response['contributions'][] = $data;
				}	
			}

			// TIMELINE 
			$response['timeline'] = array();
			$from = date('Y-m-1 00:00:00', strtotime("-5 month", strtotime( 'm' )));
			$to = date('Y-m-d 00:00:00', strtotime("+1 day"));
			
			$sql = "SELECT CONCAT( MONTH( p.post_date ), '-', YEAR( p.post_date ) ) AS month, COUNT( p.ID ) AS contributions
					FROM ".$wpdb->prefix."posts AS p 
					INNER JOIN ".$wpdb->prefix."postmeta AS m ON p.ID = m.post_id 
					WHERE m.meta_key = '".CINDA_PREFIX."author_id' 
					AND m.meta_value = ".$id_volunteer." 
					AND STR_TO_DATE( p.post_date, '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE( '".$from."', '%Y-%m-%d %H:%i:%s') 
					AND STR_TO_DATE( p.post_date, '%Y-%m-%d %H:%i:%s') <= STR_TO_DATE( '".$to."', '%Y-%m-%d %H:%i:%s')
					GROUP BY MONTH( p.post_date );";
			
			$months =  $wpdb->get_results($sql, ARRAY_A);
			
			for ($i = 5, $e = 0; $i >= 0; $i--, $e++) {
				$month_tmp = date('n-Y', strtotime("-$i month", strtotime($to) ));
				foreach($months as $month){
					if( $month['month'] == $month_tmp ){
						$response['timeline'][$e] = array(
							'month' => $month['month'],
							'contributions' => intval($month['contributions']),
						);
						break;
					}
				}
				
				if(!isset($response['timeline'][$e])){
					$response['timeline'][$e] = array(
						'month' => $month_tmp,
						'contributions' => 0
					);
				}
				
			}

			die(json_encode($response));
		}else{
			die(json_encode(0));
		}
	}
}