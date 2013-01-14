$(document).ready(function(){					   
/* ---------- AJAX para guardar comentarios ------------- */
	var commentsToday = $('#opsAndComments span').attr('class');
	$('#addCommentForm').submit(function(event){
		event.preventDefault();
		if($('#msg').val()==''){
			$('#saveMsgError').text('Debes escribir un mensaje!').show();	
		}else{
			// Un poco de diseño jaja
			$('#saveMsgError').text('').hide();	
			$('#loader').fadeIn('slow');
			$('#saveBtn').attr('disabled','disabled');
			var pid = $('#pid').val();
			// Vamos a empezar a enviar esto :)
			$.post("/ajax.php", { type:'saveComment', ajax:'true', pid:pid, name:$('#name').val(), email:$('#email').val(), msg:$('#msg').val(), pname:$('#pname').val(), ident:$('input:radio[name=ident]:checked').val() },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							if(data.msg.length>0){
								$('#saveMsgError').html(stripslashes(data.msg)).show();
							}else{
								$('#saveMsgError').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
							}
							$('#loader').hide();
							restoreMsg();
						}else{
							if(data.pid){
								if(data.pid>0) window.location = '/'+data.pid;	
							}
							// Comentario guardado:
							commentsToday++;
							$('#opsAndComments span').text(' (+'+commentsToday+')');
							
							$('.timeline').prepend('<li id="timeline-'+data.id+'" class="tl-element"><img src="'+data.pic+'" width="50" class="timelinePic" /><font class="info">'+data.name+'<font class="timestamp"><span class="reply" style="display: none;"><a class="'+pid+'" rel="'+data.id+'" href="#replyBox">Responder</a></span>'+data.time+'<a href="?act=mark" rel="'+data.id+'" class="greyBtn" title="Marcar comentario">+</a></font></font><div class="comment-body">'+stripslashes(data.msg)+'</div></li>');
							
							$('#loader').hide();
							$('#saveBtn').removeAttr('disabled');
							$('#commentsSpan').text(parseInt($('#commentsSpan').text())+1);
							
							restoreMsg();
						}
				  }, "json");
		}
	});

/* --------  Formularios ---------------- */

	$('#name').focus(function(){
		if($(this).val() == 'Anonimo'){
			$(this).val('');
		}
	});
	$('#email').focus(function(){
		if($(this).val() == 'Email...'){
			$(this).val('');
		}
	});
	$('#name').blur(function(){
		if($(this).val() == ''){
			$(this).val('Anonimo');
		}
	});
	$('#email').blur(function(){
		if($(this).val() == ''){
			$(this).val('Email...');
		}
	});
/*  -------- Eventos ---------------- */

	function restoreMsg(){
		$('#loader').hide();
		$('#opinaHidden').hide();
		$('#msg').css('height','12px');
		$('#msg').val('');
		$('#msg').css('color','#cccccc');
	}

	$('#opinaHidden').hide();
	$('#msg').focus(function(){
		$('#opinaHidden').slideDown(300);
		$(this).animate({'height':'50px'},300);
		if($(this).val()=='') $(this).val('');
		$(this).css('color','#333333');
		$('#loader').hide();
	});
	
	$('html').click(function() {
		if($('#msg').val()=='') restoreMsg();
	});
	
	$('#opina').click(function(event){
		event.stopPropagation();
	});
/*  -------- Follow / Unfollow ---------------- */
	$('a[href="#follow"]').click(function(event){
		event.preventDefault();
		var obj = $(this);
		var pid=$(this).attr('id');
		var id=pid.replace(/follow-/,"");
		$.post("/ajax.php", { type:$(this).attr('rel'), ajax:'true',  id: id},
			function(data){
				if(data.done=='false'){
					//Mensaje de error
					alert('error');
				}else{
					//Cambiamos el boton a siguiendo
					$('#'+pid);
					if(obj.attr('rel')=='follow'){
						obj.attr('rel','unfollow').text('Siguiendo').removeClass('follow').addClass('following');
					}else{
						obj.attr('rel','follow').text('Seguir').removeClass('following').addClass('follow');
					}
				}
		});
	});
	$('a[href="#follow"]').hover(function(){
		if($(this).attr('rel')=='unfollow') $(this).text('Dejar de seguir');
	},function(){
		if($(this).attr('rel')=='unfollow') $(this).text('Siguiendo');
	});
});