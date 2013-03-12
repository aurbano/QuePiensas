// JavaScript para persona:
// En bloques separados por funcion
$(document).ready(function(){
    
						   
/* ---------- AJAX  ------------- */
	$('#registerForm').submit(function(event){
		event.preventDefault();
		console.log('clicked')
		if($('#nameReg').val()=='' || $('#emailReg').val()=='' || $('#passReg').val()==''){
			$('#saveMsgErrorReg').text('Debes rellenar todos los campos').show();	
		}else{
			// Un poco de diseño jaja
			$('#saveMsgError').text('').hide();	
			$('#registerBtn').attr('disabled','disabled');
			// Vamos a empezar a enviar esto :)
			$.post("/ajax.php", { type:'register', ajax:'true', name:$('#nameReg').val(), email:$('#emailReg').val(), pass:$('#passReg').val() },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							if(data.msg.length>0){
								$('#saveMsgErrorReg').html(stripslashes(data.msg)).show();
							}else{
								$('#saveMsgErrorReg').html('No ha sido posible completar el registro, por favor int&eacute;ntalo m&aacute;s tarde.').show();
							}
							$('#registerBtn').removeAttr('disabled');
						}else{
							// Registration complete!
							$('#preRegistration').remove();
							$('#postRegistration').fadeIn();
						}
				  }, "json");
		}
		return false;
	});
	
	$('.paddedContent #loginForm').submit(function(event){
		event.preventDefault();
		if($('#emailLog').val()=='' || $('#passLog').val()==''){
			$('#loginMsgError').text('Debes rellenar todos los campos').show();	
		}else{
			// Un poco de diseño jaja
			$('#loginMsgError').text('').hide();	
			$('#loginBtnLog').attr('disabled','disabled');
			$('.hideOnAction').hide().after('<div style="margin-top: 55px; text-align: center; color:#3B86C5" id="loginLoaderDiv"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Iniciando sesión</p></div>');
			// Vamos a empezar a enviar esto :)
			$.post("/ajax.php", { type:'login', ajax:'true', email:$('#emailLog').val(), pass:$('#passLog').val() },
				  function(data){
					  	if(data){
							if(data.done == 'false'){
								// No se pudo guardar:
								if(data.msg.length>0){
									if(data.msg == 'reload'){
										window.location.reload();
										return;
									}
									$('#loginMsgError').html(stripslashes(data.msg)).show();
								}else{
									$('#loginMsgError').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
								}
								$('#loginBtnLog').removeAttr('disabled');
								$('.hideOnAction').show();
								$('#loginLoaderDiv').remove();
							}else{
								window.location = '/do/profile';
							}
						}else{
							$('#loginMsgError').html('Ha ocurrido un error, intentelo de nuevo mas tarde').show();
							$('.hideOnAction').show();
							$('#loginLoaderDiv').remove();
						}
				  }, "json");
		}
	});
	/* PROFILE FUNCTIONS */
	$('.profilePic').mouseover(function(){
		$('#changePicLink').show();
	}).mouseout(function(){
    	$('#changePicLink').hide();
	});
	// Unlink social networks
	$('a.unlink').click(function(e){
		e.preventDefault();
		$('.hideOnAjax').hide().after('<div style="margin-top: 15px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Desvinculando</p></div>');
		$.post("/ajax.php", { type:'unlink', acc:$(this).attr('rel'), ajax:'true' },
			  function(data){
					window.location.reload();
			  }, "json");
	});
/* -------------- Auto focus -------------------- */
	if($('#registerForm #name').val()!==''){
		$('#registerForm #pass').focus();
	}else $('#registerForm #name').focus();
	
	if($('#loginForm #email').val()!==''){
		$('#loginForm #pass').focus();
	}else $('#loginForm #email').focus();

/* --------- SOPORTE LOGIN EXTERNO (FACEBOOK Y TWITTER) -------- */

	$('.paddedContent .externalLogin').click(function(event){
		event.preventDefault();
		// Check for errors
		if($(this).attr('href')=='#error'){
			alert('Ha ocurrido un error, vamos a reiniciar tu sesión');
			window.location = '/do/reset';
			return;
		}
		// Lo de stats es para desactivar el gif en el perfil
		$('.hideOnActionFile').hide().after('<div style="margin-top: 55px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Conectando</p></div>');
		
		var url = $(this).attr('href');
		var oauthWindow = window.open(url,'Conectando',"height=500,width=700,scrollTo,resizable=0,scrollbars=0,location=0");
		var oauthInterval = window.setInterval(function(){
			if (oauthWindow.closed) {
				window.clearInterval(oauthInterval);
				window.location.reload();
			}
		}, 1000);	
	});
	
/* -------- PRELOAD GIF DE CARGANDO -------- */
	dripCircle = new Image();
	dripCircle.src = 'http://static.quepiensas.es/img/load/transparent-circle-drip.gif';
	
});