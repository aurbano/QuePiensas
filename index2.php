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

// Columnas de contenido (de momento las quitamos)

$content['js'][] = 'session';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Inicio</h1>
<div class="paddedContent" style="padding-top:20px;">
    <!-- Comentarios -->
    <?php
    	$tl = new Timeline(2);
		$tl->displayTimeline();
	?>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>