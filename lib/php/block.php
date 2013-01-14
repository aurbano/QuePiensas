<?php
// This a security file that blocks access to certain pages
// when accessed directly
$blockPages = array('login');
if(!($sess instanceof Session)){
	$_GET['err']=500;
	include($sess->home.'errorDoc.php');
	die();
}
// sess exists:
if(in_array($sess->curPage,$blockPages)){
	$_GET['err']=500;
	include($sess->home.'errorDoc.php');
	die();
}