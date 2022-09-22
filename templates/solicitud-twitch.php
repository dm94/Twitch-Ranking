<?php  
/*
Template Name: Solicitud de Twitch
*/

require plugin_dir_path( __DIR__ ).'/libraries/TwitchAPIConector.php';
?>
<?php

	function aniadirSolicitud($nickstreamer, $tipo, $mensaje, $imagen, $email, $idtwitch) {
		global $wpdb;
		
		if (get_option('tr_conf_auto_accept', false) == 'true') {
			if (isStreamer($nickstreamer)) {
				wp_redirect(get_home_url());
				return;
			}
			$new_post = array(
				'post_title' => $nickstreamer,
				'post_status' => 'publish',
				'post_type' => 'canales',
				'post_category' => array(0)
			);
			$post_id = wp_insert_post($new_post);
			
			avisoEnDiscord(sprintf(__('Se ha añadido el streamer %s al ranking: %s', 'twitch-ranking'), $nickstreamer, get_permalink($post_id)), 'Twitch Ranking');
			update_post_meta($post_id, 'twitchID', $idtwitch);
			update_post_meta($post_id, 'imagen', $imagen);
			update_post_meta($post_id, 'tr_wp_user_id', get_current_user_id());
			update_post_meta($post_id, 'usuario', $nickstreamer);
			require plugin_dir_path(__DIR__).'admin/utilidades.php';
			tr_generate_featured_image($imagen,$post_id);
			wp_redirect(get_permalink($post_id));
		} else {
			if (!haySolicitudPendiente($nickstreamer)) {
				$ip = sacarIP();
				$fecha =strtotime('now');
				$mysqldate = date('Y-m-d', $fecha);
				$wp_user_id = get_current_user_id();
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trsolicitutwitch (nickstreamer,Fecha,estado,mensaje,imagen,email,userid,ip,wp_user_id) values(%s,%s,%d,%s,%s,%s,%s,%s,%d)", 
					$nickstreamer,
					$mysqldate,
					$tipo,
					$mensaje,
					$imagen,
					$email,
					$idtwitch,
					$ip,
					$wp_user_id
				));
				avisoEnDiscord(get_option('tr_discord_mensaje'), 'SolicitudStreamers');
			}
		}
		
		wp_redirect(get_option('tr_conf_redirection_page', get_home_url()));
	}
	
	function haySolicitudPendiente($nickstreamer) {
		global $wpdb;
		$votaciones = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}trsolicitutwitch where nickstreamer='%s'",$nickstreamer));
		foreach ($votaciones as $row) {
			$segundos = strtotime('now')-strtotime($row->Fecha);
			$diferencia_dias = intval($segundos/60/60/24);
			if ($diferencia_dias < 30) {
				return true;
			}
		}
		return false;
	}
	
	function sacarIP() {
		$ip = '';
		$headers = array('HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR');
		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				$ip = $_SERVER[$header];
				return $ip;
			}
		}
	}
	
	$accion = isset($_POST['accion']) ? $_POST['accion'] : null;
	$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : null;
	$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : null;
	$imagen = isset($_POST['imagen']) ? $_POST['imagen'] : null;
	$idtwitch = isset($_POST['idtwitch']) ? $_POST['idtwitch'] : null;
	$email = isset($_POST['email']) ? $_POST['email'] : null;
	
	if ($accion=='enviarsoli') {
		aniadirSolicitud($usuario,2,$mensaje,$imagen,$email,$idtwitch);
	} else if ($accion=='enviarsoloranking') {
		aniadirSolicitud($usuario,12,$mensaje,$imagen,$email,$idtwitch);
	}
?>
<?php get_header(); ?>
<div id="main-content" class="page-card">

			<?php while (have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content row">
					<div class="col-12">
						<?php the_content(); ?>
					</div>
					<?php
						$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), get_permalink());
						$ttv_code = isset($_GET['code']) ? $_GET['code'] : null;
						
						if ($ttv_code) {
							$access_token = $twitchtv->get_access_token($ttv_code);
							$streamer_data = $twitchtv->get_user($access_token);
							if (!empty($streamer_data) && !empty($streamer_data->data) && !empty($streamer_data->data[0])) {
								$idtwitch			= $streamer_data->data[0]->id;
								$logo 				= $streamer_data->data[0]->profile_image_url;
								$visitas 			= $streamer_data->data[0]->view_count;
								$user_name 			= $streamer_data->data[0]->display_name;
								$tipobroadcaster 	= $streamer_data->data[0]->broadcaster_type;
								$seguidores 		= $twitchtv->get_followers($idtwitch);
							}
						}
						
						if (empty($user_name)) {
						?>
						<div class="col-lg-12">
							<div class="card">
								<div class="card-body text-center">
										<p class="mx-auto">
											<?php
												if (is_user_logged_in()) {
												?>
													<a href="<?php echo $twitchtv->authenticate();?>" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-lg btn-block"><?php _e('Comprobar si tengo los requisitos','twitch-ranking');?></a>
												<?php
												} else {
													?>
													<a href="<?php echo wp_login_url(); ?>" title="Conectarte" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-lg"><?php _e('Conéctate para poder mandar solicitud','twitch-ranking'); ?></a>
													<?php
												}
											?>
										</p>
								</div>
							</div>
						</div>
						<?php
						} else if (is_user_logged_in()) {
							$idusuario = get_current_user_id();
							update_user_meta($idusuario, 'usuariotwitch', $user_name);
							update_user_meta($idusuario, 'idtwitch', $idtwitch);
							
							$minSeguidores = get_option('tr_min_seguidores', '500');
							$minSeguidores = intval($minSeguidores/2);
							
							$minViews = get_option('tr_min_views', '500');
							$minViews = intval($minViews/2);
							
							$esvalido = true;
							
							$user_info = get_userdata($idusuario);
							$email = $user_info->user_email;
							
							global $wpdb;
							$hamandadosoli = false;

							$listado = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}trsolicitutwitch where  estado=2 and nickstreamer='%s'",$user_name));
							foreach ($listado as $row) {
								$hamandadosoli = true;
							}
							if ($hamandadosoli) {
							?>
							<div class="col-lg-12">
								<div class="card">
									<div class="card-header text-left">
										<div class="huge"><?php _e('Solicitud en revisión','twitch-ranking'); ?></div>
									</div>
									<div class="card-footer text-center">
										<p class="mx-auto"><?php _e('Tu solicitud esta siendo votada, espera a que te enviemos un correo con el resultado de la votación','twitch-ranking'); ?></p>
									</div>
								</div>
							</div>
							<?php
							} else {
							?>
							<div class="col-lg-12">
								<div class="card">
									<div class="card-header text-left">
										<div class="huge"><?php _e('Comprobación automatica','twitch-ranking'); ?></div>
									</div>
									<div class="card-body text-center">
										<p><?php sprintf(__('El canal de %s ha sido comprobado', 'twitch-ranking'),$usuario) ?></p>
										<?php 
											if ($visitas>=$minViews) {
										?>
										<p><span class="fa fa-check text-success"></span><?php echo (__('Tienes','twitch-ranking').' '.$visitas.' '.__('visitas','twitch-ranking'))?></p>
										<?php 
											} else {
												$esvalido = false;
										?>
										<p><span class="fa fa-times text-warning"></span><?php echo (__('Tienes','twitch-ranking').' '.$visitas.' '.__('y necesitas tener','twitch-ranking').' '.$minViews.' '.__('views.','twitch-ranking'))?>.</p>
										<?php 		
											}		
											if ($seguidores >= $minSeguidores) {
										?>
										<p><span class="fa fa-check text-success"></span><?php echo (__('Tienes','twitch-ranking').' '.$seguidores.' '.__('seguidores','twitch-ranking'))?>.</p>
										<?php 
											} else {
												$esvalido = false;
										?>
										<p><span class="fa fa-times text-warning"></span><?php echo (__('Solo tienes','twitch-ranking').' '.$seguidores.' '.__('seguidores y necesitas','twitch-ranking').' '.$minSeguidores.' '.__('seguidores.','twitch-ranking'))?>.</p>
										<?php 		
											}
										?>
										<?php 		
											if ($tipobroadcaster=='affiliate') {
										?>
										<p><span class="fa fa-check text-success"></span><?php _e('Eres afiliado de twitch','twitch-ranking'); ?></p>
										<?php 
											} else if ($tipobroadcaster=='partner') {
										?>
										<p><span class="fa fa-check text-success"></span><?php _e('Eres partner de twitch.','twitch-ranking'); ?></p>
										<?php 
											} else {
												$esvalido = false;
										?>
										<p><span class="fa fa-times text-warning"></span><?php _e('Necesitas ser afiliado para entrar en la comunidad.','twitch-ranking'); ?></p>
										<?php 		
											}
										?>
									</div>
								</div>
							</div>
							<div class="col-lg-12 mt-2">
								<div class="card">
									<div class="card-header">
										<div class="huge"><?php _e('Comprobación manual','twitch-ranking'); ?></div>
									</div>
									<div class="card-body text-center">
										<?php 
											if ($esvalido) {
										?>
										<p><span class="fa fa-check text-success"></span><?php _e('Tu canal ha superado la comprobación automatica','twitch-ranking'); ?></p>
										<p><?php _e('Dado que has superado la comprobación automatica vamos a pedirte que realices el siguiente formulario para poder realizar nuestra comprobación manual','twitch-ranking'); ?></p>
										<p><?php _e('Los streamers de la comunidad verán tu solicitud y son ellos los que votan si entras o no en la comunidad','twitch-ranking'); ?></p>
										<form method="POST" action="" id="enviarsoli">
											<input type="hidden" name="usuario" value="<?php echo $user_name;?>"/>
											<input type="hidden" name="imagen" value="<?php echo $logo;?>"/>
											<input type="hidden" name="idtwitch" value="<?php echo $idtwitch;?>"/>
											<input type="hidden" name="accion" value="enviarsoli"/>
											<div class="form-group">
												<label for="email"><?php _e('Email:','twitch-ranking'); ?> </label> 
												<input class="form-control" type="email" name="email" value="<?php echo $email;?>" required>
											</div>
											<div class="form-group">
												<label for="mensajeid"><?php _e('¿Por qué crees que encajas en nuestra comunidad? (Max: 200 caracteres)','twitch-ranking'); ?></label>
												<textarea class="form-control" id="mensajeid" name="mensaje" maxlength="200" form="enviarsoli"></textarea>
											</div>
											<button class="btn btn-primary" type="submit"><?php _e('Enviar','twitch-ranking'); ?></button>
										</form>
										<p><?php _e('Una vez envies el formulario nosotros te contestaremos por correo con el resultado de tu solicitud. (7 días max)','twitch-ranking'); ?></p>
										<?php 
											} else {
										?>
										<p><span class="fa fa-times" style="color:#DF0C22"></span><?php _e('Tu canal no ha superado la comprobación automatica','twitch-ranking'); ?></p>
										<p><?php _e('Aunque tu canal no cumpla con todos los requisitos sigue esforzandote y creciendo para conseguir superarlos','twitch-ranking'); ?></p>
										<?php 		
											}
										?>
									</div>
								</div>
							</div>
						<?php
							}
						}
						?>
				</div>
				</article>
			<?php endwhile; ?>
</div>
<?php get_footer(); ?>