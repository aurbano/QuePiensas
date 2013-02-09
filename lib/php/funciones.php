<?php
/**
 * Some global namespaced functions
 */
 
/**
 * Generate the right hand menu
 */
function menu(){
	return '<h3>Perfil</h3>
			<ul class="links">
				<li><a href="/do/editar">Editar perfil</a></li>
				<li><a href="/do/messages">Mensajes privados</a></li>
				<li><a href="/do/people">Buscar amigos</a></li>
				<li><a href="/do/reset">Cerrar sesión</a></li>
			</ul>';
}