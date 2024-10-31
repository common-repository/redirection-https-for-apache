<?php

/*
Plugin Name: Redirection HTTPS for Apache
Plugin URI: 
Description: Plugin para crear redireccion de HTTP a HTTPS
Version:1.0
Author:Infranetworking
Author URI: https://www.infranetworking.com/
License: GPL2
*/

if ( ! function_exists( 'get_home_path' ) ) {
	include_once ABSPATH . '/wp-admin/includes/file.php';
}
if ( ! function_exists( 'insert_with_markers' ) ) {
	include_once ABSPATH . '/wp-admin/includes/misc.php';
}

//Return redirection HTTPS code for htaccess
function rhfpa_get_code() {
return "# BEGIN Redirection HTTPS for Apache (Infranetworking)
# The directives (lines) between `BEGIN Redirect to HTTPS` and `END Redirect to HTTPS` are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
# END Redirection HTTPS for Apache (Infranetworking)";	
}

//Check if .htaccess if redirecction not exists then create it
function rhfpa_apache301_install(){

	$code = rhfpa_get_code();

	$htaccess_path = $_SERVER["DOCUMENT_ROOT"]."/.htaccess";

	if (!file_exists($htaccess_path)) {
		$file = fopen($htaccess_path,'w');
		fwrite($file, "\n".$code);
		fclose($file);
	} else {

		$htaccess_content = file_get_contents($htaccess_path);

		if (!extract_from_markers($htaccess_path,'Redirection HTTPS for Apache (Infranetworking)')  || !strstr($htaccess_content,$code))	 {
			$save = "\n".$code . "\n\n" . $htaccess_content;
			$file = fopen($htaccess_path,'w');
			fwrite($file, $save);
			fclose($file);
		}
	}
}

#Remove https redirecton from htaccess
function rhfpa_apache301_disable(){

	$code = rhfpa_get_code();

	$htaccess_path = $_SERVER["DOCUMENT_ROOT"] . "/.htaccess";

	$htaccess_content = file_get_contents($htaccess_path);

	if (strstr($htaccess_content,"\n".$code."\n\n")){
		$htaccess_content = str_replace("\n".$code."\n\n","",$htaccess_content);
	} elseif(strstr($htaccess_content,$code."\n\n")){
		$htaccess_content = str_replace($code."\n\n","",$htaccess_content);
	} else {
		$htaccess_content = str_replace($code,"",$htaccess_content);
	}

	$file = fopen($htaccess_path,'w');
	fwrite($file, $htaccess_content);
	fclose($file);
}

#Activate, add redirection
add_action( 'init', 'rhfpa_apache301_install');

#Remove redirecction
add_action('deactivated_plugin','rhfpa_apache301_disable')

?>
