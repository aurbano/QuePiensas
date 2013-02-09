<?php
include('lib/php/session.php');
// Procesa las busquedas y redirige a donde tiene que redirigir:
if(!isset($_POST['search']) || strlen($_POST['search']) <= 4){
	header('Location: /');
	die();
}
// CONFIG:

$smin = 1; // MATCH AGAINST score MINIMUM:

// ---------------------------

$person = strtolower(trim(addslashes(htmlspecialchars($_POST['search'],ENT_COMPAT,'UTF-8')) ));
$parts = explode(' ',$person);
// Primero probaremos con una exact match
$db = $sess->db();
$id = $db->queryUniqueValue('SELECT id FROM personas WHERE name LIKE \''.$person.'\'');
// If there is an exact match, send there
if($id){ header('Location: /'.$id); die(); }

// No se encuentra la persona:
// Vamos a buscar mejor, con match against
// Y la distancia
// MYSQL DISTANCE
include('lib/php/distance.php');
if(!$_SESSION['debug']){
	//$people = $db->query('SELECT personas.id, personas.name, location.region, '.distance($user->getLoc($user->id())).', (MATCH(personas.name) AGAINST(\''.$person.'\')) AS score FROM personas, users, location WHERE users.ip = location.ip AND personas.usid = users.id AND MATCH(personas.name) AGAINST(\''.$person.'\') > '.$smin.' ORDER BY distance, score DESC LIMIT 0,5');
	// No distance
	$people = $db->query('SELECT personas.id, personas.name, location.region, (MATCH(personas.name) AGAINST(\''.$person.'\')) AS score FROM personas, users, location WHERE users.ip = location.ip AND personas.usid = users.id AND MATCH(personas.name) AGAINST(\''.$person.'\') > '.$smin.' AND LENGTH(personas.name) - LENGTH(REPLACE(personas.name, \' \', \'\')) + 1 = \''.sizeof($parts).'\' ORDER BY score DESC LIMIT 5');
	if($db->numRows($people)<1){ header('Location: /'.str_replace(' ','-',$person)); die(); }
}else{
	// Unlimited query:
	//$people = $db->query('SELECT personas.id, personas.name, location.region, '.distance($user->getLoc($user->id())).', (MATCH(personas.name) AGAINST(\''.$person.'\')) AS score FROM personas, users, location WHERE users.ip = location.ip AND personas.usid = users.id AND MATCH(personas.name) AGAINST(\''.$person.'\') > '.$smin.' AND LENGTH(personas.name) - LENGTH(REPLACE(personas.name, \' \', \'\')) + 1 = \''.sizeof($parts).'\' ORDER BY distance, score DESC');
	//$people = $db->query('SELECT personas.id, personas.name, location.region, '.distance($user->getLoc($user->id())).', (MATCH(personas.name) AGAINST(\''.$person.'\')) AS score FROM personas, users, location WHERE users.ip = location.ip AND personas.usid = users.id ORDER BY distance, score DESC');
	
	// No distance
	$people = $db->query('SELECT personas.id, personas.name, location.region, (MATCH(personas.name) AGAINST(\''.$person.'\')) AS score FROM personas, users, location WHERE users.ip = location.ip AND personas.usid = users.id AND MATCH(personas.name) AGAINST(\''.$person.'\') > '.$smin.' AND LENGTH(personas.name) - LENGTH(REPLACE(personas.name, \' \', \'\')) + 1 = \''.sizeof($parts).'\' ORDER BY score DESC LIMIT 10');
}

// Ordernar por distancia
if($db->numRows($people)>1){
	$cur = 0;
	$peopleArray = array();
	while($a = $db->fetchNextObject($people)){
		$cmp1 = str_replace(' ','',strtolower($person));
		$cmp2 = str_replace(' ','',strtolower($a->name));
		$distance = levenshtein($cmp1,$cmp2);
		if($distance>4) continue;
		// Guardar valores
		$dist[$cur] = $distance;
		$peopleArray[$cur]['id'] = $a->id;
		$peopleArray[$cur]['name'] = $a->name;
		$peopleArray[$cur]['region'] = $a->region;
		$cur++;
	}
	if($cur==0 && !$_SESSION['debug']){ header('Location: /'.str_replace(' ','-',$person)); die(); }
}
// Ahora es preciso explicar que se encontraron 1 o mas personas
// que podrian ser las que buscamos. Y el user tendra la opcion de
// seleccionarlas si lo considera apropiado.
$person = ucwords($person);

$content['title'] = 'Buscando a '.$person;

include('lib/content/top.php');

echo '<h1>Quiz&aacute;s estes buscando a... </h1><ul>';
// Primero comprobamos con Levenshtein
if($db->numRows($people)>1){
	function cmp($a,$b){
		if($a == $b) return 0;
		return($a < $b) ? -1 : 1;
	}
	// Now sort array
	uasort($dist,'cmp');
	for($i=0;$i<$cur;$i++){
		//if($dist[key($dist)]>4) break;
		echo '<li><a href="/'.$peopleArray[key($dist)]['id'].'" title="Ir a su perfil">'.ucwords($peopleArray[key($dist)]['name']).'</a> <small>('.htmlentities($peopleArray[key($dist)]['region']).')</small></li>';
		next($dist);
	}
}else{
	$a = $db->fetchNextObject($people);
	echo '<li><a href="/'.$a->id.'" title="Ir a su perfil">'.ucwords($a->name).'</a> <small>('.htmlentities($a->region).')</small></li>';
}
$totalPeople = $db->numRows($people);
if($i>0) $totalPeople = $i;
echo '</ul><p align="center"><a href="/'.str_replace(' ','-',$person).'">';
if($totalPeople>1) echo 'No es ninguna de esas';
else echo 'No es esa persona';
echo '</a></p><p align="center"><small>La localizaci&oacute;n es aproximada, y en algunas ocasiones la ciudad puede no corresponderse</small></p>';
include('lib/content/footer.php');