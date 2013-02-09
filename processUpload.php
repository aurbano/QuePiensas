<?php
// Picture upload processor
// When done, redirects to referrer
include('lib/php/session.php');
if(strlen($sess->referrer)<1) $sess->referrer = '/';

// Requires logged in user to post
if(!$sess->logged()){
	$sess->set_msg('Debes iniciar sesión para subir una foto');
	header('Location: /do/login');
	die('Debes iniciar sesión para subir una foto');	
}

// Procesado de la foto
include('lib/php/uploader.php');

// Nuevo objeto:
//Initialize the object:
$up = new Uploader(200000,'gif,png,jpeg,jpg');
// Eliminamos una posible foto anterior de ese usuario:
if($user->hasPic()){
	if(!unlink('img/user/uploads/'.$user->id().'.gif')){
		$sess->set_msg('No ha sido posible subir tu foto, intentalo mas tarde');
		header('Location: '.$sess->referrer); // Redireccion a la pagina anterior
		die('No ha sido posible eliminar tu foto anterior');
	}
}
// Subimos el archivo
if($up->upload('pic','img/user/uploads/',$user->id().'.gif')){
	// Siempre en gif, no se si eso influira para jpeg por ejemplo, hay que mirarlo
	// y unificar formatos o algo
	// Ahora hay que seleccionar la imagen subida como foto de perfil
	$user->set('usePic',1,true);
}
header('Location: '.$sess->referrer); // Redireccion a la pagina anterior
die('Haz click <a href="'.$sess->referrer.'">aqui</a> si no eres redirigido automaticamente');