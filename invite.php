<?php
include('lib/php/session.php');
if(isset($_POST['email'])){
	if($sess->valid($_POST['email'],'email')){
		$db = $sess->db();
		$secret = rand(10000,100000);
		$db->execute('INSERT INTO invites (email, token) VALUE (\''.strtolower($_POST['email']).'\',\''.$secret.'\')');
		$msg = "Bienvenido,\nPara empezar a usar Que Piensas sigue el siguiente enlace e introduce tu email cuando se te pida:\n\nhttp://quepiensas.es/invite/$secret \n\nInformacion:\nCuando descubras algún problema, o se te ocurra alguna función que deberiamos añadir, responde a este email con ella, o envialo a soporte@quepiensas.es\nTambién puedes utilizar el botón que veras siempre a la izquierda, que dice 'Sugerencias'.\n\nMuchas gracias,\nEquipo Que Piensas";
		mail($_POST['email'],'Invitacion Que Piensas',$msg,"From:soporte@quepiensas.es\r\n");
		$created = true;
	}
}

$content['title'] = 'Invitar';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<h1 style="border-bottom:#7dc3ff 1px solid;">Invitar</h1>
<div class="paddedContent">
	<?php if($created) echo 'Invitacion enviada'; ?>
    <form action="/invite.php" name="inviteForm" method="post">
    <fieldset>
    	<legend>Genera una invitación:</legend>
    	<label>Email:<br /><input type="text" name="email" class="formNormal" /></label> <input type="submit" class="btnBlue" style="padding:4px 20px;" name="btn" value="Enviar" />
    </fieldset>
    </form>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>