<?php

/*
Template Name: Ranking Streamers
*/

get_header();

?>
<?php
	$mensaje="";
	
	function puedeVotar($votante){
		global $wpdb;
		$puede=true;
		$votaciones = $wpdb->get_results('select fecha from '.$wpdb->prefix.'trvotaciones where votante='.$votante);
		
		foreach ($votaciones as $row){
			$segundos=strtotime('now')-strtotime($row->fecha);
			
			$diferencia_dias=intval($segundos/60/60/24);
			
			if($diferencia_dias<1){
				$puede=false;
			}
		}
		return $puede;
	}
	
	function puedeVotarIP($ip){
		if (strpos($ip, ':') !== false) {
			return false;
		}
		
		global $wpdb;
		$puede=true;
		
		$votaciones = $wpdb->get_results('select fecha from '.$wpdb->prefix.'trvotaciones where ip like "'.$ip.'"');
		
		foreach ($votaciones as $row){
			$segundos=strtotime('now')-strtotime($row->fecha);
			
			$diferencia_dias=intval($segundos/60/60/24);
			
			if($diferencia_dias<1){
				$puede=false;
			}
		}
		return $puede;
	}
	
	function votar($streamervotado,$votante){
		global $wpdb;
		//if (is_user_logged_in()){
			$ip=get_the_user_ip();
			if(puedeVotarIP($ip)){
				if($streamervotado!=''){
					$fecha =strtotime('now');
					
					$mysqldate = date('Y-m-d', $fecha );
					
					$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trvotaciones (Usuario,Votante,Fecha,ip) values(%s,%s,%s,%s)", 
					$streamervotado,
					$votante,
					$mysqldate,
					$ip
					));
					mandarAvisoStreamlabs($streamervotado,$votante);
					$mensaje = __('Streamer votado','twitch-ranking');
				
				}else{
					$mensaje = __('Este streamer no puede ser votado','twitch-ranking');
				}
			}else{
				$mensaje = __('Solo se puede votar una vez al dia','twitch-ranking');
			}
		/*}else{
			$mensaje='Tienes que estar registrado para poder votar';
			echo "<script>location.href='".wp_login_url()."';</script>";
		}*/

		return $streamervotado;
	}
	
	function mandarAvisoStreamlabs($usuarioStreamlabs,$usuarioVotante) {
		if (is_user_logged_in()){
			$access_token = '';
			$type = 2;
			$message = 'ComunidadGzone te ha votado';
			$direccionimage = '';
			$direccionsonido = '';
			$durationtype = 5;
			$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
			foreach ($postcanales as $p ){
				if (strcasecmp(get_post_meta($p->ID,'usuario',true),$usuarioStreamlabs) == 0){
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
	}
	

	function get_the_user_ip() {
		$ip = '';
		$headers = array('HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR');
		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				$ip = $_SERVER[$header];
				return $ip;
			}
		}
	}
	
	if(isset($_POST['action'])){
		$action = $_POST['action'];
		$streamervotado=isset($_POST['streamervotado']) ? $_POST['streamervotado'] : null;
		
		if ($action == "votar"){
			if (is_user_logged_in()){
				$cu = wp_get_current_user();
				if(puedeVotar(($cu->display_name))){
					votar($streamervotado,($cu->display_name));
				}else{
					$mensaje = __('Solo se puede votar una vez al dia','twitch-ranking');
				}
			
			}else{
				$ip=get_the_user_ip();
				if (puedeVotarIP($ip)){
					votar($streamervotado,"No registrado");
				} else {
					$mensaje = __('Solo se puede votar una vez al dia','twitch-ranking');
				}
				//$mensaje='Necesitas estar logueado para votar <br />';
				//echo "<script>location.href='".wp_login_url()."';</script>";
			}
		}
	}
	
	function finaltemporada() {
		$datetime1 = new DateTime("now");
		$datetime2 = new DateTime("now");
		$datetime2->modify('+1 month');
		$datetime2->setDate($datetime2->format('Y'), $datetime2->format('m'), 1);
		$interval = $datetime1->diff($datetime2);
		$cuenta_atras = $interval->format('%a');

		if ($cuenta_atras <= 0) {
			$cuenta_atras = 0;
		}

		return $cuenta_atras;
	}

?>
<?php
	global $wpdb;
	$streamers = $wpdb->get_results('select usuario, count(IdVotacion) vota from '.$wpdb->prefix.'trvotaciones group by usuario order by vota DESC');
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	$col = 0;
	$votostotales = 0;
	
	foreach ($postcanales as $p ){
		$votos = 0;
		foreach ($streamers as $row){
			if (strcasecmp(get_post_meta($p->ID,'usuario',true),$row->usuario) == 0) {
				$votos = $row->vota;
			}
		}
		
		$client_data[$col] = array(
			'votaciones'	=>	$votos,
			'usuario'		=>	get_post_meta($p->ID,'usuario',true),
			'logo'	=>	get_post_meta($p->ID,'imagen',true),
			'enlace'	=>	get_permalink($p->ID),
			'online'	=>	get_post_meta($p->ID,'online',true)
		);
		$votostotales = $votostotales + $votos;
		$col++;
	}
	arsort($client_data);
?>
<div id="main-content">

			<?php while ( have_posts()) : the_post(); ?>
			
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
					<?php
						the_content();
					?>	
						<div id="page-wrapper">
							<div class="col-lg-12">
								<div class="row">
									<!-- Tiempo Temporada -->
									<div class="col-lg-6">
										<div class="huge"><?php _e('Final de temporada en:','twitch-ranking'); ?></div>
										<?php
											$tiempo = finaltemporada();
											$inicio = 30; //30 dias
											$porcentaje=($tiempo/$inicio)*100;
											$porcentaje=100-$porcentaje;
										?>
										<div class="progress">
											<div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="min-width: 2em; width: <?PHP echo $porcentaje ?>%;" aria-valuenow="<?PHP echo $porcentaje ?>" aria-valuemin="0" aria-valuemax="100">
												<?PHP echo $tiempo," dias"; ?>
											</div>
										</div>
									</div>
									<!-- Donaciones -->
									<div class="col-lg-6">
										<div class="card m-2">
											<div class="card-header">
												<p class="text-center"><?php _e('Dona a la comunidad y ayuda a mejorarla','twitch-ranking'); ?></p>
												<a class="btn btn-block btn-danger mx-auto" href="https://www.paypal.me/dm94dani/5" rel="noopener noreferrer" target="_blank"><?php _e('Donar','twitch-ranking'); ?></a>
											</div> 
										</div>
									</div>
								</div>				
							</div>
							<div class="col-lg-12">
								<div class="row">
							<?php
							$numero = 1;
							foreach ($client_data as $streamer){
								if ($streamer['votaciones'] != 0) {
									if ($numero==1 || $numero==2 || $numero==3) {
							?>
										<div class="col-lg-12 mb-2">
											<div class="card border border-danger">
												<div class="card-body">
													<div class="row">
														<div class="col-sm-2">
															<?php 
																if($numero == 1) {
																	echo '<p class="text-center align-self-center"><i class="fa fa-trophy fa-4x" style="color:#ffbf00">'.$numero.'</i></p>';
																} else if($numero == 2) {
																	echo '<p class="text-center align-self-center"><i class="fa fa-trophy fa-4x">'.$numero.'</i></p>';
																} else if($numero == 3) {
																	echo '<p class="text-center align-self-center"><i class="fa fa-trophy fa-4x" style="color:#cd7f32">'.$numero.'</i></p>';
																} else {
																	echo '<p class="text-center align-self-center"><i class="fa fa-trophy">'.$numero.'</i></p>';
																}
															?>
														</div>
														<div class="col-sm-2">
															<span title=<?PHP echo '"' .$streamer['usuario'] .'"'?>><?PHP echo "<a href='".$streamer['enlace']."'> <img data-no-lazy='1' class='img-fluid mb-0 rounded-circle' alt='Twitch Image ".$streamer['usuario']."' src='".$streamer['logo'] ."' width='100%'></img></a>" ?></span>
														</div>
														<div class="col-sm-6 text-center">
															<div>
																<?PHP echo "<a href='".$streamer['enlace']."' class='text-capitalize'> ".$streamer['usuario']."</a>" ?>
																<?php
																	if($streamer['online'] == 1) {
																		echo '<div class="small meta online">Online <i class="fa fa-circle text-danger"></i></div>';
																	} else {
																		echo '<div class="small meta offline">Offline <i class="fa fa-circle"></i></div>';
																	}
																?>
															</div>
															<div class="stats-fat-bar">
																<div class="stats-fat-value"><?php _e('Votos totales:','twitch-ranking'); ?> <?PHP echo $streamer['votaciones'];?></div>
																<div class="progress progress-normal">
																	<div class="progress-bar bg-danger" style="width:<?PHP echo ($streamer['votaciones'] / $votostotales) * 100 ;?>%"></div>
																</div>
															</div>
														</div>
														<div class="col-sm-2">
															<?php 
																if(empty($mensaje)){
															?>
																<div id='votaciones'>
																	<form id="formvotar-<?php echo $streamer['usuario'];?>" method='POST' action="">
																		<input type="hidden" name="action" value="votar" />
																		<input type="hidden" name="streamervotado" value="<?php echo $streamer['usuario'];?>"/>
																		<input class="btn btn-success btn-lg btn-block" type="submit" value="<?php _e('Votar','twitch-ranking'); ?>">
																	</form>
																</div>
																<?php
																} else {
																	echo $mensaje;
																}
															?>
															<a class="btn btn-success btn-lg btn-block" href="<?php echo get_site_url()."/canales/".$streamer['usuario'];?>" value="Perfil"><?php _e('Perfil','twitch-ranking'); ?></a>
														</div>
													</div>
												</div> 
											</div>
										</div>
							<?php
									} else {
							?>
								<div class="col-sm-6 mt-1">
									<div class="card">
										<div class="card-body">
											<div class="row">
												<div class="col-sm-4">
													<span title=<?PHP echo '"' .$streamer['usuario'] .'"'?>><?PHP echo "<a href='".$streamer['enlace']."'> <img class='img-fluid mb-0 rounded-circle' data-no-lazy='1' alt='Twitch Image ".$streamer['usuario']."' src='".$streamer['logo'] ."'></img></a>" ?></span>
												</div>
												<div class="col-sm-8">
													<p class="text-center"><i class="fa fa-trophy fa-2x"><?PHP echo $numero;?></i> <span class="font-weight-bold text-capitalize h4"><?php echo $streamer['usuario'];?></span></p>
													<p class="text-center"> Votos totales: <?PHP echo $streamer['votaciones'];?> </p> 
													<?php
														if ($streamer['online'] == 1) {
															echo '<div class="offline">Online <i class="fa fa-circle" text-danger></i></div>';
														} else {
															echo '<div class="offline">Offline <i class="fa fa-circle"></i></div>';
														}
														?>
													<div class="row">
														<div class="col-sm-8 align-middle">
														<?php 
															if(empty($mensaje)){
														?>
															<div id='votaciones'>
																<form id="formvotar<?php echo $streamer['usuario'];?>" method='POST' action="">
																	<input type="hidden" name="action" value="votar" />
																	<input type="hidden" name="streamervotado" value="<?php echo $streamer['usuario'];?>"/>
																	<input class="btn btn-success btn-lg btn-block" type="submit" value="<?php _e('Votar','twitch-ranking'); ?>">
																</form>
															</div>
															
															<?php
															} else {
																echo $mensaje;
															}
														?>
														</div>
														<div class="col-sm-3">
															<a class="btn btn-success btn-lg" value="Perfil" href="<?php echo $streamer['enlace'];?>"><?php _e('Perfil','twitch-ranking'); ?></a>
														</div>
													</div>
												</div>
											</div>
										</div> 
									</div>
								</div>
							<?php
									}
									$numero++;
								}
							}
							?>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="row">
							<?php
							foreach ($client_data as $streamer){
								if ($streamer['votaciones'] == 0) {
							?>
									<div class="col-sm-4 mt-1">
										<?php echo do_shortcode('[streamer nombre="'.$streamer['usuario'].'"]'); ?>
									</div>
							<?php
									}
								}
							?>
								</div>
							</div>
					</div> <!-- .entry-content -->
				</article> <!-- .et_pb_post -->
			<?php endwhile; ?>

</div>
<?php get_footer(); ?>