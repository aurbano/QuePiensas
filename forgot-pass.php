<?php
include('lib/php/session.php');

if($sess->logged()){
	header('Location: /do/profile');
	die();	
}

if(isset($_POST['email']) && strlen($_POST['email'])>0){
	// Iniciar restauracion:
	$err = false;
	if(!$sess->valid($_POST['email'],'email')) $err = 	'Debes introducir un email válido';
	else{
		$db = $sess->db();
		$data = $db->queryUniqueObject('SELECT id,name FROM users WHERE email LIKE \''.addslashes($_POST['email']).'\'');
		if(!$data || $data->id < 1) $err = 'El email introducido no corresponde a ninguna cuenta';
		else{
			// Generate restoration code and send mail
			$code = (rand(2,10)*$data->id).(123); // Random secret code
			if(!$data->name) $data->name = 'Anonimo';
			$db->execute('INSERT INTO `restore` VALUES ('.$data->id.','.$code.','.time().') ON DUPLICATE KEY UPDATE secret = \''.$code.'\', timestamp=\''.time().'\'');
			// Generate and send mail
			$msg = "Hola {$data->name},\n\nSi no has solicitado que tu contraseña sea reseteada ignora este mail.\nSi has sido tú quien ha solicitado el reseteo, sigue el enlace a continuación para cambiar tu contraseña:\nhttp://quepiensas.es/reset/{$data->id}/".sha1($code)."\n\nGracias por utilizar Que Piensas!\nAtentamente,\nEquipo Que Piensas";
			$sent = mail($_POST['email'],'Cuenta Que Piensas',$msg,"From:no-reply@quepiensas.es\r\n");
		}
	}
}
$content['css'] = 'social';

$content['title'] = 'Restaurar contraseña';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Restaurar contraseña</h1>
<div class="paddedContent">
	<?php if($err){ echo '<div class="errorBox">'.$err.'</div>'; }elseif($sent){ echo '<div class="infoBox">Te hemos enviado un email para resetear tu contraseña<br />(Si no aparece mira en la carpeta de Correo no deseado/Spam)</div>'; } ?>
	<p>Si has vinculado tu cuenta con Facebook/Twitter utiliza los botones a continuación para iniciar sesión:</p>
    <div style="text-align:center; margin:15px;">
            <a class="social fb externalLogin" href="<?php echo $fb->fbLogin(); ?>" title="Inicia sesión con Facebook">
            <img src="http://static.quepiensas.es/img/social/f.png" border="0" alt="f" /> Inicia sesión con Facebook</a>
        </div>
    <p>Utiliza el email de tu cuenta para restaurarla en caso contrario:</p>
	<form action="/do/forgot-pass" name="resetForm" method="post">
    <fieldset>
    	<legend>Cuenta Que Piensas:</legend>
    	<label>Email:<br /><input type="text" name="email" class="formNormal" /></label> <input type="submit" class="btnBlue" style="padding:5px 20px;" name="btn" value="Restaurar" />
    </fieldset>
    </form>
    <p align="center"><small>Si el email es válido se te enviará un correo con un enlace para restaurar la contraseña, únicamente podrás restaurarla utilizando dicho enlace. Dispondrás de 3 dias para restaurarla</small></p>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>