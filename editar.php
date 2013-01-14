<?php
include('lib/php/session.php');

if(!$sess->logged()){ 
   header('Location: /do/login');
}

include('lib/php/style.php');
$color = '#FFF';
if($user->pic() == 'http://img.quepiensas.es/noimage.png') $color = colorID($user->id);

$content['title'] = 'Editar perfil';
$content['js'][] = 'editar';

$extraCss='<style media="all">
.profilePic{
	border:#fff solid 1px;
	outline:#CCC solid 1px;
	margin:10px;
	float:left;
	position:relative;
}
#changePicLink{
	position:absolute;
	background:rgba(255,255,255,0.8);
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

<h1 style="border-bottom:#7dc3ff 1px solid;">Editar perfil</h1>
<div class="paddedContent hideOnAction">
<form action="/ajax.php?db=noerrorfile" method="post">
	<input type="hidden" name="type" value="editProfile" />
    <input type="hidden" name="ajax" value="false" />
    <input type="hidden" name="next" value="/do/editar" />
    <fieldset>
    	<legend>Sobre ti:</legend>
        <div class="profilePic">
            <img src="<?php echo $user->pic(); ?>" width="200" style="background:<?php echo $color; ?>" />
            <div id="changePicLink"><a href="#changePicBox" class="fBox tooltip" title="Cambiar foto de perfil">Cambiar</a></div>
        </div>
        <label>Nombre:<br /><input type="text" name="name" value="<?php echo $user->g('name'); ?>" class="formNormal" /></label><br />
        <label>Bio:<br /><textarea style="width:350px;" name="bio" class="formNormal" /><?php echo $user->g('bio'); ?></textarea></label><br />
    </fieldset>
    
    <fieldset style="position:relative;">
    	<legend>Tu cuenta:</legend>
        <?php if(strlen($user->g('email'))>0){ ?>
        <p style="margin:0 0 20px;"><label>Email asociado: <em><?php echo $user->g('email'); ?></em></label></p>
        <label><a href="#changePass">Cambiar contraseña</a></label><br />
        <div id="changePass" class="hide">
        	<label>Contraseña actual:<br /><input type="password" name="oldpass" class="formNormal" /></label><br />
            <label>Contraseña nueva:<br /><input type="password" name="pass1" class="formNormal" /></label><br />
            <label>Repite la nueva:<br /><input type="password" name="pass2" class="formNormal" /></label>
        </div>
        <?php }else{ ?>
        	<p style="margin:0 0 20px;">Guarda tu email y contraseña para poder acceder sin usar una red social:</p>
            <label>Email:<br /><input type="text" name="email" value="<?php echo $fb->email(); ?>" class="formNormal" /></label><br />
            <label>Contraseña:<br /><input type="password" name="pass" class="formNormal" /></label>
        <?php } ?>
        <a href="/do/deactivate" class="btn greyBtn" style="position:absolute; top:13px; right:6px;">Desactivar cuenta</a>
    </fieldset>
    <div align="center"><input type="submit" name="save" value="Guardar cambios" class="btn btnBlue" /> &bull; <a href="/do/profile">Cancelar</a></div>
</form>
</div>
<div id="changePicBox" class="hide">
    	<h2 style="padding:0"><img src="http://static.quepiensas.es/img/icons/edit_profile.png" align="absmiddle" /> Cambiar foto de perfil:</h2>
        <div style="clear:both;"></div>
        <div class="hideOnAjax">
        	Selecciona alguna de las opciones a continuación:
            <ul id="changePicList">
				<?php if($user->fb()){ ?><li><a href="#changePic" rel="facebook"><img src="<?php echo $fb->pic('square'); ?>" width="60" alt="Facebook" /><br />Facebook</a></li><?php } ?>
                <?php if($user->tw()){ ?><li><a href="#changePic" rel="twitter"><img src="<?php echo $tw->pic('normal'); ?>" width="60" alt="Twitter" /><br />Twitter</a></li><?php } ?>
                <?php if($user->hasPic()){ ?><li><a href="#changePic" rel="uploaded"><img src="<?php echo 'http://img.quepiensas.es/'.$user->id.'.gif'; ?>" width="60" alt="Subida" /><br />QuePiensas</a></li><? } ?>
            	<li><form action="/processUpload.php" method="post" id="picChangeForm" enctype="multipart/form-data" style="display:inline"><label for="picChanger"><img src="http://static.quepiensas.es/img/form/addPic.gif" width="60" alt="Subir foto" /><br />Subir foto<div style="display: block; position: fixed; visibility: hidden;"><input type="file" name="pic" id="picChanger" /></div></label></form></li>
                <li><a href="#changePic" rel="nopic"><img src="http://img.quepiensas.es/noimage.png" width="60" alt="Sin foto" style="background-color:<?php echo colorID($user->id); ?>" /><br />No mostrar</a></li>
            </ul>
        </div>
    </div>
<?php
include('lib/content/footer.php');
?>