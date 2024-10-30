<?php

/**
 * Add custom single templates (in plugin or theme folder) for Custom Post Types
 * @param string $single_template
*/
function cinda_CPT_single_templates($single_template) {
	global $post;

	/* CAMPAIGN */
	if ($post->post_type == CINDA_PREFIX . "campaign"){
		
		if($template = cinda_get_template_dir('single-campaign.php'))
			$single_template = $template;
		
		else if($template = cinda_get_template_dir('campaign.php'))
			$single_template = $template;

	}

	/* CONTRIBUTION */
	else if ($post->post_type == CINDA_PREFIX . "contribution"){

		if($template = cinda_get_template_dir('single-contribution.php'))
			$single_template = $template;
		
		else if($template = cinda_get_template_dir('contribution.php'))
			$single_template = $template;

	}

	return $single_template;
}
add_filter('single_template','cinda_CPT_single_templates');

/**
 * Check if file single template exists
 */
function cinda_get_template_dir($filename){
	global $CINDA;
	
	// Theme template page
	if(file_exists($CINDA->theme_uri() . "/" . $filename)){
		return $CINDA->theme_uri() . "/" . $filename;
	}

	// Theme template page in /cinda/templates/
	else if(file_exists($CINDA->theme_uri() . "/cinda/" . $filename)){
		return $CINDA->theme_uri() . "/cinda/" . $filename;
	}

	// Plugin template
	else if(file_exists($CINDA->plugin_uri() . "/templates/" . $filename)){
		return $CINDA->plugin_uri() . "/templates/" . $filename;
	}

	return false;
}