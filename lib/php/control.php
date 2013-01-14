<?php
/* VERY IMPORTANT FILE
 * Este archivo se encarga de asegurar el control de acceso a C13
 * NO se encarga de gestionar las cuentas, unicamente se asegura
 * de que nadie que no deba entrar pueda entrar
 *
 * El control de acceso funcionara (De momento) de la siguiente manera
 * Para entrar, no puedes venir de un enlace (Referrer = '')
 * Ademas, la primera entrara, tiene que ser a /open/#USERID
 * El sistema mirara si esa ID puede entrar, si es asi, te llevara a la ventana de login,
 * si no, o si fallas el login 2 veces, banea tu IP
 * En caso de entrar bien, te abre el acceso completo a la web
 */
// CONFIGURATION
$allowedPages = array('index','process');
// Funciones de control
function out($msg=false){
	global $sess;
	if(!is_numeric($msg)){
		$sess->set_msg($msg);
		header('Location: /');
	}else{
		header('Location: /'.$msg);
	}
	die('QuePiensas.es');		
}
// Inicio de sesion
if(!$_SESSION) session_start();

// Comprobacion de variables de usuario
if(!$_SESSION['user']){
	// No hay sesion iniciada
	// Comprobar si puede entrar
	if(!$_GET['usid']){
		if(!in_array($sess->curPage,$allowedPages)){
			out(403);
		}
	}else{
		include($sess->home.'login.php');
		die();	
	}
}