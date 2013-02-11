$(document).ready(function(){	
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

/* -------- Marcar como Ofensivo/Spam ------ */
	$('.opinion').mouseover(function() {
		$(this).children('h3').children('a.greyBtn').show();
	}).mouseout(function(){
		$(this).children('h3').children('a.greyBtn').hide();
	});
	$('a.greyBtn').click(function(event){
		event.preventDefault();
		event.stopPropagation();
		var obj = $(this);
		$('.markOptions').remove();
		var cid = $(this).attr('rel');
		if(cid!=''){
			$(this).after('<div class="markOptions"><small>Marcar como</small><ul><li><a href="?act=markOffensive" style="color:#eb0000" rel="ofensivo">Ofensivo</a></li><li><a href="?act=markSpam" rel="spam">Spam</a></li></ul></div>').show();
			$('.markOptions a').click(function(event){
				event.stopPropagation();
				event.preventDefault();
				// Set up the gif
				// Now perform the action
				var opt = $(this).attr('rel');
				$(this).html('<img src="http://static.quepiensas.es/img/load/transparent-circle.gif" width="24" border="0" />');
				$.post("/ajax.php", { type:'flagComment', ajax:'true', cid:cid, opt:opt },
					  function(data){
							if(data.done == 'false'){
								// No se pudo guardar:
								if(data.msg.length>0){
									alert(stripslashes(data.msg))
								}else{
									alert('No ha sido posible guardar el mensaje, por favor inténtalo más tarde.')
								}
							}else{
								// OK, ahora ocultar y marcar
								$('.markOptions').remove();
								obj.attr('rel','');
								alert('Este comentario se ha marcado como '+opt+'. \nNuestro equipo se encargará de revisarlo lo antes posible. \n\nGracias! :)');
							}
					  }, "json");
			});
		}else alert('Ya has marcado este comentario, así que no puedes volver a marcarlo. Nuestro equipo se ocupará de revisarlo.');
	});
	function applyEvents(){
		$('body').click(function() {
			$('.markOptions').remove();
		});
		$('a[rel="showSpam"]').click(function(event){
			event.preventDefault();
			$(this).hide().prev('i').hide().next().next('.spam').show();
		});
		$('.timeline li').hover(function(){
			$(this).children('.info').children('.timestamp').children('.reply').show();
		},function(){
			$(this).children('.info').children('.timestamp').children('.reply').hide();
		});
		$('.timeline li .reply a').click(function(event){
			event.preventDefault();
			var id = $(this).attr('rel');
			var pid = $(this).attr('class');
			$('.timeline li .replyBox').remove();
			$('#timeline-'+id).append($('#replyBoxCopy').html());
			$('#timeline-'+id+' .replyBox input[name="rid"]').val(id);
			$('#timeline-'+id+' .replyBox input[name="pid"]').val(pid);
			$('#timeline-'+id+' .replyBox textarea').focus();
			$('.timeline form[name="addCommentForm"]').submit(function(event){
				event.preventDefault();
				sendReply($(this))
			});
			var obj = $('#timeline-'+id+' .replyBox');
			$('#timeline-'+id+' .replyBox a[href="#cancel"]').click(function(event){
				event.preventDefault();
				obj.remove();
			});
			/* ---------- RESPONDER COMO... --------------- */
			$('.timeline li .replyBox label').click(function(){
				$('.timeline li .replyBox label').removeClass('hoverLabel');
				$(this).addClass('hoverLabel');
				$('.timeline li .replyBox textarea').focus();
			});
			$('.timeline li .replyBox select').change(function(){
				$('.timeline li .replyBox textarea').focus();
			});
			// Reaplicar el autogrow
			$('textarea').autogrow();
		});
	}
	applyEvents();
/* ---------- RESPONDER ----------------------- */
	function sendReply(obj){
		if($('.timeline li .replyBox textarea').val()==''){
			$('.timeline li .replyBox .errorMsg').text('Debes escribir un mensaje!').show();	
		}else{
			// Un poco de diseño jaja
			$('.timeline li .replyBox .errorMsg').text('').hide();	
			$('.timeline li .replyBox .btn').attr('disabled','disabled');
			var pid = $('.timeline li .replyBox input[name="pid"]').val();
			var rid = $('.timeline li .replyBox input[name="rid"]').val();
			var name = $('.timeline li .replyBox input[name="name"]').val();
			var email = $('.timeline li .replyBox input[name="email"]').val();
			var msg = $('.timeline li .replyBox textarea').val();
			var ident = $('.timeline li .replyBox input[name=ident]:checked').val();
			var msgType =  $('.timeline li .replyBox select').val();
			// Vamos a empezar a enviar esto :)
			$.post("/ajax.php", { type:'saveComment', ajax:'true', pid:pid, name:name, email:email, msg:msg, ident:ident, rid:rid, msgType:msgType },
				  function(data){
						if(data.done == 'false'){
							// No se pudo guardar:
							if(data.msg.length>0){
								$('.timeline li .replyBox .errorMsg').html(stripslashes(data.msg)).show();
							}else{
								$('.timeline li .replyBox .errorMsg').html('No ha sido posible guardar el mensaje, por favor int&eacute;ntalo m&aacute;s tarde.').show();
							}
							$('.timeline li .replyBox .btn').removeAttr('disabled');
						}else{
							if(data.pid) if(data.pid>0) window.location = '/'+data.pid;
							
							if(msgType == 1){
								// Mensaje privado
								$('#timeline-'+rid).append('<div class="ok">Mensaje privado enviado</div>');
								$('#timeline-'+rid+' .ok').fadeOut(2000,function(){ $(this).remove(); });
							}else{
								// Respuesta publica
								// Las respuestas deben ir anidadas al principio del li donde se ponen
								$('.timeline #timeline-'+rid).after('<li id="timeline-'+data.id+'" class="tl-element thread"><img src="'+data.pic+'" width="50" class="timelinePic" /><font class="info">'+data.name+'<font class="timestamp"><span class="reply" style="display: none;"><a class="'+pid+'" rel="'+data.id+'" href="#replyBox">Responder</a></span>'+data.time+'<a href="?act=mark" rel="'+data.id+'" class="greyBtn" title="Marcar comentario" style="padding:0 5px 0 5px;">+</a></font></font><div class="comment-body">'+stripslashes(data.msg)+'</div></li>');
								
								$('#commentsSpan').text(parseInt($('#commentsSpan').text())+1);
							}
							obj.parent('.replyBox').remove();
						}
				  }, "json");
		}
	}
	
	/* -------- LOAD MORE COMMENTS ---------- */
	// Configuration
		var loadingComments = false;// Evitar llamadas repetidas
		var lastLoaded = 1;			// Ultimo bloque cargado
		var prevScroll = 0;			// Previous scroll (Determine direction)
	// ---------- //
	$(window).scroll(function(){
		if ($("#loadingOlder").length > 0){
			if($(window).scrollTop() >= $(document).height() - $(window).height() - 5){
			   // El usuario ha llegado abajo del todo
			   loadMoreComments()
			}
		}
		return true;
	});
	$('#loadingOlder a').click(function(e){
		e.preventDefault();
		loadMoreComments()
	});
	function loadMoreComments(){
		if(loadingComments) return true;
		loadingComments = true; // Activar el bloqueo
		// Mostrar el cargador
		$('#loadingOlder a').hide();
		$('#loadingOlder div').fadeIn();
		// Solicitar nuevos comentarios
		if(lastLoaded<1) lastLoaded = 1;
		$.ajax({
			type: "POST",
			url:"/ajax.php",
			data:{ type:'loadComments', ajax:'true', last:lastLoaded, tl_type:$('#tl_type').text(), tl_var:$('#tl_var').text() },
			dataType:"html",
			timeout:5000,
			success:function(data){
				// Quita el loader
				$('#loadingOlder div').hide();
				if(data == '-1' || data == '-2'){
					if(data=='-1') $('.timeline').append('<div class="errorBox" style="margin-top:15px">Ha ocurrido un error cargando comentarios</div>');
					return true;	
				}
				$('.timeline').append(data);
				if(typeof footer == 'function') footer() // Colocar bien el footer
				embedElements()
				applyEvents()
				loadingComments = false; // Desactivar el bloqueo
				$('#loadingOlder a').show();
				lastLoaded++;
			},
			error:function(xhr,err,e){
				$('#loadingOlder div').hide();
				$('#loadingOlder a').show();
				var display = false;
				if(err=='timeout') display = 'Se ha perdido la conexión con el servidor, por favor inténtalo de nuevo más tarde.';
				display = err+':: '+e;
				if(display.length>0) if(typeof dispError == 'function') dispError(display);
				loadingComments = false; // Desactivar el bloqueo
			}
		}); // $.ajax()
	}
	/*-------LOAD REPLY-------*/
	$("a[href='#loadConversation']").click(function(event){
		event.preventDefault();
		var obj=$(this).parent();
		var r=$(this).attr('rel');
		obj.html('<img src="http://static.quepiensas.es/img/load/fb-orange.gif" alt="gif"/>');
		$.ajax({
			type: "POST",
			url:"/ajax.php",
			data:{ type:'loadReply',rid: r, ajax:'true'},
			dataType:"html",
			timeout:5000,
			success:function(data){
				var d = new Date();
				var id = 'replies'+r+d.getTime();
				obj.parent().parent().after('<div style="display:none;" id="'+id+'">'+data+'</div>');
				$('#'+id).slideDown(function(){
					obj.remove();
				});
			}
		});
	});
});