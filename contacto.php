<?php
include('lib/php/session.php');

if(isset($_POST['name']) && !$_POST['web'] && !$_POST['website'] && !$_POST['url']){
	// Ready to send
	include('lib/php/style.php');
	if(!$sess->valid($_POST['email'],'email')) $err = 'Debes introducir un email válido';
	else{
		$sent = mail('admin@quepiensas.es',$_POST['subject'],"Consulta de {$_POST['name']} <{$_POST['email']}>\n\n{$_POST['message']}","From:{$_POST['email']}\r\n");
	}
}

$extraCss='<style media="all">
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 82px;
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
	margin:5px 10px;
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
.stats:hover .vinc, .logged{background-position:0 40px;}
</style>';

$content['title'] = 'Contacto';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Contacto</h1>
<div class="paddedContent" style="text-align:left">
	<?php if($err){ echo '<div class="errorBox">'.$err.'</div>'; }elseif($sent){ echo '<div align="center" class="infoBox">Mensaje enviado, dentro de poco alguien de nuestro equipo lo revisará y se pondrá en contacto contigo.</div>'; } ?>
	<p style="text-align:center; float:right; padding-left:20px; margin-top:0;">
	 <a target="_blank" class="stats tooltip" href="http://facebook.com/quepiensas.oficial">
        <font id="facebook" class="vinc logged"></font>
        Facebook
    </a>
     <a target="_blank" class="stats tooltip"  href="http://twitter.com/quepiensas_es">
        <font id="twitter" class="vinc logged"></font>
        Twitter
    </a><br/>
    <a href="https://twitter.com/quepiensas_es" class="twitter-follow-button" data-show-count="false" data-lang="es">@QuePiensas_es</a>
	</p>
    
    <p>Si tienes alguna duda relacionada con la web, recuerda consultar las <a href="/info/faq">preguntas frecuentes</a> antes de continuar.<br/><br/> Para cualquier otra cosa puedes contactar con nosotros a través de twitter y facebook (links a la derecha) o escribirnos directamente a través de este formulario de contacto:</p>
    <form style="margin:30px 0 0 80px;" action="/do/contacto" name="contactForm" method="post">
      <label>Nombre:<br /><input type="text" class="formNormal" name="name" /></label>
        <div style="display:none"><input type="text" name="web" id="web" /><input type="text" name="url" id="url" />
        <input type="text" name="website" id="website" /></div>
      <label>Email:<br /><input type="text" class="formNormal" name="email" /></label><br />
      <label>Asunto:<br /><input type="text" class="formNormal" style="width:470px" name="subject" /></label>
      <label>Mensaje:<br /><textarea class="formNormal" name="message" style="width:461px" wrap="virtual" ></textarea></label>
      <div style="margin:10px; 0 0 13px"><input type="submit" value="Enviar" class="btn btnBlue" /></div>
    </form>
</div>
</div>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<?php 
include('lib/content/footer.php');
?>