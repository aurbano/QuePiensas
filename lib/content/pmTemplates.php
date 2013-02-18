<?php
/**
 * @file
 * Private Message Templates
 * This file will have an array of supported codes, which, if found in a PM, will display
 * a custom template.
 * 
 * Templating system:
 * {[@CODE@][{Var1}:{value};{Var2}:{value}]}
 * 
 * Available templates:
 * 	- 101: The welcome message from the administration, requires 1 variable: user name.
 */
function decodePM($code){
	// Runs the PM against a regexp to see if it matches a known code
	return preg_replace_callback('$\{\[@([0-9]+)@\](?:\[(?:\{(.+)\};?)*\])?\}$','replacePM',$code);
}
function replacePM($matches){
	$msg = $matches[0];
	if(count($matches)<2) return $msg;
	// List of supported PMs
	$supported = array('101');
	if(!in_array($matches[1], $supported)) return $msg;
	// Message Code is valid
	switch($matches[1]){
		case '101':
			// I need the username
			global $user;
			// Requiere 1 variable, el nombre.
			$msg = "Hola {$user->g('name')}!\nBienvenido a Que Piensas, te saludo en nombre de todo el equipo! Siempre que tengas alguna duda podrás dirigirte a mi respondiendo a este privado. También puedes ponerte en contacto con soporte@quepiensas.es desde tu email\n\nEspero que disfrutes de la experiencia, y la compartas con tus amigos,\nUn abrazo";
			break;
	}
	return $msg;
}
