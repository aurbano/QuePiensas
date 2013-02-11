<?php
include('lib/php/session.php');

if($sess->logged()){
	header('Location: /do/profile');
	die();	
}

if( (!$_GET['id'] || !$_GET['secret']) && !$_POST['pass1']){
	header('Location: /');
	die();
}

if(isset($_POST['token'])) $_GET['secret'] = $_POST['token'];
if(isset($_POST['usid'])) $_GET['id'] = $_POST['usid'];

if(isset($_POST['pass1']) && strlen($_POST['pass1'])>0){
	// Iniciar restauracion:
	$err = false;
	if($_POST['pass1'] !== $_POST['pass2']) $err = 'Las contraseñas deben coincidir';
	else if(!$sess->valid($_POST['usid'],'int') || $sess->clean($_POST['token'])!==$_POST['token'] ||$_POST['usid']<1) $err = 'Debes modificar únicamente los campos visibles';
	else{
		$db = $sess->db();
		// Check token
		$usid = $db->queryUniqueValue('SELECT usid FROM `restore` WHERE usid = \''.addslashes($_POST['usid']).'\' AND SHA1(secret)=\''.addslashes($_POST['token']).'\'');
		if(!$usid || $usid < 1) $err = 'El enlace no era válido, recuerda que sólo dura 3 dias. ¿Quieres <a href="/do/forgot-pass">generar otro</a>?';
		else{
			// Update pass
			if($auth->changePass($_POST['usid'],$_POST['pass1'])){
				// Elimina el enlace de restauracion
				$db->execute('DELETE FROM `restore` WHERE usid = \''.addslashes($_POST['usid']).'\' LIMIT 1');
				header('Location: /do/login');
				die();	
			}
			else $err = 'No se ha podido cambiar la contraseña, inténtalo más tarde';
		}
	}
}

$content['title'] = 'Restaurar contraseña';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Restaurar contraseña</h1>
<div class="paddedContent">
	<?php if($err){ echo '<div class="errorBox">'.$err.'</div>'; } ?>
	<p>Introduce a continuación tu nueva contraseña:</p>
    <form action="/do/resetPass" name="resetForm" method="post">
    	<input type="hidden" name="usid" value="<?php echo $_GET['id']; ?>" />
        <input type="hidden" name="token" value="<?php echo $_GET['secret']; ?>" />
    	<label>Contraseña:<br /><input type="password" name="pass1" class="formNormal" /></label><br />
        <label>Confirma la contraseña:<br /><input type="password" name="pass2" class="formNormal" /></label><br /> <input type="submit" class="btnBlue" style="padding:5px 20px;" name="btn" value="Restaurar" />
    </form>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>