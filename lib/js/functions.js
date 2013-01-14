$(document).ready(function(){
	if($('input[name=search]').val() !== 'Nombre completo...'){
		if($(this).val() !== ' ' || $(this).val() == ''){
			$(this).css('color','#3e8ac9');
		}else{
			$(this).val('Nombre completo...');
			$(this).css('color','#CCCCCC');
		}
	}
	$('input[name=search]').focus(function(){
		if($(this).val() == 'Nombre completo...'){
			$(this).val('');
			$(this).css('color','#3e8ac9');
		}else if($(this).val() == ' ' || $(this).val() == ''){
			$(this).val('Nombre completo...');
			$(this).css('color','#CCCCCC');
		}else{
			$(this).css('color','#3e8ac9');	
		}
	});
	$('input[name=search]').blur(function(){
	 	if($(this).val() == ' ' || $(this).val() == ''){
			$(this).val('Nombre completo...');
			$(this).css('color','#CCCCCC');
		}
	});
	
	// Autocompletar:
	$('input[name=search]').autocomplete({
		source:'/autosearch.php',
		minLength:4,
		delay:150,
		autofocus:true,
		select: function(event, ui){
			event.preventDefault();
			$(this).val(ui.item.label);
			window.location = '/'+ui.item.value;
		}
	});
	
	$('.btnBlue').mouseover(function() {
		$(this).css('background','#4084c1 url(http://static.quepiensas.es/img/form/btn-blue-bg-hover.gif) repeat-x top');
	}).mouseout(function(){
		$(this).css('background','#4e7cad url(http://static.quepiensas.es/img/form/btn-blue-bg.gif) repeat-x top');
	});
	
	var inputBorderColor = '';
	
	$('input[type="text"], input[type="password"]').focus(function(){
		inputBorderColor = $(this).css('borderColor');
		$(this).css('borderColor','#7DB6DF');
	}).blur(function(){
		$(this).css('borderColor',inputBorderColor);
	});
	$('#debug h2').click(function(){
		$('#debug #debugInfo').slideToggle();
	});
	$('textarea').autogrow();
});
function stripslashes(str) { str=str.replace(/\\'/g,'\''); str=str.replace(/\\"/g,'"'); str=str.replace(/\\0/g,'\0'); str=str.replace(/\\\\/g,'\\'); return str; }

function dispError(msg){
	if(msg=='') msg = 'No ha podido completarse la acción en curso, por favor recarga la página e inténtalo de nuevo.';
	$.fancybox.open('<img src="http://static.quepiensas.es/img/icons/delete.png" style="float:left;" /><h3 style="padding-top:5px; text-indent:10px; ">Ha ocurrido un error</h3><div style="clear:left"></div><p>'+msg+'</p>',{maxWidth:600});	
}