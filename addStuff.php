<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gestion de la base de datos</title>
<style type="text/css">
<!--
body {
	background-color: #F3F3F3;
}
#load{
	position:absolute;
	top:10px;
	right:10px;
	display:none;
	z-index:999;
}
.done{
	display:inline-block;
	color:#009900;
	font-size:12px;
}
-->
</style>
<script type="text/javascript" language="javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
<script type="text/javascript" language="javascript">
<!--
$(document).ready(function(){
	// Do some cool stuff
	$("input[type='text']").focus(function(){
		$(this).val('');
	});
	$("input[type='submit']").click(function(event){
		event.preventDefault();
		$('#load').show();
		alert( $(this).parents('form').nextUntil(".done","input[type='text']").map(function() { return this.id; }).get().join(','))
		//alert(attrib)
		/*
		$.post("/ajax.php", { type:'manageBD-'+$(this).parents('form').attr('id'),  },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							$(this).next('.done').text('Error');
						}else{
							// Comentario guardado:
							$(this).next('.done').text('Done. '+data.msg);
						}
				  }, "json");*/
		 $('#load').hide();
	});
});
-->
</script>
</head>
<body>
<div id="load"><img src="img/load/F3F3F3-loader.gif" alt="Loading" width="32" height="32" border="0" /></div>
<h1>Añade datos:</h1>
<h2>vkeys (Dato y Pregunta):</h2>
<form id="vkeys" name="form1" method="post" action="addStuff.php">
  <fieldset>
  <input name="textfield" type="text" id="dato" value="Dato..." /> 
  &bull; ¿
  <input name="textfield2" type="text" id="pregunta" value="Pregunta..." />
  ? 
  <input type="submit" name="button" id="button" value="Guardar" />
  <div class="done"></div>
  </fieldset>
</form>
<h2>Personas:</h2>
<form id="personas" name="form2" method="post" action="addStuff.php">
  <fieldset><p>
    <input name="textfield3" type="text" id="textfield3" value="Name..." />
    <input type="submit" name="button2" id="button2" value="Guardar" />
  </p>
  <p>Asignar informacion a esta persona: 
    <select name="select" id="select">
      <option value="0">Dato</option>
    </select>
    <input name="textfield4" type="text" id="textfield4" value="Valor" />
  </p><div class="done"></div></fieldset>
</form>
</body>
</html>
