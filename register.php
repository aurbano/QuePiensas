<?php
include('lib/php/session.php');

if($sess->logged()){ 
   header('Location: /do/profile');
   die('Ya has iniciado sesion');
}

$content['title'] = 'Registrarse';
$content['js'][] = 'session';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Registro</h1>
<div class="paddedContent hideOnActionFile">
	<div id="preRegistration">
        <p>Si te registras podrás recibir respuestas a tus comentarios, sin revelar tu identidad. Además podrás seguir los perfiles que te interesen, para estar siempre al tanto de los nuevos comentarios</p>
        <p align="center"><small>También puedes entrar directamente con tu cuenta de Facebook/Twitter y saltarte este paso</small></p>
        <div style="text-align:center; margin:0 15px;">
           <a class="social fb externalLogin" href="<?php echo $fb->fbLogin(); ?>" title="Inicia sesión con Facebook">
            <img src="http://static.quepiensas.es/img/social/f.png" border="0" alt="f" /> Inicia sesión con Facebook</a>
        </div>
        <p align="center" style="font-size:10px; margin-top:5px; color:#777;">Al registrarte confirmas haber leido y aceptado nuestra <a href="/info/nota-legal">Nota Legal</a> y <a href="/info/privacidad">Politica de privacidad</a></p>
        <form action="/do/ajax" name="registerForm" id="registerForm" method="post" enctype="multipart/form-data">
        <input name="ajax" id="ajax" type="hidden" value="false" />
        <input name="type" id="register" type="hidden" value="register" />
        <input name="next" id="next" type="hidden" value="/do/register" />
        <fieldset>
            <div id="saveMsgErrorReg" class="errorMsg" style="display:none;"></div>
            <legend>Rellena tus datos:</legend>
            <label>Nombre:
            <input type="text" name="name" id="nameReg" class="formNormal" value="<?php echo ucwords($user->g('name')); ?>" /></label><br />
            <label>Email:
            <input type="text" name="email" id="emailReg" class="formNormal" value="<?php echo $user->g('email'); ?>" /></label></label>
            <label>Contraseña:
            <input type="password" name="pass" id="passReg" class="formNormal" /></label></label>
            <input name="save" id="registerBtn" type="submit" value="Registrarse" class="btn btnBlue" style="position:absolute; right:15px; bottom:47px; "/><br/>
            <p style="font-size:10px; padding:10px 10px 3px 10px; margin:0; color:#777;"><input type="checkbox" name="confirm" value="1" required /> He leido y aceptado vuestra <a href="/info/nota-legal">Nota Legal</a> y <a href="/info/privacidad">Politica de privacidad</a></p>
        </fieldset>
        </form>
    </div>
    <div id="postRegistration" class="hide">
    	<h2>Bienvenido <span></span></h2>
        <p>En unos minutos recibirás un email con el que podrás confirmar tu cuenta y empezar a usar Que Piensas!</p>
        <p>Gracias por registrarte</p>
    </div>
</div>
<?php
include('lib/content/footer.php');
?>