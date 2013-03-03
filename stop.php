<?php
if(!($sess instanceof Session)){
	include_once('lib/php/session.php');
}

$content['title'] = 'Alto';

// Captcha
// Keys and info: https://www.google.com/recaptcha/admin
include('lib/php/recaptchalib.php');
$publickey = "6LdIxd0SAAAAAN1fXXHmJqbhvuXFRYG78-F-OCe2";

// Display error message
$error = false;

// Check if user has already submitted a test
if($_POST["recaptcha_response_field"] && $_POST["recaptcha_challenge_field"]){
	$privatekey = "6LdIxd0SAAAAANJZ24KnbSNAQUZKGNqGqlL9pisZ";
	$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if($resp->is_valid){
		// Remove bot block
		$sess->unblockWithCAPTCHA();
		$next = '/';
		if(isset($_POST['next']) && $_POST['next']!=='/do/stop' && strlen($_POST['next'])>0) $next = $_POST['next'];
		header('Location: '.$next);
		die('OK');
	}else $error = true;
}


include('lib/content/top.php');
?>
<h1>Tenemos un problema</h1>
<div class="paddedContent" style="text-align: center;">
	<p>Nuestro sistema ha detectado un ritmo demasiado rápido de actividad por tu parte, o comentarios con un contenido sospechoso.</p><p>Para poder continuar, y que el sistema no te confunda con un bot, por favor completa la siguiente prueba:</p>
	<form method="post" action="/do/stop">
		<input type="hidden" name="next" value="<?php echo $sess->referrer; ?>" />
		<?php if($error){ echo '<p style="color:red">Código incorrecto, intentalo de nuevo</p>'; } ?>
		<div align="center"><?php echo recaptcha_get_html($publickey); ?></div>
		<input type="submit" value="Continuar" class="btn btnBlue" />
	</form>
	<p><small>Si este mensaje te aparece demasiado a menudo ponte en contacto con nosotros.</small></p>
</div>
<?php
include('lib/content/footer.php');