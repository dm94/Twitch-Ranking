<?php  
/*
Template Name: Solicitud Pendiente
*/

	get_header();
	global $wpdb;
?>
	<div id="main-content" class="page-card">
	<?php while ( have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content row">
			<?php
				if(is_user_logged_in()){
					$user_name = "";
					$usuarioAComprobarSoli = "";
					$idusuario = get_current_user_id();
					$havemeta = get_user_meta($idusuario, 'usuariotwitch', true);
					if ($havemeta) {
						$user_name = $havemeta;
					}
					
					if (empty($user_name)) {
					?>
						<div class="col-lg-12">
							<div class="card">
								<div class="card-header">
									<div class="row">
										<div class="col-xs-9 text-left">
											<div class="huge"><?php _e('Error','twitch-ranking'); ?></div>
										</div>
									</div>
								</div>
								<div class="card-footer text-center">
										<p class="mx-auto">
											<?php _e('No hemos encontrado ninguna solicitud','twitch-ranking'); ?>
										</p>
								</div>
							</div>
						</div>
					<?php
					} else {
						$estado = 0;
						if (!empty($user_name)) {
							$usuarioAComprobarSoli = $user_name;
						}
						
						$solicitudes = $wpdb->get_results('select nickstreamer,estado,fecha from '.$wpdb->prefix.'trsolicitutwitch where nickstreamer like "'.$usuarioAComprobarSoli.'" order by fecha ASC');
						foreach ($solicitudes as $row) {
							$estado = $row->estado;
						}
						
						?>
						<div class="col-lg-12">
							<div class="card">
								<div class="card-header">
									<div class="row">
										<div class="col-xs-6 text-left">
											<div class="huge"><?php _e('Estado de la solicitud','twitch-ranking'); ?></div>
										</div>
									</div>
								</div>
								<div class="card-footer text-center">
									<?php
									if ($estado == 1) {
									?>
										<p><span class="fa fa-times fa-5x text-danger"></span></p>
										<p><?php _e('Tu solicitud no cumple los requisitos','twitch-ranking'); ?></p>
									<?php
									} else if ($estado == 2 || $estado == 5) {
									?>
										<p><span class="fa fa-hourglass-start fa-5x"></span></p>
										<p><?php _e('Tu solicitud se está comprobando','twitch-ranking'); ?></p>
									<?php
									} else if ($estado == 3) {
									?>
										<p><span class="fa fa-times fa-5x text-danger"></span></p>
										<p><?php _e('Tu solicitud ha sido rechazada','twitch-ranking'); ?></p>
									<?php
									} else if ($estado == 4) {
									?>
										<p><span class="fa fa-check fa-5x text-success"></span></p>
										<p><?php _e('La solicitud ha sido aceptada','twitch-ranking'); ?></p>
									<?php
									} else if ($estado == 10 || $estado == 11) {
									?>
										<p><span class="fas fa-tasks fa-5x text-success"></span></p>
										<p><?php _e('La solicitud ha sido aceptada, por favor comprueba tu correo.','twitch-ranking'); ?></p>
									<?php
									} else {
									?>
										<p><span class="fa fa-times fa-5x text-warning"></span></p>
										<p><?php _e('No hemos encontrado ninguna solicitud','twitch-ranking'); ?></p>
									<?php
									}
									?>
								</div> 
							</div>
						</div>
							
					<?php
					
						if ($estado == 2 || $estado == 5) {
							the_content();
						}
						
					}
				} else {
					?>
					<a href="<?php echo wp_login_url(); ?>" title="Conectarte" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-lg text-center"><?php _e('Conéctate para poder ver tu solicitud','twitch-ranking'); ?></a>
					<?php
				}
			?>
		</div> <!-- .entry-content -->
	</article> <!-- .et_pb_post -->
	<?php endwhile; ?>
</div> <!-- #main-content -->
<?php get_footer(); ?>