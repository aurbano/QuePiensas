<?php
/**
 * Autocomplete backend
 * Receives the word in $_GET['term'];
 * @author Alejandro U. Alvarez
 */
if(!$_GET['term'] || strlen($_GET['term'])<3) die(json_encode(array('status'=>'error')));
include('lib/php/session.php');
include('lib/php/style.php');
$person = clean($_GET['term']); // Usamos la de style

// Ejecutamos la busqueda
$db = $sess->db();
$people = $db->query('SELECT personas.id, personas.name FROM personas, users WHERE personas.usid = users.id AND personas.name LIKE \''.$person.'%\' ORDER BY name ASC LIMIT 0,8');
$return = array();
$data = array();
$i = 0;
while($a = $db->fetchNextObject($people)){
	$return[$i]['label'] = $a->name;
	$return[$i]['value'] = $a->id;
	$i++;
}
echo json_encode($return);