<?php
include('lib/php/session.php');

if($sess->logged()){ 
   header('Location: /do/profile');
}

$content['title'] = 'Login';
$content['js'][] = 'session';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Iniciar sesión</h1>
<div class="paddedContent hideOnActionFile"><p><strong>¿Aún no tienes una cuenta?</strong> &nbsp;&nbsp; <a href="/do/register">Regístrate!</a></p>
    <div style="text-align:center; margin:15px">
             <a class="social fb externalLogin" href="<?php echo $fb->fbLogin(); ?>" title="Inicia sesión con Facebook">
            <img src="http://static.quepiensas.es/img/social/f.png" border="0" alt="f" /> Inicia sesión con Facebook</a>
    </div>
    <form action="/do/ajax" name="loginForm" id="loginForm" method="post" enctype="multipart/form-data">
    <input name="ajax" id="ajax" type="hidden" value="false" />
    <input name="type" id="login" type="hidden" value="login" />
    <fieldset>
        <div id="saveMsgErrorLog" class="errorMsg" style="display:none;"></div>
        <legend>O con tu cuenta de Que Piensas:</legend>
        <label>Email:
        <input type="text" name="email" id="emailLog" class="formNormal" value="<?php echo $user->g('email'); ?>" /></label></label>
        <label>Contraseña:
        <input type="password" name="pass" id="passLog" class="formNormal" /></label></label>
        <input name="save" id="loginBtnLog" type="submit" value="Iniciar sesión" class="btn btnBlue" style="position:absolute; right:20px; bottom:11px; "/>
    </fieldset>
    </form>
    </div>
</div>
<?php
include('lib/content/footer.php');
?>