<?php
/* Archivo principal de Que Piensas
 *	El seguimiento de usuarios esta en session, junto con las demas cosas.
 *  Session genera un objeto ya iniciado contenido en $sess
 *  El usuario esta en el objeto $user
 */
include('lib/php/session.php');
// Si estas logueado, mostramos index2.php, si no, mostramos este archivo
if($sess->logged()){
	include('index2.php');
	die();	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Que piensas de mi?</title>
<meta name="description" content="Descubre lo que la gente piensa de ti y publica tus opiniones sobre tus amigos" />
<meta name="keywords" content="piensas, opinas, opinion, comentario, amigos, red, social, network, española" />
<link rel="shortcut icon" href="http://static.quepiensas.es/favicon.ico" />
<style type="text/css" media="all">
body{
	background:#a8d7ff url(http://static.quepiensas.es/img/body/bg.gif) repeat-x top;	
	font-family:Arial, Helvetica, sans-serif;
	margin:0;
	padding:0;
	font-size:14px;
}
.slogan{
	position:absolute;
	font-size:12px;
	top:62px;
	left:135px;
	color:#3aa0d3;	
}
a, a:link, a:active, a:visited{
	color:#ef9120;
	text-decoration:none;
}
a:hover{
	text-decoration:underline;
}
#mainWrap{
	width:550px;
	margin:150px auto;
	position:relative;
	overflow:visible;
	z-index:999;
}
#logo{
	position:absolute;
	width:319px;
	height:99px;
	top:-76px;
	left:110px;
	background:url(http://static.quepiensas.es/img/body/logo-only.png) no-repeat;
	z-index:99;
}
#content{
	background:url(http://static.quepiensas.es/img/body/index-main-box-bg.gif) repeat-x;
	width:550px;
	margin:0 auto;
	height:73px;
}
.corner1{
	position:absolute;
	top:0;
	left:0;
	width:25px;
	height:73px;
	background:url(http://static.quepiensas.es/img/body/index-main-box-corners-c.gif) no-repeat left;
	z-index:2;
}
.corner2{
	position:absolute;
	top:0;
	right:0;
	width:25px;
	height:73px;
	background:url(http://static.quepiensas.es/img/body/index-main-box-corners-c.gif) no-repeat right;
	z-index:2;
}
.innerContent{
	font-size:25px;
	color:#135b96;
	text-shadow:#FFFFFF 0 1px;
	z-index:99;
	position:absolute;
	top:23px;
	left:25px;
}
.form{
	position:absolute;
	top:-7px;
	left:92px;
	overflow:visible;
	margin-left:2px;
}
.input{
	background:#FFFFFF;
	border:#d7d7d7 1px solid;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	outline:none;
}

.big{
	height:40px;
	width:400px;
	font-size:15px;
	color:#CCCCCC;
	background:#f1f1f1 url(http://static.quepiensas.es/img/form/input-big-bg.gif) repeat-x;
	font-size:19px;
	text-indent:10px;
}
.insideBtnBig{
	position:absolute;
	border:none;
	width:52px;
	top:0px;
	right:-10px;
	cursor:pointer;
}
<?php if($_GET['xxx']){ ?>
	#babe{
		background:url(Baby-Girl.png) no-repeat;	
		position:absolute;
		bottom:144px;
		left:30%;
		width:225px;
		height:244px;
	}
<?php } ?>
/* ----- FOOTER ----------- */
#footer{
	position:fixed;
	bottom:0px;
	left:0px;
	width:100%;
	z-index:1;
}
#footer #topBar{
	border-top:#FFFFFF 1px solid;
	background:url(http://static.quepiensas.es/img/body/footer-dark-bg-alpha.png) repeat;
	border-bottom:#265781 5px solid;
	color:#a8d7ff;
	padding:6px;
	text-align:center;
	font-size:14px;
}
#footer #metaNav{
	background:#265781 url(http://static.quepiensas.es/img/body/footer-stripes-bg.gif);
	color:#a8d7ff;
	text-align:center;
	padding-top:15px;
	padding-bottom:15px;
}
#footer #metaNav ul{
	margin: 0;
	padding: 0;
	padding-top:11px;
	list-style: none;
	border: none;
	white-space: nowrap;
}
#footer #metaNav ul li{
	margin: 0;
	padding: 0;
	display:inline;
}
#footer #metaNav ul li a,#footer #metaNav ul li a:active,#footer #metaNav ul li a:visited,#footer #metaNav ul li a:link {
	font-size:20px;
	color:#CAEDFF;
	text-decoration:none;
	display:inline-block;
	margin-right:20px;
	padding:6px;
	border:transparent 1px solid;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
}
#footer #metaNav ul li a:hover{
	background: rgba(0, 75, 122, 0.4);
	color:#FFFFFF;
	border:#507da3 1px solid;
}
#footer #metaLink{
	background:url(http://static.quepiensas.es/img/body/footer-metalinks-bg.png);
	border-top:#265781 5px solid;
	color:#75a9d5;
	font-size:13px;
	text-align:left;
	padding:6px;	
}
#footer #metaLink a,#footer #metaLink a:active,#footer #metaLink a:visited,#footer #metaLink a:link {
	color:#75a9d5;
	text-decoration:none;
}
#footer #metaLink a:hover{
	color:#aed2f1;
}
#footer #moreInfoText{
	margin-bottom:15px;
}
#footer #moreInfoText p{
	padding:0 20px;
}
#entrar{
text-align: center !important;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
outline: none;
width: 186px;
cursor: pointer;
font-size: 12px;
display: inline-block;
padding: 8px 6px 9px 6px;
text-align: center;
margin: 7px 0 0 175px;
font-weight: 100;
color: 
#999 !important;
text-shadow: 
white 1px 1px;
border: 1px solid #CCC;
background: url(img/form/greyBtn-bg-c.gif) white bottom repeat-x;
}
.ui-autocomplete{ z-index:999 !important; }

</style>
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/lib/js/fancybox/jquery.fancybox.css" />
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/jqueryUI/custom-theme/theme.css" />
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/login.css" />
<script type="text/javascript" language="javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/fancybox/jquery.fancybox.js"></script>
<script type="text/javascript" language="javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

<script type="text/javascript" language="javascript">
<!--
$(document).ready(function(){
	<?php $sess->msg(); ?>
	$('.fBox').fancybox({
		width : 650
	});
	$('.externalLogin').click(function(event){
		event.preventDefault();
		$('.hideOnAction').hide().after('<div style="margin-top: 55px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Conectando</p></div>');
		var url = $(this).attr('href');
		var oauthWindow = window.open(url,'Conectando',"height=500,width=700,scrollTo,resizable=0,scrollbars=0,location=0");
		var oauthInterval = window.setInterval(function(){
			if (oauthWindow.closed) {
				window.clearInterval(oauthInterval);
				window.location.reload();
			}
		}, 1000);	
	});
	
	$('#loginForm').submit(function(event){
		event.preventDefault();
		if($('#email').val()=='' || $('#pass').val()==''){
			$('#saveMsgError').text('Debes rellenar todos los campos').show();	
		}else{
			// Un poco de diseño jaja
			$('#saveMsgError').text('').hide();	
			$('#loginBtn').attr('disabled','disabled');
			$('.hideOnAction').hide().after('<div style="margin-top: 55px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Iniciando sesión</p></div>');
			// Vamos a empezar a enviar esto :)
			$.post("/ajax.php", { type:'login', ajax:'true', email:$('#email').val(), pass:$('#pass').val() },
				  function(data){
					  	if(data){
							if(data.done == 'false'){
								// No se pudo guardar:
								if(data.msg.length>0){
									$('#saveMsgError').html(stripslashes(data.msg)).show();
								}else{
									$('#saveMsgError').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
								}
								$('#loginBtn').removeAttr('disabled');
							}else{
								$('#loginForm').remove();
								$('.paddedContent').html('<div style="text-align:center">Sesión iniciada: Abriendo perfil...</div>');
								window.location = '/do/profile';
							}
						}else{
							$('#saveMsgError').html('Ha ocurrido un error, intentelo de nuevo mas tarde').show();
						}
				  }, "json");
		}
	});
});
-->
</script>
</head>

<body>
<div id="mainWrap">
	<div id="logo"><div class="slogan">&iquest;De qui&eacute;n quieres hablar?</div></div>
    <div id="box">
    	<div id="content">
        	<div class="innerContent">
            Buscar:
            <div class="form">
            	<form action="/do/buscar" method="post" enctype="application/x-www-form-urlencoded">
                	<input name="search" type="text" value="Nombre completo..." class="input big" />
                    <input name="searchBtn" type="image" src="http://static.quepiensas.es/img/form/btn-inside-big-c.gif" class="insideBtnBig" title="Buscar" />
                </form>
            </div>
            </div>
        </div>
        <a id="entrar" href="#loginBox" class="fBox">Inicia sesión</a>
        
        <div class="corner1"></div>
    	<div class="corner2"></div>
    </div>
    <div id="loginBox" style="width:350px">
		<div style="width:300px; margin:25px auto;">
			<img src="http://static.quepiensas.es/img/body/logo-grey-transparent.png" border="0" />
			<div class="hideOnAction">
				<p>
					<a class="fb social externalLogin" href="<?php echo $fb->fbLogin(); ?>" title="Inicia sesión con Facebook">
					<img src="http://static.quepiensas.es/img/social/f.png" border="0" alt="f" /> Inicia sesión con Facebook</a>
					<a class="tw social externalLogin" href="<?php echo $tw->loginLink(); ?>" title="Inicia sesión con Twitter">
					<img src="http://static.quepiensas.es/img/social/t.png" border="0" alt="t" /> Inicia sesión con Twitter</a>
				</p>
				<hr style="border-bottom:none;" />
				<form action="/do/ajax" name="loginForm" id="loginForm" method="post" enctype="multipart/form-data">
					<div id="saveMsgError" class="errorMsg" style="display:none;"></div>
					<label>Email:
					<input type="text" name="email" id="email" class="loginInput" value="<?php echo $user->g('email'); ?>" /></label></label>
					<label>Contraseña: <a style="float:right;" href="/do/forgot-pass">¿Olvidate tu contraseña?</a>
					<input type="password" name="pass" id="pass" class="loginInput" /></label></label>
					<input name="save" id="loginBtn" type="submit" value="Iniciar sesión"/>
					<span id="aviso">¿No tienes cuenta? <a href="/do/register">Regístrate</a></span>
				</form>
			</div>
		 </div>
    </div>
</div>
<?php if($_GET['xxx']){ echo '<div id="babe"></div>'; } ?>
<?php $fullFooter = true; include('lib/content/footer.php'); ?>