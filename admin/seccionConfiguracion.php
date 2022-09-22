<?php

function mostrarTodoConfiguracion(){
	if(!current_user_can('administrator')) {
		return;
	}
	global $wpdb;

	$accion=isset($_GET['accion']) ? $_GET['accion'] : null;
	$usuario=isset($_GET['usuario']) ? $_GET['usuario'] : null;
	$tipo=isset($_GET['tipo']) ? $_GET['tipo'] : null;
	$dato=isset($_GET['dato']) ? $_GET['dato'] : null;
	
	$postcanales = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	
	if ($accion != null) {
		if ($accion=='aniadirusuario') {
			require 'twitch_utils.php';
			tr_add_new_streamer($usuario);
		} else if($accion=='borrarusuario') {
			foreach ($postcanales as $p){
				if (strcasecmp($p->post_title, $usuario) == 0) {
					wp_delete_post($p->ID);
				}
			}
		} else if ($accion=='vaciarvotos') {
			foreach ($postcanales as $p ){
				$wpdb->query($wpdb->prepare("delete from ".$wpdb->prefix."trvotaciones where Usuario=%s", 
				get_post_meta($p->ID,'usuario',true)
				));
			}
			avisoEnDiscord(__('Se han borrado los votos del ranking. Nueva temporada!!', 'twitch-ranking'), 'Twitch Ranking');
		} else if ($accion == 'sacarganador') {
			$votosTotal = $wpdb->get_results('SELECT MAX(IDVotacion) idMax FROM '.$wpdb->prefix.'trvotaciones');
			foreach ($votosTotal as $row){
				$idMax = $row->idMax;
			}
			$aleatorio = rand(1, $idMax);
			$ganador = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'trvotaciones where IDVotacion='.$aleatorio);
			foreach ($ganador as $row1){
				$nickGanador = $row1->Votante;
				echo 'El ganador es '.$nickGanador;
			}
		} else if($accion=='asignarganadorranking'){
			update_option('tr_ganador_ranking', $usuario, 'no');
			avisoEnDiscord(sprintf(__('El nuevo ganador del ranking es %s', 'twitch-ranking'),$usuario), 'Twitch Ranking');
		} else if($accion=='asignarstreamermes'){
			update_option('tr_streamer_mes', $usuario, 'no');
			avisoEnDiscord(sprintf(__('El nuevo streamer del mes es %s', 'twitch-ranking'),$usuario), 'Twitch Ranking');
		} else if ($accion=='enviarcorreomasivo') {
			require_once('sistemaCorreo.php');
			foreach ($postcanales as $p ){
				$usuario = get_post_meta($p->ID,'usuario',true);
				$user = get_user_by('ID', get_post_meta($p->ID,'tr_wp_user_id',true));
				enviarCorreo($user->user_email,'50');
			}
		} else if ($accion == 'changeConfigValue') {
			$configOption = isset($_GET['option']) ? $_GET['option'] : null;
			if (!empty($configOption)) {
				update_option($configOption, $dato);
			}
		} else if ($accion=='crearjuego') {
			$juego=isset($_GET['juego']) ? $_GET['juego'] : null;
			$url=isset($_GET['url']) ? $_GET['url'] : null;
			$urlimagen=isset($_GET['urlimagen']) ? $_GET['urlimagen'] : null;
			
			if (!$yaRegistrado) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trjuegos (nombre,url,urlimagen) VALUES (%s,%s,%s)", 
				$juego,
				$url,
				$urlimagen
				));
			}
			avisoEnDiscord(sprintf(__('El juego %s se ha añadido al apartado de streamer por juegos por lo que ya podeís poner vuestro perfil si jugaís.', 'twitch-ranking'),$juego), 'ApartadoPrivado');
		}
	}

	$config_fields = array(
		array(
			'sectionName' => 'Configurar el minimo para poder mandar solicitud como streamers. Si se pone a 0 se usará la media de todos los streamers',
			'fields' => array(
				array(
					'title' => 'Mínimo de seguidores',
					'config_name' => 'tr_conf_min_seguidores'
				),
				array(
					'title' => 'Mínimo de viewers',
					'config_name' => 'tr_conf_min_views'
				),
				array(
					'title' => 'Auto aceptar solicitudes',
					'config_name' => 'tr_conf_auto_accept',
					'boolean' => true
				),
				array(
					'title' => 'Página de redirección al mandar la solicitud',
					'config_name' => 'tr_conf_redirection_page'
				)
			),
		),
		array(
			'sectionName' => 'Con esta configuración se saca la información de los streamers',
			'fields' => array(
				array(
					'title' => 'Twitch Client ID',
					'config_name' => 'tr_twitch_client_id'
				),
				array(
					'title' => 'Twitch Client Secret',
					'config_name' => 'tr_twitch_client_secret'
				),
				array(
					'title' => 'Twitch Team ID',
					'config_name' => 'tr_twitch_team_id'
				)
			),
		),
		array(
			'sectionName' => 'Con esta información se puede vincular streamlabs para que salgan avisos cuando votan a un streamer',
			'fields' => array(
				array(
					'title' => 'StreamLabs Client ID',
					'config_name' => 'tr_streamlabs_client_id'
				),
				array(
					'title' => 'StreamLabs Client Secret',
					'config_name' => 'tr_streamLabs_client_secret'
				)
			),
		),
		array(
			'sectionName' => 'La configuración de twitter se usa principalmente para que se haga RT automatico a los streamers y sus comprobaciones',
			'fields' => array(
				array(
					'title' => 'Twitter Oauth Access Token',
					'config_name' => 'tr_twitter_oauth_access_token'
				), 
				array(
					'title' => 'Twitter Oauth Access Token Secret',
					'config_name' => 'tr_twitter_oauth_access_secret'
				), 
				array(
					'title' => 'Twitter Consumer Key',
					'config_name' => 'tr_twitter_consumer_key'
				), 
				array(
					'title' => 'Twitter Consumer Secret',
					'config_name' => 'tr_twitter_consumer_secret'
				)
			),
		),
		array(
			'sectionName' => 'El Discord WebHook y su mensaje son necesarios para avisar a los streamers cuando se añada un streamer, se borre, exista una nueva solicitud..',
			'fields' => array(
				array(
					'title' => 'Discord WebHook',
					'config_name' => 'tr_discord_webhook'
				), 
				array(
					'title' => 'Mensaje de discord para nuevas solicitudes',
					'config_name' => 'tr_discord_mensaje'
				)
			),
		),
		array(
			'sectionName' => 'El Discord Client ID, Discord Client Secret, Discord Guild Id, Discord Role ID son necesarios para que funcione la vinculación con  discord y asi asignar el rango automaticamente.',
			'fields' => array(
				array(
					'title' => 'Discord Client ID',
					'config_name' => 'tr_discord_client_id'
				), 
				array(
					'title' => 'Discord Client Secret',
					'config_name' => 'tr_discord_client_secret'
				), 
				array(
					'title' => 'Discord Access Token (Para el bot)',
					'config_name' => 'tr_discord_accesss_token'
				), 
				array(
					'title' => 'Discord Guild ID',
					'config_name' => 'tr_discord_guild_id'
				), 
				array(
					'title' => 'Discord Role ID',
					'config_name' => 'tr_discord_role_id'
				)
			),
		),
	);
	
	?>
		<div class="container-fluid bg-light">
			<table class="mx-auto">
				<tr>
					<td>
						<div class="wrap">
							<h2><?php _e('Añadir un usuario al ranking','twitch-ranking'); ?></h2>
							<form method="GET" action="" id="addrank">
								<label><?php _e('Usuario:','twitch-ranking'); ?></label>
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="aniadirusuario"/>
								<input type="text" name="usuario" id="usuario"/>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Añadir usuario twitch','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Borrar un usuario del ranking','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<label><?php _e('Usuario:','twitch-ranking'); ?></label>
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="borrarusuario"/>
								<select type='text' name='usuario'>
								<?php
									foreach ($postcanales as $p ){
										$usuario = get_post_meta($p->ID,'usuario',true);
										echo '<option value="'.$usuario.'">'.$usuario.'</option>';
									}
								?>
								</select>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Borrar usuario','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Vaciar votos','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="vaciarvotos"/>
								<p><?php _e('Si vacias los votos no habrá vuelta atras','twitch-ranking'); ?></p>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Vaciar votos','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Sacar un Ganador de viewers','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="sacarganador"/>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Sacar Ganador','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wrap">
							<h2><?php _e('Elegir ganador del ranking','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<label><?php _e('Usuario:','twitch-ranking'); ?></label>
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="asignarganadorranking"/>
								<select type='text' name='usuario'>
								<?php
									echo '<option value="'.get_option('tr_ganador_ranking', 'gzonecomunidad').'">'.get_option('tr_ganador_ranking', 'gzonecomunidad').'</option>';
									foreach ($postcanales as $p ){
										$usuario = get_post_meta($p->ID,'usuario',true);
										echo '<option value="'.$usuario.'">'.$usuario.'</option>';
									}
								?>
								</select>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Asignar ganador','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Elegir streamer del mes','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<label><?php _e('Usuario:','twitch-ranking'); ?></label>
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="asignarstreamermes"/>
								<select type='text' name='usuario'>
								<?php
									echo '<option value="'.get_option('tr_streamer_mes', 'gzonecomunidad').'">'.get_option('tr_streamer_mes', 'gzonecomunidad').'</option>';
									foreach ($postcanales as $p ){
										$usuario = get_post_meta($p->ID,'usuario',true);
										echo '<option value="'.$usuario.'">'.$usuario.'</option>';
									}
								?>
								</select>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Asignar Streamer','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Enviar correo a todos de prueba','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="enviarcorreomasivo"/>
								</select>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Enviar correo','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wrap">
							<h2><?php _e('Añadir Juego','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="twitch_ranking"/>
								<input type="hidden" name="accion" value="crearjuego"/>
								<p><label for="juego"><?php _e('Juego:','twitch-ranking'); ?></label> <input type="text" name="juego" maxlength="50"></input></p>
								<p><label for="url"><?php _e('URL:','twitch-ranking'); ?></label> <input type="text" name="url" maxlength="150"></input></p>
								<p><label for="urlimagen"><?php _e('URL Imagen:','twitch-ranking'); ?></label> <input type="text" name="urlimagen" maxlength="200"></input></p>
								<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Añadir Juego','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
				</tr>
			</table>
			<div class="col-12">
				<?php 
				foreach ($config_fields as $config_section) :
				?>
					<div class="row">
						<div class="col">
							<div class="wrap">
								<h3><?php _e($config_section['sectionName'],'twitch-ranking'); ?></h3>
							</div>
						</div>
					</div>
					<div class="row">
					<?php 
						foreach ($config_section['fields'] as $section_field) :
					?>
						<div class="col">
							<div class="wrap">
								<h2><?php _e($section_field['title'],'twitch-ranking'); ?></h2>
								<form method="GET" action="">
									<input type="hidden" name="page" value="twitch_ranking"/>
									<input type="hidden" name="accion" value="changeConfigValue"/>
									<input type="hidden" name="option" value="<?php echo $section_field['config_name']; ?>"/>
									<?php
									if (!empty($section_field['boolean']) && $section_field['boolean']):
										if (get_option($section_field['config_name'], false)) : ?>
											<input type="checkbox" name="dato" id="dato" value="true" checked></input>
										<?php 
										else: ?>
											<input type="checkbox" name="dato" id="dato" value="true"></input>
										<?php 
										endif;
									else:
									?>
										<input type="text" size="75" name="dato" id="dato" value="<?php echo get_option($section_field['config_name'], '') ?>"></input>
									<?php
									endif;
									?>
									<p><button class="btn btn-secondary mt-1" type="submit"><?php _e('Actualizar','twitch-ranking'); ?></button></p>
								</form>
							</div>
						</div>
					<?php
					endforeach;
					?>
					</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
<?php
}
?>