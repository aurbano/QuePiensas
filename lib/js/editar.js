$(document).ready(function(){
	$("a[href='#changePic']").click(function(e){
		e.preventDefault();
		$('.hideOnAjax').hide().after('<div style="margin-top: 15px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Cambiando</p></div>');
		$.post("/ajax.php", { type:'changePic', change:$(this).attr('rel'), ajax:'true' },
			  function(data){
					window.location.reload();
			  }, "json");
	});
	$('#picChanger').change(function(){
		$('#picChangeForm').submit();
		$('.hideOnAjax').hide().after('<div style="margin-top: 15px; text-align: center; color:#3B86C5"><img src="http://static.quepiensas.es/img/load/transparent-circle-drip.gif" alt="Cargando..." /><p>Cambiando</p></div>');
	});
	$("a[href='#changePass']").click(function(e){
		e.preventDefault();
		$(this).hide();
		$('#changePass').slideDown(200);
		$('#footer').css({'position':'static'});
	});
	// Arreglo para el label de subir foto
	if ($.browser.mozilla) {
		console.log('firefox');
		$("label").click(function (event) {
			console.log('clicked');
			$("#" + $(this).attr("for")).click();
		});
	}
});