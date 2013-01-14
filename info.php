<?php
include('lib/php/session.php');
$text = array('nota-legal','privacidad','acerca-de','trabajo','prensa','anunciantes','como-funciona','faq','tos');
$slug = $sess->clean($_GET['title']);
if(!isset($slug) || strlen($slug)<1 || !in_array($slug,$text)){
	header('Location: /');
	die();	
}
$titles['nota-legal'] = 'Nota legal';
$titles['acerca-de'] = 'Acerca de Que Piensas';
$titles['como-funciona'] = 'Cómo funciona';
$titles['faq'] = 'Preguntas frecuentes';
$titles['tos'] = 'Términos de uso';
$title = ucfirst($slug);
if($titles[$slug]) $title = $titles[$slug];
$content['title'] = $title;

// Genera una lista de todos los enlaces de info
$links = '';
for($i=0;$i<count($text);$i++){
	if($text[$i]==$slug) continue;
	$title1 = ucfirst($text[$i]);
	if($titles[$text[$i]]) $title1 = $titles[$text[$i]];
	$links .= '<li><a href="/info/'.$text[$i].'" title="Abrir">'.$title1.'</a></li>';
}

$content['cols'][] = '<h3>Información:</h3><ul class="links">'.$links.'</ul>';

include('lib/content/top.php');
echo '<h1 style="border-bottom:#7dc3ff 1px solid;">'.$title.'</h1>
	  <div class="paddedContent">';
include('lib/content/info/'.$slug.'.html');
echo '<p align="center"><a href="/" title="Ir al inicio">Inicio</a></div>';
include('lib/content/footer.php');