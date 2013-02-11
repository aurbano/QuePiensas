<?php
$now = time();
$target = mktime(
	0, 
	0, 
	0, 
	12, 
	15, 
	2012
);

$diffSecs = $target - $now;

$date = array();
$date['secs'] = $diffSecs % 60;
$date['mins'] = floor($diffSecs/60)%60;
$date['hours'] = floor($diffSecs/60/60)%24;
$date['days'] = floor($diffSecs/60/60/24)%7;
$date['weeks']	= floor($diffSecs/60/60/24/7);

foreach ($date as $i => $d) {
	$d1 = $d%10;
	$d2 = ($d-$d1) / 10;
	$date[$i] = array(
		(int)$d2,
		(int)$d1,
		(int)$d
	);
}


$content['title'] = 'Beta privada';
include('lib/content/top.php');

/* --------------------------------------------- */

?>
<link rel="Stylesheet" type="text/css" href="/lib/js/countdown.css"></link>
<script language="Javascript" type="text/javascript" src="/lib/js/jquery.lwtCountdown-1.0.js"></script>
<script language="javascript" type="text/javascript">
	jQuery(document).ready(function() {
		$('#countdown_dashboard').countDown({
			targetDate: {
				'day': 		15,
				'month': 	12,
				'year':		2012,
				'hour': 	00,
				'min': 		00,
				'sec':		00
			}
		});
	});
</script>
<h1 style="border-bottom:#7dc3ff 1px solid;">Bienvenido a Que Piensas</h1>
<div class="paddedContent" style="padding:0 15px;">
	<p style="text-align:justify;">Actualmente Que Piensas es una <strong>beta privada</strong>, y únicamente es posible entrar por invitación.<br />Aproximadamente el <strong>15 de Diciembre</strong> pasaremos a beta pública y todo el mundo podrá entrar.</p>
    

<!-- Countdown dashboard start -->
<div id="countdown_dashboard">
    <div class="dash weeks_dash">
        <div class="digit"><?=$date['weeks'][0]?></div>
        <div class="digit"><?=$date['weeks'][1]?></div>
        <span class="dash_title">semanas</span>
    </div>

    <div class="dash days_dash">
        <div class="digit"><?=$date['days'][0]?></div>
        <div class="digit"><?=$date['days'][1]?></div>
        <span class="dash_title">días</span>
    </div>

    <div class="dash hours_dash">
        <div class="digit"><?=$date['hours'][0]?></div>
        <div class="digit"><?=$date['hours'][1]?></div>
        <span class="dash_title">horas</span>
    </div>

    <div class="dash minutes_dash">
        <div class="digit"><?=$date['mins'][0]?></div>
        <div class="digit"><?=$date['mins'][1]?></div>
        <span class="dash_title">minutos</span>        
    </div>

    <div class="dash seconds_dash">
        <div class="digit"><?=$date['secs'][0]?></div>
        <div class="digit"><?=$date['secs'][1]?></div>
        <span class="dash_title">segundos</span>
    </div>

</div>
<!-- Countdown dashboard end -->
    <img src="http://static.quepiensas.es/img/beta.png" style="padding:0 15px 10px 5px;" width="230" align="left" alt="beta" />
    <p style="text-align:right;">Si te han mandado una invitación y no te ha llegado, comprueba la carpeta Spam de tu servidor. Si no está, ponte en <a href="/do/contacto">contacto</a> con nosotros e intentaremos resolverlo cuanto antes.</p>
    
    <p style="text-align:right;">Si lo que deseas es una invitación, escríbenos un mail explicando por qué a <a href="mailto:beta@quepiensas.es">beta@quepiensas.es</a>, estaremos encantados de escucharte :)</p>
</div><br/>
<div style="padding:0 15px;">
	<h2>Pero... ¿Qué es QuePiensas?</h2>
    <img src="http://static.quepiensas.es/img/logo/que.png" width="135" height="99" alt="Que Piensas" style="padding-left:20px;" align="right">
     <p>QuePiensas es una iniciativa Española, creada con el objetivo de romper con los esquemas de las redes sociales actuales.</p>
	<p>Hasta ahora para poder entrar y usar un servicio había que registrarse, recibir una invitación, confirmar el email... Aquí buscamos crear un nuevo concepto, donde cada persona sea libre de expresar su opinión.</p>
</div>
</div>

<?php 
include('lib/content/footer.php');
?>