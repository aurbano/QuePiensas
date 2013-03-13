<?php
include('lib/php/session.php');
// Start
if(!$sess->logged() && $fb->logged()){
	// No QP session, valid FB session:
	// Check if user is already in:
	$usid = $fb->checkFBuser();
	if(!$usid){
		// Add new user:
		$fb->addFBuser(true);
		// Register user:
		$usid = $auth->addUser($fb->name(),$fb->email(),'NULL',$fb->fbid);
		$sess->setSecret($usid);
		$newUser = true;
		// Set user ID, avoid new is creation when using $user->set
		$user->set('id',$usid);
		// Set Facebook profile pic
		$user->set('usePic',2,true);
	}
	if(is_numeric($usid) && $usid >0) $sess->loginUser($usid);
	else $sess->set_msg('No ha sido posible iniciar sesion con Facebook');
	
	$user->set('fbuser',$fb->fbid);
	if($newUser) $user->set('usePic',2,true);
	$fb->getFriends();
}else if($sess->logged()){
	if($user->fb()){ $sess->set_msg('Ya tenias Facebook vinculado'); }else{
		// Añadir a la cuenta actual
		$fb->addFBuser(true);
		$user->linkFB($fb->fbid);
		$fb->getFriends();
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Que Piensas</title>
<link rel="stylesheet" media="all" type="text/css" href="http://static.quepiensas.es/common.css" />
<script type="text/javascript" language="javascript">
<!--
window.close();
-->
</script>
</head>

<body>
Procesando... En breves serás redirigido a la aplicacion principal
<small>(Si se queda atascado cierra esta ventana)</small>
</body>
</html>