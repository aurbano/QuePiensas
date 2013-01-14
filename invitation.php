<?php
include('lib/php/session.php');
if(isset($_COOKIE['qpin'])){
	header('Location: /do/login');
	die();	
}
if(!isset($_GET['id']) && !isset($_POST['email'])){
	header('Location: /');
	die();	
}
if(isset($_POST['email'])){
	if($sess->valid($_POST['email'],'email')){
		$db = $sess->db();
		if($db->queryUniqueValue('SELECT id FROM invites WHERE token = \''.addslashes($_POST['token']).'\' AND email LIKE \''.addslashes(strtolower($_POST['email'])).'\'')){
			// Invitacion valida, creamos la cookie
			setcookie('qpin',sha1($_POST['token'].(12345)).'_'.$_POST['token'],time()+12*30*24*60*60,'/','.quepiensas.es',false, true);
			header('Location: /do/beta-info');
			die();
		}else{
			$err = 'El email que has introducido no se corresponde con el de la invitación!';	
		}
	}
}

$content['title'] = 'Invitar';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Beta privada Que Piensas</h1>
<?php if($err) echo '<div class="errorBox">'.$err.'</div>'; ?>
<div class="paddedContent">
	Bienvenido a la beta privada de Que Piensas! Introduce el email desde el cual recibiste la invitación para acceder a la prueba:
    <form action="/invitation.php" name="inviteForm" method="post">
    <fieldset>
    	<input type="hidden" name="token" value="<?php echo $_GET['id']; ?>" />
    	<label>Email:<br /><input type="text" name="email" class="formNormal" /></label> <input type="submit" class="btnBlue" style="padding:4px 20px; float:right" name="btn" value="Entrar" />
    </fieldset>
    </form>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>