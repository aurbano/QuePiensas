<?php
// Verifica nombre de usuario/pass
// Si estan bien los guarda en la tabla usuarios e inicia sesion
if(!isset($_GET['id']) || !isset($_GET['code'])){ header('Location: /'); die(); }
$code = $_GET['code'];
$id = $_GET['id'];
include('lib/php/session.php');
if(!$code){ header('Location: /'); die(); }
if(!$sess->valid($id,'int')){ header('Location: /'); die(); }
$db = $sess->db();
// Trasladamos el usuario a users, luego lo eliminaremos de unrev
$move = $db->execute('INSERT INTO `users` (name, email, pass) SELECT name, email, pass FROM unrev_users WHERE id = \''.$_GET['id'].'\' AND SHA1(secret)=\''.$code.'\' LIMIT 1');
$usid = $db->lastInsertedId();
if($move){
	// Listo, elimina de unrev_users y redirige a login
	$db->execute('DELETE FROM unrev_users WHERE id = \''.$_GET['id'].'\' AND SHA1(secret)=\''.$code.'\' LIMIT 1');
	// Send welcome PM
	if($usid >0){
		$msg = "Hola $name!\nBienvenido a Que Piensas, te saludo en nombre de todo el equipo! Siempre que tengas alguna duda podrás dirigirte a mi respondiendo a este privado. También puedes ponerte en contacto con soporte@quepiensas.es desde tu email\n\nEspero que disfrutes de la experiencia, y la compartas con tus amigos,\nUn abrazo";
		$db->execute('INSERT INTO `msg` (`thread`,`from`,`to`,`msg`,`status`,`timestamp`) VALUES (\'\',\'1\',\''.$usid.'\',\''.$msg.'\',\'0\',\''.time().'\');');
	}
	
	header('Location: /do/login');
	die('Email verificado! Ya puedes iniciar sesión con tu cuenta');	
}else{
	$sess->set_msg('No ha sido posible verificar tu email, inténtalo de nuevo más tarde.');
	header('Location: /do/login');
	die('Error');	
}