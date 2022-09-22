<?php

/*
Template Name: Archive canales
*/
?>
<?php
get_header();
$postcanales = get_posts( array('post_type' => 'canales', 'orderby' => 'name','order' => 'ASC','numberposts' => -1, 'no_found_rows' => true));
	echo '<div class="row">';
	foreach ($postcanales as $p ){
		echo '<div class="col-lg-4 mt-2">';
		echo do_shortcode('[streamer nombre="'.get_post_meta($p->ID,'usuario',true).'"]');
		echo '</div>';
	}
	echo '</div>';
get_footer();
?>