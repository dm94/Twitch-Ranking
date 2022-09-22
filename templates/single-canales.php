<?php

/*
Template Name: Canal streamer
Template Post Type: canales
*/

get_header();

$streamer=get_the_title();
?>
<?php
	if (empty($streamer)) {
		$streamer = 'dm94dani';
	}
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
					$mensaje=__('Streamer votado','twitch-ranking');
				
				}else{
					$mensaje=__('Este streamer no puede ser votado','twitch-ranking');
				}
			}else{
				$mensaje=__('Solo se puede votar una vez al dia','twitch-ranking');
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
	
	function mostrarAviso($men){
		echo '<SCRIPT>';
		echo 'alert("'.$men.'")';
		echo '</SCRIPT>';
	} 
	
	function get_the_user_ip() {
		$ip = '';
		$headers = array('HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR');
		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				$ip = $_SERVER[$header];
				
				if (strpos($ip, ':') !== false) {
					$ip = inet_pton($ip);
				}
				return $ip;
			}
		}
	}
	
	$idstreamer='';
	$views=0;
	$seguidores=0;
	$imagen='';
	$generarboton=false;
	
	$idstreamer = get_post_meta(get_the_ID(),'usuario',true);
	$imagen = get_post_meta(get_the_ID(),'imagen',true);
	$views = get_post_meta(get_the_ID(),'views',true);
	$seguidores = get_post_meta(get_the_ID(),'seguidores',true);
	$seguidoresantes = get_post_meta(get_the_ID(),'segudiresantes',true);
	$viewsantes = get_post_meta(get_the_ID(),'viewsantes',true);
		
	if(isset($_POST['action'])){
		$action = $_POST['action'];
		
		if ($action == 'votar'){
			if (is_user_logged_in()){
				$cu = wp_get_current_user();
				if(puedeVotar(($cu->display_name))){
					votar($streamer,($cu->display_name),$idstreamer);
				}else{
					$mensaje = __('Solo se puede votar una vez al dia','twitch-ranking');
				}
			
			}else{
				$ip=get_the_user_ip();
				if (puedeVotarIP($ip)){
					votar($streamer,__("No registrado",'twitch-ranking'));
				} else {
					$mensaje=__('Solo se puede votar una vez al dia','twitch-ranking');
				}
				//$mensaje='Necesitas estar logueado para votar <br />';
				//echo "<script>location.href='".wp_login_url()."';</script>";
			}
		}else if($action =='generarboton'){
			$generarboton=true;
		}
	}
	
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script>
    function votando(token) {
		document.getElementById("formvotar").submit();
    }
	function boton(token) {
		document.getElementById("formgenerador").submit();
    }
</script>
<div id="main-content" class="page-card">

			<?php while ( have_posts()) : the_post(); ?>
			
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content row">
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-8">
								<iframe id="streamning" title="Twitch Streaming" src="https://player.twitch.tv/?channel=<?php echo $streamer;?>&parent=<?php echo $_SERVER['HTTP_HOST'];?>" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="100%"></iframe>
							</div>
							<div class="col-lg-4">
								<iframe title="Twitch Chat" src="https://www.twitch.tv/embed/<?php echo $streamer;?>/chat?parent=<?php echo $_SERVER['HTTP_HOST'];?>" id="chat" frameborder="0" scrolling="no" height="378" width="100%"></iframe>
							</div>
						</div>
					</div>
				<div class="col-lg-12">
					<div class="row">
						<div class="col-lg-8">
							<div class="row no-gutters border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
							<div class="col p-3 m-0 d-flex flex-column position-static">
								<h3 class="mb-0"><?php echo $streamer;?></h3>
								<div class="card-text">
									<div class="float-left">
											<p><strong><?php _e('Visitas','twitch-ranking'); ?></strong></p>
											<p><strong><?php _e('Seguidores','twitch-ranking'); ?></strong></p>
											<p><strong><?php _e('Visitas el dia 1','twitch-ranking'); ?></strong></p>
											<p><strong><?php _e('Seguidores el dia 1','twitch-ranking'); ?></strong></p>
									</div>
									<div class="float-right">
										<p class="text-right"><?PHP echo $views; ?></p>
										<p class="text-right"><?PHP echo $seguidores; ?></p>
										<p class="text-right"><?PHP echo $viewsantes; ?></p>
										<p class="text-right"><?PHP echo $seguidoresantes; ?></p>
									</div>
									<div class="clearfix"></div>
									<div class="mx-auto text-center">
										<a class='btn btn-outline-danger' target='_blank' rel="noopener noreferrer" href='https://www.twitch.tv/<?php echo $streamer;?>'><?php _e('Ir a su canal','twitch-ranking'); ?></a>
									</div>
								</div>
							</div>
							<div class="col-auto d-none d-lg-block">
								<a href="https://www.twitch.tv/<?php echo $streamer;?>">
									<img data-no-lazy='1' class="img-fluid m-0" src="<?php echo $imagen;?>" alt="<?php echo $imagen;?>"/>
								</a>
							</div>
						</div>
						</div>
						<div class="col-lg-4">
							<div class="card">
								<div class="card-header text-left">
									<div class="huge"><?PHP echo sprintf(__('Vota a %s', 'twitch-ranking'),$streamer);?></div>
								</div>
								<div class="card-body text-center">
									<?php
										if(empty($mensaje)){
									?>
										<div id='votaciones' class="mx-auto"><form id="formvotar" method='POST' action="">
											<input type="hidden" name="action" value="votar" />
											
											<button
												class="g-recaptcha btn btn-danger btn-block"
												data-sitekey="6LdtFzAUAAAAAIXoYfvEbMpjtDNLVM_3Eyfcx-By"
												data-callback="votando">
												<?php _e('Votar','twitch-ranking'); ?>
											</button>
											
											
										</form></div>
									<?php
										}else{
											echo $mensaje;
										}
									?>
								</div> 
							</div>
						</div>
					</div>
				</div>
				<?php
				if(is_user_logged_in()){
					if (strcasecmp(get_current_user_id(),get_post_meta( get_the_ID(), 'tr_wp_user_id', truE)) == 0){
						if($generarboton){
						?>
						<div class="col-lg-12">
							<div class="row">
								<div class="col-lg-6 col-md-6 col-xs-12">
									<div class="card">
										<div class="card-header text-left">
											<div class="huge"><?php _e('Banner','twitch-ranking'); ?></div>
										</div>
										<div class="card-footer text-center">
											<a href='<?php echo get_permalink();?>'><img src='http://comunidadgzone.es/externo/imagen.php?texto=<?php echo $streamer;?>'></a>
										</div> 
									</div>
								</div>
							</div>
						</div>
						<?php
						} else {
						?>
						<div class="col-lg-6">
							<div class="text-center mx-auto">
								<form id="formgenerador" method='POST' action="">
									<input type="hidden" name="action" value="generarboton" />
									<button
										class="g-recaptcha"
										data-sitekey="6LdtFzAUAAAAAIXoYfvEbMpjtDNLVM_3Eyfcx-By"
										data-callback="boton">
										<?php _e('Generar imagen para banner','twitch-ranking'); ?>
									</button>
								</form>
							</div> 
						</div>
						<?php
						}
						?>
						<div class="col-lg-6">
							<div class="card">
								<div class="card-header">
									<div class="huge"><?php _e('Votaciones','twitch-ranking'); ?></div>
								</div>
								<div class="card-footer">
									<div class="float-left">
									<p><strong><?php _e('Usuario','twitch-ranking'); ?></strong></p>
									<?php
									$streamers = $wpdb->get_results($wpdb->prepare("select votante, count(IdVotacion) vota from {$wpdb->prefix}trvotaciones where Usuario=%s group by votante", $streamer));
									foreach ($streamers as $row1){
										$votante=$row1->votante;
									?>
										<p><strong><?php echo $votante;?></strong></p>
									<?php } ?>
									</div>
									<div class="float-right">
									<p class="text-right"><?php _e('Nº Votos','twitch-ranking'); ?></p>
									<?php
									foreach ($streamers as $row1){
										$votaciones=$row1->vota;
										?>
										<p><strong><?php echo $votaciones;?></strong></p>
									<?php } ?>
									</div>
									<div class="clearfix"></div>
								</div> 
							</div>
						</div>
					<?php
					}
				}
				?>
				</div> <!-- .entry-content -->
				</article> <!-- .et_pb_post -->
			<?php endwhile; ?>

</div> <!-- #main-content -->

<?php get_footer(); ?>