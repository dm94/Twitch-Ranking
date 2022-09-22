<?php  
/*
Template Name: Vincular StreamLabs/Usuario
*/

get_header();
?>
<?php
	function get_token_streamlabs($code) {
        $ch = curl_init('https://streamlabs.com/api/v1.0/token');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        $fields = array(
            'grant_type' => 'authorization_code',
            'client_id' => get_option('tr_streamlabs_client_id'),
            'client_secret' => get_option('tr_streamLabs_client_secret'),
            'redirect_uri' => get_permalink(),
            'code' => $code
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $data = curl_exec($ch);
		curl_close($ch);
        $response = json_decode($data, true);
        return $response['access_token'];
    }

	function insertarUsuarioStreamlabs($accesstoken) {
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p){
			if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
				update_post_meta($p->ID, 'tr_streamlabs_acesstoken', $accesstoken);
				update_post_meta($p->ID, 'tr_streamlabs_type', '4');
				update_post_meta($p->ID, 'tr_streamlabs_message', __('El usuario -user- te ha votado','twitch-ranking'));
				update_post_meta($p->ID, 'tr_streamlabs_direccionimage', 'https://comunidadgzone.es/wp-content/uploads/2018/05/logonuevo.jpg');
				update_post_meta($p->ID, 'tr_streamlabs_durationtype', '5');
			}
		}
	}
	function actualizarDatosStreamlabs($type,$mensaje,$dirimagen,$dirsonido,$duration) {
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p ){
			if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
				update_post_meta($p->ID, 'tr_streamlabs_acesstoken', $accesstoken);
				update_post_meta($p->ID, 'tr_streamlabs_type', $type);
				update_post_meta($p->ID, 'tr_streamlabs_message', $mensaje);
				update_post_meta($p->ID, 'tr_streamlabs_direccionimage', $dirimagen);
				update_post_meta($p->ID, 'tr_streamlabs_direccionsonido', $dirsonido);
				update_post_meta($p->ID, 'tr_streamlabs_durationtype', $duration);
			}
		}
		wp_redirect(get_permalink());
	}

	function borrarVinculoStreamlabs() {
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p ){
			if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
				delete_post_meta($p->ID, 'tr_streamlabs_acesstoken');
				delete_post_meta($p->ID, 'tr_streamlabs_type');
				delete_post_meta($p->ID, 'tr_streamlabs_message');
				delete_post_meta($p->ID, 'tr_streamlabs_direccionimage');
				delete_post_meta($p->ID, 'tr_streamlabs_direccionsonido');
				delete_post_meta($p->ID, 'tr_streamlabs_durationtype');
			}
		}
		wp_redirect(get_permalink());
	}

	function testVotacionStreamlabs() {
		$access_token = '';
		$type = 2;
		$message = 'ComunidadGzone te ha votado';
		$direccionimage = '';
		$direccionsonido = '';
		$durationtype = 5;
		$usuarioVotante = 'ComunidadGzone';
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p ){
			if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
				$access_token = get_post_meta($p->ID, 'tr_streamlabs_acesstoken',true);
				$type = get_post_meta($p->ID, 'tr_streamlabs_type',true);
				$message = get_post_meta($p->ID, 'tr_streamlabs_message',true);
				$direccionimage = get_post_meta($p->ID, 'tr_streamlabs_direccionimage',true);
				$direccionsonido = get_post_meta($p->ID, 'tr_streamlabs_direccionsonido',true);
				$durationtype = get_post_meta($p->ID, 'tr_streamlabs_durationtype',true);
			}
		}
		
		if(!empty($access_token)){
			$tipoaviso = 'donation';
			if($type == 2) {
				$tipoaviso = 'donation';
			} else if($type == 1) {
				$tipoaviso = 'follow';
			} else if($type == 3) {
				$tipoaviso = 'host';
			} else if($type == 4) {
				$tipoaviso = 'merch';
			}
			
			$ch = curl_init('https://streamlabs.com/api/v1.0/alerts');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, 1);
			$fields = array(
				'access_token' => $access_token,
				'type' => $tipoaviso,
				'image_href' => $direccionimage,
				'message' => (str_replace("-user-",$usuarioVotante,$message)),
				'duration' => ($durationtype*1000)
			);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
			$data     = curl_exec($ch);
			curl_close($ch);
		}
	}
?>
			<?php while ( have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content row">
					<?php
						$usuariovalido = false;
						if(is_user_logged_in()){
							$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
							foreach ($postcanales as $p ){
								if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
									$usuariovalido = true;
								}
							}
						
							if($usuariovalido){
								$access_token = '';
								$ttv_code = isset($_GET['code']) ? $_GET['code'] : null;
								if(!empty($ttv_code)){
									$access_token = get_token_streamlabs($ttv_code);
									if(!empty($access_token)) {
										insertarUsuarioStreamlabs($access_token);
									}
								}
								
								$type = 2;
								$message = 'ComunidadGzone te ha votado';
								$direccionimage = '';
								$direccionsonido = '';
								$durationtype = 5;
								$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
								foreach ($postcanales as $p ){
									if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true), get_current_user_id()) == 0) {
										$access_token = get_post_meta($p->ID, 'tr_streamlabs_acesstoken',true);
										$type = get_post_meta($p->ID, 'tr_streamlabs_type',true);
										$message = get_post_meta($p->ID, 'tr_streamlabs_message',true);
										$direccionimage = get_post_meta($p->ID, 'tr_streamlabs_direccionimage',true);
										$direccionsonido = get_post_meta($p->ID, 'tr_streamlabs_direccionsonido',true);
										$durationtype = get_post_meta($p->ID, 'tr_streamlabs_durationtype',true);
									}
								}
								if (!empty($access_token)) {
									$accion = isset($_GET['accion']) ? $_GET['accion'] : null;
									if($accion == 'probaraviso'){
										testVotacionStreamlabs();
									} else if ($accion == 'borrarvinculo') {
										borrarVinculoStreamlabs();
									} else if ($accion == 'actualizar') {
										$type=isset($_GET['type']) ? $_GET['type'] : null;
										$mensaje=isset($_GET['mensaje']) ? $_GET['mensaje'] : null;
										$dirimagen=isset($_GET['dirimagen']) ? $_GET['dirimagen'] : null;
										$dirsonido=isset($_GET['dirsonido']) ? $_GET['dirsonido'] : null;
										$duration=isset($_GET['duration']) ? $_GET['duration'] : null;
										actualizarDatosStreamlabs($type,$mensaje,$dirimagen,$dirsonido,$duration);
									} 
					?>
										<div class="col-lg-3">
											<div class="card">
												<div class="card-header">
													<?php _e("Testear aviso",'twitch-ranking');?>
												</div>
												<div class="card-body text-center">
													<p><?php _e('Al pulsarlo se creará una alerta.','twitch-ranking');?></p>
													<form method="GET" action="">
														<input type="hidden" name="accion" value="probaraviso"/>
														<button class="btn btn-primary btn-block" type="submit"><?php _e('Probar','twitch-ranking');?></button>
													</form>
												</div>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="card">
												<div class="card-header">
													<?php _e("Borrar vinculación",'twitch-ranking');?>
												</div>
												<div class="card-body text-center">
													<p><?php _e('Si no quieres recibir más avisos pulsa este botón.','twitch-ranking');?></p>
													<form method="GET" action="">
														<input type="hidden" name="accion" value="borrarvinculo"/>
														<button class="btn btn-danger btn-block" type="submit"><?php _e('Borrar','twitch-ranking');?></button>
													</form>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="card">
												<div class="card-header">
													<?php _e('Widget URL','twitch-ranking');?>
												</div>
												<div class="card-body text-center">
													<p><?php _e('Es el mismo que el de stream alerts para los follows, host, etc. Si lo quieres lo puedes coger desde su card.','twitch-ranking');?></p>
													<p><a href="https://streamlabs.com/dashboard/#/alertbox" class="btn btn-primary btn-block"><?php _e("Panel de StreamLabs",'twitch-ranking');?></a></p>
												</div>
											</div>
										</div>
										<div class="col-lg-12 mt-2">
											<div class="card">
												<div class="card-header">
													<?php _e('Personalizar Alertas','twitch-ranking');?>
												</div>
												<div class="card-body text-center">
													<p><?php _e('Para los enlaces de imagenes y sonido recomiendo subirlos a la misma streamlabs y coger el enlace desde alli.','twitch-ranking');?></p>
													<p><?php _e('Si dejas el campo de url de la imagen vacios se coge el que tienes configurado en streamlabs apartado merch','twitch-ranking');?></p>
													<form method="GET" action="">
														<input type="hidden" name="accion" value="actualizar"/>
														<div class="form-group">
															<label for="type"><?php _e("Tipo de alerta: ",'twitch-ranking');?></label>
															<select name="type" class="form-control">
																<option value="4" <?php if($type == 4){ echo ' selected="selected"'; } ?>><?php _e('Merch','twitch-ranking');?></option>
															</select>
														</div>
														<div class="form-group">
															<label for="mensaje"><?php _e('Mensaje a mostrar. Poned -user- donde quereis que se vea el nombre del usuario:','twitch-ranking');?> </label>
															<input class="form-control" type="text" name="mensaje" maxlength="99" value="<?php echo $message;?>" required />
														</div>
														<div class="form-group">
															<label for="dirimagen"><?php _e('URL de la imagen: ','twitch-ranking');?></label>
															<input class="form-control" type="text" name="dirimagen" maxlength="249" value="<?php echo $direccionimage;?>"/>
														</div>
														<div class="form-group">
															<label for="dirsonido"><?php _e('URL del sonido: ','twitch-ranking');?></label>
															<input class="form-control" type="text" maxlength="249" value="El sonido cambiarlo en streamlabs apartado merch para evitar problemas de volumen" disabled/>
														</div>
														<div class="form-group">
															<label for="duration"><?php _e('Duración de la alerta (Segundos) : ','twitch-ranking');?></label>
															<input class="form-control" type="number" name="duration" min="1" max="20" value="<?php echo $durationtype;?>" required/>
														</div>
														<button class="btn btn-primary" type="submit"><?php _e('Actualizar Alertas','twitch-ranking');?></button>
													</form>
												</div>
											</div>
										</div>
									<?php
								} else {
									?>
									<div class="col-lg-12">
										<div class="card">
											<div class="card-header">
												<?php _e('Puedes vincular Streamlabs facilmente pulsando a este botón','twitch-ranking');?>
											</div>
											<div class="card-body text-center">
												<a href="https://www.streamlabs.com/api/v1.0/authorize?client_id=<?php echo get_option('tr_streamlabs_client_id', '00000000000')?>&redirect_uri=<?php echo get_permalink();?>&response_type=code&scope=alerts.create" style="color:white; background-color:rgba(111,72,185,1);" class="btn btn-primary btn-lg mx-auto"><?php _e('Vincular Streamlabs','twitch-ranking');?></a>
											</div>
										</div>
									</div>
									<?php
								}
							}
						} else {
							wp_redirect(get_permalink());
						}
					?>
				</div> <!-- .entry-content -->
				</article> <!-- .et_pb_post -->
			<?php endwhile; ?>
</div> <!-- #main-content -->
<?php get_footer(); ?>