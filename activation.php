<?php
include('lib/php/session.php');
if(isset($_POST['email'])){
	if($sess->valid($_POST['email'],'email')){
		// Comprobamos que existe en unrev, y enviamos la confirmación
		$db = $sess->db();
		$data = $db->queryUniqueObject('SELECT id, name, email, secret FROM unrev_users WHERE email LIKE \''.addslashes($_POST['email']).'\'');
		if($data->id > 0){
			// Resend activation
			if($auth->sendActivationMail($data->id,$data->name,$data->email,$data->secret)) $msg = 'Email de confirmación enviado';
			else $msg = 'El email de confirmación no se pudo enviar';
		}else{
			$msg = 'El email que has introducido ya ha sido verificado o no habia sido registrado.';
		}
	}
}

$content['title'] = 'Confirmar cuenta';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Activación de la cuenta</h1>
<div class="paddedContent">
	Si no has recibido el email de confirmación puedes utilizar el formulario a continuación para que sea reenviada. Si aún asi no la recibes, ponte en <a href="/do/contacto">contacto</a> con nosotros.
<?php if($msg){ echo '<div class="errorBox" style="margin-top:20px;">'.$msg.'</div>'; }else{ ?>
    <form action="/do/activation" name="confirmForm" method="post">
    <fieldset>
    	<legend>Confirmación del email:</legend>
    	<label>Email:<br /><input type="text" name="email" class="formNormal" /></label> <input type="submit" class="btnBlue" style="padding:4px 20px; position:relative; bottom:-20px" name="btn" value="Enviar" />
    </fieldset>
    </form>
<?php } ?>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>