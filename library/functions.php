<?php

/**
 * Load Cinda Class
 */
require_once( 'Cinda.php' );

/**
 * Global Functions
 */
require_once 'global.php';

/**
 * Functions for Custom Post Types
 */
require_once 'CPT/functions.php';

/**
 * APP
 */
require_once 'APP/functions.php';


function get_cinda(){
	return \cinda\Cinda::init();
}

/**
 * Initialize CINDA
 * @var \cinda\Cinda $CINDA
 */
$CINDA = get_cinda();

cindaApp();