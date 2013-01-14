<?php
include('lib/php/session.php');
// Variables de la pagina
$person = ucwords(strtolower(str_replace('-',' ',$sess->clean(trim($_GET['person'])))));

$content['title'] = 'Resultados de la b&uacute;squeda: '.$person;

$db = $sess->db();
$partes = explode(' ',$person);
$search = 'personas.name LIKE \'%'.$partes[0].'%\'';

for($i=1;$i<sizeof($partes);$i++){
	$search .= 'AND personas.name LIKE \'%'.$partes[$i].'%\'';
}

$personas = $db->query('SELECT personas.id, personas.name FROM personas WHERE '.$search);
$totalPersonas = $db->numRows($personas);
$atrib = $db->query('SELECT personas.id,vkeys.key AS dato, values.val FROM `vkeys`,`values`,`infoperson`,`personas` WHERE infoperson.pid = personas.id AND vkeys.id = infoperson.key AND values.id = infoperson.value AND personas.id IN (SELECT personas.id FROM personas WHERE '.$search.')');
// Disfruta del buen codigo ;) Mira que guapo lo que sigue jaja
if($db->numRows($atrib)>0){
	while($info = $db->fetchNextObject($atrib)){
		$datos[$info->dato]++;
	}
	arsort($datos);
	$totalDatos = sizeof($datos);
}else{
	// ... y ahora??
}
include('lib/content/top.php');
?>
<script type="text/javascript" language="javascript" src="lib/js/preguntas.js"></script>
<script type="text/javascript" language="javascript">
<!--
// Preparacion de los arrays para que JavaScript pueda filtrar los resultados:

// Funcion para capitalizar la primera letra
function ucword(string){
    return string.charAt(0).toUpperCase() + string.slice(1);
}
/* -------------------------- CASO 1: Hay resultados ------------------------------- */
<?php if($db->numRows($personas)>0){ ?>
	personas = new Array();
	p2a = new Array();
	p = new Array();
	var totalP = <?php echo $totalPersonas; ?>;
	var remain = totalP;
	questions = new Array(); // Para recordar las preguntas hechas
	<?php
	$a=0;
	while($info=$db->fetchNextObject($personas)){
		echo 'personas['.$info->id.']="'.$info->name.'";';
		echo 'p['.$a.']="'.$info->id.'";';
		$a++;
	}
	$db->resetFetch($atrib);
	while($info=$db->fetchNextObject($atrib)){
		if(!$p2a[$info->id]){ $p2a[$info->id]=true; echo 'p2a['.$info->id.'] = new Array();';}
		echo 'p2a['.$info->id.']["'.$info->dato.'"]="'.strtolower($info->val).'";';
	}
	?>
	datos = new Array(<?php $totalDatos; ?>);
	<?php $i=0; foreach($datos as $key => $value){ echo 'datos['.$i.'] = "'.$key.'";'; $i++; } }else{ /* Si hay resultados: */?>
/* -------------------------- CASO 2: No hay resultados ------------------------------- */
// Como no hay rsultados vamos a preguntar las 3 preguntas mas "habituales",
// y guardaremos los resultados en un array o alguna mierda asi para luego
// mandarlos por AJAX a ajax.php para crear la nueva persona, luego, redirigimos
// a la pagina de la persona y todo perfecto.
questions = new Array(0,1,2); // Solo hay 3 asi que no queda otra... xD Luego se sacan de MySQL por COUNT, o SUM da igual..
datos = new Array();
for(var i=0;i<questions.length;i++){
	datos[i] = iP[questions[i]];
}
var remain = 0;
// El resto de variables del caso 1 a false:
var personas = false;
var totalP = false;
var p2a = false;
var p = false;
<?php } ?>
// Declaracion de variables en general
var finalId = 0;
var confirmed = false;
var confirmInit = false;
var confirmType = 0;
var searchName = '<?php echo $person; ?>';
	$(document).ready(function(){
		var pNum = 1; // Numero de preguntas efectuadas
		respuestas = new Array(); // Recordaremos todas las respuestas, a todas las preguntas
		debug('Var Dump: questions='+questions+'; datos='+datos+'; searchName='+searchName+'; personas='+personas+'; p2a='+p2a);
		function nextQ(){
			debug('nextQ(), remain='+remain);
			if(remain>1 || remain==0){
				pNum++;
				$('#filter-input').val('').focus();
				$('#filter-top span').hide().text(pNum).fadeIn('slow');
				$('#filter-top h2').hide().html(preguntas[datos[pNum-1]]).fadeIn('slow');
				questions[pNum-1] = datos[pNum-1];
				debug('Using dato: '+datos[pNum-1]);
			}
		}
		// Falta arreglar esto para los casos en los que no hay datos (Mostrar los 3 minimos)
		function processQ(a){
			a = a.toLowerCase();
			respuestas[pNum-1] = a;
			debug('processQ: a='+a+', remain='+remain);
			if(remain > 1){
				for(var i=0;i<totalP;i++){
					if(p[i] != undefined && p2a[p[i]][datos[pNum-1]] != a){ debug('Deleted person: '+p[i]); delete p[i]; remain--;}
				}
			}else if(remain==1){
				// Hay que recorrer el array buscando al no undefined porque quitamos valores pero no keys
				debug('remain=1');
				if(!finalId){
					for(var i=0;i<totalP;i++){
						if(p[i] != undefined){ finalId = p[i]; }
					}
					debug('Created finalId: '+finalId);
				}
				// Toca confirmar al pavo
				if(!confirmed){
					// Si no esta confirmado:
					if(!confirmInit){
						debug('confirmed=false; confmirmInit=false');
						// Aun no le hemos preguntado la confirmacion
						// Comprobar todos los apellidos:
						if(searchName.toLowerCase() !== personas[finalId].toLowerCase()){
							// Primero vamos a confirmar que sea el que tenemos
							pNum++;
							$('#filter-input').val('').focus();
							$('#filter-top span').hide().text(pNum).fadeIn('slow');
							$('#filter-top h2').hide().html('&iquest;Buscas a '+personas[finalId]+'?').fadeIn('slow');
							searchName = personas[finalId];
							confirmInit = true;
							confirmType = 1;
							debug('Confirm by name; confirmInit=true; confirmType=1');
							return true;
						}else{
							// Joder dime que queda algun dato mas!!
							pNum++;
							if(datos[pNum-1]!== undefined){
								// Vale nos queda algo aun!
								// Para confirmar hacemos: su {dato} es {valor}? y esperamos un si o no
								$('#filter-input').val('').focus();
								$('#filter-top span').hide().text(pNum).fadeIn('slow');
								$('#filter-top h2').hide().html('&iquest;Su '+datos[pNum-1].toLowerCase()+' es '+ucword(p2a[finalId][datos[pNum-1]])+'?').fadeIn('slow');
								questions[pNum-1] = datos[pNum-1];
								confirmInit = true;
								confirmType = 1;
								debug('Confirm by dato, using '+datos[pNum-1]+'; confirmInit=true; confirmType=1');
								return true;
							}else{
								// Meca tio vamos apañados, no hay puta manera humana de confirmar a este
								alert('Nun se como confirmar a este');
								return true;
							}
						}
					}else{
						debug('confirmInit=true; confirmType='+confirmType+'; answer='+a);
						if(confirmType == 1 && (a=='si' || a == 'sí' || a=='s&iacute;' || a=='yes' || a=='true') ){
							confirmed = true;
							window.location='/'+finalId;
						}else if(confirmType == 2 && (p2a[finalId][datos[pNum-1]] == a) ){
							confirmed = true;
							window.location='/'+finalId;
						}else{
							$('#filter-input').val('').focus();
							$('#filter-top span').hide().text(pNum).fadeIn('slow');
							$('#filter-top h2').hide().html('Joder pues haber quien pollas es...').fadeIn('slow');
							searchName = personas[finalId];
							return true;	
						}
					}
				}else{
					debug('confirmed=true');
					window.location='/'+finalId;
				}
			}else{
				// No queda nadie :)
				respuestas[pNum-1] = a; // El indice se corresponde con el de questions, que contiene la vkey de la respuesta
				// Segun la info que se tenga, hay que seguir hasta tener al menos 3 datos:
				if(respuestas.length>=3){
					// Ya esta, enviamos por AJAX
					alert('Envio por AJAX: '+questions[0]+': '+respuestas[0]+'. '+questions[1]+': '+respuestas[1]+'. '+questions[2]+': '+respuestas[2]);
					$.post("/ajax.php", { type:'newPerson', name:"<?php echo $person; ?>", q0:questions[0], a0:respuestas[0], q1:questions[1], a1:respuestas[1], q2:questions[2], a2:respuestas[2] },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							$('#saveMsgError').html('Vaya... Si tal prueba mas tade que ahora no va ni de co&ntilde;a ;)').show();
						}else{
							// Flipaalo, nueva persona lista :)
							// Marchamos a su nuevita pagina!
							window.location='/'+data.pid;
						}
				  }, "json");
				}
			}
			if(remain==1){
				for(var i=0;i<totalP;i++){
					if(p[i] != undefined){ window.location='/'+p[i]; }
				}
			}
		}
		questions[pNum-1] = datos[pNum-1];
		$('#filter-top span').text(pNum);
		$('#filter-top h2').html(preguntas[datos[pNum-1]]);
		$('#filter-input').text('').focus();
		$('#filter-btn').click(function(event){
			event.preventDefault();
			processQ($('#filter-input').val());
			nextQ();
		});
		$('#nextQ').click(function(event){
			event.preventDefault();
			nextQ();
		});
	});
-->
</script>
<h1>Buscando a <?php echo $person; ?></h1>
<div id="opiniones">
	<div id="saveMsgError" style="display:none;"></div>
    <div id="filter" style="width:442px; margin:0 auto; position:relative; margin-bottom:50px;">
    	<div id="filter-top" style="background:url(img/body/search-steps-title.gif) no-repeat; width:442px; height:74px; position:relative;">
        	<span style="position:absolute; top:5px; left:25px; color:#FFFFFF; text-shadow:0 -1px #4785b7; font-family:Georgia, 'Times New Roman', Times, serif; font-size:50px;"></span>
            <h2 style="position:absolute; top:10px; left:60px; text-align:center; width:360px; color:#FFFFFF; text-shadow:0 -1px #4785b7; font-family:Georgia, 'Times New Roman', Times, serif"></h2>
        </div>
        <div id="filter-bottom" style="position:absolute; top:62px; right:5px; width:360px">
        	<form action="/" method="post" enctype="application/x-www-form-urlencoded">
            	<div class="bigInputWrap"><b></b><input name="filter-input" type="text" class="clearInput" id="filter-input" /><input name="searchBtn" type="image" src="img/form/btn-orange-go.gif" class="insideBtnGo" title="Siguiente" id="filter-btn" /></div>
            </form>
            <small>No s&eacute;, <a href="#" id="nextQ">siguiente pregunta &raquo;</a></small>        </div>
    </div>
</div>
<?php
include('lib/content/footer.php');
?>