<?php
include('lib/php/session.php');

if(!isset($_GET['id']) || !is_numeric($_GET['id']) || !($_GET['id'] = $sess->valid($_GET['id'],'int'))) header('Location: /');
$_GET['id'] = $sess->valid($_GET['id'],'int');
// Inicio de base de datos
$db=$sess->db();

// INFORMACION Y ESTADISTICAS
	// User data
	$users = $db->queryUniqueObject('SELECT users.id, users.name, users.fbuser, users.twuser, users.visits, (CASE usePic WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\' WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',users.id,\'.gif\') WHEN 2 THEN (SELECT pic_big FROM facebook WHERE fbid = users.fbuser) WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic,(SELECT COUNT(*) FROM comments WHERE usid=users.id) as comments, (SELECT COUNT(*) FROM relations WHERE usid=users.id AND follow=1) as following FROM users WHERE users.id='.$_GET['id']);
	// Llamemosle Anonimo
	if(strlen($users->name)<1) $users->name = 'Anonimo';
	// Comprobamos si existe:
	if(!$users){
		$sess->set_msg('El usuario especificado no existe');
		header('Location: /');
		die();
	}
	
	// Paginacion
	$limit = 20;
	if($_GET['p']>0 && $sess->valid($_GET['p'],'int')){ $inPage = true; $limit = $limit*$_GET['p'].',20'; }
	
	//Cargamos following
	$following = $db->query('SELECT personas.id, personas.name, relations.follow, (SELECT COUNT(*) FROM relations WHERE relations.follow=1 AND relations.pid=personas.id) AS seguidores, (SELECT COUNT(*) FROM comments WHERE comments.pid=personas.id) AS comments, (SELECT relations.follow FROM relations WHERE personas.id=relations.pid AND relations.usid='.$user->id().') as following FROM personas, relations WHERE personas.id=relations.pid AND relations.follow=1 AND relations.usid='.$_GET['id'].' LIMIT '.$limit);
	
	if($_GET['p']>0 && $db->numRows($following)<1){
		// Algun capullo metiendo una pagina manualmente que no existe
		header('Location: /user/following/'.$_GET['id']);
		die();	
	}
	// ----------- Navegacion:
	$back = false;
	$next = false;
	if($_GET['p']>0) $back = '<a href="?p='.($_GET['p']-1).'">&laquo; Anterior</a>';
	if($db->numRows($following) == $limit) $next = '<a href="?p='.($_GET['p']+1).'">Siguiente &raquo;</a>';
	if($back && $next) $middle = ' &bull; ';
	// ----------------------------
	
	// Actualizar visitas
	if($_GET['id'] !== $user->id()) $db->execute('UPDATE `users` SET `visits` = `visits`+1 WHERE id = \''.$_GET['id'].'\'');
// ----------------------------

include('lib/php/funciones.php');
include('lib/php/style.php');
// Clase Timeline
include('lib/php/timeline.php');

$color = '#FFF';
if($users->pic == 'http://img.quepiensas.es/noimage.png') $color = colorID($users->id);

$content['title'] = $users->name;
$content['fancybox'] = true;

// Columnas de contenido (de momento las quitamos)

$content['js'][] = 'session';
$content['js'][] = 'timeline';
$content['js'][] = 'persona';

$content['css'][] = 'timeline';
$content['css'][] = 'buttons';


$extraCss='<style media="all">
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 125px;
	margin-left:5px;
	margin-bottom:10px;
	padding:5px;
	text-transform: uppercase;
	display: inline-block;
	border-radius:5px;
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
#facebook{
	background-image:url("/img/user/verified_facebook.gif");
	width:40px;
	height:40px;
}
#twitter{
	background-image:url("/img/user/verified_twitter.gif");
	width:56px;
	height:40px;
}
.vinc{
	margin-right:120px;
	margin-bottom:5px;
}
.profilePic{
	border:#fff solid 1px;
	outline:#CCC solid 1px;
	margin:10px;
	float:left;
	position:relative;
}
#changePicLink{
	position:absolute;
	background:#fff;
	border-bottom:1px solid #ccc;
	border-left:1px solid #ccc;
	top:5px;
	right:5px;
	padding:3px;
	font-size:14px;	
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-left-radius: 5px;
}
.logged{background-position:0 40px;}
#info{
	min-height:220px;
}
#changePicList{
	list-style:none;
	display:block;
	padding:0;
	margin:15px auto;
	text-align:center;	
}
#changePicList li{
	display:inline-block;
	margin-right:10px;	
}
#changePicList li a{
	padding:5px;
	background:#fff;
	border:1px solid #ccc;
	display:block;
	border-radius:5px;
	color:#666666 !important;
	font-size:11px;
}
#changePicList li a:hover{
	text-decoration:none;
	box-shadow:0 0 5px #ccc;
	-moz-box-shadow:0 0 5px #ccc;
	-webkit-box-shadow:0 0 5px #ccc;
	color:#06C !important;
	background-color:#f2f2f2;
}
#changePicList li a img{
	margin-bottom:5px;	
}
</style>';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Personas que sigue <a href="/user/<?php echo $users->id; ?>"><?php echo $users->name; ?></a></h1>
<div class="paddedContent">
	<div class="profilePic">
    	<img src="<?php echo $users->pic; ?>" width="200" style="background-color:<?php echo $color; ?>" />
    </div>
    
    <div id="info"><br />
    <a href="/user/following/<?php echo $users->id; ?>" class="stats">
        <font><?php echo $users->following; ?></font>
        Siguiendo
    </a>
    <div class="stats">
        <font><?php echo $users->comments; ?></font>
        Comentario<?php if($users->comments!=='1') echo 's'; ?>
    </div>
    <div class="stats">
        <font><?php echo $users->visits; ?></font>
        Visita<?php if($users->visits!=='1') echo 's'; ?>
    </div>
    
    <?php //FACEBOOK VINCULADO
	if($users->fbuser!=0) { ?> 
    	<a href="http://facebook.com/people/@/<?php echo $users->fbuser; ?>" title="Ver perfil de Facebook" target="_new" 
	<?php }else{ ?>
    	<div 
    <?php } ?>
        class="stats tooltip" style="width:196px; display:inline-block;">
        <font id="facebook" class="vinc <?php if($users->fbuser!=0) echo 'logged'; ?>"></font>
        <?php if($users->fbuser!=0) echo 'facebook vinculado';
        else echo 'facebook no vinculado'; ?>
    <?php if($users->fbuser!=0) { ?> 
    	</a>
	<?php }else{ ?>
    	</div> 
    <?php } ?>
    
    <?php //TWITTER VINCULADO
	if($users->twuser!=0) { ?> 
    	<a href="https://twitter.com/account/redirect_by_id?id=<? echo $users->twuser; ?>" title="Ver perfil de twitter" target="_new" 
	<?php }else{ ?>
    	<div 
    <?php } ?>
    	class="stats tooltip" style="width:196px; display:inline-block;">
        <font id="twitter" class="vinc <?php if($users->twuser!=0) echo 'logged'; ?>"></font>
        <?php if($users->twuser!=0) echo 'twitter vinculado';
        else echo 'twitter no vinculado'; ?>
    <?php if($users->twuser!=0) { ?> 
    	</a>
	<?php }else{ ?>
    	</div>
    <?php } ?>  
    <div style="clear:both"></div>
        <ul class="timeline">
	<?php if($db->numRows($related)<1){echo '<div class="errorBox" style="margin-top:30px;">'.$users->name.' no sigue a nadie aun...</div><p align="center"><a href="'.$sess->referrer.'">Volver</a></p>';}else{ while($data=$db->fetchNextObject($following)){ ?>
    	<li style="height:70px;" id="follow-<?php echo $data->id; ?>" class="tl-element">
        	<a style="position:relative; top:25px; left:-45px;" href="/<?php echo $data->id; ?>"><?php echo stripslashes($data->name); ?></a>
			<font style="position:relative; top:-12px; margin-left:150px;" class="info">
                <a href="/<?php echo $data->id; ?>/followers" class="stats">
                    <font><?php echo $data->seguidores; ?></font>
                    Seguidores
                </a>
                <a href="/<?php echo $data->id; ?>"  class="stats">
                    <font><?php echo $data->comments; ?></font>
                    Comentario<?php if($data->comments!=='1') echo 's'; ?>
                </a>
            </font>
                <?php if($sess->logged()){ if($data->following==0){ ?>
                    <a href="#follow" class="large awesome follow" rel="follow" id="follow-<?php echo $data->id; ?>" style="position: absolute; top:22px; right:30px;">Seguir</a>
                <?php }else{ ?>
                    <a href="#follow" class="large awesome following" rel="unfollow" id="follow-<?php echo $data->id; ?>" style="position: absolute; top:22px; right:30px;">Siguiendo</a>
                <?php } }else{ ?>
                    <a href="#loginBox" class="large awesome follow fBox" rel="follow" id="follow-<?php echo $id; ?>" style="position: absolute; top: 12px; right:30px;">Seguir</a>
                <?php } ?>
			</font>
        </li>
    <?php } } ?>	
	</ul>
    <div style="margin-top:20px; text-align:center"><?php echo $back.$middle.$next; ?></div>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>