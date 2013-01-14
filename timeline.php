<?php
include('lib/php/session.php');

include('lib/php/funciones.php');
include('lib/php/style.php');
// Timeline class
include('lib/php/timeline.php');
	
// ----------------------------

$content['title'] = 'Timeline';

// Columnas de contenido (de momento las quitamos)

$content['js'][] = 'session';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Novedades</h1>
<div class="paddedContent">
	
	<h3>Novedades</h3>
    <!-- Comentarios -->
    <?php
    	$tl = new Timeline(2,$user->id);
		$tl->displayTimeline();
	?>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>