<?php
if (!empty($_GET['elementor-preview'])) {
    return "";
}
?>
<div class="col-lg-12 row">
<?php
    $postcanales = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
    foreach ($postcanales as $p):
        if (strcasecmp(get_post_meta($p->ID,'online',true),'1') == 0) :
            $usuario = get_post_meta($p->ID,'usuario',true);
?>
    <div class="col-lg-4 mt-2" style="cursor: pointer" onclick="window.location='<?php echo get_permalink($p->ID);?>';">
        <?php echo do_shortcode('[streamer nombre="'.$usuario.'"]'); ?>
    </div>
<?php
        endif;
    endforeach;
?>
</div>