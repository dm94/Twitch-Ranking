<?php 
	function tr_is_team_streamer($userid){
		$esStreamer = true;
		
		$url='https://api.twitch.tv/kraken/channels/'.$userid.'/teams';
		$content = file_get_contents_utf8($url);
		if (!(strpos($content, get_option('tr_twitch_team_id', 'Twitch Team ID')))) {
			$esStreamer = false;
		}
		
		return $esStreamer;
	}
	
	function tienewebenmuro($user_name){
		$tieneweb = true;
		
		$url = 'https://www.twitch.tv/'.$user_name.'/about';
		$content = file_get_contents($url);
		if (!(strpos($content, get_site_url()))) {
			$tieneweb = false;
		}
		
		return $tieneweb;
	}
	
	function update_status_twitch() {
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p ){
			$userid = get_post_meta($p->ID,'twitchID',true);
			$estaOnline = 0;
			$titulostreaming = "";
			$url = 'https://api.twitch.tv/kraken/streams/'.$userid;
			$content = file_get_contents_utf8($url);
			$content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$json = json_decode($content);
			if ($json->{'stream'} != null) {
				$estaOnline = 1;
				$titulostreaming = $json->{'stream'}->{'channel'}->{'status'};
				$titulostreaming = esc_sql($titulostreaming);
				
				update_post_meta($p->ID, 'game', $json->{'stream'}->{'game'});
				update_post_meta($p->ID, 'viewersCurrent', $json->{'stream'}->{'viewers'});
				update_post_meta($p->ID, 'titulostreaming', $titulostreaming);
			}
			if(isset($json->{'channel'})) {
				update_post_meta($p->ID, 'views', $json->{'channel'}->{'views'});
				update_post_meta($p->ID, 'seguidores', $json->{'channel'}->{'followers'});
			}
			update_post_meta($p->ID, 'online', $estaOnline);
		}
	}
	
	function updateTwitchData() {
		$streamersTotales = 0;
		$visitasTotales = 0;
		$seguidoresTotales = 0;
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		foreach ($postcanales as $p ){
			$userid = get_post_meta($p->ID,'twitchID',true);
			$url = 'https://api.twitch.tv/kraken/channels/'.$userid;
			$content = file_get_contents_utf8($url);
			$json = json_decode($content);
			$logo=$json->{'logo'};
			$visitas=$json->{'views'};
			$seguidores=$json->{'followers'};
			$usuario = $json->{'name'};
			
			$visitasTotales = $visitasTotales + $visitas;
			$seguidoresTotales = $seguidoresTotales + $seguidores;
			$streamersTotales++;
			
			update_post_meta($p->ID, 'views', $visitas);
			update_post_meta($p->ID, 'seguidores', $seguidores);
			update_post_meta($p->ID, 'imagen', $logo);
			update_post_meta($p->ID, 'usuario', $usuario);
			
			if (strcasecmp($p -> post_title, $usuario) != 0) {
				$updatepost = array(
					'ID' => $p->ID,
					'post_title' => $usuario,
				);
				wp_update_post($updatepost);
			}
		}
		actualizarMinimoSolicitud(intval($visitasTotales/count($postcanales)),intval($seguidoresTotales/count($postcanales)));
	}
	
	function file_get_contents_utf8($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);     
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Client-ID: '.get_option('tr_twitch_client_id', 'Twitch Client ID'),
			'Accept: application/vnd.twitchtv.v5+json'
		));

		$data = curl_exec($ch);
		curl_close($ch);
		return mb_convert_encoding($data, 'UTF-8',
			mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true));
	}
?>