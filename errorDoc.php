<?php
$names = array(400=>'Peticion mal formada',401=>'Sin autorizacion',403=>'Prohibido',404=>'No encontrado',500=>'Error interno del servidor');
$content['title'] = $names[$_GET['e']];
include('lib/content/top.php');
/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Error <?php echo $_GET['e']; ?></h1>
<div class="paddedContent">
<h2><?php echo $names[$_GET['e']]; ?></h2>
<?php if($_GET['e']==100){ ?>
<p>Uups! Ha ocurrido un error con el servidor y he enviado un email con la información del mismo al equipo técnico. Dentro de poco estará solucionado,<br />Disculpa las molestias</p>
<?php }else{ ?>
<p>Ha ocurrido un error inesperado, si ha introducido manualmente la URL, compruebe que esté bien escrita. En caso contrario vuelva atras y vuelva a intentarlo dentro de un tiempo,<br />Disculpe las molestias</p>
<?php echo $debugger; } ?>
<p style="text-align:center"><a href="javascript:history.back(-1)">Volver</a></p>
</div>
<?php
include('lib/content/footer.php');
?>