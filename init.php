<?php
/*
Plugin Name: CINDA: Volunteers Networks
Plugin URI: http://www.cinda.science/
Description: Gestión de campañas para acciones de voluntariado.
Version: 1.4.3
Author: SI2 Pequeñas y Grandes Soluciones
Author URI: http://si2.info
Text Domain: Cinda
Domain Path: /languages

Copyright 2015-2016 - SI2 Pequeñas y Grandes Soluciones  (email : contacto@si2.info)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

define('CINDA_NAME','Cinda');

// Plugin Versión
define('CINDA_VERSION','1.4.3');

define('CINDA_PREFIX','cinda_');

// Database
define('CINDA_TABLE_MODEL_NAME', $wpdb->prefix.CINDA_PREFIX."model_fields");
define('CINDA_TABLE_SUSCRIPTIONS_NAME', $wpdb->prefix.CINDA_PREFIX."suscriptions");
define('CINDA_TABLE_TOKENS_NAME', $wpdb->prefix.CINDA_PREFIX."tokens");
define('CINDA_TABLE_TRACKINGS_NAME',$wpdb->prefix.CINDA_PREFIX."trackings");
define('CINDA_DATABASE_VERSION','0.0.1');

define('CINDA_DEFAULT_COLOR','#9E9E9E');

// Plugin Slug
define('CINDA_SLUG', plugin_basename( __FILE__ ) );

// Plugin URL
define('CINDA_URL', plugin_dir_url( __FILE__ ) );

// Plugin DIRECTORY
define('CINDA_DIR', plugin_dir_path( __FILE__ ) );

// Cookie Session
define('CINDA_COOKIE', CINDA_PREFIX.'session' );

// Type of data values acepted
global $data_types;
$data_types = array(
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
);

// PLUGIN FUNCTIONS
require_once( 'library/functions.php' );

/**
 * On plugin activation
 */
register_activation_hook( __FILE__, array( '\cinda\Cinda', 'install' ) );
