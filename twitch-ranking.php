<?php
/**
 * Plugin Name: Twitch Ranking
 * Plugin URI: https://github.com/dm94/twitch-ranking
 * Description: Create an ecosystem capable of managing a group of streamers on twitch, including rankings, requests, events...
 * Version: 2.4.10
 * Author: Daniel Martin
 * Author URI: https://github.com/dm94
 * Text Domain: twitch-ranking
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
	die;
}

require_once('pagetemplater.php');
add_action('plugins_loaded', array('TRPageTemplater', 'get_instance'));

/**
 * Include CSS file
 */
function twitch_ranking_styles() {
    wp_register_style('bootstrap', plugin_dir_url( __FILE__ ).'templates/css/bootstrap.min.css', array(), '4.6.0', false);
    wp_enqueue_style('bootstrap');
	wp_enqueue_style('main-styles', plugin_dir_url( __FILE__ ).'templates/css/style.css', array(), filemtime(plugin_dir_path(__FILE__).'templates/css/style.css'), false);
}
add_action('wp_enqueue_scripts', 'twitch_ranking_styles');

function twitch_ranking_admin_styles() {
	$page = isset($_GET['page']) ? $_GET['page'] : null;

	if ('tr_options_control' == $page || 'tr_streamers_list' == $page || 'twitch_ranking' == $page) {
		wp_register_style('bootstrap', plugin_dir_url( __FILE__ ).'templates/css/bootstrap.min.css', array(), '4.6.0', false);
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('main-styles', plugin_dir_url( __FILE__ ).'templates/css/style.css', array(), filemtime(plugin_dir_path(__FILE__).'templates/css/style.css'), false);
    }
}

add_action('admin_enqueue_scripts', 'twitch_ranking_admin_styles');

/**
 * Creation of a custom post
 */

add_action('init', 'make_ctp_canales');
function make_ctp_canales() {
	$args = array(
		'labels' => array(
			'name' => __('Canales'),
			'singular_name' => __('Canal')
		),
		'rewrite' => array('slug' => 'canales'),
		'public' => true,
		'show_in_rest' => true,
		'supports' => array('thumbnail','title','custom-fields'),
	);
	register_post_type('canales', $args);
}

function canal_metabox() {
	add_meta_box('datos-streamer', __('Datos del streamer','twitch-ranking'), 'canal_meta_box_content', 'canales', 'normal', 'high');
}
add_action('add_meta_boxes', 'canal_metabox');

function canal_meta_box_content($post) {
	$values = get_post_custom($post->ID);
	$twitchID = isset($values['twitchID']) ? esc_attr($values['twitchID'][0]) : '';
	$imagen = isset($values['imagen']) ? esc_attr($values['imagen'][0]) : '';
	$usuario = isset($values['usuario']) ? esc_attr($values['usuario'][0]) : '';
	?>
	<p>
		<label for="usuario">Nick en Twitch: </label>
		<input type="text" name="usuario" id="usuario" value="<?php echo esc_html($usuario);?>" />
	</p>
	<p>
		<label for="twitchID">ID de Twitch: </label>
		<input type="text" name="twitchID" id="twitchID" value="<?php echo esc_html($twitchID);?>" />
	</p>
	<p>Imagen de Twitch:</p>
	<p>
		<img src="<?php echo esc_html($imagen);?>"/>
	</p>
	<?php
}

function canales_metabox_save($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if (isset($_POST['usuario'])) {
		update_post_meta($post_id, 'usuario', esc_attr($_POST['usuario']));
	}

	if (isset($_POST['twitchID'])) {
		update_post_meta($post_id, 'twitchID', esc_attr($_POST['twitchID']));
	}
}
add_action('save_post', 'canales_metabox_save');

/**
 * Custom Post Type: Eventos
 */

add_action('init', 'make_ctp_eventos');
function make_ctp_eventos() {
	$args = array(
		'labels' => array(
			'name' => __('Eventos'),
			'singular_name' => __('Evento')
		),
		'rewrite' => array('slug' => 'eventos'),
		'public' => true,
		'show_in_rest' => true,
		'supports' => array('editor','thumbnail','title','custom-fields', 'author'),
	);
	register_post_type('eventos', $args);
}

add_filter('use_block_editor_for_post_type', 'disable_gutenberg_eventos', 10, 2);
function disable_gutenberg_eventos($current_status, $post_type) {
    if ($post_type === 'eventos') {
		return false;
	}
    return $current_status;
}

function eventos_metabox() {
	add_meta_box('datos-eventos', __('Datos del evento','twitch-ranking'), 'evento_meta_box_content', 'eventos', 'normal', 'high');
}
add_action('add_meta_boxes', 'eventos_metabox');

function evento_meta_box_content($post) {
	$values = get_post_custom($post->ID);
	$tr_date_end = isset($values['tr_date_end']) ? esc_attr($values['tr_date_end'][0]) : '';
	$tr_date_start = isset($values['tr_date_start']) ? esc_attr($values['tr_date_start'][0]) : '';
	$tr_inscribed_list = get_post_meta($post->ID,'tr_inscribed_list',true);
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	?>
	<p>
		<label for="tr_date_start"><?php _e('Fecha Inicio: ','twitch-ranking'); ?></label>
		<input type="datetime-local" name="tr_date_start" value="<?php echo $tr_date_start;?>"/>
	</p>
	<p>
		<label for="tr_date_end"><?php _e('Fecha Fin: ','twitch-ranking'); ?></label>
		<input type="datetime-local" name="tr_date_end" value="<?php echo $tr_date_end;?>"/>
	</p>
	<p>
		Listado de apuntados: 
		<?php
			if(!empty($tr_inscribed_list)) {
				foreach ($postcanales as $p ){
					if (in_array(get_post_meta($p->ID,'twitchID',true), $tr_inscribed_list)) {
						echo '<a target="_blank" href="https://www.twitch.tv/'.get_post_meta($p->ID,'usuario',true).'">'.get_post_meta($p->ID,'usuario',true).'</a> - ';
					}
				}
			}
		?>
	</p>
	<?php
}

function eventos_metabox_save($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post')) {
		return;
	}

	if (isset($_POST['tr_date_start'])) {
		update_post_meta($post_id, 'tr_date_start', $_POST['tr_date_start']);
	}

	if (isset($_POST['tr_date_end'])) {
		update_post_meta($post_id, 'tr_date_end', $_POST['tr_date_end']);
	}
}
add_action('save_post', 'eventos_metabox_save');

function load_customs_template($template) {
	global $post;
	if ('canales' == $post->post_type && locate_template( array('single-canales.php')) != $template) {
		return plugin_dir_path( __FILE__ ) . '/templates/single-canales.php';
	} else if ('eventos' == $post->post_type && locate_template( array('single-eventos.php')) != $template) {
		return plugin_dir_path( __FILE__ ) . '/templates/single-eventos.php';
	}
	return $template;
}

add_filter('single_template', 'load_customs_template');

/*
* Add the plugin to the menu so you can click it
*/

add_action('admin_menu', 'tr_add_admin_menu');

function tr_add_admin_menu(){
	add_menu_page('Twitch Ranking', 'Control de streamers', 'view_twitch_system', 'tr_options_control','tr_managerstreamers','dashicons-admin-generic');
	add_submenu_page ('tr_options_control', 'TR Listado', 'Listado Solicitudes', 'view_twitch_system', 'tr_streamers_list', 'tr_listadosolicitudes');
	add_submenu_page ('tr_options_control', 'TR Ranking', 'Configuración', 'manage_options', 'twitch_ranking', 'tr_seccionranking');
}

function actualizarDatos() {
	require_once('admin/twitch_utils.php');
	tr_update_twitch_data();
}
add_action('actualizarstreamers', 'actualizarDatos');

function search_rts(){
	require_once('admin/controlTwitter.php');
	require_once('admin/twitch_utils.php');
	tr_update_status_twitch();
	tr_search_on_twitter();
	tr_update_requests();
}
add_action('trbuscarrts', 'search_rts');

function update_new_season() {
	$datetime1 = new DateTime('now');

	if ($datetime1->format('d') == '01') {
		$hasNewSesasson = get_transient('tr_new_seasson_day');

		if (empty($hasNewSesasson)) {
			global $wpdb;
			$voting = $wpdb->get_results('select usuario, count(IdVotacion) vota from '.$wpdb->prefix.'trvotaciones group by usuario order by vota DESC');

			$first = true;
			$winner = '';
			if ($voting && is_array($voting)) {
				foreach ($voting as $row) {
					if ($first) {
						$winner = $row->usuario;
						$first = false;
					}
				}
			}

			if (!empty($winner)) {
				update_option('tr_ganador_ranking', $winner, 'no');
				avisoEnDiscord(sprintf(__('El nuevo ganador del ranking es %s', 'twitch-ranking'), $winner), 'Twitch Ranking');

				$postcanales = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
				foreach ($postcanales as $p){
					$wpdb->query($wpdb->prepare("delete from ".$wpdb->prefix."trvotaciones where Usuario=%s", get_post_meta($p->ID,'usuario',true)));
				}

				avisoEnDiscord(__('Se han borrado los votos del ranking. Nueva temporada!!', 'twitch-ranking'), 'Twitch Ranking');
				wp_schedule_single_event(time()+600,'trupdatestreamersstats');
			}
			set_transient('tr_new_seasson_day', 'newseasson', 172800);
		}
	} else {
		delete_transient('tr_new_seasson_day');
	}
}
add_action('trupdatenewseason', 'update_new_season');

function update_streamers_stats() {
	require_once('admin/twitch_utils.php');

	tr_update_twitch_data(true);

	avisoEnDiscord(__('Se han actulizado los datos de visitas y seguidores a este mes'), 'Twitch Ranking');
}
add_action('trupdatestreamersstats', 'update_streamers_stats');


register_activation_hook( __FILE__, 'tr_activate_plugin');

function tr_activate_plugin() {
	require_once('crearBasesDeDatos.php');
	createDatabases();
	tr_activate_schedule();
}

function tr_activate_schedule() {
	if (!wp_next_scheduled('actualizarstreamers')) {
		wp_schedule_event(time(), 'twicedaily', 'actualizarstreamers');
	}
	if (!wp_next_scheduled('trbuscarrts')) {
		wp_schedule_event(time(), 'hourly', 'trbuscarrts');
	}
	if (!wp_next_scheduled('trupdatenewseason')) {
		wp_schedule_event(time(), 'hourly', 'trupdatenewseason');
	}
	if (!wp_next_scheduled('tr_tweet_requirement')) {
		wp_schedule_event(time(), 'weekly', 'tr_tweet_requirement');
	}
}

register_deactivation_hook( __FILE__, 'tr_deactivate_schedule');

function tr_deactivate_schedule() {
	wp_clear_scheduled_hook('actualizarstreamers');
	wp_clear_scheduled_hook('trbuscarrts');
	wp_clear_scheduled_hook('trupdatenewseason');
	wp_clear_scheduled_hook('tr_tweet_requirement');
}

/*
* Functions for the plugin administration pages
*/

function tr_seccionranking(){
	require_once('admin/seccionConfiguracion.php');
	mostrarTodoConfiguracion();
}

function tr_listadosolicitudes(){
	require_once('admin/seccionSolicitudes.php');
	tr_show_aplications_admin_section();
}

function tr_managerstreamers(){
	require_once('admin/seccionControlStreamers.php');
	mostrarTodoControlStreamers();
}


function tr_tweet_minimun_requirement() {
	require_once('admin/controlTwitter.php');
	$message = 'Actualmente para poder mandar solicitud se necesitan '.get_option('tr_min_seguidores', '500').' seguidores. https://www.comunidadgzone.es/solicitudes/solicitud-twitch';
	tr_send_tweet($message);
}
add_action('tr_tweet_requirement', 'tr_tweet_minimun_requirement');

/*
* Discord alert function
*/

function avisoEnDiscord($mensaje,$username){
	$url = get_option('tr_discord_webhook','');
	
	if (empty($url)) {
		return;
	}
	
	$data = array("content" => $mensaje, "username" => $username);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($curl);

	require_once('admin/controlTwitter.php');
	tr_send_tweet($mensaje);
}

function actualizarMinimoSolicitud($visitas,$seguidores) {
	if (empty(get_option('tr_conf_min_views', '')) || strcasecmp(get_option('tr_conf_min_views', ''),'0') == 0) {
		update_option('tr_min_views', $visitas, 'no');
	} else {
		update_option('tr_min_views', get_option('tr_conf_min_views', ''), 'no');
	}
	if (empty(get_option('tr_conf_min_seguidores', '')) || strcasecmp(get_option('tr_conf_min_seguidores', ''),'0') == 0) {
		update_option('tr_min_seguidores', $seguidores, 'no');
	} else {
		update_option('tr_min_seguidores', get_option('tr_conf_min_seguidores', ''), 'no');
	}
}

function getTwitchId($wpuserid) {
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	foreach ($postcanales as $p ){
		if (strcasecmp(get_post_meta($p->ID,'tr_wp_user_id',true),$wpuserid) == 0) {
			return get_post_meta($p->ID,'twitchID',true);
		}
	}
	return '';
}

function getTwitchUser($twitchid) {
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	foreach ($postcanales as $p ){
		if (strcasecmp(get_post_meta($p->ID,'twitchID',true),$twitchid) == 0) {
			return get_post_meta($p->ID,'usuario',true);
		}
	}
	return $twitchid;
}

function hasVotes($streamer) {
	global $wpdb;
	$streamers = $wpdb->get_results('select usuario, count(IdVotacion) vota from '.$wpdb->prefix.'trvotaciones group by usuario order by vota DESC');
	
	foreach ($streamers as $row){
		if (strcasecmp($streamer,$row->usuario) == 0) {
			return true;
		}
	}
	return false;
}

function isStreamer($nickstreamer) {
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	foreach ($postcanales as $p){
		if (strcasecmp(get_post_meta($p->ID,'usuario',true),$nickstreamer) == 0){
			return true;
		}
	}
	return false;
}

function tr_update_requests() {
	require_once('admin/twitch_utils.php');
	global $wpdb;

	$requests = $wpdb->get_results('select nickstreamer, email, userid, imagen, idsolicitud, wp_user_id from '.$wpdb->prefix.'trsolicitutwitch where estado = 10');
	if ($requests != null && is_array($requests)) {
		foreach ($requests as $col) {
			$nickstreamer 	= $col->nickstreamer;
			$email 			= $col->email;
			$userid 		= $col->userid;
			$image			= $col->imagen;
			$idrequest 		= $col->idsolicitud;
			$wp_user_id 	= $col->wp_user_id;
	
			if (tr_is_team_streamer($userid)) {
				require_once('admin/sistemaCorreo.php');
				require_once('admin/utilidades.php');
	
				$wpdb->query($wpdb->prepare("update ".$wpdb->prefix."trsolicitutwitch set estado=4 where idsolicitud=%s",
				$idrequest
				));
	
				$new_post = array(
					'post_title' => $nickstreamer,
					'post_status' => 'publish',
					'post_type' => 'canales',
					'post_category' => array(0)
				);
	
				$post_id = wp_insert_post($new_post);
				avisoEnDiscord(sprintf(__('Se ha añadido el streamer %s al ranking: %s', 'twitch-ranking'), $nickstreamer, get_permalink($post_id)), 'Twitch Ranking');
				tr_generate_featured_image($image,$post_id);
				update_post_meta($post_id, 'twitchID', $userid);
				update_post_meta($post_id, 'imagen', $image);
				if ($wp_user_id == null || $wp_user_id == 0) {
					$user = get_user_by('email', $email );
					$wp_user_id = $user->ID;
				}
				update_post_meta($post_id, 'tr_wp_user_id', $wp_user_id);
				update_post_meta($post_id, 'usuario', $nickstreamer);
			} 
		}
	}
}

require 'shortcodes/MyShortcodes.php';
$my_shorcodes = new MyShortcodes();
$my_shorcodes->init();
?>