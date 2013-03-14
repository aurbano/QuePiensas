<?php
include('lib/php/session.php');

if(!isset($_GET['id']) || !is_numeric($_GET['id']) || !($_GET['id'] = $sess->valid($_GET['id'],'int'))) header('Location: /');
$_GET['id'] = $sess->valid($_GET['id'],'int');
// Inicio de base de datos
$db=$sess->db();

// INFORMACION Y ESTADISTICAS
	// User data
	$users = $db->queryUniqueObject('SELECT users.id, users.name, users.bio, users.fbuser, users.twuser, users.visits, INET_NTOA(ip) AS ip, (CASE usePic WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\' WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',users.id,\'.png\') WHEN 2 THEN (SELECT pic_big FROM facebook WHERE fbid = users.fbuser) WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic,(SELECT COUNT(*) FROM comments WHERE usid=users.id) as comments, (SELECT COUNT(*) FROM relations WHERE usid=users.id AND follow=1) as following FROM users WHERE users.id='.$_GET['id']);
	// Llamemosle Anonimo
	if(strlen($users->name)<1) $users->name = 'Anonimo';
	// Comprobamos si existe:
	if(!$users){
		$sess->set_msg('El usuario especificado no existe');
		header('Location: /');
		die();
	}
	// Actualizar visitas
	if($sess->logged() && $_GET['id'] !== $user->id()) $db->execute('UPDATE `users` SET `visits` = `visits`+1 WHERE id = \''.$_GET['id'].'\'');
// ----------------------------

include('lib/php/funciones.php');
include('lib/php/style.php');
// Clase Timeline
include('lib/php/timeline.php');

$color = '';
if($users->pic == 'http://img.quepiensas.es/noimage.png') $color = colorID($users->id);

$content['title'] = $users->name;
$content['fancybox'] = true;

// Columnas de contenido (de momento las quitamos)

$content['js'][] = 'session';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';

$extraCss='<style media="all">
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 125px;
	margin-left:10px;
	margin-bottom:10px;
	margin-top:20px;
	margin-right:-5px;
	padding:5px;
	text-transform: uppercase;
	display: inline-block;
	border-radius:5px;
	text-decoration:none;
}
.stats:hover{
	box-shadow:0 0 5px #ccc;
	-moz-box-shadow:0 0 5px #ccc;
	-webkit-box-shadow:0 0 5px #ccc;
	text-decoration:none;
	color:#06C;
	background-color:rgba(240,240,240,0.5);
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

// Location:
$loc = $user->getLoc($_GET['id'],$users->ip);

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Perfil de <?php echo $users->name; ?></h1>
<div id="map" style="position:absolute; z-index:0; width:692px; height:250px; overflow:hidden;">
	<img src="http://maps.google.com/maps/api/staticmap?center=<?php echo $loc[3].','.$loc[4]; ?>&zoom=8&size=692x290&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Clightness:70&style=feature:all%7Celement:labels%7Cvisibility:off&style=feature:landscape%7Chue:0xffffff%7Clightness:100" border="0" width="692px" height="290px" />
</div>
<div class="paddedContent">
	<div id="profileInfo" style="position:relative; min-height:240px; background:url(http://static.quepiensas.es/img/body/white-bg.png) repeat-x bottom; margin:0 -15px 20px -15px; padding:0 20px 0 20px;">
        <div class="profilePic">
            <img src="<?php echo $users->pic; ?>" width="200" style="background-color:<?php echo $color; ?>" />
        </div>
        
        <div id="info">
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
            <?php if($users->bio!='') { ?>
            <fieldset><legend>Sobre mi</legend>
            <div style="color:#333;"><?php echo $users->bio; ?></div>
            </fieldset>
            <?php } ?>
            <div style="clear:both"></div>
        </div>
    </div>
    
    
    <h3>Últimos comentarios</h3>
    <?php
	$tl = new Timeline(3,$_GET['id']);
	$tl->displayTimeline(true,$users->pic);
	?>
</div>

<?php 
include('lib/content/footer.php');
?>