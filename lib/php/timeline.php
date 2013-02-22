<?php
/**
 * Timeline management
 */
 
 
// Parseador de comentarios:
if(!function_exists('parse')) include('lib/php/linker.php');

/**
 * Timeline management class
 *
 * This class takes care of all aspects of a Twitter-like timeline. Even handles pagination, replies... etc
 * @author Alejandro U. Alvarez
 * @version 1.0
 * @package Display
 */
class Timeline{
	
	/** Numeric timeline type
	 *
	 * These are the possible types:
	 *  0. Twitter style timeline "{User}"
	 *	1. Private profile timeline "You said about {Person}"
	 *	2. News timeline "{User} said about {Person}"
	 *	3. Public profile timeline "Said about {User}"
	 *	4. Replies to your comments timeline "{User} said about {Person}"
	 *	5. Conversation "{User} said about {Person}"
	 */
	protected $type;
	/** Identifies the timeline if needed (User ID, Person ID...)
	 */
	protected $identifier;
	/** The query being used to load comments
	 */
	protected $query;
	/** Comments to be displayed per page
	 */
	protected $commentsPerPage;
	/** Initial offset, for pagination
	 */
	protected $offset;
	
	/** Timeline constructor
	 *
	 * Types 1 and 3 require the picture to be passed by parameter
	 * @param int Timeline type, defaults to 0
	 * @param int Timeline identifier, defaults to 0
	 * @param int Comments per page, defaults to 20
	 * @param int Pagination offset, defaults to 0
	 */
	function Timeline($type=0,$identifier=0,$limit=20,$offset=0){
		$this->type = $type;
		$this->identifier = $identifier;
		$this->commentsPerPage = $limit;
		$this->offset = $offset;
	}
	
	/**
	 * Builds timeline query, depends on type and identifier
	 * @access private
	 */
	protected function buidQuery(){
		$limit = $this->commentsPerPage;
		if($this->offset > 0) $limit = ($this->offset*$this->commentsPerPage).','.$this->commentsPerPage;
		//
		switch($this->type){
			case 0:
				$this->query = '
					SELECT
						comments.id,
						comments.pid,
						users.name,
						comments.usid,
						comments.msg,
						comments.timestamp,
						comments.state,
						comments.spam,
						comments.ident,
						comments.reply,
						(CASE usePic
							WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\'
							WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',comments.usid,\'.gif\')
							WHEN 2 THEN (SELECT pic_square FROM facebook WHERE fbid = users.fbuser)
							WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END)
						AS pic
					FROM
						(SELECT
							a.*, IF(b.rid IS NULL, a.id, b.rid ) AS reply
						 FROM
							comments a LEFT OUTER JOIN replies b ON (b.id=a.id)
						WHERE
							pid='.$this->identifier.'
						ORDER BY reply DESC, a.id ASC) AS comments, users
					WHERE users.id = comments.usid
					LIMIT '.$limit;
				break;
			case 1:
				$this->query = '
					SELECT
						personas.name AS pname,
						comments.msg,
						comments.usid,
						comments.id,
						comments.pid,
						comments.timestamp,
						comments.ident,
						comments.spam,
						comments.state
					FROM
						personas,
						comments
					WHERE
						comments.usid = '.$this->identifier.'
						AND comments.pid = personas.id
					ORDER BY
						comments.timestamp DESC
					LIMIT '.$limit;
				break;
			case 2:
				$this->query = '
					SELECT
						personas.name AS pname,
						comments.id,
						comments.msg,
						comments.pid,
						comments.timestamp,
						comments.ident,
						comments.state,
						comments.spam,
						(SELECT rid FROM replies WHERE id = comments.id) AS rid,
						users.name,
						users.id AS usid,
						(CASE users.usePic
							WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\'
							WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',comments.usid,\'.gif\')
							WHEN 2 THEN (SELECT pic_square FROM facebook WHERE fbid = users.fbuser)
							WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser)
						END) AS pic
					FROM
						personas, comments, relations, users
					WHERE
						personas.id=relations.pid
						AND relations.usid = '.$this->identifier.'
						AND relations.follow=1
						AND comments.usid = users.id
						AND comments.pid = personas.id
					ORDER BY
						comments.timestamp DESC
					LIMIT '.$limit;
				break;
			case 3:
				$this->query = '
					SELECT
						personas.name AS pname, 
						comments.id, 
						comments.usid, 
						comments.msg, 
						comments.pid, 
						comments.state, 
						comments.spam, 
						comments.timestamp, 
						comments.ident
					FROM
						personas, comments
					WHERE 
						comments.usid = '.$this->identifier.' 
						AND comments.pid = personas.id 
						AND ident=1 
					ORDER BY 
						comments.timestamp DESC 
					LIMIT '.$limit;
				break;
			case 4:
				$this->query = '
					SELECT
						personas.name AS pname,
						comments.id,
						comments.pid,
						comments.msg,
						comments.pid,
						comments.timestamp,
						comments.ident,
						comments.state,
						comments.spam,
						(SELECT rid FROM replies WHERE id = comments.id) AS rid,
						users.name,
						users.id AS usid,
						(CASE users.usePic
							WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\'
							WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',comments.usid,\'.gif\')
							WHEN 2 THEN (SELECT pic_square FROM facebook WHERE fbid = users.fbuser)
							WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser)
						END) AS pic
					FROM
						(SELECT
							comments.*
						FROM
							comments, replies
						WHERE
							comments.id = replies.id
							AND replies.rid IN (SELECT id FROM comments WHERE usid = '.$this->identifier.')
						) AS comments, users, personas
					WHERE
						comments.usid = users.id 
						AND comments.pid = personas.id
					ORDER BY
						comments.timestamp DESC
					LIMIT '.$limit;
				break;
			case 5:
				$this->query = '
					SELECT
						personas.name AS pname,
						comments.id,
						comments.msg,
						comments.pid,
						comments.timestamp,
						comments.ident,
						comments.state,
						comments.spam,
						users.name,
						users.id AS usid,
						(CASE users.usePic
							WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\'
							WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',comments.usid,\'.gif\')
							WHEN 2 THEN (SELECT pic_square FROM facebook WHERE fbid = users.fbuser)
							WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser)
						END) AS pic
					FROM
						personas, comments, users, replies
					WHERE
						comments.usid = users.id
						AND comments.pid = personas.id
						AND comments.id = '.$this->identifier.'
					GROUP BY
						comments.id ORDER BY comments.timestamp DESC
					LIMIT '.$limit;
				break;
		}
	}
	
	/**
	 * Displays the timeline. This function directly prints the data
	 *
	 * This should be called after the constructor directly on the page.
	 * @param boolean Whether the comments should be parsed
	 * @param string User profile picture, only needed for some types
	 */
	public function displayTimeline($parseComments=true,$pic=false){
		global $sess, $user;
		// Configuracion para cargar comentarios antiguos
		echo '<div style="display:none"><span id="tl_type">'.$this->type.'</span><span id="tl_var">'.$this->identifier.'</span></div><ul class="timeline">';
			$disp = $this->displayComments($parseComments,$pic, false);
			if($disp=='-2') echo '<div class="errorBox">No hay nada para mostrar</div>';
		echo '</ul>';
		if(!$disp || $disp < 0) return false;
		// Espacio de "Cargando comentarios..."
		if($disp == 1) echo '<div id="loadingOlder"><a href="#loadMore">Cargar más comentarios</a><div><img src="http://static.quepiensas.es/img/load/transparent-circle.gif" align="absmiddle" /> Cargando comentarios antiguos</div></div>';	
		// Caja para comentar
		echo '<div id="replyBoxCopy" style="display:none;'.$margin.'"><div class="replyBox replyBoxInline"> <form action="/ajax.php" method="post" name="addCommentForm">
					<input name="pid" type="hidden" value="'.$this->identifier.'" />
					<input name="ajax" type="hidden" value="false" />
					<input name="type" type="hidden" value="saveComment" />
					<input name="rid" type="hidden" value="" />
					<div class="errorMsg" style="display:none"></div>
					<textarea name="msg" cols="6" rows="1" wrap="virtual" class="formNormal" placeholder="Escribe un comentario..."></textarea>
					<div class="options">';
		if(!$sess->logged()){
			echo '<input name="name" type="text" placeholder="Nombre..." value="'.$user->g('name').'" class="formNormal" style="width:170px; margin:3px" /> <input name="email" type="text" value="'.$user->g('email').'" placeholder="Email (Privado)..." class="formNormal" style="width:170px" /></label>';
	   }else{
			echo '<label title="Comentar como '.$user->g('name').'" class="tooltip hoverLabel"><input type="radio" name="ident" value="1" style="display:none !important;" checked="checked" />'.$user->g('name').'</label><label title="Comentar de manera Anonima" class="tooltip"><input type="radio" name="ident" value="0" style="display:none !important;" />Anónimo</label><select name="msgType"><option value="0" selected="selected">Público</option><option value="1">Privado</option></select>';
		}
		echo '<div style="position:absolute; font-size:12px; top:3px; right:3px;"><a href="#cancel" style="margin-right:10px; font-size:11px">Cancelar</a> <input name="save" type="submit" value="Publicar" class="btn btnBlue" /></div></div></form></div></div>';
	}
	
	/** 
	 * Displays comments, it's like display timeline but this function only displays comments, without wrapping HTML
	 *
	 * It's meant to be used in AJAX calls that want to get more comments for an already displayed timeline
	 * @param boolean Whether the comments should be parsed
	 * @param string User profile picture, depending on the timeline type it may not be required
	 * @param boolean Whether you want the comments printed or returned
	 * @return string Comments, if $echo is set to false
	 */
	public function displayComments($parseComments=true,$paramPic=false,$echo=true){
		// Muestra comentarios	
		global $db, $user, $sess;
		if(!($db instanceof DB)){
			if(!($sess instanceof Session)) return false;
			$db = $sess->db();	
		}
		if($this->type == 2 && $this->identifier<1) $this->identifier = $user->id();
		// Check data
		if($this->identifier < 1){if($echo){ echo '-1'; } return -1; }
		// Build resource from MySQL
		if(!$this->query) $this->buidQuery();
		if(!$this->query){if($echo){ echo '-1'; } return -1; }
		$resource = $db->query($this->query);
		if(!$resource){ if($echo){ echo '-1'; } return -1; }
		$loadedComs = $db->numRows($resource);
		if($loadedComs<1){if($echo){ echo '-2'; } return -2; }
		//
		// Standard pic for specific timelines:
		if($this->type==1) $pic = $user->pic('square');
		if($this->type==3 && $paramPic) $pic = $paramPic;
		
		while($data = $db->fetchNextObject($resource)){
			$c = $data->id;
			$link = false;
			$style = false;
			// Usuario
			if(strlen($data->name)<1) $data->name = 'An&oacute;nimo'; else $data->name = '<a href="/user/'.$data->usid.'">'.$data->name.'</a>';
			$data->name = ucwords($data->name);
			
			if(function_exists('colorID')) $color = colorID($data->usid);
			
			if($pic) $data->pic = $pic;
			
			if($data->ident=='0'){
				$color = '#ccc';
				$data->name = 'Anónimo';
				$data->pic = 'http://img.quepiensas.es/noimage.png';
			}
			
			// Determinamos lo que hay que mostrar en el "titulo"
			switch($this->type){
				case 1:
					// Dijiste de {User}
					$title = '<small>Dijiste de</small> <a href="/'.$data->pid.'" title="Ir a la pagina de '.$data->pname.'">'.$data->pname.'</a>';
					$data->pic = $user->pic('square');
					break;
				case 2:
					// "{User} dijo de {Person}"
					$title = $data->name.' <small>dijo de</small> <a href="/'.$data->pid.'" title="Ir a la pagina de '.$data->pname.'">'.$data->pname.'</a>';
					break;
				case 3:
					// Dijiste de {User}
					$title = '<small>Dijo de</small> <a href="/'.$data->pid.'" title="Ir a la pagina de '.$data->pname.'">'.$data->pname.'</a>';
					break;
				case 4:
					// Dijiste de {User}
					$title = $data->name.' <small>respondió a tu comentario sobre</small> <a href="/'.$data->pid.'" title="Ir a la pagina de '.$data->pname.'">'.$data->pname.'</a>';
					break;
				case 0:
				default:
					// "{User}"
					$title = $data->name;
			}

			if(($data->ident=='0' && $this->type!=='1') || !$data->pic) $data->pic = 'http://img.quepiensas.es/noimage.png';
			if(($data->ident == '1' || $data->type=='1') && $data->pic !== 'http://img.quepiensas.es/noimage.png') $color = '';
			
			// Color de la barra izquierda
			$leftBar = colorID($data->usid);
			if($this->type == 1 || $this->type == 3) $leftBar = colorID($data->pid);
			if($data->ident == '0') $leftBar = '#ccc';
			
			// No leido
			$unread = '';
			if($data->state==0 && $this->type==4){
				$unread = ' unread';
			}
			
			$replyClass = '';
			if(($data->reply!==$data->id && $data->reply >0) || $this->type == 5) $replyClass = 'thread';
			
			// En respuesta a...
			$inReply = '';
			if($data->rid) $inReply = '<div class="info2"><a href="#loadConversation" rel="'.$data->rid.'"><img src="http://static.quepiensas.es/img/reply.png" alt="reply" border="0" /> En respuesta a...</a></div>';
			
			echo '<li id="timeline-'.$c.'" class="'.$replyClass.' tl-element '.$unread.'" style="border-left:'.$leftBar.' solid 4px;">';
			echo '<img src="'.$data->pic.'" style="background-color:'.$color.'" width="50" class="timelinePic" />
				  <font class="info">'.$title.'<font class="timestamp"><span class="reply"><a href="#replyBox" rel="'.$c.'" class="'.$data->pid.'">Responder</a></span>'.
					dispTimeHour($data->timestamp).' <a href="?act=mark" rel="'.$data->id.'" class="greyBtn tooltip" title="Marcar comentario" style="padding:0 5px 0 5px;">+</a>
				  </font>
				</font>
				<div class="comment-body">';
			if($data->state !== '1' && $data->spam < 3)
				if($parseComments){
					echo nl2br(parse(stripslashes($data->msg)));
				}else echo nl2br(stripslashes($data->msg));
			elseif($data->spam > 2){
				echo '<i>Comentario marcado como spam</i> <a href="?act=show" class="discreto" rel="showSpam">[Mostrar]</a><div class="spam">';
				if($parseComments){
					// Parseador de comentarios:
					echo nl2br(parse(stripslashes($data->msg)));
				}else echo nl2br(stripslashes($data->msg));
				echo '</div>';
			}elseif($data->state == '1') echo '<i>Comentario eliminado</i>';
			echo $inReply;
			echo '</div></li>';
		} // Fin del bucle While
		// Devuelve 1 si habia tantos comentarios como LIMIT, 2 si habia menos
		if($loadedComs >= $this->commentsPerPage) return 1;
		return 2;
	}
};