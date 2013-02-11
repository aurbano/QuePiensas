<?php
include('lib/php/session.php');
// Variables de la pagina
if(!is_numeric($_GET['id'])){
	header('Location: /');
	die('Wrong ID');
}

$id = $sess->clean($_GET['id']);

// Includes
include('lib/php/style.php');
include('lib/php/person.php');
// Clase Timeline
include('lib/php/timeline.php');


$persona = new Person($id);
// Llamadas a la base de datos y comprobacion de si existe
if(!$persona->getData()){ header('Location: /'); die('NO DATA'); }

// Anadimos una visita a la persona, solo si no es visita consecutiva
if($_SESSION['current']!==$id){ $persona->addVisit(); }
// Guardamos la persona actual (Util para algunas comprobaciones)
$_SESSION['current'] = $id;

// Paginacion
	$limit = 20;
	if($_GET['p']>0 && $sess->valid($_GET['p'],'int')){ $inPage = true; $limit = $limit*$_GET['p'].',20'; }
	

// Inicio de base de datos
$db=$sess->db();
// Seguidores
$followers = $db->query('SELECT users.id, users.name, users.bio, users.visits, (CASE usePic WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\' WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',users.id,\'-square.png\') WHEN 2 THEN CONCAT(\'http://graph.facebook.com/\',users.fbuser,\'/picture?type=square\') WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic, (SELECT COUNT(*) FROM comments WHERE usid=users.id) as comments, (SELECT COUNT(*) FROM relations WHERE usid=users.id AND follow=1) as following FROM relations, users WHERE relations.pid='.$id.' AND relations.follow=1 AND relations.usid=users.id ORDER BY timestamp DESC LIMIT '.$limit);

	if($_GET['p']>0 && $db->numRows($followers)<1){
		// Algun capullo metiendo una pagina manualmente que no existe
		header('Location: /user/following/'.$_GET['id']);
		die();	
	}
	// ----------- Navegacion:
	$back = false;
	$next = false;
	if($_GET['p']>0) $back = '<a href="?p='.($_GET['p']-1).'">&laquo; Anterior</a>';
	if($db->numRows($followers) >= $limit) $next = '<a href="?p='.($_GET['p']+1).'">Siguiente &raquo;</a>';
	if($back && $next) $middle = ' &bull; ';

$person = ucwords($persona->name);
$partes = explode(' ',$person);

$content['title'] = 'Seguidores de '.$person;
// Javascript
$content['js'][] = 'persona';
$content['js'][] = 'timeline';
// CSS
$content['css'][] = 'timeline';
$content['css'][] = 'buttons';

$extraCss = '<style type="text/css">
.timeline{margin:0 0 !important;}
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 125px;
	margin-left:5px;
	margin-bottom:10px;
	margin-top:3px;
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
.bio{
	float:left;
	font-size:11px;
	display: block;
width: 180px;
line-height: 12px;
font-weight: normal;
position: absolute;
top: 20px;
left: 70px;
color:#555;
}
</style>';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Seguidores de <a href="/<?php echo $_GET['id']; ?>"><?php echo $person; ?></a></h1>
<ul class="timeline">
	<?php if($db->numRows($followers)<1){echo '<div class="errorBox" style="margin-top:30px;">'.$person.' no tiene seguidores todavia... ¿Quieres ser el primero?</div><p align="center"><a href="'.$sess->referrer.'">Volver</a></p>';}else{ while($data=$db->fetchNextObject($followers)){ ?>
    	<li id="follow-<?php echo $data->id; ?>" class="tl-element">
        <img src="<?php echo $data->pic; ?>" width="50" class="timelinePic" <?php if($data->pic=='http://img.quepiensas.es/noimage.png'){ echo ' style="background:'.colorID($data->id).'"';} ?>>
			<font class="info">
            	<?php if(!$data->name){ 
					echo 'Anónimo';
				}else{ ?>
            	<a href="/user/<?php echo $data->id; ?>"><?php echo stripslashes($data->name); ?></a>
                <?php } ?>
                <?php if($data->bio!='') { ?><p class="bio"><?php echo substr($data->bio,0,70); if(strlen($data->bio)>70){ echo '...';} ?></p><?php } ?>
                  <a href="/user/following/<?php echo $data->id; ?>" class="stats" >
                        <font><?php echo $data->following; ?></font>
                        Siguiendo
                    </a>
                    <a class="stats" href="/user/<?php echo $data->id; ?>">
                        <font><?php echo $data->comments; ?></font>
                        Comentario<?php if($data->comments!=='1') echo 's'; ?>
                    </a>
                    <a class="stats" href="/user/<?php echo $data->id; ?>">
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