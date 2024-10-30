<?php

namespace cinda;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CindaQuery{
	
	public static function create_tables(){
		global $data_types;		
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$enum = "'";
		foreach( $data_types as $type => $data )
			$enum .= $type."','";
		$enum = substr( $enum, 0, -2 );
		
		// Table Fields
		$sql = "CREATE TABLE IF NOT EXISTS `".CINDA_TABLE_MODEL_NAME."` (
		    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		    `id_campaign` bigint(20) UNSIGNED NOT NULL,
			`field_position` INT(2) NOT NULL,
			`field_label` VARCHAR(20) NOT NULL,
		    `field_name` VARCHAR(20) NULL,
			`field_type` ENUM(". $enum ."),
			`field_description` TEXT NULL,
			`field_required` SMALLINT(1) NOT NULL DEFAULT 0,
			`field_options` TEXT NULL DEFAULT NULL, 
		    PRIMARY KEY (`id`)
		) ". $wpdb->get_charset_collate();
		
		dbDelta( $sql );
		
		// Table suscriptions
		$sql = 	"CREATE TABLE IF NOT EXISTS `".CINDA_TABLE_SUSCRIPTIONS_NAME."` (
		    `id_campaign` bigint(20) UNSIGNED NOT NULL,
			`id_volunteer` bigint(20) UNSIGNED NOT NULL,
			`date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		    PRIMARY KEY (`id_campaign`,`id_volunteer`)
		) ". $wpdb->get_charset_collate();

		dbDelta( $sql );
		
		// Table TOKENS
		$sql = 	"CREATE TABLE IF NOT EXISTS `".CINDA_TABLE_TOKENS_NAME."` (
			`id_volunteer` bigint(20) UNSIGNED NOT NULL,
			`token` VARCHAR(40) NULL DEFAULT NULL,
			`date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		    PRIMARY KEY (`id_volunteer`,`token`)
		) ". $wpdb->get_charset_collate();
		
		dbDelta( $sql );
		
		// Table suscriptions
		$sql = 	"CREATE TABLE IF NOT EXISTS `".CINDA_TABLE_TRACKINGS_NAME."` (
		    `id` VARCHAR(32) NOT NULL,
			`id_campaign` BIGINT(20) UNSIGNED NOT NULL,
			`id_volunteer` BIGINT(20) UNSIGNED NOT NULL,
			`tracking` LONGTEXT,
			`create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		    PRIMARY KEY (`id`)
		) ". $wpdb->get_charset_collate();
		
		dbDelta( $sql );
	}
	
	public static function get_model($id){
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM `".CINDA_TABLE_MODEL_NAME."` WHERE `id_campaign` = ". $id ." ORDER BY `field_position`;");
	}
	
}