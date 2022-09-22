<?php
function tr_show_aplications_admin_section() {
	/*
		Types of applications
		1 - Not eligible
		2 - Pending review
		3 - Rejected
		4 - Accepted
		5 - Pending review in Mixer
		10 - Mail sent Twitch
		11 - Mail sent Mixer
		12 - You have requested to enter the ranking
	*/
	global $wpdb;
	$accion = isset($_GET['accion']) ? $_GET['accion'] : null;
	$idsolicitud = isset($_GET['idsoli']) ? $_GET['idsoli'] : null;
	
	if ($accion=='aceptarusuario'){
		require 'sistemaCorreo.php';
		$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."trsolicitutwitch set estado=4 where idsolicitud=%s",
		$idsolicitud
		));
		
		$sel = $wpdb->get_row('select nickstreamer,email,userid, imagen, wp_user_id from '.$wpdb->prefix.'trsolicitutwitch where idsolicitud='.$idsolicitud, ARRAY_A);
		$nickstreamer=$sel['nickstreamer'];
		$correo = $sel['email'];
		$userid = $sel['userid'];
		$imagen = $sel['imagen'];
		$wp_user_id = $sel['wp_user_id'];
		
		$new_post = array(
			'post_title' => $nickstreamer,
			'post_status' => 'publish',
			'post_type' => 'canales',
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($new_post);
		avisoEnDiscord(sprintf(__('Se ha añadido el streamer %s al ranking: %s', 'twitch-ranking'), $nickstreamer, get_permalink($post_id)), 'Twitch Ranking');
		require 'utilidades.php';
		tr_generate_featured_image($imagen,$post_id);
		update_post_meta($post_id, 'twitchID', $userid);
		update_post_meta($post_id, 'imagen', $imagen);
		if ($wp_user_id == null || $wp_user_id == 0) {
			$user = get_user_by('email', $correo );
			$wp_user_id = $user->ID;
		}
		update_post_meta($post_id, 'tr_wp_user_id', $wp_user_id);
		update_post_meta($post_id, 'usuario', $nickstreamer);
	} else if ($accion=='rechazarusuario'){
		require 'sistemaCorreo.php';
		$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."trsolicitutwitch set estado=3 where idsolicitud=%d",
		$idsolicitud
		));
		
		$sel = $wpdb->get_row('select email from '.$wpdb->prefix.'trsolicitutwitch where idsolicitud='.$idsolicitud, ARRAY_A);
		$correo = $sel['email'];
		
		enviarCorreo($correo,22);
	} else if($accion=='borrarsolicitud'){
		$wpdb->query($wpdb->prepare("delete from ".$wpdb->prefix."trsolicitutwitch where idsolicitud=%d",
		$idsolicitud
		));
		
	} else if ($accion == 'enviarcorreo' || $accion == 'correomixer') {
		require 'sistemaCorreo.php';
		$sel = $wpdb->get_row('select nickstreamer,email from '.$wpdb->prefix.'trsolicitutwitch where idsolicitud='.$idsolicitud, ARRAY_A);
		$nickstreamer=$sel['nickstreamer'];
		$correo = $sel['email'];
		
		if ($accion == 'correomixer') {
			$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."trsolicitutwitch set estado=11 where idsolicitud=%d",
			$idsolicitud
			));
		} else {
			$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."trsolicitutwitch set estado=10 where idsolicitud=%d",
			$idsolicitud
			));
		}
		
		enviarCorreo($correo,21);
	}
	
	$solicitudes = $wpdb->get_results('select nickstreamer,Fecha,estado,idsolicitud,email from '.$wpdb->prefix.'trsolicitutwitch where not estado = 1 order by fecha DESC LIMIT 20');
?>
	<div class="container-fluid bg-light">
		<h2 class="text-center"><?php _e('Listado Solicitudes','twitch-ranking'); ?></h2>
		<table class="table table-striped mx-auto text-center">
			<tr>
				<th scope="col"><?php _e('Usuario','twitch-ranking'); ?></th>
				<th scope="col"><?php _e('Fecha solicitud','twitch-ranking'); ?></th>
				<th scope="col"><?php _e('Estado','twitch-ranking'); ?></th>
				<th scope="col"><?php _e('Acciones','twitch-ranking'); ?></th>
		
		<?php if (current_user_can('administrator')) : ?>
				<th scope="col"><?php _e('Email','twitch-ranking'); ?></th>
				<th scope="col"><?php _e('A Favor','twitch-ranking'); ?></th>
				<th scope="col"<?php _e('En Contra','twitch-ranking'); ?>></th>
			</tr> 
			<?php endif; ?>
<?php
	foreach ($solicitudes as $row)
	{
		$usuario=$row->nickstreamer;
		$fecha=$row->Fecha;
		$estado=$row->estado;
		$idsoli=$row->idsolicitud;
		$email=$row->email;
	?>
		<tr>
			<td><a href="https://www.twitch.tv/<?php echo $usuario;?>"><?php echo $usuario;?></a></td>
			<td><?php echo $fecha;?></td>
		<?php
		if($estado == 1){
		?>
			<td class="text-danger"><?php _e('No cumple requisitos','twitch-ranking'); ?></td>
			<td colspan="4"></td>
		<?php
		} else if($estado == 2 || $estado == 5 || $estado == 10 || $estado == 11){
				$votaciones = $wpdb->get_results('select tipovoto,streamervotante from '.$wpdb->prefix.'trvotosstwitchsoli where idsolicitud='.$idsoli);
				$votosafavor=0;
				$votosencontra=0;
				$votantesAFavor = array();
				$votantesEnContra = array();
				foreach ($votaciones as $row2){
					$tipovoto=$row2->tipovoto;
					if($tipovoto==1){
						$votosafavor++;
						array_push($votantesAFavor, ($row2->streamervotante));
					}else if($tipovoto==2){
						$votosencontra++;
						array_push($votantesEnContra, ($row2->streamervotante));
					}else if($tipovoto==3){
						$votosafavor = $votosafavor + 4;
						array_push($votantesAFavor, ($row2->streamervotante));
					}else if($tipovoto==4){
						$votosencontra=$votosencontra+4;
						array_push($votantesEnContra, ($row2->streamervotante));
					}
				}
			if ($estado == 10 || $estado == 11) {
		?>
				<td class="text-warning"><?php _e('Correo enviado','twitch-ranking'); ?></td>
		<?php
			} else {
		?>
				<td><?php echo (__('A favor','twitch-ranking').' '.$votosafavor.' - '.$votosencontra.' '.__('En contra','twitch-ranking')); ?></td>
		<?php
			}
		?>
			<td>
				<form method="GET" action="">
					<input type="hidden" name="page" value="tr_streamers_list"/>
					<input type="hidden" name="idsoli" value="<?php echo $idsoli;?>"/>
					<select class="form-control" type="text" name="accion">
						<option value="aceptarusuario"><?php _e('Aceptar Twitch','twitch-ranking'); ?></option>
						<option value="enviarcorreo"><?php _e('Enviar Correo','twitch-ranking'); ?></option>
						<option value="rechazarusuario"><?php _e('Rechazar Streamer','twitch-ranking'); ?></option>
						<option value="borrarsolicitud"><?php _e('Borrar solicitud','twitch-ranking'); ?></option>
					</select>
					<br>
					<button class="btn btn-primary mt-1" type="submit"><?php _e('Realizar acción','twitch-ranking'); ?></button>
				</form>
			</td>
			<?php
			if(current_user_can('administrator')) { 
			?>
				<td><?php echo $email;?></td>
				<td>
			<?php
				foreach($votantesAFavor as $key => $value)
				{
					echo getTwitchUser($value).' - ';
				}
			?>
				</td>
				<td>
			<?php
				foreach($votantesEnContra as $key => $value)
				{
					echo getTwitchUser($value).' - ';
				}
			?>
				</td>
		<?php
			}
		}else if($estado==3){
		?>
			<td colspan="6" class="text-danger"><?php _e('Rechazado','twitch-ranking'); ?></td>
		<?php
		}else if($estado==4){
		?>
			<td colspan="6" class="text-success"><?php _e('Aceptado','twitch-ranking'); ?></td>
		<?php
		}else if($estado==12){
			?>
			<td colspan="6" class="text-success"><?php _e('Para entrar en ranking','twitch-ranking'); ?></td>
		<?php
		}else{
			?>
			<td colspan="6" class="text-info"><?php _e('Sin estado','twitch-ranking'); ?></td>
			<?php
		}
		?>
		</tr>
		<?php
	}
?>
		</table>
	</div>
<?php
}


?>