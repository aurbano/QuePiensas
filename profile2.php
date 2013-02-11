<?php
include('lib/php/session.php');

if(!$sess->logged()){
	header('Location: /do/login');
	die();	
}

include('lib/php/funciones.php');
include('lib/php/style.php');
// Clase Timeline
include('lib/php/timeline.php');

$color = '#FFF';
if($user->pic() == 'http://img.quepiensas.es/noimage.png') $color = colorID($user->id);

// Inicio de base de datos
$db=$sess->db();

// INFORMACION Y ESTADISTICAS
	// Following:
	$following = $db->queryUniqueValue('SELECT COUNT(*) FROM relations WHERE usid = \''.$user->id.'\' AND follow=1');
	// Comment count
	$commentCount = $db->queryUniqueValue('SELECT COUNT(*) FROM comments WHERE usid = \''.$user->id.'\'');
	if(!$commentCount) $commentCount = 0;
// ----------------------------

// VINCULACION CON CUENTAS
$fbLogin = '#fbLoginBox';
$twLogin = '#twLoginBox';
$fbTitle = $twTitle = 'Edita tus preferencias de ';
if(!$user->fb()){
	$fbLoginClass = 'externalLogin';
	$fbTitle = 'Vincula tu cuenta de ';
	$fbLogin = $fb->fbLogin();
}
if(!$user->tw()){
	$twLoginClass = 'externalLogin';
	$twLogin = $tw->loginLink();
	$twTitle = 'Vincula tu cuenta de ';
}
// ----------------------------

$content['title'] = $user->g('name');
$content['fancybox'] = true;

// Columnas de contenido:
$content['cols'][] = menu();
$content['cols'][] = '<h3>Personas relacionados</h3>
<ul class="col personCol">
	<li><a href="/1">Jaime Caballero</a>
		<a class="stats" href="/1">
        	<font>13</font> comentarios
		</a>
		<a class="stats" href="/1">
        	<font>20</font> visitas
		</a>
	</li>
	<li><a href="/1">Jaime Caballero</a>
		<a class="stats" href="/1">
        	<font>13</font> comentarios
		</a>
		<a class="stats" href="/1">
        	<font>20</font> visitas
		</a>
	</li>
</ul>';

$content['js'][] = 'session';
$content['js'][] = 'editar';
$content['js'][] = 'timeline';

$content['css'][] = 'timeline';
$content['css'][] = 'columna';

$extraCss='<style media="all">
.timeline{margin:0 -15px;}
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
	top:0px;
	right:0px;
	padding:3px;
	font-size:14px;	
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-left-radius: 5px;
}
.stats:hover .vinc, .logged{background-position:0 40px;}
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
#changePicList li a, #changePicList li label{
	padding:5px;
	background:#fff;
	border:1px solid #ccc;
	display:block;
	border-radius:5px;
	color:#666666 !important;
	font-size:11px;
	margin:0;
	cursor:pointer;
}
#changePicList li a:hover, #changePicList li label:hover{
	text-decoration:none;
	box-shadow:0 0 5px #ccc;
	-moz-box-shadow:0 0 5px #ccc;
	-webkit-box-shadow:0 0 5px #ccc;
	color:#06C !important;
	background-color:#f2f2f2;
}
#changePicList li img{
	margin-bottom:5px;	
}
</style>';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;"><?php echo $user->g('name'); ?></h1>
<div id="map" style="position:absolute; z-index:0; width:100%; height:250px; overflow:hidden;">
	<img src="http://maps.google.com/maps/api/staticmap?center=<?php echo $user->location(3).','.$user->location(4); ?>&zoom=8&size=692x290&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Clightness:70&style=feature:all%7Celement:labels%7Cvisibility:off&style=feature:landscape%7Chue:0xffffff%7Clightness:100" border="0" width="692px" height="290px" />
</div>
<div class="paddedContent">
	<div id="profileInfo" style="position:relative; min-height:260px;">
        <div class="profilePic">
            <img src="<?php echo $user->pic(); ?>" width="200" style="background:<?php echo $color; ?>" />
            <div id="changePicLink" class="hide"><a href="#changePicBox" class="fBox tooltip" title="Cambiar foto de perfil">Cambiar</a></div>
        </div>
        
        <div id="info">
            <a href="/user/following/<?php echo $user->id; ?>" class="stats">
                <font><?php echo $following; ?></font>
                Siguiendo
            </a>
            <div class="stats">
                <font><?php echo $commentCount; ?></font>
                Comentario<?php if($commentCount>'1') echo 's'; ?>
            </div>
            <div class="stats">
                <font><?php echo $user->g('visits'); ?></font>
                Visita<?php if($user->g('visits')>'1') echo 's'; ?>
            </div>
            
            <a class="stats <?php echo $fbLoginClass; if($user->fb()){echo ' fBox ';} ?> tooltip" style="width:196px; display:inline-block;" href="<?php echo $fbLogin; ?>" title="<?php echo $fbTitle; ?> Facebook">
                <font id="facebook" class="vinc <?php if($user->fb()) echo 'logged'; ?>"></font>
                <?php if($user->fb()) echo 'facebook vinculado';
                else echo 'facebook no vinculado'; ?>
            </a>
            <a class="stats <?php echo $twLoginClass; if($user->tw()){echo ' fBox ';} ?> tooltip" style="width:196px; display:inline-block;" href="<?php echo $twLogin; ?>" title="<?php echo $twTitle; ?> Twitter">
                <font id="twitter" class="vinc <?php if($user->tw()) echo 'logged'; ?>"></font>
                <?php if($user->tw()) echo 'twitter vinculado';
                else echo 'twitter no vinculado'; ?>
            </a>
            
            <div style="clear:both"></div>
            
			<?php if($user->g('bio')!='') { ?>
            <fieldset style="background:rgba(255,255,255,0.7)"><legend>Sobre mi</legend>
            <div style="color:#333;"><?php echo $user->g('bio'); ?></div>
            </fieldset>
            <?php } ?>  
        </div>  
    </div>
	
	<h3>Últimos comentarios</h3>
    <?php
	$tl = new Timeline(1,$user->id);
	$tl->displayTimeline(true,$user->pic('square'));
	?>
<p align="center"><a href="/do/reset">Cerrar sesión</a></p>
</div>

<?php  /* FANCYBOXES */ if($user->fb()){ /* CONTENIDO DEL FANCYBOX DE FB */ ?>
    <div id="fbLoginBox" class="hide">
    	<h2 style="padding:0"><img src="http://static.quepiensas.es/img/social/facebook_32.png" align="absmiddle" /> Facebook</h2>
        <div style="clear:both;"></div>
        <div class="hideOnAjax">
        <p>Actualmente tu cuenta está asociada a Facebook. Eso te permite ver comentarios sobre tus amigos e iniciar sesión con Facebook.</p>
        <p align="center"><a href="#unlinkFB" rel="fb" title="Mas adelante podras volver a vincularla desde tu perfil" class="unlink tooltip">Desvincular cuenta</a></p></div>
    </div>
    <?php } ?>
    
    <?php if($user->tw()){ /* CONTENIDO DEL FANCYBOX DE TW */ ?>
    <div id="twLoginBox" class="hide">
    	<h2 style="padding:0"><img src="http://static.quepiensas.es/img/social/twitter_32.png" align="absmiddle" /> Twitter</h2>
        <div style="clear:both;"></div>
        <div class="hideOnAjax">
        <p>Actualmente tu cuenta está asociada a Twitter. Eso te permite ver comentarios sobre tus amigos e iniciar sesión con Twitter.</p>
        <p align="center"><a href="#unlinkTW" rel="tw" title="Mas adelante podras volver a vincularla desde tu perfil" class="unlink tooltip">Desvincular cuenta</a></p></div>
    </div>
    <?php } ?>
    
    <div id="changePicBox" class="hide">
    	<h2 style="padding:0"><img src="http://static.quepiensas.es/img/icons/edit_profile.png" align="absmiddle" /> Cambiar foto de perfil:</h2>
        <div style="clear:both;"></div>
        <div class="hideOnAjax">
        	Selecciona alguna de las opciones a continuación:
            <ul id="changePicList">
				<?php if($user->fb()){ ?><li><a href="#changePic" rel="facebook"><img src="<?php echo $fb->pic('square'); ?>" width="60" alt="Facebook" /><br />Facebook</a></li><?php } ?>
                <?php if($user->tw()){ ?><li><a href="#changePic" rel="twitter"><img src="<?php echo $tw->pic('normal'); ?>" width="60" alt="Twitter" /><br />Twitter</a></li><?php } ?>
                <?php if($user->hasPic()){ ?><li><a href="#changePic" rel="uploaded"><img src="<?php echo 'http://img.quepiensas.es/'.$user->id.'.gif'; ?>" width="60" alt="Subida" /><br />QuePiensas</a></li><? } ?>
                <li id="chromeChanger" style="display:none"><form action="/processUpload.php" method="post" id="picChangeForm" enctype="multipart/form-data" style="display:inline"><label for="picChanger"><img src="http://static.quepiensas.es/img/form/addPic.gif" width="60" alt="Subir foto" /><br />Subir foto<div style="display: block; position: fixed; visibility: hidden;"><input type="file" name="pic" id="picChanger" /></div></label></form></li>
                <li id="normalChanger"><a href="#changePicForm" class="fBox"><img src="http://static.quepiensas.es/img/form/addPic.gif" width="60" alt="Subir foto" /><br />Subir foto</a></li>
                <li><a href="#changePic" rel="nopic"><img src="http://img.quepiensas.es/noimage.png" width="60" alt="Sin foto" style="background-color:<?php echo colorID($user->id); ?>" /><br />No mostrar</a></li>
            </ul>
        </div>
    </div>
    
    <div id="changePicForm" class="hide">
    	<h2 style="padding:0"><img src="http://static.quepiensas.es/img/icons/edit_profile.png" align="absmiddle" /> Cambiar foto de perfil:</h2>
        <div style="clear:both;"></div>
        <form action="/processUpload.php" method="post" id="picChangeForm" enctype="multipart/form-data" style="display:inline">
        	<input type="file" name="pic" id="picChanger" /> &bull; <input type="submit" name="send" value="Subir" class="btn btnBlue" />
        </form>
    </div>
<?php 
include('lib/content/footer.php');
?>