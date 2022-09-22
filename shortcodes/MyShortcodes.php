<?php

class MyShortcodes {
    public function __construct() {}

    public function init() {
        $this->init_shortcodes();
    }

    public function init_shortcodes() {
        add_shortcode('streamermes', array($this, 'mostrarStreamerMes'));
        add_shortcode('streamerganador', array($this, 'show_ranking_winner'));
        add_shortcode('minseguidores', array($this, 'show_min_follows'));
        add_shortcode('minviews', array($this, 'show_min_views'));
        add_shortcode('tr_eventos', array($this, 'show_events'));
        add_shortcode('tr_solicitudes', array($this, 'show_requests'));
        add_shortcode('tr_mostrar_karma', array($this, 'show_karma'));
        add_shortcode('seccionAsignarRangoDiscord',  array($this, 'assign_discord_rank'));
        add_shortcode('seccionStreamerPorJuego', array($this, 'show_streamers_per_game'));
        add_shortcode('streamer', array($this, 'show_streamer'));
        add_shortcode('streamersonline', array($this, 'show_streamers_online'));
        add_shortcode('streamerfavoritos', array($this, 'show_favourite_streamers'));
    }

    public function mostrarStreamerMes($atts, $content = null) {
		return do_shortcode('[streamer nombre="'.get_option('tr_streamer_mes', 'gzonecomunidad').'"]');
	}

    public function show_ranking_winner($atts, $content = null) {
		return do_shortcode('[streamer nombre="'.get_option('tr_ganador_ranking', 'gzonecomunidad').'"]');
	}

    public function show_min_follows($atts, $content = null) {
		$follows = get_option('tr_min_seguidores', '500');
        ob_start();
		echo "<p>".$follows.'</p>';
        return ob_get_clean();
	}

    public function show_min_views($atts, $content = null) {
		$views = get_option('tr_min_views', '1000');
        ob_start();
		echo "<p>".$views.'</p>';
        return ob_get_clean();
	}

    public function show_events($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_events.php';
        return ob_get_clean();
    }

    public function show_requests($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_requests.php';
        return ob_get_clean();
    }

    public function show_karma($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_karma.php';
        return ob_get_clean();
    }

    public function assign_discord_rank($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/assign_discord_rank.php';
        return ob_get_clean();
    }

    public function show_streamers_per_game($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_streamers_per_game.php';
        return ob_get_clean();
    }

    public function show_streamer($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_streamer.php';
        return ob_get_clean();
    }

    public function show_streamers_online($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_streamers_online.php';
        return ob_get_clean();
    }

    public function show_favourite_streamers($atts, $content = null) {
        $dir = plugin_dir_path( __FILE__ );
        ob_start();
        include $dir.'views/show_favourite_streamers.php';
        return ob_get_clean();
    }
}

?>