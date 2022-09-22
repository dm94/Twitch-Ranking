<?php
	require plugin_dir_path( __DIR__ ).'/libraries/TwitchAPIConector.php';
	
	function tr_is_team_streamer($userid) {
		$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), '');
		
		$content = $twitchtv->get_teams($userid);
		if ($content != null) {
			$content = json_encode($content);
			if (strpos($content, get_option('tr_twitch_team_id', 'Twitch Team ID')) !== false) {
				return true;
			}
		}
		
		return false;
	}
	
	function tr_has_website_in_channel($user_name){
		$tieneweb = true;
		
		$url = 'https://www.twitch.tv/'.$user_name.'/about';
		$content = file_get_contents($url);
		if (!(strpos($content, get_site_url()))) {
			$tieneweb = false;
		}
		
		return $tieneweb;
	}
	
	function tr_update_status_twitch() {
		$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), '');
		$streamers_ids = $twitchtv->get_members_ids(get_option('tr_twitch_team_id'));

		$streamers_data = $twitchtv->get_streams(implode("&user_id=", $streamers_ids));
		$streamer_list = array();

		if (is_array($streamers_data)) {
			foreach($streamers_data as $streamer) {
				$id = $streamer->user_id;
				$title = $streamer->title;
				$title = esc_sql($title);

				$streamer_list[$id] = array(
					'title' => $title,
					'game' => $streamer->game_name,
					'viewer_count' => $streamer->viewer_count
				);
			}
			$channels = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
			foreach ($channels as $p){
				$userid = get_post_meta($p->ID,'twitchID',true);

				if (!empty($streamer_list[$userid])) {
					$streamer_data = $streamer_list[$userid];
					$title = $streamer_data['title'];
					$game = $streamer_data['game'];
					$viewer_count = $streamer_data['viewer_count'];

					update_post_meta($p->ID, 'game', $game);
					update_post_meta($p->ID, 'viewersCurrent', $viewer_count);
					update_post_meta($p->ID, 'titulostreaming', $title);
					update_post_meta($p->ID, 'online', 1);
				} else {
					update_post_meta($p->ID, 'online', 0);
				}
			}
		}
	}
	
	function tr_update_twitch_data($monthly = false) {
		$totalVisits = 0;
		$totalFollows = 0;
		$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), '');
		$streamers_ids = $twitchtv->get_members_ids(get_option('tr_twitch_team_id'));
		$streamers_data = $twitchtv->get_users(implode("&id=", $streamers_ids));
		$streamer_list = array();

		if (is_array($streamers_data)) {
			foreach($streamers_data as $streamer) {
				$id = $streamer->id;
				$logo = $streamer->profile_image_url;
				$views = $streamer->view_count;
				$userName = $streamer->display_name;
				$follows =  $twitchtv->get_followers($id);

				$streamer_list[$id] = array(
					'logo' => $logo,
					'views' => $views,
					'username' => $userName,
					'follows' => $follows
				);
			}
			global $wpdb;
			$date = date('Y-m-d', time());
			$channels = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
			foreach ($channels as $p){
				$userid = get_post_meta($p->ID,'twitchID',true);

				if (!empty($streamer_list[$userid])) {
					$streamer_data 	= $streamer_list[$userid];
					$logo 			= $streamer_data['logo'];
					$views 			= $streamer_data['views'];
					$followers 		= $streamer_data['follows'];
					$userName 		= $streamer_data['username'];

					$totalVisits 	= $totalVisits + $views;
					$totalFollows 	= $totalFollows + $followers;

					if ($monthly) {
						$followers_before 		= intval(get_post_meta($p->ID,'segudiresantes',true));
						$views_before 			= intval(get_post_meta($p->ID,'viewsantes',true));
						$difference_followers 	= $followers - $followers_before;
						$difference_views 		= $views - $views_before;

						if ($difference_followers <= 0 && $difference_views < 300) {
							$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trkarma(IDStreamer,tipo,fechaCreacion,motivo) VALUES (%d,%d,%s,%d)", 
								$userid,
								1,
								$date,
								1
							));
						}
					}
					
					update_post_meta($p->ID, ($monthly ? 'viewsantes' : 'views'), $views);
					update_post_meta($p->ID, ($monthly ? 'segudiresantes' : 'seguidores'), $followers);
					update_post_meta($p->ID, 'imagen', $logo);
					update_post_meta($p->ID, 'usuario', $userName);
					
					if (strcasecmp($p -> post_title, $userName) != 0) {
						$updatepost = array(
							'ID' => $p->ID,
							'post_title' => $userName,
						);
						wp_update_post($updatepost);
					}
				}
			}
			actualizarMinimoSolicitud(intval($totalVisits/count($channels)),intval($totalFollows/count($channels)));
		}
	}

	function tr_add_new_streamer($username) {
		$twitchtv = new TwitchAPIConector(get_option('tr_twitch_client_id', 'ClientID'), get_option('tr_twitch_client_secret', 'ClientSecret'), '');
		$streamer_data = $twitchtv->get_user_by_username($username);
		if (!empty($streamer_data)) {
			$id = $streamer_data->id;
			$display_name = $streamer_data->display_name;
			$logo = $streamer_data->profile_image_url;

			$new_post = array(
				'post_title' => $display_name,
				'post_status' => 'publish',
				'post_type' => 'canales',
				'post_category' => array(0)
			);
			$post_id = wp_insert_post($new_post);
			avisoEnDiscord(sprintf(__('Se ha añadido el streamer %s al ranking: %s', 'twitch-ranking'), $display_name, get_permalink($post_id)), 'Twitch Ranking');
			require 'utilidades.php';
			tr_generate_featured_image($logo,$post_id);
			update_post_meta($post_id, 'usuario', $display_name);
			update_post_meta($post_id, 'twitchID', $id);
			update_post_meta($post_id, 'imagen', $logo);
		}
	}
?>