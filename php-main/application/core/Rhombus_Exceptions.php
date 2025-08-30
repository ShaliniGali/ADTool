<?php


class Rhombus_Exceptions extends CI_Exceptions {
/*
     public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	 {
	 	$templates_path = config_item('error_views_path');
	 	if (empty($templates_path))
	 	{
	 		$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
	 	}

	 	if (is_cli())
	 	{
	 		$message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
	 		$template = 'cli'.DIRECTORY_SEPARATOR.$template;
	 	}
	 	else
	 	{
	 		set_status_header($status_code);
	 		$message = '<p>'.(is_array($message) ? implode('</p><p>', $message) : $message).'</p>';
	 		
	 		if (
	 			isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
	 			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
	 		) {
	 			$template = 'html'.DIRECTORY_SEPARATOR.'error_json';
	 		} else if (class_exists('CI_Controller')) {
	 			$template = 'html'.DIRECTORY_SEPARATOR.'error_rhombus';
	 		} else {
	 			$template = 'html'.DIRECTORY_SEPARATOR.'error_rhombus_text';
	 		}
	 	}

	 	log_message('error', $message);

	 	if (ob_get_level() > $this->ob_level + 1)
	 	{
	 		ob_end_flush();
	 	}
		
	 	ob_start();
		
	 	include($templates_path.$template.'.php');
	 	$buffer = ob_get_contents();
	 	ob_end_clean();
	 	return $buffer;
	 }
	 */
}