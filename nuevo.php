<?php
// Seguridad
include('lib/php/session.php');

if(!$sess->logged()){
	header('Location: /');
	die();	
}

// Lista de ID de comentarios que hace falta actualizar
$db = $sess->db();
$newMsgs = $db->query('SELECT comments.id FROM comments, replies WHERE comments.id = replies.id AND replies.rid IN (SELECT id FROM comments WHERE usid = '.$user->id.') AND comments.state=0');
if($db->numRows($newMsgs)>0){
	// Create list
	while($a = $db->fetchNextObject($newMsgs)){
		$toUpdate .= ','.$a->id;	
	}
	$toUpdate = substr($toUpdate,1); // Quita la primera coma
}

include('lib/php/funciones.php');
include('lib/php/style.php');
// Timeline class
include('lib/php/timeline.php');

// ----------------------------

$content['title'] = 'Nuevo';
$content['fancybox'] = true;

// Columnas de contenido (de momento las quitamos)

$content['js'][] = 'session';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Nuevo</h1>
<div class="paddedContent">
	<p>Respuestas a tus comentarios</p>
    <?php
    	$tl = new Timeline(4,$user->id);
		$tl->displayTimeline();
	?>
</div>
</div>

<?php 
include('lib/content/footer.php');
// Odio hacer esto, pero tiene que ser despues de ejecutar timeline...
// Quitamos comentarios nuevos
if($toUpdate) $db->execute('UPDATE comments SET comments.state=2 WHERE comments.id IN ('.$toUpdate.')');
?>