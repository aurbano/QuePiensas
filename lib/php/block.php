<?php
/**
 * This a security file that blocks access to certain pages
 *
 * If a user tries to access a page without permission it will be sent to an error page with more information
 * blocked pages can be specified in an array below.
 * @author Alejandro U. Alvarez
 * @version 1
 * @package Security
 */

/**
 * Array of pages to be blocked
 */
$blockPages = array('login');
if(!($sess instanceof Session)){
	$_GET['err']=500;
	include($sess->home.'errorDoc.php');
	die();
}
// If the session exists
if(in_array($sess->curPage,$blockPages)){
	$_GET['err']=500;
	include($sess->home.'errorDoc.php');
	die();
}