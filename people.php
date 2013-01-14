<?php
include('lib/php/session.php');

$id = $sess->clean($_GET['id']);

// Paginacion
	$limit = 20;
	if($_GET['p']>0 && $sess->valid($_GET['p'],'int')){ $inPage = true; $limit = $limit*$_GET['p'].',10'; }
	

// Inicio de base de datos
$db=$sess->db();
// Seguidores
$related = $db->query('SELECT personas.id, personas.name, personas.visits, (SELECT COUNT(*) FROM comments WHERE pid=personas.id) as comments, (SELECT COUNT(*) FROM relations WHERE pid=personas.id AND follow=1) as followers FROM relations, personas WHERE relations.usid='.$user->id.' AND relations.pid = personas.id AND relations.follow = 0 ORDER BY relations.relation DESC LIMIT '.$limit);

	if($_GET['p']>0 && $db->numRows($followers)<1){
		// Algun capullo metiendo una pagina manualmente que no existe
		header('Location: /do/people/');
		die();	
	}
	// ----------- Navegacion:
	$back = false;
	$next = false;
	if($_GET['p']>0) $back = '<a href="?p='.($_GET['p']-1).'">&laquo; Anterior</a>';
	if($db->numRows($followers) == $limit) $next = '<a href="?p='.($_GET['p']+1).'">Siguiente &raquo;</a>';
	if($back && $next) $middle = ' &bull; ';

$content['title'] = 'Personas relacionadas';
// Javascript
$content['js'][] = 'persona';
// CSS
$content['css'][] = 'timeline';
$content['css'][] = 'buttons';

$extraCss = '<style type="text/css">
.timeline{margin:0 0 !important;}
.timeline li.tl-element{
	padding: 5px 20px 0px 10px !important;
	padding: 0 15px;
	line-height: 70px;
}
.info{
	position: absolute;
	top: 6px;
	right: -1px;	
}
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 125px;
	padding:5px;
	text-transform: uppercase;
	display: inline-block;
	border-radius:5px;
	float:right;
}
.stats:hover{
	box-shadow:0 0 5px #ccc;
	-moz-box-shadow:0 0 5px #ccc;
	-webkit-box-shadow:0 0 5px #ccc;
	text-decoration:none;
	color:#06C;
	background-color:#f2f2f2;
}
.stats font{
	font-size:24px;
	color:#000;
	float:left;
	display: block;
	width: 130px;
}
</style>';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Personas relacionadas</h1>
<ul class="timeline">
	<?php if($db->numRows($related)<1){echo '<div class="errorBox" style="margin-top:30px;">Empieza a utilizar Que Piensas para que podamos sugerirte amigos.<br />Si vinculas Facebook desde tu perfil sería mejor aun!</div><p align="center"><a href="'.$sess->referrer.'">Volver</a></p>';}else{ while($data=$db->fetchNextObject($related)){ ?>
    	<li id="follow-<?php echo $data->id; ?>" class="tl-element">
			<a href="/<?php echo $data->id; ?>"><?php echo $data->name; ?></a>
            <font class="info">
                  <a href="/<?php echo $data->id; ?>/followers" class="stats" >
                        <font><?php echo $data->followers; ?></font>
                        Seguidores
                    </a>
                    <a class="stats" href="/<?php echo $data->id; ?>">
                        <font><?php echo $data->comments; ?></font>
                        Comentario<?php if($data->comments!=='1') echo 's'; ?>
                    </a>
                    <a class="stats" href="/<?php echo $data->id; ?>">
                        <font><?php echo $data->visits; ?></font>
                        Visita<?php if($data->visits!=='1') echo 's'; ?>
                    </a>
                
			</font>
        </li>
    <?php } } ?>	
</ul>

</div>
<?php
include('lib/content/footer.php');
?>