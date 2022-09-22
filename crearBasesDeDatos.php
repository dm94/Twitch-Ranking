<?php
	function createDatabases() {
		makeTableJuegos();
		makeTableKarma();
		makeTableVotosTwitchSoli();
		makeTableVotaciones();
		makeTableSolicituTwitch();
	}

	function makeTableJuegos() {
		global $wpdb;
		$table_name = $wpdb->prefix."trjuegos"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			idjuego int(11) NOT NULL AUTO_INCREMENT,
			nombre varchar(100) DEFAULT '' NOT NULL,
			url varchar(255) DEFAULT '' NOT NULL,
			urlimagen varchar(255) DEFAULT '' NOT NULL,
			PRIMARY KEY  (idjuego)
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function makeTableKarma() {
		global $wpdb;
		$table_name = $wpdb->prefix."trkarma"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			idkarma int(11) NOT NULL,
			IDStreamer int(11) NOT NULL,
			tipo int(10) NOT NULL,
			fechaCreacion date NOT NULL,
			motivo int(10) DEFAULT NULL
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function makeTableVotosTwitchSoli() {
		global $wpdb;
		$table_name = $wpdb->prefix."trvotosstwitchsoli"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			idVoto int(11) NOT NULL,
			idsolicitud int(11) NOT NULL,
			streamervotante varchar(100) NOT NULL,
			tipovoto int(11) NOT NULL
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function makeTableVotaciones() {
		global $wpdb;
		$table_name = $wpdb->prefix."trvotaciones"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			IDVotacion int(11) NOT NULL,
			Usuario varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
			Votante varchar(250) CHARACTER SET latin1 COLLATE latin1_german1_ci DEFAULT NULL,
			Fecha date DEFAULT NULL,
			ip varchar(30) DEFAULT NULL
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function makeTableSolicituTwitch() {
		global $wpdb;
		$table_name = $wpdb->prefix."trsolicitutwitch"; 
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			idsolicitud int(11) NOT NULL,
			nickstreamer varchar(100) NOT NULL,
			email varchar(500) DEFAULT NULL,
			fecha date NOT NULL,
			mensaje varchar(500) DEFAULT NULL,
			imagen varchar(250) DEFAULT NULL,
			estado int(11) DEFAULT NULL,
			userid varchar(32) DEFAULT NULL,
			ip varchar(100) DEFAULT NULL,
			wp_user_id bigint(20) DEFAULT NULL
			) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
?>