<?php
include('lib/php/session.php');
// Procesa las busquedas y redirige a donde tiene que redirigir:
if(!isset($_POST['search']) || strlen(trim(rtrim($sess->clean($_POST['search']))))<5){
	header('Location: /');
	die();
}
/*$parts = explode(' ',$sess->clean($_POST['search']));
if(sizeof($parts)<2){
	$_SESSION['lastSearch'] = $sess->clean($_POST['search']);
	$sess->setMsg('Debes buscar con al menos un apellido');
	header('Location: /');
	die();
}
$person = strtolower($sess->clean(str_replace(' ','-',$_POST['search'])));
header('Location: /'.$person);
die();*/
$nombre = strtolower($sess->clean($_POST['search']));
$parts = explode(' ',$sess->clean($_POST['search']));
// Ahora componemos la query
$db = $sess->db();
$id = $db->queryUniqueValue('SELECT id FROM personas WHERE name LIKE \''.$nombre.'\'');
// If there is an exact match, send there
if($id){ header('Location: /'.$id); die(); }
// No se encuentra la persona:
// Pues vamos a crearla :D
include('lib/php/person.php');
$p = new Person(0,$nombre);
// Tecnicamente ya deberiamos tener la ID:
if($p->pid!==0){ header('Location: /'.$p->pid); die(); }
echo 'Ha ocurrido algo raro';