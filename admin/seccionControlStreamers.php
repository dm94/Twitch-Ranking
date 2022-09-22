<?php
function mostrarTodoControlStreamers(){
	global $wpdb;
	
	$activarcomprobacion=false;
	$comprobarteam=false;
	$accion=isset($_GET['accion']) ? $_GET['accion'] : null;
	$usuario=isset($_GET['usuario']) ? $_GET['usuario'] : null;
	$idstreamer=isset($_GET['idstreamer']) ? $_GET['idstreamer'] : null;
	$motivo=isset($_GET['motivo']) ? $_GET['motivo'] : null;
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	
	if ($accion != null) {
		if ($accion == 'aniadirstrike') {
			$date = date('Y-m-d', time());
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trkarma(IDStreamer,tipo,fechaCreacion,motivo) VALUES (%d,%d,%s,%d)", 
			$idstreamer,
			1,
			$date,
			$motivo
			));
			require_once('sistemaCorreo.php');
			foreach ($postcanales as $p ){
				if (strcasecmp(get_post_meta($p->ID,'twitchID',true),$idstreamer) == 0){
					$user = get_user_by('ID', get_post_meta($p->ID,'tr_wp_user_id',true));
					enviarCorreo($user->user_email,$motivo);
				}
			}
		} else if ($accion == 'anidiarparticipacion') {
			$date = date('Y-m-d', time());
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trkarma(IDStreamer,tipo,fechaCreacion,motivo) VALUES (%d,%d,%s,%d)", 
			$idstreamer,
			2,
			$date,
			$motivo
			));
			
			require_once('sistemaCorreo.php');
			foreach ($postcanales as $p ){
				if (strcasecmp(get_post_meta($p->ID,'twitchID',true),$idstreamer) == 0){
					$user = get_user_by('ID', get_post_meta($p->ID,'tr_wp_user_id',true));
					enviarCorreo($user->user_email,$motivo);
				}
			}
		} else if ($accion == 'comprobarTweets') {
			do_action('trbuscarrts');
		} else if ($accion == 'comprobarmuros') {
			require 'twitch_utils.php';
			$activarcomprobacion = true;
		} else if ($accion == 'actualizarstreamers') {
			wp_schedule_single_event(time()+600,'trupdatestreamersstats');
		}
	}
	$minViews = get_option('tr_min_views', '1000');
	$minSeguidores = get_option('tr_min_seguidores', '500');
	
	if (current_user_can('administrator')) {
?>

		<div class="container-fluid bg-light">
			<h2 class="text-center"><?php _e('Control de streamers','twitch-ranking'); ?></h2>
			<table class="mx-auto">
				<tr>
					<td>
						<div class="wrap">
							<h2 class="mb-1"><?php _e('Comprobar Tweets','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="tr_options_control"/>
								<input type="hidden" name="accion" value="comprobarTweets"/>
								<p><button class="btn btn-primary" type="submit"><?php _e('Comprobar','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2 class="mb-1"><?php _e('Comprobar Grupo','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="tr_options_control"/>
								<input type="hidden" name="accion" value="comprobarmuros"/>
								<p><button class="btn btn-primary" type="submit"><?php _e('Comprobar grupo','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2 class="mb-1"><?php _e('Actualizar streamers','twitch-ranking'); ?></h2>
							<form method="GET" action="">
								<input type="hidden" name="page" value="tr_options_control"/>
								<input type="hidden" name="accion" value="actualizarstreamers"/>
								<p><button class="btn btn-primary" type="submit"><?php _e('Actualizar streamers Mes','twitch-ranking'); ?></button></p>
							</form>
						</div>
					</td>
				</tr>
				<tr class="text-center">
					<td>
						<div class="wrap">
							<h2><?php _e('Total de streamers','twitch-ranking'); ?></h2>
							<h3><?php echo count($postcanales);?></h3>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Mínimo de seguidores ideal','twitch-ranking'); ?></h2>
							<h3><?php echo $minSeguidores;?></h3>
						</div>
					</td>
					<td>
						<div class="wrap">
							<h2><?php _e('Mínimo de views ideal','twitch-ranking'); ?></h2>
							<h3><?php echo $minViews;?></h3>
						</div>
					</td>
				</tr>
			</table>
			<table class="mt-2 table table-striped mx-auto text-center">
				<tr>
					<th scope="col"><?php _e('Streamer','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Views Antes','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Seguidores Antes','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Views','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Seguidores','twitch-ranking'); ?></th>
					<?php if ($activarcomprobacion) { ?><th scope="col"><?php _e('En Team','twitch-ranking'); ?></th><?php } ?>
					<th scope="col"><?php _e('Strikes','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Participaciones','twitch-ranking'); ?></th>
					<th scope="col"><?php _e('Karma','twitch-ranking'); ?></th>
					<th scope="col" colspan="2"><?php _e('Acciones','twitch-ranking'); ?></th>
				</tr>
<?php
				sort($postcanales);
				foreach ($postcanales as $p ) {
					$link = get_permalink($p->ID);
					$usuario = get_post_meta($p->ID,'usuario',true);
					$views = intval(get_post_meta($p->ID,'views',true));
					$seguidores = intval(get_post_meta($p->ID,'seguidores',true));
					$seguidoresantes = intval(get_post_meta($p->ID,'segudiresantes',true));
					$viewsantes = intval(get_post_meta($p->ID,'viewsantes',true));
					$userid = get_post_meta($p->ID,'twitchID',true);
					$strikes = 0;
					$participacion = 0;
					
					if ($activarcomprobacion) {
						$teamvalido = tr_is_team_streamer($userid);
					} else {
						$teamvalido = false;
					}
					
					$time = strtotime(date("d-m-Y H:i:00",time()));
					$participaciones = $wpdb->get_results('select * from '.$wpdb->prefix.'trkarma where IDStreamer='.$userid.' and tipo=2 order by fechaCreacion');
					foreach ($participaciones as $row2){
						$fechaCreacion1 = $row2->fechaCreacion;
						$dateini1 = date($fechaCreacion1);
						$mod_date1 = strtotime($dateini1."+ 60 days");
						if ($time < $mod_date1) {
							$participacion++;
						}
					}
					
					$strikeList = $wpdb->get_results('select * from '.$wpdb->prefix.'trkarma where IDStreamer='.$userid.' and tipo=1 order by fechaCreacion');
					foreach ($strikeList as $row3){
						$fechaCreacion = $row3->fechaCreacion;
						$dateini = date($fechaCreacion);
						$mod_date = strtotime($dateini."+ 120 days");
						if ($time < $mod_date) {
							$strikes++;
						}
					}
					
					$karma = ($participacion)-($strikes*5);
					
					$diferenciaseguidores = ($seguidores-$seguidoresantes);
					$diferenciaviews = ($views-$viewsantes);
					if ($diferenciaseguidores > 0) {
						$diferenciaseguidores = '<span class="text-success">'.$diferenciaseguidores.'</span>';
					} else {
						$diferenciaseguidores = '<span class="text-danger">'.$diferenciaseguidores.'</span>';
					}
					
					if ($diferenciaviews > 300) {
						$diferenciaviews = '<span class="text-success">'.$diferenciaviews.'</span>';
					} else {
						$diferenciaviews = '<span class="text-danger">'.$diferenciaviews.'</span>';
					}
					?>
					<tr>
						<td><a href="<?php echo $link;?>"><?php echo $usuario;?></a></td>
						<td><?php echo $viewsantes;?></td>
						<td><?php echo $seguidoresantes;?></td>
						<td><?php echo $views.'('.$diferenciaviews;?>)</td>
						<td><?php echo $seguidores.'('.$diferenciaseguidores;?>)</td>
						<?php
						if ($activarcomprobacion) {
							if ($teamvalido) {
						?>
							<td><span class="fa fa-check text-success">✓</span></td>
						<?php
							} else {
						?>
							<td><span class="fa fa-times text-danger">X</span></td>
						<?php
							}
						}
						?>
						<td><?php echo $strikes;?></td>
						<td><?php echo $participacion;?></td>
						<td><?php echo $karma;?></td>
						<td>
							<form method="GET" action="">
								<input type="hidden" name="page" value="tr_options_control"/>
								<input type="hidden" name="idstreamer" value="<?php echo $userid;?>"/>
								<input type="hidden" name="accion" value="aniadirstrike"/>
								<select class="form-control" name="motivo">
									<option value="1"><?php _e('Minimo mensual','twitch-ranking'); ?></option>
									<option value="2"><?php _e('No tener logo o link','twitch-ranking'); ?></option>
									<option value="3"><?php _e('No Participar','twitch-ranking'); ?></option>
									<option value="4"><?php _e('Generico','twitch-ranking'); ?></option>
								</select>
								<button class="btn btn-danger mt-1" type="submit"><?php _e('Añadir Strike','twitch-ranking'); ?></button>
							</form>
						</td>
						<td>
							<form method="GET" action="">
								<input type="hidden" name="page" value="tr_options_control"/>
								<input type="hidden" name="idstreamer" value="<?php echo $userid;?>"/>
								<input type="hidden" name="accion" value="anidiarparticipacion"/>
								<select class="form-control" name="motivo">
									<option value="13"><?php _e('Crear evento','twitch-ranking'); ?></option>
									<option value="12"><?php _e('Participar','twitch-ranking'); ?></option>
									<option value="11"><?php _e('Generico','twitch-ranking'); ?></option>
								</select>
								<button class="btn btn-success mt-1" type="submit"><?php _e('Añadir Participación','twitch-ranking'); ?></button>
							</form>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
<?php
	}
}


?>