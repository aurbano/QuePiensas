<?php
include('lib/php/session.php');
// --------- CONFIGURATION ----------------
//
//	Numero maximo de nombre+apellidos para buscar
		$maxSearchTerms = 4;
// ----------------------------------------

// Variables de la pagina
$person = ucwords(strtolower(str_replace('-',' ',$sess->clean(trim($_GET['person'])))));
$content['title'] = 'Resultados de la b&uacute;squeda: '.$person;

// Construccion de query SQL
$partes = explode(' ',$person);
$search = 'personas.name LIKE \'%'.$partes[0].'%\'';
$total = sizeof($partes);
if($total>$maxSearchTerms) $total = $maxSearchTerms;
for($i=1;$i<sizeof($partes);$i++){
	$search .= 'AND personas.name LIKE \'%'.$partes[$i].'%\'';
}
// Inicio de objeto gestor de base de datos:
$db = $sess->db();
// Ejecutar query
$personas = $db->query('SELECT personas.id, personas.name FROM personas WHERE '.$search);
$totalPersonas = $db->numRows($personas);
// Disfruta del buen codigo ;) Mira que guapo lo que sigue jaja
// Como vienen ordenadas por distancia del que busca, ahora mostramos los resultados
// ordenados por probabilidad de que sea.
include('lib/content/top.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Resultados de busqueda</title>
</head>

<body>
</body>
</html>