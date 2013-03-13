<?php
include('lib/php/session.php');
// Start
if($_GET['oauth_token'] && $_GET['oauth_verifier'] && !$sess->logged()){
	try{
		// Get secret token
		$tw->twitter->setToken($_GET['oauth_token']);
		$token = $tw->twitter->getAccessToken();
		$_SESSION['twitter']['oauth_token'] = $token->oauth_token;
		$_SESSION['twitter']['oauth_secret'] = $token->oauth_token_secret;
		// Reload object
		unset($tw);
		$tw = new Twitter();
		// Object should have now loaded with tokens
		// it takes them from the session variables
		$sess->debug('Logged twitter user: '.$tw->twid);
		// Check if user is already in:
		$usid = $tw->checkTWuser($tw->twid);
		// Update Twitter data in DB
		$tw->addTWuser($token->oauth_token,$token->oauth_token_secret);
		if(!$tw->twid){
			//$sess->debug('No hay ID',true,false,true);
			echo 'No va. ostia';
			die();
			$sess->set_msg('No se ha podido iniciar sesión en Twitter, por favor inténtalo más tarde.');
		}
		if(!$usid){
			// Register user:
			$usid = $auth->addUser($tw->name(),false,false,0,$tw->twid);
			$sess->setSecret($usid);
			// Set Twitter profile pic
			$user->set('id',$usid);
			$user->set('usePic',3,true);
		}
		if(is_numeric($usid) && $usid >0) $sess->loginUser($usid);
		else $sess->set_msg('No ha sido posible iniciar sesion con Twitter');
		// Set bio if doesn't have one yet
		$user->set('twuser',$tw->twid);
	}catch(Exception $e){
		die($e->getMessage());
	}
}else if($sess->logged()){
	// Link account
	if($user->tw()){ $sess->set_msg('Ya tenias Twitter vinculado'); }else{
		$sess->debug('Vinculando Twitter a tu cuenta actual');
		// Añadir a la cuenta actual
		$tw->twitter->setToken($_GET['oauth_token']);
		$token = $tw->twitter->getAccessToken();
		
		$_SESSION['twitter']['oauth_token'] = $token->oauth_token;
		$_SESSION['twitter']['oauth_secret'] = $token->oauth_token_secret;
		
		unset($tw);
		$tw = new Twitter();
		
		// Update Twitter data in DB
		$tw->addTWuser($token->oauth_token,$token->oauth_token_secret,true);
		
		$user->linkTW($tw->twid);	
	}
}
//$sess->debug('END',true);

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
</body>
</html>