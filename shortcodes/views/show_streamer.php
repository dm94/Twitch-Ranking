<?php

if (!empty($_GET['elementor-preview'])) {
    return;
}

$data = shortcode_atts(array('nombre' => 'dm94dani'), $atts);

$p = get_page_by_title($data['nombre'],'OBJECT','canales');
if ($p != null) :
    $image = get_post_meta($p->ID,'imagen',true);
    $game = '';
    $online = get_post_meta($p->ID,'online',true);
    $views = get_post_meta($p->ID,'views',true);
    $follows = get_post_meta($p->ID,'seguidores',true);
    $viewers_current = 0;
    $streaming_tittle = '';

    if ($online == 1) {
        $game = get_post_meta($p->ID,'game',true);
        $streaming_tittle = get_post_meta($p->ID,'titulostreaming',true);
        $streaming_tittle = strip_tags($streaming_tittle);
        $viewers_current = get_post_meta($p->ID,'viewersCurrent',true);
    }

    $channel_link = get_permalink($p);
?>
    <div class="card" role="button" onclick="window.location = '<?php echo $channel_link;?>'">
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                <a href="<?php echo $channel_link;?>" class="stretched-link" target="_blank"><img data-no-lazy="1" class="img-fluid mb-0 rounded-circle align-self-center" alt="Twitch Image <?php echo $p->post_title;?>" src="<?php echo $image;?>"></a>
                </div>
                <div class="col-8">
                    <div class="row">
                        <div class="col-12 text-center">
                            <p class="card-title h4"><?php echo $p->post_title;?></p>
                        </div>
                        <div class="col-12">
                            <?php if ($online == 1) :?>
                                <div class="juego"><i class="fa fa-gamepad text-success"></i> <?php echo $game; ?></div>
                                <div class="titulo"><i class="fa fa-bullhorn text-warning"></i> <?php echo $streaming_tittle; ?></div>
                            <?php else: ?>
                                <div class="offline">Offline <i class="fa fa-circle"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 pt-3">
                    <?php if ($online == 1) : ?>
                        <div class="float-left"><i class="fa fa-eye text-danger"></i> <?php echo $viewers_current;?> viewers</div>
                    <?php endif; ?>
                    <div class="text-right float-right"><i class="fa fa-users text-info"></i> <?php echo $follows;?> seguidores</div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>