<?php
// Variables and configuration
$links = '<a href="/info/privacidad">Privacidad</a> | <a href="/info/nota-legal">Nota legal</a> | <a href="/info/acerca-de">Acerca de nosotros</a> | <a href="/do/contacto">Contacto</a> | <a href="/info/prensa">Prensa</a> | <a href="/info/anunciantes">Anunciantes</a>';

if($fullFooter){ ?>
    <div id="footer">
        <div id="topBar">EL ANONIMATO NUNCA FUE TAN INTERESANTE!</div>
        <div id="metaNav">
            <ul>
              <li><a href="/info/como-funciona">&iquest;C&oacute;mo funciona?</a></li>
              <li><a href="/info/faq">Preguntas frecuentes</a></li>
              <li><a href="/info/privacidad">Privacidad</a></li>
            </ul>
        </div>
        <div id="metaLink"><?php echo $links; ?> &bull; &copy; <a href="http://quepiensas.es">QuePiensas.es</a> (2009-2015) Todos los derechos reservados </div>
    </div>
<?php }else{ ?>
	</div></div>
    <?php if($content['cols']){ ?>
        <div id="colRightWrap">
            <?php
            foreach($content['cols'] as $a){
                echo ' <div class="contentWrap col2"><div class="contentCol2">'.$a.'</div></div>';
            }
            ?>
        </div>
    <?php  } ?>
    </div> <!-- #mainWrap -->
    <div id="footer" style="position:fixed; bottom:0; width:100%;">
        <div id="metaLink"><?php echo $links; ?> &bull; &copy; <a href="http://quepiensas.es">QuePiensas.es</a> (2009-2015) Todos los derechos reservados </div>
    </div>
<?php } ?>

<script type="text/javascript">
  var uvOptions = {};
  (function() {
    var uv = document.createElement('script'); uv.type = 'text/javascript'; uv.async = true;
    uv.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'widget.uservoice.com/4pNJxIPS0dQXIpyB8LqB6g.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uv, s);
  })();

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-3181088-10']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
  })();
<?php /* ----- DEBUG ------- */ if($debug){ ?>
function debug(msg){ $('#debug').html($('#debug').html()+'<p>'+msg+'</p>'); }
<?php }else{ ?>
function debug(msg){ return true; }
<?php } ?>
</script>
<script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/tipsy.js"></script>
<script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/autogrow.js"></script>
<?php if($content['fancybox']){ ?><script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/fancybox/jquery.fancybox.js"></script><?php } ?>
<script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/functions.js"></script>
<?php for($i=0;$i<sizeof($content['js']);$i++){ ?><script type="text/javascript" language="javascript" src="http://static.quepiensas.es/lib/js/<?php echo $content['js'][$i]; ?>.js"></script><?php } ?>

<?php if($_SESSION['debug']=='set') $sess->debug('END OF debug, by footer',true);?>

</body>
</html>