<?php
if (!empty($_GET['elementor-preview'])) {
    return;
}
?>
<?php
    $postcanales = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
    foreach ($postcanales as $p) :
        if (strcasecmp(get_post_meta($p->ID,'online',true),'1') == 0 && (strcasecmp($p -> post_title, get_option('tr_ganador_ranking', 'gzonecomunidad')) == 0 || 
        strcasecmp($p -> post_title, get_option('tr_streamer_mes', 'gzonecomunidad')) == 0)) :
            $usuario = get_post_meta($p->ID,'usuario',true);
?>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-8">
                    <?php
                        echo "<iframe title='Twitch Streaming' id='streamning' src='https://player.twitch.tv/?channel=$usuario&parent=".$_SERVER['HTTP_HOST']."' autoplay='false' frameborder='0' allowfullscreen='true' scrolling='no' height='378' width='100%'></iframe>";
                    ?>
            </div>
            <div class="col-lg-4">
                <?php 
                    echo '<iframe title="Twitch Chat" src="https://www.twitch.tv/embed/'.$usuario.'/chat?parent='.$_SERVER['HTTP_HOST'].'" id="chat" frameborder="0" scrolling="no" height="378" width="100%"></iframe>';
                ?>
            </div>
        </div>
    </div>
<?php
        endif;
    endforeach;
?>