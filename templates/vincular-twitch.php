<?php  
/*
Template Name: Vincular Twitch/Usuario
*/

get_header();

require plugin_dir_path( __DIR__ ).'/libraries/TwitchAPIConector.php';

?>
	<?php while (have_posts()) : the_post(); ?>
	
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content row">
			<?php
				$usuariovalido=false;
				$idtwitch = '';
				$user_name = '';
				if(is_user_logged_in()){
					$cu = wp_get_current_user();
					$email= $cu->user_email;
					$idusuario=get_current_user_id();
					
					$havemeta = get_user_meta($idusuario, 'usuariotwitch', true);
					if ($havemeta){
						$user_name = $havemeta;
					}
					
					$idtwitch = get_user_meta($idusuario, 'idtwitch', true);
					
					$accion=isset($_GET['accion']) ? $_GET['accion'] : null;
					global $wpdb;
					
					if($accion=="borrarmeta"){
						$havemeta = get_user_meta($idusuario, 'usuariotwitch', true);
						if ($havemeta){
							delete_user_meta($idusuario, 'usuariotwitch');
						}
					}
					
				} else {
					wp_redirect(wp_login_url());
				}
				
				if(empty($user_name)){
					$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), get_permalink());
					$ttv_code = isset($_GET['code']) ? $_GET['code'] : null;
					
					if ($ttv_code) {
						$access_token = $twitchtv->get_access_token($ttv_code);
						$streamer_data = $twitchtv->get_user($access_token);
						if (!empty($streamer_data) && !empty($streamer_data->data) && !empty($streamer_data->data[0])) {
							$idtwitch	= $streamer_data->data[0]->id;
							$user_name 	= $streamer_data->data[0]->display_name;
						
							if (is_user_logged_in()) {
								$idusuario = get_current_user_id();
								update_user_meta($idusuario, 'usuariotwitch', $user_name);
								update_user_meta($idusuario, 'idtwitch', $idtwitch);
								$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
								foreach ($postcanales as $p ){
									if (strcmp(get_post_meta($p->ID,'twitchID',true), $idtwitch) == 0) {
										update_post_meta($p->ID, 'tr_wp_user_id', $idusuario);
									}
								}
							}
						}
					}
				}
				if (empty($user_name) && is_user_logged_in()) {
				?>
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<?php _e('Puedes vincular twitch facilmente pulsando a este botón','twitch-ranking'); ?>
						</div>
						<div class="card-body text-center">
							<a href="<?php echo $twitchtv->authenticate();?>" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-block"><?php _e('Vincular twitch','twitch-ranking'); ?></a>
						</div>
					</div>
				</div>
				<?php
				} else if(is_user_logged_in()) {
					?>
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<?php _e('Twitch ya esta vinculado','twitch-ranking'); ?>
							</div>
							<div class="card-body text-center">
								<?php echo(__('Tu ID en Twitch es','twitch-ranking')." ".$idtwitch); ?>
							</div>
						</div>
					</div>
				<?php
				} else {
					?>
					<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<?php _e('Error','twitch-ranking'); ?>
						</div>
						<div class="card-footer text-center">
							<?php _e('Necesitas conectarte para poder vincular tu perfil con twitch','twitch-ranking'); ?>
						</div>
					</div>
				</div>
					
				<?php
				}
			?>
		</div> <!-- .entry-content -->
		</article> <!-- .et_pb_post -->
	<?php endwhile; ?>
</div> <!-- #main-content -->
<?php get_footer(); ?>