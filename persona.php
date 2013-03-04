<?php
include('lib/php/session.php');
// Variables de la pagina
if(!is_numeric($_GET['id'])){
	header('Location: /');
	die('Wrong ID');
}

$id = $sess->clean($_GET['id']);

// Includes
include('lib/php/style.php');
include('lib/php/person.php');
// Clase Timeline
include('lib/php/timeline.php');


$persona = new Person($id);
// Llamadas a la base de datos y comprobacion de si existe
if(!$persona->getData()){ header('Location: /'); die('NO DATA'); }

// Anadimos una visita a la persona, solo si no es visita consecutiva
if($_SESSION['current']!==$id){ $persona->addVisit(); }
// Guardamos la persona actual (Util para algunas comprobaciones)
$_SESSION['current'] = $id;

if($sess->logged()) $persona->addRelation(1);

// Inicio de base de datos

$db=$sess->db();

if($sess->logged()) $following = $db->queryUniqueValue('SELECT follow FROM relations WHERE pid='.$id.' AND usid='.$user->id());

$followingNum = $db->queryUniqueValue('SELECT COUNT(*) FROM relations WHERE follow=1 AND pid='.$id);
$commentsNum = $db->queryUniqueValue('SELECT count(*) FROM comments,personas WHERE comments.pid=personas.id AND personas.id='.$id);

$person = ucwords(stripslashes($persona->name));
$partes = explode(' ',$person);

$content['title'] = $person;
// Javascript
$content['js'][] = 'persona';
$content['js'][] = 'timeline';
// CSS
$content['css'][] = 'timeline';
$content['css'][] = 'buttons';

$extraCss = '<style type="text/css">
.timeline{margin:0 -5px !important;}
.stats{
	font:14px Arial, Helvetica, sans-serif;
	color:#666 !important;
	width: 88px;
	margin-left:0;
	margin-bottom:10px;
	margin-top:0;
	margin-right:0;
	padding:5px;
	text-transform: uppercase;
	display: inline-block;
	border-radius:5px;
	text-decoration:none;
}
.stats:hover{
	box-shadow:0 0 5px #ccc;
	-moz-box-shadow:0 0 5px #ccc;
	-webkit-box-shadow:0 0 5px #ccc;
	text-decoration:none;
	color:#06C;
	background-color:#f2f2f2;
}
.stats font{
	font-size:24px;
	color:#000;
	float:left;
	display: block;
	width: 130px;
}
#fb-linked{
	position: absolute;
	left: -5px;
	top: 14px;	
}
#share{
	margin-bottom:15px;
	padding:13px 0 15px 0;
	border-bottom:1px solid #666;
	background: #265781 url(http://static.quepiensas.es/img/body/footer-stripes-bg.gif);
	border: #265781 solid 1px;
	border-radius:10px;
	overflow:hidden;
	position:relative;
}
#share .socialShare{
	display:inline-block;
	margin-left:24px;
}
</style>';

$content['cols'][] = '
<div id="share">
<div class="socialShare"><div id="fb-root"></div><script src="http://connect.facebook.net/es_ES/all.js#appId=225922410772604&amp;xfbml=1"></script><fb:like style="width:70px; overflow:hidden;" href="http://quepiensas.es/'.$id.'" layout="box_count" width="70" show_faces="false" font=""></fb:like>
	</div></span>

<span class="socialShare" style="position:relative; top:3px;"><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://quepiensas.es/'.$id.'" data-text="Comenta sobre '.$person.'" data-via="QuePiensas_es" data-lang="es" data-count="vertical">Twittear</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></span>
	
	
</div>

    <a class="stats" href="/'.$id.'/followers">
        <font>'.$followingNum.'</font>
        Seguidores
    </a>
	<div class="stats" style="width:98px;">
        <font>'.$commentsNum.'</font>
        Comentarios
    </div>
    <div class="stats" style="width:200px; margin-bottom:0;">
        <font style="float:none;">'.$persona->visits.'</font>
        Visitas
    </div>
';

include('lib/content/top.php');

/* --------------------------------------------- */

?>
<?php if($persona->fbid){ echo '<div id="fb-linked"><img src="http://static.quepiensas.es/img/social/fb-linked.png" title="Persona verificada" class="tooltip" border="0" /></div>'; } ?>
<h1 style="border-bottom:#7dc3ff 1px solid;"><?php echo $person; ?> 

 <?php if($sess->logged()){ if($following==0){ ?>
 	<a href="#follow" class="large awesome follow" rel="follow" id="follow-<?php echo $id; ?>" style="position: absolute; top: 12px; right:30px;">Seguir</a>
 <?php }else{ ?>
 	<a href="#follow" class="large awesome following" rel="unfollow" id="follow-<?php echo $id; ?>" style="position: absolute; top: 12px; right:30px;">Siguiendo</a>
 <?php } }else{ ?>
 	<a href="#loginBox" class="large awesome follow fBox" rel="follow" id="follow-<?php echo $id; ?>" style="position: absolute; top: 12px; right:30px;">Seguir</a>
 <?php } ?>
</h1>
<br/><br/>
    <!--<div class="stats">
    	Región: <?php echo $persona->location['region'];?><br />
        Pais: <?php echo $persona->location['country'];?>
    </div>-->

<div id="opiniones">

    <div id="opina" class="replyBox">
            <form action="/ajax.php" id="addCommentForm" method="post" name="addCommentForm">
                <input name="pid" id="pid" type="hidden" value="<?php echo $id; ?>" />
                <input name="ajax" id="ajax" type="hidden" value="false" />
                <input name="type" id="type" type="hidden" value="saveComment" />
                <div class="errorMsg" style="display:none" id="saveMsgError"></div>
                <textarea name="msg" id="msg" cols="6" rows="1" wrap="virtual" class="formNormal" placeholder="Escribe un comentario..."></textarea>
                <div style="margin:10px 10px 0 10px;" id="opinaHidden">
                	<div style="position:relative">
                	<?php if(!$sess->logged()){ ?>
                    <label>Nombre:<br/>
                        <input name="name" id="name" type="text" value="<?php if($user->g('name')){ echo ucwords($user->g('name')); }else{ echo 'Anonimo'; } ?>" placeholder="Nombre..." class="formNormal" /></label>
                    <label class="private">Email (Privado)<br/>
                    <input name="email" id="email" type="text" value="<?php echo $user->g('email');  ?>" placeholder="Email..." class="formNormal" /></label>
                    <?php }else{ ?>
                    <fieldset>
                    	<legend>Comentar como:</legend>
                        <ul>
                        	<li><label><input type="radio" name="ident" value="0" /> Anónimo</label></li>
                            <li><label><input type="radio" name="ident" value="1" checked="checked"/> <?php echo $user->g('name'); ?></label></li>
                        </ul>
                     </fieldset>
                    <?php } ?>
                  <input name="save" id="saveBtn" type="submit" value="Publicar" class="btn btnBlue" style="font-size:18px; padding:10px; position:absolute; bottom:10px; right:10px;" />
                    <div style="background:#fff; bottom: 9px;height: 50px;position: absolute;right: 8px;text-align: center;width: 100px; display:none" id="loader"><img src="http://static.quepiensas.es/img/load/transparent-circle.gif" alt="Cargando..." width="32" height="32" border="0" style="position:absolute; top:12px; left:30px;" /></div>
                  </div>
                <?php if(!$sess->logged()){ ?>
    <small style="text-align:center; display:block; color:#999999; margin:10px 0 20px 0; font-size:11px">Si nos dejas tu email, te avisaremos cuando alguien te responda. El sistema es totalmente privado y nadie sabr&aacute; tu email. <a href="/info/nota-legal">Nota Legal</a></small>
    			<?php } ?>
    			</div>
            </form>
	</div>
    
    <!-- Comentarios -->
    <?php
	$tl = new Timeline(0,$id);
	$tl->displayTimeline();
	?>
</div>

<?php if($commentsNum==0){ ?><p style="text-align:center;">
	¡Sé el primero en comentar sobre <?php echo $person; ?>!
</p><?php } ?>

<?php
include('lib/content/footer.php');
?>