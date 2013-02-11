<?php
/**
 *	XML File Generator
 *	This will help avoid having to create many files for XML feed generation
 *	Generation will depend on the GET variable "type"
 */

// ----------- Functions ------------

/**
 *	Setup the appropriate headers for cache
 *	@param boolean Whether to use cache or not
 *	@param int	Time to live in seconds
 */
function cache($use, $ttl=0){
	if($use){
		header('Pragma: public');
		header('Cache-Control: max-age=' . $ttl);
		header('Expires: '.gmdate('D, d M Y H:i:s', time()+$ttl).' GMT');
	}else{
		// Ensure cache will not be used on this file
		header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1 
    	header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1 
		header('Pragma: no-cache'); 
    	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');                 // Date in the past 
	}
}

// ------------- Main ------------

// Compress output with gzip
ob_start("ob_gzhandler");
// Start XML header
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
if(!isset($_GET['type'])){
	cache(false);
	echo '<error>404 Not found</error>';
	ob_end_flush();
	die();
}
// Start the XML generation
// Add new files with case '';
// Remember to setup cache using cache(true/false);
switch($_GET['type']){
	default:
		cache(false);
		echo '<error>404 Not found</error>';
}
ob_end_flush();