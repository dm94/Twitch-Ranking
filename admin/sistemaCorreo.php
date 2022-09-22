<?php
	function enviarCorreo($email,$tipo) {
		$mensaje = '<html><body><table>';
		
		if($tipo == "1" || $tipo == "2" || $tipo == "3" || $tipo == "4") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Has recibido un Strike</h2></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Puedes consultar el.motivo en el apartado privado</p></td></tr>';
		} else if ($tipo == "11" || $tipo == "12" || $tipo == "13") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Has recibido una colaboración</h2></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Puedes consultar el.motivo en el apartado privado</p></td></tr>';
		} else if ($tipo == "21") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Has sido aceptad@ en nuestra comunidad</h2></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Tras unos días los streamers de la comunidad han votado lo suficiente para darte el visto bueno.</p></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Una vez te llegue la invitación para unirte al grupo de twitch/mixer y la aceptes podrás entrar a nuestro <a href="https://comunidadgzone.es/streamers/informacion-streamings/">apartado privado</a> (Hay un boton en la web para entrar que pone solo streamers) y también estarás unido al <a href="https://comunidadgzone.es/streamers/">ranking de streamers</a></p></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>En el apartado privado podrás votar las solicitudes de los nuevos streamers, crear eventos, ver información útil y comprobar tu karma (También te pone lo que es)</p></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Y gracias al ranking será más fácil promocionarte al igual que podrás ganar premios si quedas en la primera posición</p></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Si tienes alguna duda puedes responder a este mensaje o hablarme por discord -►Dm94Dani◄-#6385</p></td></tr>';
		} else if ($tipo == "22") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Solicitud rechazada</h2></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>Tras unos días los streamers de la comunidad han votado y se ha decidido que no puedes entrar en nuestra comunidad. Prueba a intentarlo dentro de un tiempo para ver si tienes suerte. Gracias por todo</p></td></tr>';
		} else if ($tipo == "23") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Has sido expulsad@ de la comunidad</h2></td></tr>';
		} else if ($tipo == "50") {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Nuevo aviso en discord</h2></td></tr>';
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><p>'.__('Se pide a todos los streamers que miren el apartado de discord privado para hablar sobre un tema en concreto', 'twitch-ranking').'</p></td></tr>';
		} else {
			$mensaje = $mensaje . '<tr><td style="text-align: center;"><h2>Nuevo aviso en la web</h2></td></tr>';
		}
		$mensaje = $mensaje . '</table></body></html>';
		enviarMensajeCorreo($email,$mensaje);
	}
	
	function enviarMensajeCorreo($email, $mensaje) {
		$asunto = 'Comunidad Gzone - Nuevo aviso';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		
		wp_mail($email, $asunto, $mensaje, $headers);
	}
?>