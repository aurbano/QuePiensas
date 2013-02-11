<?php
// Seguridad
if(!($sess instanceof Session)){
	header('Location: /');
	die();	
}
if(!$sess->logged()){
	header('Location: /');
	die();	
}

include('lib/php/funciones.php');
include('lib/php/style.php');
// Timeline class
include('lib/php/timeline.php');
	
// ----------------------------

$content['title'] = 'Inicio';
$content['fancybox'] = true;

// Inicio de base de datos
$db=$sess->db();
// Columnas de contenido (de momento las quitamos)
// Columnas de la derecha
	$last = $db->query('SELECT * FROM (SELECT personas.name, personas.id, personas.visits, (SELECT COUNT(*) FROM comments WHERE comments.pid=personas.id) AS comentarios FROM personas,comments WHERE comments.pid=personas.id AND comments.timestamp>'.(time()-262974383).' GROUP BY id LIMIT 5) as subseet ORDER BY comentarios DESC');

//Generamos columna last person
$temp='';
while($data=$db->fetchNextObject($last)){
	$temp.='<li><a href="/'.$data->id.'">'.$data->name.'</a>
		<a class="stats" href="/'.$data->id.'">
        	<font>'.$data->comentarios.'</font> comentarios
		</a>
		<a class="stats" href="/'.$data->id.'">
        	<font>'.$data->visits.'</font> visitas
		</a>
	</li>';
}

// Columnas de contenido:
$content['cols'][] = '<h3 title="Personas más comentadas en este momento en QuePiensas" class="tooltip">Personas del momento</h3>
<ul class="col personCol">
	'.$temp.'
</ul>';

$content['js'][] = 'session';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';
$content['css'][] = 'columna';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Inicio</h1>
<div class="paddedContent" style="padding-top:0;">
    <!-- Comentarios -->
    <?php
    	$tl = new Timeline(2);
		$tl->displayTimeline();
	?>
</div>

<?php 
include('lib/content/footer.php');
?>