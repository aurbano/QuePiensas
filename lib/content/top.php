<?php $content['fancybox'] = true;  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $content['title']; ?> | Que Piensas</title>
<meta name="keywords" content="opina, comentarios, opinion, <?php echo str_replace(' ',', ',strtolower($content['title'])); ?>" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/common.css" />
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/tipsy.css" />
<link rel="stylesheet" media="all" href="http://static.quepiensas.es/login.css" />

<link rel="stylesheet" media="all" href="http://static.quepiensas.es/jqueryUI/custom-theme/theme.css" />
<?php if($content['fancybox']){ ?><link rel="stylesheet" media="all" href="http://static.quepiensas.es/lib/js/fancybox/jquery.fancybox.css" /><?php } ?>
<?php for($i=0;$i<sizeof($content['css']);$i++){ ?><link rel="stylesheet" media="all" href="http://static.quepiensas.es/<?php echo $content['css'][$i]; ?>.css" /></script><?php } ?>
<?php echo $extraCss;
if($content['cols']){ ?>
<style type="text/css">
#col1{
	margin:50px 300px 0 0;
}
#colRightWrap{
	position:absolute;
	top:66px;
	right:30px;
	width:215px;	
}
.col2{
	width:inherit;
	background:#f7f7f7 url(http://static.quepiensas.es/img/body/col2-bg.gif) top repeat-x;
	border:#7dc3ff 1px solid;
	padding:15px;
	border-radius:15px;
	margin:0 0 20px 0;
	-moz-box-shadow: 0 0 5px #e0f0fd;
 	-webkit-box-shadow: 0 0 5px #e0f0fd;
  	box-shadow: 0 0 5px #e0f0fd;
}
.col2 ul.links{
	list-style:none;
	margin:5px -15px 0 -15px;
	padding:0;
}
.col2 ul.links li{
	display:block;
}
.col2 ul.links li:last-child a{border-bottom:none;}
.col2 ul.links li a{
	display:block;
	padding:6px 15px;
	border-bottom:#ddd solid 1px;
	border-top:#FFF solid 1px;	
}
.col2 ul.links li a:hover{
	text-decoration:none;
	background:#FFF;
}	
</style>
<?php } ?>
<script type="text/javascript" language="javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
<script type="text/javascript" language="javascript">
<!--
$(document).ready(function(){
	function footer(){
		var offset = $('#footer').offset();
		var footerHeight = $('#footer').height();
		var height = window.innerHeight;
		if(height-offset.top-footerHeight>0)
			$('#footer').css({'position':'absolute', 'bottom':0, 'width':'100%'});
		else
			$('#footer').css({'position':'static'});
	}
	footer()
	$(window).resize(function(){ footer(); });
	function footerCol(){
		dist=($('#colRightWrap').height());
		$('#mainWrap').css('min-height', dist);
		$('#footer').css({'position':'static'});
	}
	footerCol();
	$(window).scroll(function(){ footerCol(); });
	$('.tooltip').tipsy({gravity: $.fn.tipsy.autoNS, delayIn: 300});
	<?php if($content['fancybox']){ ?>$('.fBox').fancybox({maxWidth:600});<?php }
		  if($sess instanceof Session) $sess->msg(); ?>
	// Errores de AJAX
	$(document).ajaxError(function(event, request, settings, error){
  		if(error.length>0){
			// Segun el error mostramos algo o no
			if(error=='Timeout'){
				dispError('Se ha perdido la conexión, recarga la página y prueba de nuevo');	
			}
		}
	});
	<?php if($sess instanceof Session){ if($sess->logged()){ ?>
	function updateNotifications(){
		// Notificaciones en la barra de menu
		$.post("/ajax.php", {type:'notifs',ajax:'true' },
		  function(data){
			  	// No nos interesa si falla
				if(data.done == 'true'){
					// Nuevos mensajes
					if(parseInt(data.msgs) > 0) $('a#navLinkMsg span').addClass('new').text(data.msgs);
					else $('a#navLinkMsg span').removeClass('new').text('');
					
					// Nuevos "nuevo"
					if(parseInt(data.nuevo) > 0) $('a#navLinkNew span').addClass('new').text(data.nuevo);
					else $('a#navLinkNew span').removeClass('new').text('');
				}
		  }, "json");
	}
	updateNotifications()
	// Actualiza notificaciones
	var updater = setInterval(function(){ updateNotifications(); },30000);
	
	<?php } } ?>
	
	$('#loginBox .externalLogin').click(function(event){
		event.preventDefault();
		// Lo de stats es para desactivar el gif en el perfil
		if(!$(this).hasClass('stats')){
			$('.hideOnAction').hide().after('<div style="margin-top: 55px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Conectando</p></div>');
		}
		
		var url = $(this).attr('href');
		var oauthWindow = window.open(url,'Conectando',"height=500,width=700,scrollTo,resizable=0,scrollbars=0,location=0");
		var oauthInterval = window.setInterval(function(){
			if (oauthWindow.closed) {
				window.clearInterval(oauthInterval);
				window.location.reload();
			}
		}, 1000);
	});
});
-->
</script>
</head>

<body>
	<?php if($fb){ ?>
	<div id="loginBox" class="hideOnAction" style="width:300px">
        <img src="http://static.quepiensas.es/img/body/logo-grey-transparent.png" border="0" />
        <p>
            <a class="fb social externalLogin" href="<?php echo $fb->fbLogin(); ?>" title="Inicia sesión con Facebook">
            <img src="http://static.quepiensas.es/img/social/f.png" border="0" alt="f" /> Inicia sesión con Facebook</a>
            <a class="tw social externalLogin" href="<?php echo $tw->loginLink(); ?>" title="Inicia sesión con Twitter">
            <img src="http://static.quepiensas.es/img/social/t.png" border="0" alt="t" /> Inicia sesión con Twitter</a>
        </p>
        <hr style="border-bottom:none;" />
        <form action="/ajax.php" name="loginForm" id="loginForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="login" />
            <input type="hidden" name="ajax" value="false" />
            <input type="hidden" name="next" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
            <div id="saveMsgError" class="errorMsg" style="display:none;"></div>
            <label>Email:
            <input type="email" name="email" id="email" class="loginInput" value="<?php echo $user->g('email'); ?>" /></label></label>
            <label>Contraseña: <a style="float:right;" href="/do/forgot-pass">¿Olvidate tu contraseña?</a>
            <input type="password" name="pass" id="pass" class="loginInput" /></label></label>
            <input name="save" id="loginBtn" type="submit" value="Iniciar sesión"/>
            <label style="font-size: 12px;">
            	<input type="checkbox" style="position: relative; bottom: -2px; display:inline;">Recordar
            </label>
            <span id="aviso">¿No tienes cuenta? <a href="/do/register">Regístrate</a></span>
        </form>
    </div>
    <?php } ?>


<div id="mainWrap">
  <div id="topNavBar">
      <div id="logo"><a href="http://quepiensas.es" title="Inicio"><img src="http://static.quepiensas.es/img/body/logo-no-bottom.png" alt="Que Piensas?" height="81" border="0" /></a>
      <div class="slogan">&iquest;De qui&eacute;n quieres hablar?</div></div>
    <div id="bar">
            <div id="barContent">
           	  <div class="form">
            	<form action="/do/buscar" method="post" enctype="application/x-www-form-urlencoded">
               	  <input name="search" type="text" value="<?php if(isset($person)){ echo $person; }else{ echo 'Nombre completo...'; } ?>" class="input small" />
                  <input name="searchBtn" type="image" src="http://static.quepiensas.es/img/form/btn-inside-small-c.gif" class="insideBtnBig" title="Buscar" />
                </form>
           	  </div>
              <?php if($sess instanceof Session){ if(!$sess->logged()){ ?>
             	<div id="profileLink"><a href="#loginBox" class="fBox" style="color:#2f90be">Iniciar sesi&oacute;n</a> &bull; <a href="/do/register" title="Its free!" class="tooltip">Reg&iacute;strate!</a></div>
              <?php 
			  }else{ 
				$display = $user->g('email');
				if(!$display) $display = $user->g('name');
				if(!$display) $display = 'Mi perfil';
			  
			  ?>
              <div class="navLinks">
              	<a href="/">Inicio</a><a href="/do/nuevo" id="navLinkNew">Nuevo<span></span></a><a href="/do/messages" id="navLinkMsg">Mensajes<span></span></a>
              </div>
              <a id="profileLink" href="/do/profile" style="color:#2f90be" title="<?php echo $display; ?>" class="tooltip">Mi perfil</a>
				<?php } }else{
					echo '<a href="/">Volver</a>';	
				}?>
            </div>
            <div class="corner1"></div>
            <div class="corner2"></div>
    </div>
    </div>
    
    <div class="contentWrap" id="col1">
    	<div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <div class="topFade"></div>
        <div class="bottomFade"></div>
        <div class="content">