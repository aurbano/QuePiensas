<?php
include('lib/php/session.php');

$person = ucwords(strtolower(str_replace('-',' ',trim(addslashes(htmlspecialchars($_GET['person'],ENT_COMPAT,'UTF-8')) ))));
if(strlen($person)<5){ header('Location: /'); die(); }
// Antes de nada vamos a volver a buscarla, por si acaso:
// (Solo exact match)
$db = $sess->db();
$id = $db->queryUniqueValue('SELECT id FROM personas WHERE name LIKE \''.$person.'\'');
// If there is an exact match, send there
if($id){ header('Location: /'.$id); die(); }

$partes = explode(' ',$person);

$content['title'] = $person;
$content['js'][] = 'persona';
include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;"><?php echo $person; ?></h1>
<div id="aboutInfo">
<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
    <ul>
    	<li>Visitas: <span>1</span></li>
    </ul>
</td><td>
    <ul>
        <li>Comentarios: <span>0</span></li>
    </ul>
</td></tr></table>
</div>

<h2 id="opsAndComments"><?php if($commentsNum==0){ ?>S&eacute; el primero en comentar! <?php }else{ ?>Opiniones y comentarios <?php } ?><span title="Comentarios hoy" style="cursor:help"><?php if($commentsToday>0){echo '(+'.$commentsToday.')';} ?></span></h2>
<?php if($commentsNum>0){ ?><input name="Opina" id="showOpinaBtn" type="button" value="Opina sobre <?php echo $partes[0]; ?>" class="btn btnOrange" style="position:absolute; top:100px; right:22px;" /><?php } ?>

<div id="opiniones">

    <div id="opina">
        <div class="greyBox" id="opina">
            <form action="/ajax.php" id="addCommentForm" method="post" name="addCommentForm">
                <input name="pid" id="pid" type="hidden" value="0" />
                <input name="type" type="hidden" value="saveComment" />
                <input name="pname" id="pname" type="hidden" value="<?php echo $person; ?>" />
                <div class="errorMsg" style="display:none" id="saveMsgError"></div>
                <label for="msg">Comentario:</label><textarea name="msg" id="msg" cols="6" rows="5" wrap="virtual" class="formNormal"></textarea>
                <div style="margin:10px 10px 0 10px">
                   <?php if(!$sess->logged()){ ?>
                    <label>Nombre:
                        <input name="name" id="name" type="text" value="<?php if($user->g('name')){ echo ucwords($user->g('name')); }else{ echo 'Anonimo'; } ?>" class="formNormal" /></label>
                    <label class="private">Email (Privado)
                    <input name="email" id="email" type="text" value="<?php if($user->g('email')){ echo $user->g('email'); }else{ echo 'Email...'; } ?>" class="formNormal" /></label>
                    <?php }else{ ?>
                    <fieldset style="margin-right:100px;">
                    	<legend>Comentar como:</legend>
                        <ul style="list-style:none; margin:0; padding:0;">
                        	<li><label><input type="radio" name="ident" value="0" style="display:inline-block;"/> Anónimo</label></li>
                            <li><label style="color:rgb(17, 123, 221)"><input type="radio" checked="checked" name="ident" value="1" style="display:inline-block;"/> <?php echo $user->g('name'); ?></label></li>
                        </ul>
                     </fieldset>
                    <?php } ?>
                    <input name="save" id="saveBtn" type="submit" value="Publicar" class="btn btnBlue" style="font-size:18px; padding:10px; position:absolute; bottom:<?php if($sess->logged()){ echo 30; }else{ echo 15; } ?>px; right:20px;" />
                    <div style="background:#f5f5f5 url(http://static.quepiensas.es/img/body/greyBox-topFade.gif) 0 9px repeat-x;bottom: 15px;height: 50px;position: absolute;right: 15px;text-align: center;width: 100px; display:none" id="loader"><img src="http://static.quepiensas.es/img/load/transparent-circle.gif" alt="Cargando..." width="32" height="32" border="0" style="position:absolute; top:12px; left:30px;" /></div>
                </div>
            </form>
        </div>
        <small style="text-align:center; display:block; color:#999999; margin:10px 0 20px 0; font-size:11px">Si nos dejas tu email, te podremos avisar cuando alguien te responda. El sistema es totalmente privado y nadie sabr&aacute; tu email<br />Al publicar un comentario confirmas que has leído y aceptas nuestra <a href="/info/nota-legal">Nota Legal</a></small>
    </div>
</div>
<?php
include('lib/content/footer.php');
?>