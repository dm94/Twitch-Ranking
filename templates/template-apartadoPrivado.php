<?php  
/*
Template Name: ApartadoPrivadoSoloStreamers
*/

get_header();

?>
<div>
	<?php while ( have_posts()) : the_post(); ?>
	
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<br>
		<div class="entry-content row">
			<?php
				$usuariovalido = false;
				if (is_user_logged_in()) {
					$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
					foreach ($postcanales as $p ){
						if (strcmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) === 0) {
							$usuariovalido = true;
						}
					}
				}
				if (!$usuariovalido) {
				?>
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-xs-9 text-left">
									<div class="huge"><?php _e('Solo los streamers de la comunidad pueden ver esta sección','twitch-ranking'); ?></div>
								</div>
							</div>
						</div>
						<div class="card-footer text-center">
							<p class="mx-auto">
								<p><a href="https://comunidadgzone.es/vincular-twitch/" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-lg"><?php _e('Pulsa aquí para vincular tu perfil de Twitch','twitch-ranking'); ?></a></p>
							</p>
						</div>
					</div>
				</div>
				<?php
				} else if($usuariovalido){
					the_content();
				}
				
			?>
		</div> <!-- .entry-content -->
		</article> <!-- .et_pb_post -->
	<?php endwhile; ?>
</div> <!-- #main-content -->
<?php get_footer(); ?>