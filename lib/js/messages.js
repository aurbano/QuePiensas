/* JS para mensajes privados */
$(document).ready(function(){
	$('a.header').click(function(e){
		e.preventDefault();
		var obj = $(this);
		var unread = false;
		var com = obj.attr('data-com');
		var ident = obj.attr('data-ident');
		if(obj.hasClass('unread')) unread = true;
		obj.removeClass('unread').addClass('read');
		if(!obj.children('.count').is(":visible")){
			// Check if thread was open:
			closeMsg();
			return true;
		}
		closeMsg();
		var total = parseInt(obj.children('.count').text().slice(1).substring(0, obj.children('.count').text().length-1));
		obj.children('.extract').hide();
		obj.children('.count').hide();
		obj.next('.thread').show();
		var thread = obj.parent('li').attr('id');
		// Añade caja de responder arriba del todo
		obj.after($('#replyBoxCopy').html());
		// Asignamos acciones a los botones de la caja de responder:
			// Fill in thread:
			$(".messages .replyBox input[name='thread']").val(thread);
			// Hide Anonymous option if ident is already not private
			$('.replyBox .replyPrivate').show();
			console.log("Ident="+ident);
			if(ident==1){
				console.log("Hidden!:D");
				$('#'+thread+' .replyBox .replyPrivate').remove();
				// Set it for all
				$('#'+thread+' .replyBox .options input').attr('checked','checked');
				$('#'+thread+' .replyBox .options label').addClass('hoverLabel');
			}
			/* ---------- RESPONDER COMO... --------------- */
			$('.replyBox label').click(function(){
				$('.replyBox label').removeClass('hoverLabel');
				$(this).addClass('hoverLabel');
				$('.messages .replyBox textarea').focus();
			});
			// Reaplicar el autogrow
			$('textarea').autogrow();
			/* ---------- RESPONDER ----------------------- */
			$(".messages form[name='replyMessageForm']").submit(function(e){
				e.preventDefault();
				$('.replyBox .errorMsg').hide();
				var msg = $('.messages textarea').val();
				var thread = $(this).find("input[name='thread']").val();
				var ident = $(this).find("input[name='ident']:checked").val();
				if($(this).find("input[name='ident']").val()==0 && ident == 1){
					// Changing mode, confirm
					var sure = confirm('Vas a revelar tu identidad, ¿Estás seguro de que es lo que quieres?');
					if(!sure) return;
				}
				if(msg.length < 1){
					$('.replyBox .errorMsg').html('Debes escribir un mensaje').show();
					$('.replyBox .btn').removeAttr('disabled');
					return;
				}
				$('.replyBox .btn').attr('disabled','disabled');
				$.post("/ajax.php", { type:'replyPM', ajax:'true', th:thread, msg:msg, ident:ident },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							if(data.msg.length>0){
								$('.replyBox .errorMsg').html(stripslashes(data.msg)).show();
							}else{
								$('.replyBox .errorMsg').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
							}
							$('.replyBox .btn').removeAttr('disabled');
						}else{
							$('.replyBox .btn').removeAttr('disabled');
							$('.messages textarea').val('').focus();
							// Variables
							var name = '<a href="/user/'+data.usid+'" style="color:#333"><strong>'+data.name+'</strong></a>';
							var pic = data.pic;
							var color = data.color;
							// Determine what data to use, according to ident code
							if(ident==0){
								// Keeping it private
								name = 'Tú en modo <strong>Anónimo</strong>';
								pic = 'http://img.quepiensas.es/noimage.png';
								color = '#ccc';
							}
							// Now add the newly sent PM
							obj.next().next('.thread').prepend('<li><img src="'+pic+'" style="background-color:'+color+'" width="50" /><div class="header"> '+name+' <small>'+data.time+'</small></div><div class="msgContent">'+data.msg+'</div></li>');
							// Actualizar el contador de mensajes en el hilo:
							obj.children('.count').text('('+(total+1)+')');
							var extract = msg;
							if(msg.length>50) extract = extract.substr(0,50)+'...';
							// Extracto:
							obj.children('.extract').text(extract);
							embedElements()
						}
				  }, "json");
			});
		$('.messages .replyBox textarea').focus();
		// Carga mensajes del hilo, si los hay
		if((total>1 || com > 0) && obj.attr('rel')!=='loaded'){
			more = 0;
			loadMsgs(obj,thread,com);
		}else if(total==1 && unread){
			// Si solo hay 1 privado y esta sin leer, ajax
			$.post("/ajax.php", { type:'changeStatus', ajax:'true', status:'read', th:thread },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							if(data.msg.length>0){
								$('.timeline li .replyBox .errorMsg').html(stripslashes(data.msg)).show();
							}else{
								$('.timeline li .replyBox .errorMsg').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
							}
						}
				  }, "json");
		}
	});
	var more = 0;
	function loadMsgs(obj,thread,com){
		// Colocamos loader
		obj.next().next('.thread').append($('#loadContainer').html());
		// Hay mas de 1, vamos a por ellos
		$.ajax({
			type: "POST",
			url:"/ajax.php",
			data:{ type:'getMsg', ajax:'true', th:thread, more:more, com:com },
			dataType:"xml",
			success:function(xml){
				obj.attr('rel','loaded');
				// Quita el loader
				$('.thread .loader').remove();
				var count = 0;
				$(xml).find('error').each(function(){
					alert($(this).text());
				});
				$(xml).find('msg').each(function(){
					var elem = $(this);
					var content = elem.children('content').text();
					var time = elem.children('timestamp').text();
					var style = '';
					if(parseInt(elem.attr('curUser'))==1) style = 'color:#333';
					if(content.length > 0){
						if(elem.attr('id')!=='error'){
							var extra = '';
							if(elem.attr('type')=='comment'){
								/*var prefix = 'Respondiste al comentario de '+name;
								if(elem.attr('curUser')==1){
									prefix = 'Respondió a tu comentario';
								}*/
								var prefix = '';
								var name = 'Anonimo';
								if(parseInt(elem.attr('usid'))>0){
									name = '<a href="/user/'+elem.attr('usid')+'" style="'+style+'">'+elem.attr('uname')+'</a>';
								}
								obj.next().next('.thread').append('<h3>'+prefix+'</h3><li><img src="'+elem.attr('src')+'" width="50" style="background:'+elem.attr('color')+'" /><div class="header"><strong>'+name+'</strong> dijo de <a href="/'+elem.attr('pid')+'"><strong>'+elem.attr('pname')+'</strong></a>  <small>'+time+'</small></div><div class="msgContent">'+content+'</div></li>');
								count = 0; // No hay mas fijo
							}else{
								var name = '';
								if(parseInt(elem.attr('usid'))>0){
									name = '<a href="/user/'+elem.attr('usid')+'" style="'+style+'"><strong>'+elem.attr('user')+'</strong></a>';
								}else{
									name = elem.attr('user')+' en modo <strong>Anónimo</strong>';
								}
								obj.next().next('.thread').append('<li><img src="'+elem.attr('src')+'" width="50" style="background:'+elem.attr('color')+'" /><div class="header">'+name+'  <small>'+time+'</small></div><div class="msgContent">'+content+'</div></li>');
								count++;
							}
						}else{
							obj.next().next('.thread').append('<li>'+content+'</li>');
						}
					}
					if(count >= 5){
						obj.next().next('.thread').append('<li><a href="#loadPrevious" class="greyBtn" style="color:#666 !important; padding: 5px 15px; margin-left: 170px;" rel="'+thread+'">Cargar anteriores</a></li>');
						// Cargar anteriores:
						$("a[href='#loadPrevious']").click(function(event){
							event.preventDefault();
							more ++;
							loadMsgs(obj,thread,0)
							$(this).parent('li').remove();
						});
					}
				});
				if(typeof updateNotifications == 'function') updateNotifications() // Actualiza las notificaciones
				embedElements()
			},
			error:function(xhr,err,e){
				$('.thread .loader').remove();
				//if(typeof dispError == 'function') dispError()
				return false;
			}
		}); // $.ajax()
	}
	/* ------------- EMBEDS -------------------- */
	function embedElements(){
		$("a[rel='playVideo']").each(function(){
			var obj = $(this);
			var container = $(this).parent('.embedElement');
			$.post("/videoInsert.php", {url:obj.attr('href') },
				  function(data){
						if(data.done == 'true'){
							obj.html(data.title).attr('href',data.linkURL);
							obj.next('.videoContainer').html('<div class="containerTitle">'+data.providerIcon+' '+data.elemTitle+' <a href="#closeVideo">Cerrar</a></div>'+data.embed);
							if(data.type=='video' || data.type=='rich'){
								obj.html(obj.html()+' <span class="provider">'+data.provider+' '+data.providerIcon+'</span><span class="description">'+data.aTitle+'</span>');
								container.addClass('video');
							}
							$("a[href='#closeVideo']").click(function(event){
								event.preventDefault();
								$("a[rel='playVideo']").show().next('.videoContainer').hide();
							});
						}
				  }, "json");
		});
		$("a[rel='playVideo']").click(function(event){
			event.preventDefault();
			$("a[rel='playVideo']").show().next('.videoContainer').hide();
			if($(this).next('.videoContainer').html().length>0){
				$(this).hide();
				$(this).next('.videoContainer').show();
			}else{
				window.location = $(this).attr('href');
				return true;
			}
		});
	}
	embedElements()
});
function closeMsg(){
	// Cierra todos los hilos abiertos
	$('.messages .thread').hide();
	$('.messages .extract').show();
	$('.messages .count').show();
	$('.messages .replyBox').remove();
}